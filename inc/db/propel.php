<?php

return [
    'propel' => [
        'database' => [
            'connections' => [
                'main' => [
                    'adapter'    => 'sqlite',
                    'dsn'        => 'sqlite:/data/main.sqlite',
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
