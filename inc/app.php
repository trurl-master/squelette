<?php

//
class App {

	private static $config = array();
	private static $modules = array();
	private static $request = array();
	// private static $query = array();
	private static $data = array();
	private static $db = null;
	private static $webpack;
	private static $lang;
	private static $jsExport = array();
	private static $react_indexes = array();

	//
	public static function init($config = array())
	{

		// parse url
		$decoded_uri = rawurldecode($_SERVER['REQUEST_URI']);
		$request = explode('?', $decoded_uri);
		$requestPath = explode('/', trim($request[0], '/'));

		//
		self::$config = $config;
		// self::$lang = array_shift($requestPath); // fetch language from request
		self::$lang = 'ru';
		self::$request = $requestPath; // request vars

		if (isset($config['webpack']) && isset($config['webpack']['variants'])) {

			self::$webpack = array(
				'' => require "webpack.php"
			);

			foreach ($config['webpack']['variants'] as $variant) {
				self::$webpack[$variant] = require $variant . ".webpack.php";
			}
		} else {
			self::$webpack = require "webpack.php"; // webpack hash value
		}

		// if (isset($request[1])) {
		// 	parse_str($request[1], self::$query);
		// }
	}


	//
	public static function cfg($key) {
		return self::$config[$key];
	}

	//
	public static function webpack($variant = '') {
		return isset(self::$webpack[$variant]) ? self::$webpack[$variant] : self::$webpack;
	}


	//
	public static function filterRequestPathParam($index, $filter, $options)
	{

		$param = self::requestPath($index, null);

		if ($param === null) {
			if (isset($options['optional']) && $options['optional'] === true) {
				return true;
			} else {
				if (isset($options['if_empty_404']) && $options['if_empty_404'] === true) {
					self::to404();
				} else {
					return false;
				}
			}
		} else {
			if (preg_match($filter, $param)) {
				return true;
			} else {
				if (isset($options['if_fail_404']) && $options['if_fail_404'] === true) {
					self::to404();
				} else {

					if (isset($options['if_fail'])) {
						self::$request[$index] = $options['if_fail'];
					} else {
						self::$request[$index] = false;
					}

					return false;
				}
			}
		}

	}


	//
	public static function localize($strings)
	{
		return $strings[self::$lang];
	}

	//
	public static function lang()
	{
		return self::$lang;
	}

	//
	public static function home()
	{
		return self::cfg('live_site') . '/';
	}

	//
	public static function requestPath($index, $default = null)
	{
		return isset(self::$request[$index]) ? self::$request[$index] : $default;
	}

	//
	public static function setRequestPathMax($max)
	{
		if (isset(self::$request[$max])) {
			self::to404();
		}
	}

	//
	public static function page($default = 'index')
	{
		$page = self::requestPath(0, $default);
		return $page === '' ? $default : $page;
	}

	//
	public static function data($key, $default = null)
	{
		return isset(self::$data[$key]) ? self::$data[$key] : $default;
	}

	//
	public static function setData($_data)
	{
		self::$data = array_replace(self::$data, $_data);
	}

	//
	public static function module($_module_name, $_props = null)
	{
		array_push(self::$modules, $_module_name);

		if ($_props) {
			extract($_props);
		}

		include "modules/" . $_module_name . '.php';
	}

	//
	public static function reactComponent($_react_component_name, $_props = null)
	{

		if (!isset(self::$react_indexes[$_react_component_name])) {
			$index = self::$react_indexes[$_react_component_name] = 0;
		} else {
			$index = self::$react_indexes[$_react_component_name] += 1;
		}

		echo
			'<span class="react-component" data-name="', $_react_component_name, '" data-index="', $index,'">',
				'<script class="props">',
					'if (typeof react_data === \'undefined\') { react_data = {} };',
					'if (typeof react_data.' , $_react_component_name , ' === \'undefined\') {',
						'react_data.' , $_react_component_name , ' = {};',
					'}',
					'react_data.' , $_react_component_name , '[' , $index , ']' , ' = ' , json_encode($_props, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ,
				'</script>',
			'</span>';

	}

	//
	public static function controller($_controller_name, $_props = null)
	{
		if ($_props) {
			extract($_props);
		}

		include 'inc/controllers/' . $_controller_name . '.php';
	}

	//
	public static function to404()
	{
		header("HTTP/1.0 404 Not Found");

		if (file_exists('inc/controllers/404.php')) {
			App::controller('404');
		}

		die();
	}

	//
	public static function to($what, $query = false)
	{
		$location = '/' . $what;

		if ($query !== false) {
			$location .= '?' . $query;
		}

		header('Location: ' . App::cfg('live_site') . $location);
		die();
	}

	//
	public static function sendEmail($from, $to, $subject = '(No subject)', $message = '', $type = 'plain')
	{
		// $header = "MIME-Version: 1.0\r\nContent-type: text/".$type."; charset=UTF-8\r\nFrom: $from\r\n";
		// return mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', str_replace('<br>', "\n\n", subject)$message, $header);
		return \Squelette\Email::send($to, $subject, $message);
	}

	//
	public static function renderTemplate($_template_name, $_props = null)
	{
		if ($_props) {
			extract($_props);
		}

		include 'templates/' . $_template_name . '.php';
	}

	//
	public static function jsExport(array $vars)
	{
		self::$jsExport += $vars;
	}

	//
	public static function cssBundle($variant = '')
	{
		$variant_str = $variant ? $variant.'.' : '';

		echo '<link href="/assets/bundles/', $variant_str, 'bundle.' , self::webpack($variant)['hash'] , '.css" rel="stylesheet">';
	}

	//
	public static function jsBundle($variant = '')
	{

		$variant_str = $variant ? $variant.'.' : '';

		?><script>
			app = {
				 lang: '<?=self::lang()?>'
				,live_site: '<?=self::cfg('live_site')?>'
				,assets: '<?=self::cfg('assets')?>'
				,active_modules: ['<?=implode('\',\'', array_unique(self::$modules))?>']
				<?php

				foreach (self::$jsExport as $key => $value) {
					echo ',', $key, ': ', $value;
				}

				?>
			}
		</script>
		<script src="/assets/bundles/<?=$variant_str?>bundle.<?=self::webpack($variant)['hash']?>.js" async></script><?php
	}

}

// shortcuts
function _l($strings) {
	return App::localize($strings);
}
