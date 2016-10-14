<?php

run_function_once('scraper_upgrade_20161410a');

/**
 * Move scraper data from disk to database
 * @return void
 */
function scraper_upgrade_20161410a() {

	set_time_limit(0);

	// Setup MySQL databases
	run_sql_script(dirname(dirname(__FILE__)) . '/install/mysql.sql');

	$svc = \hypeJunction\Scraper\ScraperService::getInstance();

	$site = elgg_get_site_entity();
	$dataroot = elgg_get_config('dataroot');
	$dir = new \Elgg\EntityDirLocator($site->guid);

	$paths = elgg_get_file_list($dataroot . $dir . 'scraper_cache/resources/');
	if (empty($paths)) {
		return;
	}

	foreach ($paths as $path) {
		$basename = pathinfo($path, PATHINFO_BASENAME);

		$file = new ElggFile();
		$file->owner_guid = $site->guid;
		$file->setFilename("scraper_cache/resources/$basename");
		$file->open('read');
		$json = $file->grabFile();
		$file->close();

		$data = json_decode($json, true);
		if (!$data || empty($data['url'])) {
			$file->delete();
			continue;
		}

		$url = $data['url'];
		$svc->parse($url);

		$file->delete();

		$file = new ElggFile();
		$file->owner_guid = $site->guid;
		$file->setFilename("scraper_cache/thumbs/$basename");
		$file->delete();
	}
}