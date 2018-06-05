<?php

namespace Squelette;

use \App;
use Sirius\Upload\Handler as UploadHandler;


trait Resource
{

    public function getTableName()
    {
        return ($this::TABLE_MAP)::getTableMap()->getName();
    }

    public function uploadFile($o = [])
    {

		if ($this->resPath() === false) {
            $this->updateRes();
		}

		$path = $this->resRootPath();

		$uploadHandler = new UploadHandler($path);
		$uploadHandler->setSanitizerCallback(function($name) use ($o){

            $ext = pathinfo($name, PATHINFO_EXTENSION);

			if (isset($o['filename']) && is_string($o['filename'])) {
				return $o['filename'] . '.' . $ext;
			} else {
				return uniqid() . '_' . preg_replace('/[^a-z0-9\.\-\_]+/', '-', strtolower($name));
			}
        });

		$result = $uploadHandler->process($_FILES);

		if (!$result->isValid()) {
            $messages = $result->getMessages();
            var_dump($result);
			$messages_list = [];
			foreach ($messages as $msg) {
				$messages_list[] = $msg->getTemplate();
			}
			throw new \Exception(implode("\n", $messages_list));
		}

        try {
			$result->confirm(); // this will remove the .lock file
		} catch (\Exception $e) {
			// something wrong happened, we don't need the uploaded files anymore
			$result->clear();
			throw $e;
		}

        rename($path . $result->name, $this->resPath() . $result->name);

        return $result->name;
    }

    public function removeFiles()
    {

    }

}
