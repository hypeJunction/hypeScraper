<?php

/**
 * View that can be used to generate previews URLs contained within a text
 *
 * @uses $vars['value']  STR Text to analyze
 * @uses $vars['limit']  INT Max number of links to display. 0 for no limit
 * @uses $vars['params'] ARR Params to pass to the embed view
 */
$value = elgg_extract('value', $vars, '');
$limit = elgg_extract('limit', $vars, 0);
$params = elgg_extract('params', $vars, array());
$i = 0;

$tokens = hypeapps_extract_tokens($value);
$urls = elgg_extract('urls', $tokens, []);

if (empty($urls)) {
	return;
}

foreach ($urls as $url) {
	if ($limit > 0 && $i >= $limit) {
		continue;
	}
	$params['href'] = $url;

	$preview_type = elgg_get_plugin_setting('preview_type', 'hypeScraper', 'card');
	if ($preview_type != 'card') {
		$params['fallback'] = true;
		echo elgg_view('output/player', $params);
	} else {
		echo elgg_view('output/card', $params);
	}
	$i++;
}