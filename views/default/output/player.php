<?php

$href = elgg_extract('href', $vars);

$data = hypeapps_scrape($href);
if (!$data) {
	return;
}

if ($data['html']) {
	echo $data['html'];
} else if (elgg_extract('fallback', $vars, true)) {
	echo elgg_view('output/card', [
		'href' => $href,
	]);
}
