<?php

$meta = \Squelette\MetaQuery::create()->filterByName($page)->findOne();

if ($meta === null && !App::cfg('is_production')) {
    echo 'warning: meta record “', $page, '” doesn\'t exist';
}

App::renderTemplate(
    'master',
    [
    	'page' => $page,
    	'meta' => $meta
    ]
);
