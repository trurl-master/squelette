<!doctype html>
<html class="no-js" lang="<?=App::lang()?>">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title><?= $meta->getTitle() ?></title>
		<meta name="description" content="<?= $meta->getDescription() ?>">
		<meta name="keywords" content="<?= $meta->getKeywords() ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link type="image/png" rel="icon" href="<?=App::cfg('assets')?>img/favicon.png">
		<meta property="og:title" content="<?= $meta->getTitle() ?>"/>
		<meta property="og:description" content="<?= $meta->getDescription() ?>"/>
		<meta name="theme-color" content="#1d1d1b" />
		<?php

		$custom = $meta->getCustom();

		if (isset($custom['opengraph'])) {
			foreach ($custom['opengraph'] as $key => $value) {
				echo '<meta property="', $key, '" content="', $value, '">';
			}
		}

		?>
		<?php App::cssBundle() ?>
		<script src="/assets/modernizr-custom.js"></script>
	</head><body><?php

		App::module('pages/' . $page, ['page' => $page]);
		App::jsBundle();

	?></body>
</html>
