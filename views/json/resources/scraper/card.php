<?php

$href = elgg_extract('href', $vars);
$data = hypeapps_scrape($href);
if (!$data) {
	$data = new \stdClass();
}

echo json_encode($data);