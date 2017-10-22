<?php

namespace Squelette;

use Sirius\Upload\Handler as UploadHandler;

trait ArticleContent
{

	public function uploadPic($o)
	{

		if ($this->resPath() === false) {
			throw new \Exception('Ошибка загрузки файла: ресурса не существует');
		}

		$path = $this->resRootPath();

		$uploadHandler = new UploadHandler($path);

		$uploadHandler->addRule('extension', ['allowed' => ['jpg', 'jpeg', 'png']], 'Изображение должно иметь формат jpg, jpeg или png');
		$uploadHandler->addRule('size', ['size' => '20M'], 'Изображение должно быть меньше {size}');

		$uploadHandler->setSanitizerCallback(function($name) use ($o){

			$ext = pathinfo($name, PATHINFO_EXTENSION);

			if (isset($o['filename']) && is_string($o['filename'])) {
				return $o['filename'] . '.' . $ext;
			} else {
				return uniqid() . '_' . preg_replace('/[^a-z0-9\.\-\_]+/', '-', strtolower($name));
			}
		});


		//
		$result = $uploadHandler->process($_FILES); // ex: subdirectory/my_headshot.png

		if (!$result->isValid()) {
			$messages = $result->getMessages();
			$messages_list = [];
			foreach ($messages as $msg) {
				$messages_list[] = $msg->getTemplate();
			}
			throw new \Exception('Изображение некорректно: ' . implode("\n", $messages_list));
			// return false;
			// die('not valid');
		}

		//
		try {

			$result->confirm(); // this will remove the .lock file

		} catch (\Exception $e) {

			// something wrong happened, we don't need the uploaded files anymore
			$result->clear();
			throw $e;

		}

		// $new_w = $width;
		// $new_h = 0;

		if (isset($o['resize']) && $o['resize']) {
			Image::resize(
				$path . $result->name,
				$this->resPath() . $result->name,
				$o['resize']['width'],
				$o['resize']['height'],
				$o['resize']['by']
			);

			unlink($path . $result->name);
		} else {
			rename($path . $result->name, $this->resPath() . $result->name);
		}

		return $result->name;
	}

	public function removePics(array $filenames)
	{
		// $gallery_dir = $cfg_docs_path . $path;
		$path = $this->resPath();

		foreach ($filenames as $filename) {

			$full_path = $path . $filename;

			if (file_exists($full_path)) {
				unlink($full_path);
			}

		}

		return true;
	}

}
