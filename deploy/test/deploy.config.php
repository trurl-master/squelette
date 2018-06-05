<?php

return [
    'is_production' => false,
    'live_site' => '',
    'assets' => '/assets/',
    'root' => getcwd(),
    'language_in_path' => false,
    'default_language' => 'en',
    'locales' => ['en' => 'en_US'],
    'default_timezone' => 'Europe/Moscow',
    'auth' => false,
    'smtp' => false,
    //   [
    //    'host' => 'smtp.gmail.com',
    //    'port' => 587,
    //    'encryption' => 'tls',
    //    'auth' => [
    //        'username' => '',
    //        'password' => ''
    //    ]
    //],
    'emails' => [
        'request' => 'trurl-master@ya.ru',
        'question' => 'trurl-master@ya.ru',
        'noreply' => [
            'address' => 'noreply@macs.school',
            'title' => 'M-A-C-S'
        ],
        'info' => [
            'address' => 'info@macs.school',
            'title' => 'M-A-C-S'
        ]
    ]
];
