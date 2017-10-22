<?php

use Sirius\Validation\Validator;
use \Squelette\UserAuth;
use \Squelette\Respond;

if (!UserAuth::isSignedin() || !UserAuth::getUser()->isAdmin()) {
	App::to404();
}


$validator = new Validator();
$validator->add('table', 'inlist', ['list' => ['news', 'partners']]);
$validator->add('ord[*]', 'integer');

if (!$validator->validate($_POST)) {
	Respond::fail(['message' => 'error validating data']);
}

$ord = $_POST['ord'];
$table = $_POST['table'];
$class = '\\Squelette\\' . ucwords($table, '_') . 'Query';

//
switch ($_POST['task']) {

	case 'set-order':

		if ($class::create()->setOrder($ord)) {
			Respond::success();
		} else {
			Respond::fail();
		}

		break;
	
	default: App::to404();
}