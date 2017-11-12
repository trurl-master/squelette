<?php

function normalizePath($path) {
    return array_reduce(
        explode('/', $path),
        function($a, $b) {
            if ($a === 0) {
                $a = "/";
            }

            if ($b === "" || $b === ".") {
                return $a;
            }

            if ($b === "..") {
                return dirname($a);
            }

            return preg_replace("/\/+/", "/", "$a/$b");
        },
        0
    );

}

return [
    'propel' => [
        'database' => [
            'connections' => [
                'main' => [
                    'adapter'    => 'sqlite',
                    'dsn'        => 'sqlite:' . normalizePath(getcwd() . '/../../data/main.sqlite'),
                    'user'       => '',
                    'password'   => '',
                    'settings'   => [
                        'charset' => 'utf8'
                    ]
                ]
            ]
        ],
        'runtime' => [
            'defaultConnection' => 'main',
            'connections' => ['main']
        ],
        'generator' => [
            'defaultConnection' => 'main',
            'connections' => ['main']
        ]
    ]
];
