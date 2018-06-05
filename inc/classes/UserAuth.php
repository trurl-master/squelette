<?php

namespace Squelette;

use Sirius\Validation\Validator;
use Bogushevich\UserQuery;
use Bogushevich\User;

class UserAuth
{

	static protected $user = false;

	public static function init()
	{

		if (isset($_SESSION['user_id'])) {

			$user = UserQuery::create()->findPK($_SESSION['user_id']);

			if ($user) {
				self::setUser($user);
			} else {
				session_unset();
				session_destroy();
				header("Refresh:0");
			}
		}

	}

	public static function generatePassword(
	    $length = 8,
	    $subsets = [
	        'chars' => true,
	        'capitals' => true,
	        'numbers' => true,
	        'special' => true,
	        'custom' => false
	    ]
	) {
	    $chars = "abcdefghijklmnopqrstuvwxyz";
	    $capitals = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $numbers = '0123456789';
	    $special = '!@#$%^&*()_-=+;:,.?';

	    $from = '';

	    if (isset($subsets['chars']) && $subsets['chars']) {
	        $from .= $chars;
	    }

	    if (isset($subsets['capitals']) && $subsets['capitals']) {
	        $from .= $capitals;
	    }

	    if (isset($subsets['numbers']) && $subsets['numbers']) {
	        $from .= $numbers;
	    }

	    if (isset($subsets['special']) && $subsets['special']) {
	        $from .= $special;
	    }

	    if (isset($subsets['custom'])) {
	        $from .= $subsets['custom'];
	    }

	    return substr(str_shuffle($from), 0, $length);
	}

	public static function setErrorMessages($errors_by_key)
	{
		$errors = [];

		foreach ($errors_by_key as $key => $errors_for_key) {
			$errors[$key] = [];
			foreach ($errors_for_key as $error) {
				$vars = $error->getVariables();

				Valid::setError($key, $error->getTemplate());
				Valid::setValue($key, $vars['value']);
			}
		}

		self::setState('errors', $errors);
	}

	public static function signin()
	{

		$auth_error = 0;

		$validator = new Validator();

		$rule_factory = $validator->getRuleFactory();
		$rule_factory->setMessages('required', 'This field is required', '{label} is required');
		$rule_factory->setMessages('email', 'The email is invalid', '{label} is invalid');

		$validator->add('email', 'required | email');
		$validator->add('password', 'required');

		Valid::form('signin');
		Valid::fetchValues(['email']);

		//
		if (!$validator->validate($_POST)) {
			self::setErrorMessages($validator->getMessages());
			return false;
		}

		//
		$user = UserQuery::create()->filterByEmail($_POST['email'])->findOne();

		if (!$user) {
			Valid::setError('password', 'Wrong email/password');
			return false;
		}

		if ($user->getInit() !== null && $user->getInit() !== '') {
			Valid::setError('password', 'This account is not active');
			return false;
		}

		//
		$auth_password = $_POST['password'];

		//
		if (!password_verify($auth_password, $user->getPassword())) {
			Valid::setError('password', 'Wrong email/password');
			return false;
		}

		//
		if (password_needs_rehash($user->getPassword(), PASSWORD_DEFAULT)) {
			$user->setPassword($auth_password)->save();
		}

		self::setUser($user);

		// security
		unset($_REQUEST['password']);
		unset($_POST['password']);

		return true;
	}


	public static function createAdmin()
	{

		//
		$password = self::generatePassword(8, ['chars' => true, 'capitals' => true, 'numbers' => true]);

		//
		$user = new User();
		$user->setFirstName('Admin');
		$user->setLastName('');
		$user->setEmail('admin@admin');
		$user->setPassword($password);
		$user->setInit('');
		$user->setPrivilege(User::PRIVILEGE_ADMIN);
		$user->save();

		return $password;
	}


	public static function signout()
	{

		if (!Csrf::validate('signout')) {
			return false;
		}

		session_regenerate_id();
		session_unset();

		return true;
	}


	public static function setState($key, $value)
	{
		if (!isset($_SESSION['UserAuth'])) {
			$_SESSION['UserAuth'] = ['state' => []];
		}

		$_SESSION['UserAuth']['state'][$key] = $value;
	}


	public static function getState($key)
	{
		return isset($_SESSION['UserAuth']['state'][$key]) ? $_SESSION['UserAuth']['state'][$key] : null;
	}

	public static function clearState($key)
	{
		unset($_SESSION['UserAuth']['state'][$key]);
	}


	public static function setUser($new_user)
	{
		self::$user = $new_user;

		$_SESSION['user_id'] = $new_user->getId();
	}

	public static function getUser()
	{
		return self::$user;
	}

	public static function isSignedin()
	{
		return (self::$user !== false);
	}

}
