<?php

namespace Squelette;


class Respond
{

	public static function success($o = [])
	{
		if (isset($o['success'])) {
			unset($o['success']);
		}

		header('Content-Type: application/json');
		die(json_encode(array_merge(['success' => true], $o)));
	}

	public static function fail($o = [])
	{
		if (isset($o['success'])) {
			unset($o['success']);
		}

		header('Content-Type: application/json');
		die(json_encode(array_merge(['success' => false], $o)));
	}

}
