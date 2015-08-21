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

$urls = hypeScraper()->extractor->urls($value);

if (!empty($urls)) {
	foreach ($urls as $url) {
		if ($limit > 0 && $i >= $limit) {
			continue;
		}
		$params['href'] = $url;
		echo elgg_view('output/card', $params);
		$i++;
	}
}