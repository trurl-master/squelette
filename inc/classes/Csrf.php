<?php

namespace Squelette;


class Csrf
{

	// function my_simple_crypt( $string, $action = 'e' )
	// {
	// 	// you may change these values to your own
	// 	$secret_key = 'my_simple_secret_key';
	// 	$secret_iv = 'my_simple_secret_iv';

	// 	$output = false;
	// 	$encrypt_method = "AES-256-CBC";
	// 	$key = hash( 'sha256', $secret_key );
	// 	$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

	// 	if( $action == 'e' ) {
	// 	    $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
	// 	}
	// 	else if( $action == 'd' ){
	// 	    $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
	// 	}

	// 	return $output;
	// }

	private static function getNames()
	{
		if (!isset($_SESSION['csrf-name-name'])) {
			$_SESSION['csrf-name-name'] = bin2hex(random_bytes(8));
			$_SESSION['csrf-token-name'] = bin2hex(random_bytes(8));
		}

		return array($_SESSION['csrf-name-name'], $_SESSION['csrf-token-name']);
	}

	public static function getToken($name)
	{
		if (!isset($_SESSION['csrf'])) {
			$_SESSION['csrf'] = [];
		}

		if (!isset($_SESSION['csrf'][$name])) {
			$_SESSION['csrf'][$name] = [
				'token' => bin2hex(random_bytes(32))
			];
		}

		return $_SESSION['csrf'][$name]['token'];
	}

	public static function validate($name)
	{

		$array = self::getTokenArray($name);

		// print_r($name);
		// print_r($_REQUEST);
		// print_r($_SESSION);
		// die();

		if (!isset($_REQUEST[$array['name']['name']]) ||
			!isset($_REQUEST[$array['token']['name']]) ||
			!isset($_SESSION['csrf'])) {
			return false;
		}

		$name = base64_decode($_REQUEST[$array['name']['name']]);

		// var_dump($name, $_SESSION['csrf'][$name]['token'], $_REQUEST[$array['token']['name']]);
		// die();

		return $_REQUEST[$array['token']['name']] === $_SESSION['csrf'][$name]['token'];
	}


	//
	public static function getTokenArray($name)
	{

		list($name_name, $token_name) = self::getNames();

		return [
			'name' => [
				'name' => $name_name,
				'value' => base64_encode($name)
			],
			'token' => [
				'name' => $token_name,
				'value' => self::getToken($name)
			]
		];
	}

	public static function getExportArray($name)
	{
		$array = self::getTokenArray($name);

		return [
			$array['name']['name'] => $array['name']['value'],
			$array['token']['name'] => $array['token']['value']
		];
	}

	public static function printTokenInput($name)
	{
		$array = self::getTokenArray($name);

		echo
			'<input type="hidden" name="', $array['name']['name'], '" value="', $array['name']['value'], '" />',
			'<input type="hidden" name="', $array['token']['name'], '" value="', $array['token']['value'], '" />';
	}

	public static function getTokenRequest($name)
	{

		$array = self::getTokenArray($name);

		return
			$array['name']['name'] . '=' . $array['name']['value'] . '&' .
			$array['token']['name'] . '=' . $array['token']['value'];
	}

}
