<?php

run_function_once('scraper_upgrade_20161410b');

function scraper_upgrade_20161410b() {
	// Setup MySQL databases
	run_sql_script(dirname(dirname(__FILE__)) . '/install/mysql.sql');
}

// Register upgrade scripts
$path = 'admin/upgrades/scraper/move_to_db';
$upgrade = new \ElggUpgrade();
if (!$upgrade->getUpgradeFromPath($path)) {
	$upgrade->setPath($path);
	$upgrade->title = elgg_echo('admin:upgrades:scraper:move_to_db');
	$upgrade->description = elgg_echo('admin:upgrades:scraper:move_to_db:description');
	$upgrade->save();

	$site = elgg_get_site_entity();
	$dataroot = elgg_get_config('dataroot');
	$dir = new \Elgg\EntityDirLocator($site->guid);

	$paths = elgg_get_file_list($dataroot . $dir . 'scraper_cache/resources/');
	$count = count($paths);
	if (!$count) {
		$upgrade->setCompleted();
	}
}