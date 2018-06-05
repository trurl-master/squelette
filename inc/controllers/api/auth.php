<?php

use \Squelette\UserAuth;
use \Squelette\Request;


switch (Request::path(2, null)) {

	case 'create-admin':

		if (App::cfg('is_production')) {
			App::to404();
		}

		echo UserAuth::createAdmin();

		break;

	case 'signin':

		if (UserAuth::isSignedin()) {
			App::to('');
		}

		if (UserAuth::signin()) {

			$signin_redirect = UserAuth::getState('signin_redirect');
			UserAuth::clearState('signin_redirect');

			if (!$signin_redirect) {
				App::to('');
			}

			switch ($signin_redirect['redirect_to']) {

				case 'admin':
					App::to('admin');
					break;
			}

		} else {
			App::to('auth/signin');
		}

		break;

	case 'signout':

		if (UserAuth::isSignedin()) {
			if (!UserAuth::signout()) {
				die('Internal error');
			}
		}

		App::to('');

		break;

	case 'is-signedin':
		if (!UserAuth::isSignedin()) {
			App::to404();
		}
		break;

	default: App::to404();
}
