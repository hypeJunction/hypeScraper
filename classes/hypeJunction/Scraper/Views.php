<?php

namespace hypeJunction\Scraper;

use ElggRiverItem;

/**
 * @access private
 */
class Views {

	static $domains;

	/**
	 * Output metatags for a URL
	 *
	 * @param string $hook   'extract:meta'
	 * @param string $type   'embed'
	 * @param array  $return Metatags
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function getCard($hook, $type, $return, $params) {
		$url = elgg_extract('url', $params);
		return hypeapps_scrape($url);
	}

	/**
	 * Preview a URL card
	 *
	 * @param string $hook   'format:src'
	 * @param string $type   'all'
	 * @param array  $return Metatags
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function viewCard($hook, $type, $return, $params) {
		$href = elgg_extract('src', $params);
		$preview_type = elgg_get_plugin_setting('preview_type', 'hypeScraper', 'card');
		if ($preview_type != 'card') {
			return elgg_view('output/player', [
				'href' => $href,
				'fallback' => true,
			]);
		} else {
			return elgg_view('output/card', [
				'href' => $href,
			]);
		}
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
	public static function extractTokens($hook, $type, $return, $params) {
		$source = elgg_extract('source', $params);
		return hypeapps_extract_tokens($source);
	}

	/**
	 * Linkify qualifiers such as hashtags, usernames, urls, and emails
	 *
	 * @param string $hook   Equals 'link:qualifiers'
	 * @param string $type   Equals 'scraper'
	 * @param array  $return Qualifiers
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function linkTokens($hook, $type, $return, $params) {
		$source = elgg_extract('source', $params);
		$types = elgg_extract('types', $params, [
			'urls', ''
		]);
		return hypeapps_linkify_tokens($source);
	}

	/**
	 * Display a preview of a bookmark
	 *
	 * @param string $hook   'view_vars'
	 * @param string $type   "river/elements/layout"
	 * @param array  $return View vars
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function addBookmarkRiverPreview($hook, $type, $return, $params) {

		if (!elgg_get_plugin_setting('bookmarks', 'hypeScraper')) {
			return;
		}

		$item = elgg_extract('item', $return);
		if (!$item instanceof ElggRiverItem) {
			return;
		}

		if ($item->view != 'river/object/bookmarks/create') {
			return;
		}

		$object = $item->getObjectEntity();
		if (!elgg_instanceof($object, 'object', 'bookmarks')) {
			return;
		}

		$preview_type = elgg_get_plugin_setting('preview_type', 'hypeScraper', 'card');
		if ($preview_type != 'card') {
			$return['attachments'] = elgg_view('output/player', [
				'href' =>  $object->address,
				'fallback' => true,
			]);
		} else {
			$return['attachments'] = elgg_view('output/card', [
				'href' =>  $object->address,
			]);
		}

		return $return;
	}

	/**
	 * Display a preview of a bookmark
	 *
	 * @param string $hook   'view_vars'
	 * @param string $type   "object/elements/full"
	 * @param array  $return View vars
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function addBookmarkProfilePreview($hook, $type, $return, $params) {

		if (!elgg_get_plugin_setting('bookmarks', 'hypeScraper')) {
			return;
		}

		$entity = elgg_extract('entity', $return);
		if (!elgg_instanceof($entity, 'object', 'bookmarks')) {
			return;
		}

		$return['body'] .= elgg_view('output/player', [
			'href' => $entity->address,
		]);

		return $return;
	}

	/**
	 * Linkify longtext output
	 *
	 * @param string $hook   "view"
	 * @param string $type   "output/longtext"
	 * @param array  $return View vars
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function linkifyLongtext($hook, $type, $return, $params) {
		if (!elgg_get_plugin_setting('linkify', 'hypeScraper')) {
			return;
		}
		return hypeapps_linkify_tokens($return, $params['vars']);
	}

	/**
	 * Filter parsed metatags
	 *
	 * @param string $hook   "parse"
	 * @param string $type   "framework/scraper"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function cleanEmbedHTML($hook, $type, $return, $params) {
		
		if (empty($return['html'])) {
			return;
		}

		$url = parse_url(elgg_extract('url', $return, ''), PHP_URL_HOST);
		$canonical_url = parse_url(elgg_extract('canonical', $return, ''), PHP_URL_HOST);

		$domains = self::getoEmbedDomains();
		
		$matches = array_map(function($elem) use ($url, $canonical_url) {
			$elem = preg_quote($elem);
			$domain_pattern = "/(.+?)?$elem/i";
			return preg_match($domain_pattern, $url) || preg_match($domain_pattern, $canonical_url);
		}, $domains);

		$matches = array_filter($matches);
		
		// only allow html from whitelisted domains
		if (empty($matches)) {
			unset($return['html']);
		} else if (!preg_match('/<iframe|video|audio/i', $return['html'])) {
			// only allow iframe, video, and audio tags
			unset($return['html']);
		}

		return $return;
	}

	/**
	 * Returns an array of normalized whitelisted domains
	 *
	 * @return array
	 */
	public static function getoEmbedDomains() {

		if (isset(self::$domains)) {
			return self::$domains;
		}

		$normalize = function($domain) {
			$domain = trim($domain);
			$domain = parse_url($domain, PHP_URL_HOST);
			$domain = str_replace('www.', '', $domain);
			return $domain;
		};

		$domains = elgg_get_plugin_setting('oembed_domains', 'hypeScraper', '');
		$domains = preg_split('/$\R?^/m', $domains);
		$domains = array_filter($domains);

		if (empty($domains)) {
			$root = elgg_get_plugins_path();
			$domains = include $root . '/hypeScraper/lib/whitelist.php';
		}

		self::$domains = array_map($normalize, $domains);

		return self::$domains;
	}

}
