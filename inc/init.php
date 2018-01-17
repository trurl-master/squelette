<?php

//
session_start();

// App config
$config = require 'config.php';

if (!$config['is_production']) {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
}

// Autoload
require_once 'vendor/autoload.php';

// Database
require_once 'inc/db/generated-conf/config.php';

$defaultLogger = new Monolog\Logger('defaultLogger');
$defaultLogger->pushHandler(new Monolog\Handler\StreamHandler('propel.log', Monolog\Logger::WARNING));

$serviceContainer->setLogger('defaultLogger', $defaultLogger);
