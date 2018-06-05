<?php

use \Squelette\UserAuth;
use \Squelette\Request;

$section = Request::path(1);

switch ($section) {
	// case '':
	// 	App::controller('api/' . $section);
	// 	break;

	default:

		if (!UserAuth::isSignedin() || !UserAuth::getUser()->isAdmin()) {
			App::to404();
		}

		echo \Squelette\API::handle();
		break;
}
