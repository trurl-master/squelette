<?php

use Sirius\Validation\Validator;
use \Squelette\UserAuth;
use \Squelette\Respond;

if (!UserAuth::isSignedin() || !UserAuth::getUser()->isAdmin()) {
	App::to404();
}

//
$validator = new Validator();
$validator->add('table', 'inlist', ['list' => ['news', 'partners']]);
$validator->add('id', 'required | integer');


//
switch ($_POST['task']) {

	case 'upload-title-pic':

		//
		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			die('{"success":false}');
		}

		switch ($_POST['table']) {
			case 'news':

				// $article = \Squelette\NewsQuery::create()->findPK($_POST['id']);

				if (!$article) {
					die('{"success":false,"message":"Новость не найдена"}');
				}

				break;
			default: die('{"success":false}');
		}

		//
		try {
			$article->uploadPic([
				'filename' => 'title',
				'resize' => [
					'width' => 300,
					'height' => 300,
					'by' => \Squelette\Image::RESIZE_COVER
				]
			]);
		} catch (Exception $e) {
			die(json_encode([
				'success' => false,
				'message' => $e->getMessage()
			]));
		}

		$article->updateRes();

		//
		Respond::success(['resid' => $article->getResid()]);
		// echo json_encode([
		// 	'success' => true,
		// 	'resid' => $news->getResid()
		// ]);

		break;


	case 'upload-pic':

		//
		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			die('{"success":false}');
		}

		switch ($_POST['table']) {
			case 'news':

				// $news = \Squelette\NewsQuery::create()->findPK($_POST['id']);

				if (!$news) {
					die('{"success":false,"message":"Новость не найдена"}');
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

	case 'update-resource':

		//
		if (!$validator->validate($_POST)) {
			print_r($validator->getMessages());
			die('{"success":false}');
		}

		switch ($_POST['table']) {
			case 'news':

				// $news = \Squelette\NewsQuery::create()->findPK($_POST['id']);

				if (!$news) {
					die('{"success":false,"message":"Новость не найдена"}');
				}

				$news->updateRes();

				echo json_encode([
					'success' => true,
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
			die('{"success":false}');
		}

		switch ($_POST['table']) {
			case 'news':

				// $news = \Squelette\NewsQuery::create()->findPK($_POST['id']);

				if (!$news) {
					die('{"success":false,"message":"Новость не найдена"}');
				}

				$news->removePics($_POST['filenames']);

				break;
		}

		break;

	default: App::to404();
}
