<?php

namespace Squelette;

use Sirius\Validation\Validator;


class UserAuth
{

	static protected $user = false;
	// static protected $last_visit;
	// public static $first_visit = true;
	// public static $DNT = false;


	public static function init()
	{
		// global $cfg_user_rememberme, $cfg_app_path;

		// self::$DNT = (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1);

		//
		if (isset($_SESSION['user_id'])) {
			$user = UserQuery::create()->findPK($_SESSION['user_id']); //DBM::get_row('users', $_SESSION['user_id']);

			if ($user) {
				// unset($user['password']);
				// User::set($user);
				// self::$user = $user;
				self::setUser($user);
			} else {
				session_unset();
				session_destroy();
				header("Refresh:0");
			}
		} /*elseif(!empty($cfg_user_rememberme) && isset($_COOKIE['rememberme'])) { // check remember me cookie
			// get session
			list($selector, $validator_raw) = explode(':', $_COOKIE['rememberme']);

			$auth_token = DBM::get_row_by_key('auth_tokens', 'selector', $selector);

			if (!empty($auth_token) && $auth_token['token'] === hash('sha256', $validator_raw)) {
				$user = DBM::get_row('users', $auth_token['user_id']);

				if ($user) {

					unset($user['password']);
					User::set($user);

					if (isset($auth_token['hybridauth_session']) && $auth_token['hybridauth_session'] !== '') {

						try{
							$hybridauth = new \Hybrid_Auth(require $cfg_app_path . 'config/hybridauth.php');

							// then call Hybrid_Auth::restoreSessionData() to get stored data
							$hybridauth->restoreSessionData($auth_token['hybridauth_session']);

							// call back an instance of Twitter adapter
							$ha_adapter = $hybridauth->getAdapter($user['hybridauth_provider_name']);

							// regrab te user profile
							$user_profile = $ha_adapter->getUserProfile();

							User::set_field('picurl', $user_profile->photoURL);

							// ..
						} catch( \Exception $e ){
							if (isset($hybridauth)) {
								$hybridauth->logoutAllProviders();
								User::forgetMe();
								session_unset();
								session_destroy();
							} else {
								echo "Ooophs, we got an error: " . $e->getMessage();
							}
						}

					}

					User::refreshRememberMe();
				} else {
					session_unset();
					session_destroy();
					unsetcookie('rememberme');
					header("Refresh:0");
				}
			}
		}*/

		// forget about last visit if user doesn't want you to remember
		// if (self::$DNT) {
		// 	return;
		// }

		// get last visit cookie
		// $user_time = time();

		// if (isset($_COOKIE['lastvisit'])) {
		// 	User::setLastVisit($_COOKIE['lastvisit']);
		// 	User::$first_visit = false;
		// } else {
		// 	User::setLastVisit($user_time);
		// 	User::$first_visit = true;
		// }

		// // set new one if request is not async
		// if (!isset($_REQUEST['async']) || parse_bool($_REQUEST['async']) === false) {
		// 	$last_visit_expire = $user_time + 60 * 60 * 24 * 360;
		// 	setcookie('lastvisit', $user_time, $last_visit_expire, '/');
		// }

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
				// array_push($errors[$key], ['value' => $vars['value'], 'message' => $error->getTemplate()]);
				Valid::setError($key, $error->getTemplate());
				Valid::setValue($key, $vars['value']);
			}
		}

		self::setState('errors', $errors);
	}

	public static function signin()
	{

		$auth_error = 0;
		// $auth_email = Input::get_email('email');

		$validator = new Validator();

		$rule_factory = $validator->getRuleFactory();
		$rule_factory->setMessages('required', 'Это поле необходимо', '{label} необходимо');
		$rule_factory->setMessages('email', 'Где-то закралась ошибка', '{label} — похоже, что здесь ошибка');

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
		// if (!Csrf::validate('signin')) {
		// 	Valid::setError('email', 'Внутренняя ошибка');
		// 	return false;
		// }


		//
		$user = \Top50\UserQuery::create()->filterByEmail($_POST['email'])->findOne();

		if (!$user) {
			Valid::setError('password', 'Неверная пара логин/пароль');
			return false;
		}

		if ($user->getInit() !== '') {
			Valid::setError('password', 'Этот аккаунт пока не активирован');
			return false;
		}

		//
		$auth_password = $_POST['password'];


		//
		if (!password_verify($auth_password, $user->getPassword())) {
			Valid::setError('password', 'Неверная пара логин/пароль');
			return false;
		}

		//
		if (password_needs_rehash($user->getPassword(), PASSWORD_DEFAULT)) {
			$rehashed_password = password_hash($auth_password, PASSWORD_DEFAULT);

			if ($rehashed_password !== false) {
				$user->setPassword($rehashed_password)->save();
			} else {
				Valid::setError('password', 'Внутренняя ошибка');
				return false;
			}
		}

		self::setUser($user);

		// security
		unset($_REQUEST['password']);
		unset($_POST['password']);

		return true;
	}


	public static function signup()
	{

		$validator = new Validator();
		$rule_factory = $validator->getRuleFactory();
		$rule_factory->setMessages('required', 'Это поле необходимо', '{label} необходимо');
		$rule_factory->setMessages('email', 'Где-то закралась ошибка', '{label} — похоже, что здесь ошибка');

		$validator->add('email', 'required | email');
		$validator->add('first_name', 'required | regex(/[a-zA-Zа-яА-ЯёЁ\- ]+/)(Имя может содержать буквы, пробелы и символ "-")');
		$validator->add('last_name', 'required | regex(/[a-zA-Zа-яА-ЯёЁ\- ]+/)(Фамилия может содержать буквы, пробелы и символ "-")');
		$validator->add('iaccept_tandc', 'required');

		Valid::form('signup');
		Valid::fetchValues(['email', 'first_name', 'last_name', 'iaccept_tandc']);

		//
		if (!$validator->validate($_POST)) {
			self::setErrorMessages($validator->getMessages());
			return false;
		}

		// find
		$user = UserQuery::create()->filterByEmail($_POST['email'])->findOne();

		if ($user) {
			// self::setErrorMessages(['email' => 'Пользователь с таким адресом уже существует']);
			Valid::setError('email', 'Пользователь с таким адресом уже существует');
			return false;
		}


		//
		// if (!Csrf::validate('signup')) {
		// 	Valid::setError('email', 'Внутренняя ошибка');
		// 	return false;
		// }


		//
		$password = self::generatePassword(8, ['chars' => true, 'capitals' => true, 'numbers' => true]);

		//
		$user = new User();
		$user->setFirstName($_POST['first_name']);
		$user->setLastName($_POST['last_name']);
		$user->setEmail($_POST['email']);
		$user->setPassword(password_hash($password, PASSWORD_DEFAULT));
		$user->setInit(self::generatePassword(128, ['chars' => true, 'capitals' => true, 'numbers' => true]));
		$user->setDtCreated(date("Y-m-d H:i:s"));
		unset($_POST['password']);

		$link = \App::cfg('live_site') . '/api/auth/activate?email=' . $user->getEmail() . '&init=' . $user->getInit();


		if (!\App::sendEmail(
			'top50@readlivemagazine.com',
			$user->getEmail(),
			'Top50ByLive. Активация аккаунта',
			'Благодарим за регистрацию!<br><br>'.
			'Ваш пароль: ' . $password . '<br><br>'.
			'Пожалуйста активируйте ваш аккаунт, пройдя по этой ссылке: <a href="' . $link . '">' . $link . '</a>'
		)) {
			die('Ошибка при отправке email');
		}

		$user->save();

		self::setState('signup-success', true);

		return true;
	}


	public static function activate()
	{

		$validator = new Validator();
		$validator->add('email', 'required | email');
		$validator->add('init', 'required | regex(/[a-zA-Z0-9]/)()');

		if (!$validator->validate($_GET)) {
			return false;
		}

		$user = UserQuery::create()->filterByEmail($_GET['email'])->findOne();
		// $init = $UserQuery::create()->select(['init'])->findByEmail($_POST['email']);

		// $user_init = DBM::get_value_by_key('users', 'init', 'email', $email); // $dbs->selectCell("SELECT init FROM ?_users WHERE email=?", $email);

		if (!$user) {
			return false;
		}

		if ($_GET['init'] !== $user->getInit()) {
			return false;
		}

		$user->setInit('');
		$user->save();

		self::setState('signup-activated', true);
		// DBM::set_value_by_key('users', 'init', '', 'email', $email);

		// $_SESSION['account_activated'] = true;

		// Redirect::to('signin');

		return true;
	}

	public static function restore()
	{

		$validator = new Validator();
		$rule_factory = $validator->getRuleFactory();
		$rule_factory->setMessages('required', 'Это поле необходимо', '{label} необходимо');
		$rule_factory->setMessages('email', 'Где-то закралась ошибка', '{label} — похоже, что здесь ошибка');

		$validator->add('email', 'required | email');

		Valid::form('restore');
		Valid::fetchValues(['email']);

		if (!$validator->validate($_POST)) {
			return false;
		}

		//
		// if (!Csrf::validate('restore')) {
		// 	Valid::setError('email', 'Внутренняя ошибка');
		// 	return false;
		// }

		$user = UserQuery::create()->filterByEmail($_POST['email'])->findOne();

		if (!$user) {
			Valid::setError('email', 'Пользователь не найден.');
			return false;
		}


		if ($user->getInit() !== '') {
			Valid::setError('email', 'Этот аккаунт пока не активирован. Пожалуйста пройдите по ссылке в письме, которое пришло к вам после регистрации.');
			return false;
		}

		$user->setRestore(self::generatePassword(128, ['chars' => true, 'capitals' => true, 'numbers' => true]));


		$link = \App::cfg('live_site') . '/api/auth/restore-finish?email=' . $user->getEmail() . '&restore=' . $user->getRestore();

		//
		if (!\App::sendEmail(
			'top50@readlivemagazine.com',
			$user->getEmail(),
			'Top50ByLive. Восстановление пароля, шаг 1',
			'Поступил запрос на восстановление пароля.<br><br>'.
			'Пожалуйста пройдите по этой ссылке, чтобы получить новый пароль: <a href="' . $link . '">' . $link . '</a>'
		)) {
			die('Ошибка при отправке email');
		}

		$user->save();

		self::setState('restore-link-sent', true);

		return true;
	}


	public static function sendNewPassword()
	{

		$validator = new Validator();
		$validator->add('email', 'required | email');
		$validator->add('restore', 'required | regex(/[a-zA-Z0-9]/)()');

		if (!$validator->validate($_GET)) {
			return false;
		}

		$user = UserQuery::create()->filterByEmail($_GET['email'])->findOne();
		// $init = $UserQuery::create()->select(['init'])->findByEmail($_POST['email']);

		// $user_init = DBM::get_value_by_key('users', 'init', 'email', $email); // $dbs->selectCell("SELECT init FROM ?_users WHERE email=?", $email);

		if (!$user) {
			return false;
		}

		if ($_GET['restore'] !== $user->getRestore()) {
			return false;
		}

		$password = self::generatePassword(8, ['chars' => true, 'capitals' => true, 'numbers' => true]);

		//
		$user->setPassword(password_hash($password, PASSWORD_DEFAULT));
		$user->setRestore('');

		//
		if (!\App::sendEmail(
			'top50@readlivemagazine.com',
			$user->getEmail(),
			'Top50ByLive. Восстановление пароля, шаг 2',
			'Ваш новый пароль: ' . $password
		)) {
			die('Ошибка при отправке email');
		}

		$user->save();

		self::setState('restore-finish', true);

		return true;
	}


	public static function signout()
	{

		if (!Csrf::validate('signout')) {
			return false;
		}

		session_regenerate_id();
		session_unset();
		// User::forgetMe();
		// unsetcookie('rememberme');
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

	// public static function rememberMe($data = [], $user_id = false)
	// {
	// 	global $cfg_user_rememberme, $dbs;

	// 	if(empty($cfg_user_rememberme) || isset($_COOKIE['rememberme']) || self::$DNT) {
	// 		return;
	// 	}

	// 	//
	// 	if (!$user_id) {
	// 		$user_id = User::id();
	// 	}

	// 	// if (!is_array($))

	// 	//
	// 	if (!isset($cfg_user_rememberme['period'])) {
	// 		$p = 30; // days
	// 	} else {
	// 		$p = $cfg_user_rememberme['period'];
	// 	}

	// 	// create validator, check it doesn't exist in the auth_tokens table
	// 	do {
	// 		$selector = generate_random_id(12);
	// 	} while (DBM::row_exists_by_key('auth_tokens', 'selector', $selector));

	// 	$validator_raw = generate_random_id(64);
	// 	$validator_hashed = hash('sha256', $validator_raw);
	// 	$expires_ts = time() + 60 * 60 * 24 * 30;

	// 	// token data
	// 	$token_data = array_extend_recursive([
	// 		'selector' => $selector,
	// 		'token' => $validator_hashed,
	// 		'user_id' => $user_id,
	// 		'expires' => DBM::datetime($expires_ts)
	// 	], $data);


	// 	//
	// 	if (!DBM::add_item('auth_tokens', $token_data)) {
	// 		return;
	// 	}

	// 	setcookie('rememberme', $selector . ':' . $validator_raw, $expires_ts);

	// 	// remove expired tokens
	// 	$dbs->query("DELETE FROM ?_auth_tokens WHERE expires < ?", DBM::datetime());

	// 	// remove more than 4 tokens for per user, keep newest
	// 	$obsolete_tokens_ids = $dbs->selectCol("SELECT id FROM ?_auth_tokens WHERE user_id=? ORDER BY expires DESC LIMIT 4,1000", $user_id);

	// 	if (!empty($obsolete_tokens_ids)) {
	// 		$dbs->query("DELETE FROM ?_auth_tokens WHERE id IN (?a)", $obsolete_tokens_ids);
	// 	}

	// }

	// public static function refreshRememberMe($user_id = false)
	// {
	// 	global $cfg_user_rememberme, $dbs;

	// 	if(empty($cfg_user_rememberme) || !isset($_COOKIE['rememberme']) || self::$DNT) {
	// 		return;
	// 	}

	// 	//
	// 	if (!isset($cfg_user_rememberme['period'])) {
	// 		$p = 30; // days
	// 	} else {
	// 		$p = $cfg_user_rememberme['period'];
	// 	}

	// 	$now = time();
	// 	$seconds_in_day = 60 * 60 * 24;


	// 	// Refresh cookie once in half period
	// 	if ($now - intval($_COOKIE['lastvisit']) < $seconds_in_day * $p * 0.5) {
	// 		return;
	// 	}

	// 	//
	// 	if (!$user_id) {
	// 		$user_id = User::id();
	// 	}

	// 	$expires_ts = $now + $seconds_in_day * 30;

	// 	list($selector, $validator_raw) = explode(':', $_COOKIE['rememberme']);


	// 	$dbs->query("UPDATE ?_auth_tokens SET expires=? WHERE selector=?", DBM::datetime($expires_ts), $selector);

	// }

	// public static function forgetMe()
	// {
	// 	global $cfg_user_rememberme, $dbs;

	// 	if (empty($cfg_user_rememberme) || !isset($_COOKIE['rememberme']) || self::$DNT) {
	// 		return;
	// 	}

	// 	list($selector, $validator_raw) = explode(':', $_COOKIE['rememberme']);

	// 	$dbs->query("DELETE FROM ?_auth_tokens WHERE selector=?", $selector);
	// 	unsetcookie('rememberme');
	// }

	public static function setUser($new_user)
	{
		self::$user = $new_user;

		$_SESSION['user_id'] = $new_user->getId();
	}

	// public static function set_field($key, $value, $session = true)
	// {

	// 	self::$user[$key] = $value;

	// 	if ($session) {
	// 		$_SESSION['user_' . $key] = $value;
	// 	}

	// }

	// public static function setLastVisit($lv)
	// {
	// 	self::$last_visit = $lv;
	// }

	// public static function lastVisit()
	// {
	// 	return self::$last_visit;
	// }

	public static function getUser()
	{
		return self::$user;
	}

	public static function isSignedin()
	{
		return (self::$user !== false);
	}

	// /**
	// *
	// * check if user have access to '$to'
	// * 2 cases - inclusive and exclusive:
	// *
	// * inclusive: example access list: "discuss,comments"
	// * exclusive: example access list: "all, !discuss"
	// *
	// */
	// public static function access($to)
	// {

	// 	if (self::$user === false) {
	// 		return null;
	// 	}

	// 	if (self::$user['access'] == 'all') {
	// 		return true;
	// 	}

	// 	$all = substr(self::$user['access'], 0, 3);


	// 	if ($all === 'all') { // exclusive
	// 		if (!strstr(self::$user['access'], '!'.$to)) {
	// 			return true;
	// 		} else {
	// 			return false;
	// 		}
	// 	} else { // inclusive
	// 		if (strstr(self::$user['access'], $to)) {
	// 			return true;
	// 		}
	// 	}

	// 	return false;
	// }


	// /**
	// *
	// * get user access list
	// *
	// */

	// public static function accessList()
	// {
	// 	return self::$user['access'];
	// }

	// /**
	//  * user rights
	//  */

	// public static function right($to, $def = 'no')
	// {
	// 	if (self::$user === false || !isset(self::$user['rights'])) {
	// 		return null;
	// 	}

	// 	if (!isset(self::$user['parsed_rights'])) {
	// 		self::$user['parsed_rights'] = json_decode(self::$user['rights'], true);
	// 	}

	// 	if (isset(self::$user['parsed_rights'][$to])) {
	// 		return self::$user['parsed_rights'][$to];
	// 	}

	// 	return $def;
	// }


	// public static function rightsList()
	// {
	// 	return self::$user['rights'];
	// }


	// /**
	//  *
	//  * Check if user is not a robot
	//  *
	//  */

	// public static function isRobot()
	// {
	// 	global $cfg_captcha;

	// 	if (self::$user !== false || !$cfg_captcha) {
	// 		return false;
	// 	}

	// 	$id = Input::get_int('captcha', 0);
	// 	$captcha = Input::get_raw('captcha_answer');

	// 	// check user answer
	// 	if ($_SESSION['captcha'][$id] !== $captcha) {
	// 		return true;
	// 	}

	// 	return false;
	// }

	// /**
	//  * return user id
	//  */

	// public static function id()
	// {
	// 	return self::$user['id'];
	// }


	// /**
	//  * Return usable user name
	//  */
	// public static function name()
	// {
	// 	$user = self::$user;

	// 	if ($user !== false) {
	// 		$name = empty($user['nickname']) ? $user['name'] : $user['nickname'];
	// 	} else {
	// 		return false;
	// 	}

	// 	return $name;
	// }

	// public static function realname($name)
	// {
	// 	global $dbs;

	// 	$user = self::$user;

	// 	//
	// 	if ($user !== false) {
	// 		$name = empty($user['nickname']) ? $user['name'] : $user['nickname'];
	// 	} else {
	// 		if (!empty($name)) {
	// 			$name = 'Гость( '.$name.' )';
	// 		} else {
	// 			$name = 'Гость';
	// 		}
	// 	}

	// 	return $name;
	// }

	// /**
	//  * User activity logger
	//  */

	// public static function updateLastAction()
	// {
	// 	global $dbs;

	// 	if (!self::$user || !isset(self::$user['id']) || self::$user['id'] <= 0) {
	// 		return false;
	// 	}

	// 	$dbs->query("UPDATE users SET last_action_ts = NOW() WHERE id = ?", self::$user['id']);

	// 	return true;
	// }

	// public static function log()
	// {
	// 	// TBD
	// }
}
