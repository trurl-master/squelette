<?php

use \Squelette\Request;

class App
{

    private static $config = array();
    private static $modules = array();
    private static $data = array();
    private static $db = null;
    private static $webpack;
    private static $lang;
    private static $locale;
    private static $jsExport = array();
    private static $react_indexes = array();


    public static function init($config = array())
    {

        //
        self::$config = $config;

        //
        Request::init();

        //
        if (self::cfg('language_in_path')) {
            self::$lang = Request::getLang();
        } else {
            self::$lang = self::cfg('default_language', 'en');
        }

        $locales = self::cfg('locales');

        if (!isset($locales[self::$lang])) {
            self::to404();
        } else {
			self::$locale = $locales[self::$lang];
		}

		//
        if (isset($config['webpack']) && isset($config['webpack']['variants'])) {

            self::$webpack = ['' => require "webpack.php"];

            foreach ($config['webpack']['variants'] as $variant) {
                self::$webpack[$variant] = require $variant . ".webpack.php";
            }

        } else {
            self::$webpack = require "webpack.php"; // webpack hash value
        }

    }


    public static function cfg($key, $default = NULL) {
        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }


    public static function webpack($variant = '') {
        return isset(self::$webpack[$variant]) ? self::$webpack[$variant] : self::$webpack;
    }


    public static function localize($strings)
    {
        return $strings[self::$lang];
    }


    public static function lang()
    {
        return self::$lang;
    }


    public static function locale()
    {
        return self::$locale;
    }


    public static function home()
    {
        return self::cfg('live_site') . '/';
    }


    public static function page($default = 'index')
    {
        $page = Request::path(0, $default);
        return $page === '' ? $default : $page;
    }


    public static function data($key, $default = null)
    {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }


    public static function setData($_data)
    {
        self::$data = array_replace(self::$data, $_data);
    }


    public static function module($_module_name, $_props = null)
    {
        array_push(self::$modules, $_module_name);

        if ($_props) {
            extract($_props);
        }

        return include "modules/" . $_module_name . '.php';
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


    public static function to404()
    {
        header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");

        if (file_exists('inc/controllers/404.php')) {
            App::controller('404');
        }

        die();
    }


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


    public static function renderTemplate($_template_name, $_props = null)
    {
        if ($_props) {
            extract($_props);
        }

        include 'templates/' . $_template_name . '.php';
    }


    public static function jsExport(array $vars)
    {
        self::$jsExport += $vars;
    }


    public static function cssBundle($variant = '')
    {
        $variant_str = $variant ? $variant : 'main';

        echo '<link href="/assets/bundles/bundle.', $variant_str, '.' , self::webpack($variant)['hash'] , '.css" rel="stylesheet">';
    }


    public static function jsBundle($variant = '')
    {

        $variant_str = $variant ? $variant : 'main';

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
        <script src="/assets/bundles/bundle.<?=$variant_str?>.<?=self::webpack($variant)['hash']?>.js" async></script><?php
    }

}

// shortcuts
function _l($strings) {
    return App::localize($strings);
}
