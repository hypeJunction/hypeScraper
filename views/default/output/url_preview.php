<?php

/**
 * View that can be used to generate previews URLs contained within a text
 *
 * @uses $vars['value']  STR Text to analyze
 * @uses $vars['limit']  INT Max number of links to display. 0 for no limit
 * @uses $vars['params'] ARR Params to pass to the embed view
 */
use hypeJunction\Scraper\Embedder;
use hypeJunction\Scraper\Extractor;

$value = elgg_extract('value', $vars, '');
$limit = elgg_extract('limit', $vars, 0);
$params = elgg_extract('params', $vars, array());
$i = 0;

$extractor = new Extractor();
$urls = $extractor->extractURLs($value);

if (is_array($urls) && count($urls) > 0) {
	foreach ($urls as $url) {
		if ($limit > 0 && $i >= $limit) {
			continue;
		}
		echo Embedder::getEmbedView($url, $params);
		$i++;
	}
}