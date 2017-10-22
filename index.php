<?php

require_once 'inc/init.php';
require_once 'inc/app.php';

//
date_default_timezone_set('Europe/Moscow');

//
App::init($config);

//
$page = App::page();

//
switch ($page) {

	case 'api': App::controller('api');	break;

	case 'index':

        App::controller('default', ['page' => $page]);

        break;

    default: App::to404();
}
