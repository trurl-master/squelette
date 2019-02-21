<?php

use Sirius\Validation\Validator;
use \Squelette\UserAuth;
use \Squelette\Request;
use \Squelette\Respond;

if (!UserAuth::isSignedin() || !UserAuth::getUser()->isAdmin()) {
	App::to404();
}


function getResource()
{
    $classname = '\\Yournamespace\\' . str_replace('_', '', ucwords($_POST['table'], '_')) . 'Query';

    $resource = $classname::create()->findPK($_POST['id']);

    if (!$resource) {
        Respond::fail(['message' => 'Resource not found']);
    }

    return $resource;
}


//
$validator = new Validator();
$validator->add('table', 'inlist', ['list' => ['news']]);
$validator->add('id', 'required | integer');


$task = Request::path(2, null);

if ($task === null) {
	$task = $_POST['task'];
}

//
switch ($task) {

	case 'upload-file':

		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			Respond::fail();
		}

        $resource = getResource();

        $options = [];

        if (isset($_POST['filename'])) {
            $options['filename'] = $_POST['filename'];
        }

		try {
			$filename = $resource->uploadFile($options);
		} catch (Exception $e) {
			Respond::fail(['message' => $e->getMessage()]);
		}

        $resource->updateRes();
        
        Respond::success([
			'resid' => $resource->getResid(),
			'filename' => $filename
		]);
        
		break;

	case 'upload-image':

		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			Respond::fail();
		}

        $resource = getResource();

        $options = [];

        if (isset($_POST['filename'])) {
            $options['filename'] = $_POST['filename'];
        }

        if (isset($_POST['preset'])) {
            $options['preset'] = $_POST['preset'];
		} else {
			$options['preset'] = 'main';
		}

		try {
			$filename = $resource->uploadImage($options);
		} catch (Exception $e) {
			Respond::fail(['message' => $e->getMessage()]);
		}

        $resource->updateRes();
        
        Respond::success([
			'resid' => $resource->getResid(),
			'filename' => $filename
		]);
        
		break;

	case 'remove':

		$validator->add('filenames[*]', 'regex(/[a-z0-9\.\-\_]+/)');

		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			Respond::fail();
        }

		$resource = getResource();
		        
        $resource->removeFiles($_POST['filenames']);

        Respond::success();

		break;

	case 'update':
    
		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			Respond::fail();
		}

		$resource = getResource();

		$resource->updateRes();
        
        Respond::success(['resid' => $resource->getResid()]);
    
		break;

	default: App::to404();
}
