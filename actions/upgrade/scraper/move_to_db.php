<?php

use Elgg\EntityDirLocator;
use hypeJunction\Scraper\ScraperService;

if (get_input('upgrade_completed')) {
	$factory = new ElggUpgrade();
	$upgrade = $factory->getUpgradeFromPath('admin/upgrades/scraper/move_to_db');
	if ($upgrade instanceof ElggUpgrade) {
		$upgrade->setCompleted();
	}
	return true;
}

$svc = ScraperService::getInstance();

$site = elgg_get_site_entity();
$dataroot = elgg_get_config('dataroot');
$dir = new EntityDirLocator($site->guid);

$paths = elgg_get_file_list($dataroot . $dir . 'scraper_cache/resources/');

$original_time = microtime(true);
$time_limit = 4;

$success_count = 0;
$error_count = 0;

$response = [];

while (count($paths) > 0 && microtime(true) - $original_time < $time_limit) {
	
	$path = array_shift($paths);

	error_log((string) count($paths));
	
	$response['upgraded'][] = $path;
	$success_count++;

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

	$url = elgg_normalize_url($data['url']);
	if (!filter_var($url, FILTER_VALIDATE_URL)) {
		$file->delete();
		continue;
	}

	ob_start();
	try {
		$svc->parse($url);
	} catch (Exception $e) {

	}
	ob_clean();

	$file->delete();

	$thumb = new ElggFile();
	$thumb->owner_guid = $site->guid;
	$thumb->setFilename("scraper_cache/thumbs/$basename");
	$thumb->delete();
}

if (elgg_is_xhr()) {
	$response['numSuccess'] = $success_count;
	$response['numErrors'] = $error_count;
	echo json_encode($response);
}
