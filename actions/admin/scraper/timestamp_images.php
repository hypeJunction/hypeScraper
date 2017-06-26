<?php

set_time_limit(0);

$getter = function($options) {
	$limit = (int) elgg_extract('limit', $options, 10);
	$offset = (int) elgg_extract('offset', $options, 0);

	$dbprefix = elgg_get_config('dbprefix');

	$query = "
		SELECT * FROM {$dbprefix}scraper_data
		LIMIT $offset, $limit 
	";

	return get_data($query);
};

$svc = \hypeJunction\Scraper\ScraperService::getInstance();

$batch = new ElggBatch($getter, [
	'limit' => 0,
]);

$i = 0;

foreach ($batch as $row) {

	$data = unserialize($row->data);
	$thumbnail_url = elgg_extract('thumbnail_url', $data);
	if (!$thumbnail_url) {
		continue;
	}

	if (!preg_match('~.*/serve-file/e(\d+)/l(\d+)/d([ia])/c([01])/([a-zA-Z0-9\-_]+)/\d+/(\d+)/(.*)$~', $thumbnail_url, $m)) {
		continue;
	}

	list(, $expires, $last_updated, $disposition, $use_cookie, $mac, $owner_guid, $filename) = $m;

	$file = new ElggFile();
	$file->owner_guid = $owner_guid;
	$file->setFilename($filename);

	$data['thumbnail_url'] = elgg_get_inline_url($file);

	if ($thumbnail_url != $data['thumbnail_url']) {
		$svc->save($row->url, $data);
		$i++;
	}
}

return elgg_ok_response(elgg_echo('admin:scraper:timestamp_images:updated', [$i]));
