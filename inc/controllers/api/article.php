<?php

use Sirius\Validation\Validator;
use \Squelette\UserAuth;
use \Squelette\Respond;

if (!UserAuth::isSignedin() || !UserAuth::getUser()->isAdmin()) {
	App::to404();
}

$validator = new Validator();
$validator->add('table', 'inlist', ['list' => ['news']]);
$validator->add('id', 'required | integer');

/**

Sample logic

*/

switch ($_POST['task']) {

	case 'upload-title-pic':

		//
		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			Respond::fail();
		}

		switch ($_POST['table']) {
			case 'news':

				$article = \Yournamespace\NewsQuery::create()->findPK($_POST['id']);

				if (!$article) {
					Respond::fail(['message' => 'News not found']);
				}

				$resize = [
					'width' => 400,
					'height' => 400,
					'by' => \Squelette\Image::RESIZE_COVER
				];

				break;
			default: Respond::fail();
		}

		//
		try {
			$article->uploadPic([
				'filename' => 'title',
				'resize' => $resize
			]);
		} catch (Exception $e) {
			Respond::fail(['message' => $e->getMessage()]);
		}

		$article->updateRes();

		//
		Respond::success(['resid' => $article->getResid()]);

		break;


	case 'upload-pic':

		//
		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			Respond::fail();
		}

		switch ($_POST['table']) {
			case 'news':

				if (!$news) {
					Respond::fail(['message' => 'News record not found']);
				}

				$fn = $news->uploadPic([
					'resize' => [
						'width' => 800,
						'height' => 0,
						'by' => \Squelette\Image::RESIZE_BY_WIDTH
					]
				]);

				echo json_encode([
					'success' => true,
					'filename' => $fn,
					'resid' => $news->getResid()
				]);

				break;
		}

		break;

	case 'remove-pics':

		$validator->add('filenames[*]', 'regex(/[a-z0-9\.\-\_]+/)');

		//
		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			Respond::fail();
		}

		switch ($_POST['table']) {
			case 'news':

				$news = \Yournamespace\NewsQuery::create()->findPK($_POST['id']);

				if (!$news) {
					Respond::fail(['message' => 'News record not found']);
				}

				$news->removePics($_POST['filenames']);

				Respond::success();

				break;
		}

		break;

	default: App::to404();
}
