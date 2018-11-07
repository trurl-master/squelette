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
			// throw new \Exception('Upload error: resource doesn\'t exist');
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

		//
		$file = reset($_FILES);

		switch($file['error']) {
			case UPLOAD_ERR_INI_SIZE:
				throw new \Exception('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
				break;

			case UPLOAD_ERR_FORM_SIZE:
				throw new \Exception('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
				break;

			case UPLOAD_ERR_PARTIAL:
				throw new \Exception('The uploaded file was only partially uploaded.');
				break;

			case UPLOAD_ERR_NO_FILE:
				throw new \Exception('No file was uploaded.');
				break;

			case UPLOAD_ERR_NO_TMP_DIR:
				throw new \Exception('Missing a temporary folder.');
				break;

			case UPLOAD_ERR_CANT_WRITE:
				throw new \Exception('Failed to write file to disk.');
				break;

			case UPLOAD_ERR_EXTENSION:
				throw new \Exception('A PHP extension stopped the file upload.');
				break;
		}
		
        //
		$result = $uploadHandler->process($_FILES);

		if (!$result->isValid()) {
            $messages = $result->getMessages();
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
	
	public function uploadImage($o = [])
	{

		if ($this->resPath() === false) {
			$this->updateRes();
			// throw new \Exception('File upload error: resource not found');
		}

		$path = $this->resRootPath();

		$uploadHandler = new UploadHandler($path);

		$uploadHandler->addRule('extension', ['allowed' => ['jpg', 'jpeg', 'png']], 'Изображение должно иметь формат jpg, jpeg или png');
		$uploadHandler->addRule('size', ['size' => '20M'], 'Изображение должно быть меньше {size}');

		$uploadHandler->setSanitizerCallback(function($name) use ($o) {

			$name = str_replace('.jpeg', '.jpg', mb_strtolower($name));

			$ext = pathinfo($name, PATHINFO_EXTENSION);

			if (isset($o['filename']) && is_string($o['filename'])) {
				return $o['filename'] . '.' . $ext;
			} else {
				return uniqid() . '_' . preg_replace('/[^a-z0-9\.\-\_]+/', '-', $name);
			}
		});

		// process files
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

		$preset = $this->presets[$o['preset']];
		$format = false;
		$from = $path . $result->name;

		list($image, $ext) = Image::open($from);

		foreach ($preset as $command => $config) {

			switch ($command) {
				case 'resize':
					$image = Image::resize($image, $config);
					break;

				case 'crop':
					$image = Image::crop($image, $config);
					break;

				case 'save':
					$format = isset($config['format']) ? $config['format'] : false;
					break;
			}

		}

		$to = $this->resPath() . $result->name;

		if ($format === false) {
			$format = $ext;	
        } else {
            $info = pathinfo($to);
            $to = $info['dirname'] . '/' . $info['filename'] . '.' . $format;
        }

		Image::save($image, $format, $to);

		unlink($from);

		// if (isset($preset['resize']) && $preset['resize']) {
		// 	Image::resize(
		// 		$path . $result->name,
		// 		$this->resPath() . $result->name,
		// 		$preset['resize'],
		// 		$format
		// 	);

		// 	unlink($path . $result->name);
		// } else {
		// 	// rename($path . $result->name, $this->resPath() . $result->name);
		// 	Image::rename(
		// 		$path . $result->name,
		// 		$this->resPath() . $result->name,
		// 		$format
		// 	);
		// }

		return $result->name;
	}

    public function removeFiles(array $filenames)
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
