<?php

/**
 * Scape a resource by URL
 *
 * @param string $url        URL to scrape
 * @param bool   $cache_only Only return previously scraped data
 * @param bool   $flush      Flush cache and re-parse
 * @return array|false
 */
function hypeapps_scrape($url, $cache_only = false, $flush = false) {
	if (!filter_var($url, FILTER_VALIDATE_URL)) {
		return false;
	}
	
	$svc = hypeJunction\Scraper\ScraperService::getInstance();
	if ($cache_only) {
		return $svc->get($url);
	}
	
	return $svc->parse($url, $flush);
}

/**
 * Extract URLs, emails, hashtags and usernames form text
 * 
 * @param string $text Source text
 * @return array
 */
function hypeapps_extract_tokens($text) {
	return \hypeJunction\Scraper\Extractor::all($text);
}

/**
 * Linkify URLs, emails, hashtags and usernames form text
 *
 * @param string $text    Source text
 * @param array  $options Flags indicating which tokens to parse
 * @return array
 */
function hypeapps_linkify_tokens($text, array $options = []) {

	if (elgg_extract('parse_hashtags', $options, true)) {
		$text = \hypeJunction\Scraper\Linkify::hashtags($text);
	}
	if (elgg_extract('parse_urls', $options, true)) {
		$text = \hypeJunction\Scraper\Linkify::urls($text);
	}
	if (elgg_extract('parse_usernames', $options, true)) {
		$text = \hypeJunction\Scraper\Linkify::usernames($text);
	}
	if (elgg_extract('parse_emails', $options, true)) {
		$text = \hypeJunction\Scraper\Linkify::emails($text);
	}
	return $text;
}