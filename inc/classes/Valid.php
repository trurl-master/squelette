<?php

namespace Squelette;


class Valid {

	public static  $types = array();
	public static  $messages = array();
	private static $value_ignore = array('password2'); // valid data not for database
	private static $session_value_ignore = array('password','password2'); // valid data not for session
	private static $form = 0;
	private static $init = false;

	public static function init()
	{

		if (self::$init) {
			return;
		}

		self::$init = true;

		//
		if (!isset($_SESSION['valid'])) {
			self::reset();
		}

	}

	public static function reset()
	{
		$_SESSION['valid'] = [];
		if (!isset($_SESSION['valid'][self::$form])) {
			$_SESSION['valid'][self::$form] = [
				'values' => [],
				'errors' => [],
				'fields' => [],
				'fields_by_group' => []
			];
		}
	}

	public static function form($name)
	{

		self::init();

		self::$form = $name;

		if (!isset($_SESSION['valid'][$name])) {
			$_SESSION['valid'][$name] = [
				'values' => [],
				'errors' => [],
				'fields' => [],
				'fields_by_group' => []
			];
		}

	}

	//
	public static function setFields($fields, $group = false)
	{
		if ($group) {
			$_SESSION['valid'][self::$form]['fields_by_group'][$group] = $fields;
			$_SESSION['valid'][self::$form]['fields'] = array_merge($_SESSION['valid'][self::$form]['fields'], $fields);
		} else {
			$_SESSION['valid'][self::$form]['fields'] = $fields;
		}

	}

	//
	public static function getFields($group = false)
	{
		if ($group) {
			if (is_array($group)) {

				$result = [];

				foreach($group as $g) {
					$result = array_merge($result, $_SESSION['valid'][self::$form]['fields_by_group'][$g]);
				}

				return $result;

			} else {
				return $_SESSION['valid'][self::$form]['fields_by_group'][$group];
			}
		} else {
			return $_SESSION['valid'][self::$form]['fields'];
		}
	}

	//
	public static function setValue($key, $msg, $only_if_unset = false)
	{

		if ($only_if_unset && isset($_SESSION['valid'][self::$form]['values'][$key])) {
			return;
		}

		$_SESSION['valid'][self::$form]['values'][$key] = $msg;

	}

	//
	public static function getValues()
	{
		if (isset($_SESSION['valid']) && isset($_SESSION['valid'][self::$form])) {
			return $_SESSION['valid'][self::$form]['values'];
		} else {
			return [];
		}
	}

	//
	public static function fetchValues($keys)
	{
		foreach ($keys as $key) {
			self::setValue($key, $_POST[$key]);
		}
	}


	//
	public static function clearValues($keys = null)
	{

		if ($keys === null) {
			$_SESSION['valid'][self::$form]['values'] = array();
		} else {
			if (is_string($keys)) {
				unset($_SESSION['valid'][self::$form]['values'][$keys]);
			} else {
				foreach ($keys as $key) {
					unset($_SESSION['valid'][self::$form]['values'][$key]);
				}
			}
		}

	}

	//
	public static function hasValues()
	{
		if (isset($_SESSION['valid'][self::$form])) {
			return count($_SESSION['valid'][self::$form]['values']) !== 0;
		} else {
			return false;
		}
	}



	//
	public static function setError($key, $msg)
	{
		$_SESSION['valid'][self::$form]['errors'][$key] = $msg;
	}

	public static function getErrors()
	{
		if (isset($_SESSION['valid']) && isset($_SESSION['valid'][self::$form])) {
			return $_SESSION['valid'][self::$form]['errors'];
		} else {
			return [];
		}
	}

	public static function hasErrors()
	{
		if (isset($_SESSION['valid'][self::$form])) {
			return count($_SESSION['valid'][self::$form]['errors']) !== 0;
		} else {
			return false;
		}
	}

	public static function clearErrors()
	{
		$_SESSION['valid'][self::$form]['errors'] = array();
	}

}
