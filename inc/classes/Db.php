<?php

namespace Squelette;

class Db
{

    private static $data = [];

    public static function get($file)
    {
        if (!isset(self::$data[$file])) {
            $filepath = 'data/' . $file . '.';
            $file = $filepath . \App::lang() . '.json';

            // check localized version
            if (!file_exists($file)) {
                $file = $filepath .  'json';
            }

            // check general version
            if (file_exists($file)) {
                self::$data[$file] = json_decode(file_get_contents($file), true);

                if (self::$data[$file] === null) {
                    die('error parsing json file: '. $file);
                }
            } else {
                self::$data[$file] = null;
            }

        }

        return self::$data[$file];
    }

    public static function findByKeyValue($array, $key, $value)
    {
        $results = [];

        if (!is_array($array)) {
            $array = self::get($array);
        }

        foreach ($array as $item) {
            if ($item[$key] === $value) {
                array_push($results, $item);
            }
        }

        return $results;
    }

}
