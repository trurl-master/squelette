<?php

namespace Squelette;

use Sirius\Upload\Handler as UploadHandler;

trait ArticleContent
{

	public function uploadPic($o)
	{

		if ($this->resPath() === false) {
			throw new \Exception('File upload error: resource not found');
		}

		$path = $this->resRootPath();
		$copies_filenames = [];

		$uploadHandler = new UploadHandler($path);

		$uploadHandler->addRule('extension', ['allowed' => ['jpg', 'jpeg', 'png']], 'Allowed image formats are jpg, jpeg and png');
		$uploadHandler->addRule('size', ['size' => '20M'], 'Maximum size is {size}');

		$uploadHandler->setSanitizerCallback(function($name) use ($o, &$copies_filenames){

			$ext = pathinfo($name, PATHINFO_EXTENSION);

			if (isset($o['filename']) && is_string($o['filename'])) {

				if (isset($o['copy'])) {
					foreach ($o['copy'] as $copy) {
						$copies_filenames[] = $copy['as'] . '.' . $ext;
					}
				}

				return $o['filename'] . '.' . $ext;
			} else {

				$uniqid = uniqid();
				$filename = preg_replace('/[^a-z0-9\.\-\_]+/', '-', strtolower($name));

				if (isset($o['copy'])) {
					foreach ($o['copy'] as $copy) {
						$copies_filenames[] = $uniqid . '_' . $copy['as'] . '_' . $filename;
					}
				}

				return $uniqid . '_' . $filename;
			}

		});


		//
		$result = $uploadHandler->process($_FILES);

		if (!$result->isValid()) {
			$messages = $result->getMessages();
			$messages_list = [];
			foreach ($messages as $msg) {
				$messages_list[] = $msg->getTemplate();
			}
			throw new \Exception('Изображение некорректно: ' . implode("\n", $messages_list));
		}

		//
		try {

			$result->confirm(); // this will remove the .lock file

		} catch (\Exception $e) {

			// something wrong happened, we don't need the uploaded files anymore
			$result->clear();
			throw $e;

		}

		if (isset($o['copy'])) {

			$i = 0;
			foreach ($o['copy'] as $copy) {
				Image::resize(
					$path . $result->name,
					$this->resPath() . $copies_filenames[$i],
					$copy['width'],
					$copy['height'],
					$copy['by']
				);

				$i++;
			}
		}

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
