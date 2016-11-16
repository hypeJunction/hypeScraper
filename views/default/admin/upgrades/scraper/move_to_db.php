<?php

$svc = \hypeJunction\Scraper\ScraperService::getInstance();

$site = elgg_get_site_entity();
$dataroot = elgg_get_config('dataroot');
$dir = new \Elgg\EntityDirLocator($site->guid);

$paths = elgg_get_file_list($dataroot . $dir . 'scraper_cache/resources/');
$count = count($paths);

echo elgg_view('output/longtext', [
	'value' => elgg_echo('admin:upgrades:scraper:move_to_db:description')
]);

echo elgg_view('admin/upgrades/view', [
	'count' => $count,
	'action' => 'action/upgrade/scraper/move_to_db',
]);
