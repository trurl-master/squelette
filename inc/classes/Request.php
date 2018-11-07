<?php

namespace Squelette;

Class Request
{

    private static $request = [];
    private static $query_string;
    private static $lang;

    public static function init()
    {
        // parse url
        $request = explode('?', rawurldecode($_SERVER['REQUEST_URI']));
        self::$request = explode('/', trim($request[0], '/'));
        self::$query_string = isset($request[1]) ? $request[1] : false;

        if (\App::cfg('language_in_path')) {
            self::$lang = array_shift(self::$request);
        }
    }

    public static function getLang()
    {
        return self::$lang;
    }

    public static function getRequest()
    {
        return self::$request;
    }

    public static function getQueryString()
    {
        return self::$query_string;
    }

    public static function setPathMax($max)
    {
        if (isset(self::$request[$max])) {
            \App::to404();
        }
    }

    public static function get($key, $default = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    public static function post($key, $default = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    public static function path($index, $default = null)
    {
        return isset(self::$request[$index]) ? self::$request[$index] : $default;
    }

    public static function filterPath($index, $filter, $o)
    {
        return self::filterParam(self::path($index, null), $filter, $o);
    }

    public static function filterPost($key, $filter, $o)
    {
        return self::filterParam(self::post($key, null), $filter, $o);
    }

    public static function filterGet($key, $filter, $o)
    {
        return self::filterParam(self::get($key, null), $filter, $o);
    }

    //
    private static function filterParam($param, $filter, $o)
    {
        // not defined
        if ($param === null) {

            // if parameter is optional - return
            if (isset($o['optional']) && $o['optional'] === true) {
                return true;
            }

            //
            if (isset($o['if_empty_404']) && $o['if_empty_404'] === true) {
                \App::to404();
            }

            //
            return false;
        }

        // param matches filter or filter is not set
        if ($filter === false || preg_match($filter, $param)) {

            //
            if (isset($o['value_set'])) {

                if (in_array($param, $o['value_set'])) {
                    return true;
                }

                if (isset($o['if_not_in_set_404']) && $o['if_not_in_set_404'] === true) {
                    \App::to404();
                }

                return false;
            }

            return true;
        }

        // param doesn't match filter
        if (isset($o['if_fail_404']) && $o['if_fail_404'] === true) {
            \App::to404();
        } else {

            if (isset($o['if_fail'])) {
                self::$request[$index] = $o['if_fail'];
            } else {
                self::$request[$index] = false;
            }

            return false;
        }
    }



}
