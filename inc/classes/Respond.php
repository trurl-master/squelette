<?php

namespace Squelette;


class Respond
{

	// public static $respond_method = 'JSON';

	public static function success($o = [])
	{
		if (isset($o['success'])) {
			unset($o['success']);
		}

		die(json_encode(array_merge(['success' => true], $o)));
	}

	public static function fail($o = [])
	{
		if (isset($o['success'])) {
			unset($o['success']);
		}

		die(json_encode(array_merge(['success' => false], $o)));
	}

}
