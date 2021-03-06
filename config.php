<?php

return [
    'is_production' => false,
    'live_site' => '',
    'assets' => '/assets/',
    'root' => getcwd(),
    'language_in_path' => false,
    'default_language' => 'en',
    'locales' => ['en' => 'en_US'],
    'default_timezone' => 'America/Los_Angeles',
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
        'some_email' => 'some_email@your.domain',
        'noreply' => [
            'address' => 'noreply@your.domain',
            'title' => 'Title'
        ]
    ]
];
