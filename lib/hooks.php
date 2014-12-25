<?php

namespace hypeJunction\Scraper;

/**
 * Output an embedded view of a URL
 * 
 * @param string $hook   'format:src'
 * @param string $type   'embed'
 * @param string $return HTML
 * @param array  $params Hook params
 * @return string
 */
function format_embed_view($hook, $type, $return, $params) {

	$src = elgg_extract('src', $params);
	unset($params['src']);

	return Embedder::getEmbedView($src, $params);
}

/**
 * Output metatags for a URL
 * 
 * @param string $hook   'extract:meta'
 * @param string $type   'embed'
 * @param array  $return Metatags
 * @param array  $params Hook params
 * @return array
 */
function get_embed_metatags($hook, $type, $return, $params) {

	$src = elgg_extract('src', $params);
	unset($params['src']);

	return Parser::getMeta($src);
}

/**
 * Extract qualifiers such as hashtags, usernames, urls, and emails
 * 
 * @param string $hook   Equals 'extract:qualifiers'
 * @param string $type   Equals 'scraper'
 * @param array  $return Qualifiers
 * @param array  $params Hook params
 * @return array
 */
function extract_qualifiers($hook, $type, $return, $params) {

	$source = elgg_extract('source', $params);

	$return['hashtags'] = Extractor::extractHashtags($source);
	$return['emails'] = Extractor::extractEmails($source);
	$return['usernames'] = Extractor::extractUsernames($source);
	$return['urls'] = Extractor::extractURLs($source);

	return $return;
}

/**
 * Link qualifiers to their entities
 * 
 * @param string $hook   Equals 'link:qualifiers'
 * @param string $type   Equals 'scraper'
 * @param string $return HTML
 * @param array  $params Hook params
 * @return string
 * @deprecated 1.1.3
 */
function link_qualifiers($hook, $type, $return, $params) {
	elgg_deprecated_notice("'link:qualifiers',\$type hook has been deprecated. Use 'output/linkify' view instead", $type);
	$source = elgg_extract('source', $params);
	return Extractor::render($source);
}

/**
 * Run unit tests
 *
 * @param string $hook   Equals 'unit_test'
 * @param string $type   Equals 'system'
 * @param array  $value  An array of unit test locations
 * @param array  $params Additional params
 * @return array Updated array of unit test locations
 */
function unit_test($hook, $type, $value, $params) {

	$path = elgg_get_plugins_path();
	//$value[] = $path . PLUGIN_ID . '/tests/ExtractorTest.php';

	return $value;
}