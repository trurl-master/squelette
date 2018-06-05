<?php

require_once 'inc/init.php';
require_once 'inc/app.php';

App::init($config);

switch ($page = App::page()) {

	case 'api': App::controller('api');	break;

	case 'index':

        App::controller('default', ['page' => $page]);

        break;

    default: App::to404();
}
