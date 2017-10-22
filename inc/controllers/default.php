<?php

App::renderTemplate(
    'master',
    [
    	'page' => $page,
    	'meta' => \Squelette\MetaQuery::create()->filterByName($page)->findOne()
    ]
);
