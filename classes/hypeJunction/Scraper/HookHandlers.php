<?php

namespace hypeJunction\Scraper;

use hypeJunction\Scraper\Models\Resources;
use hypeJunction\Scraper\Services\Extractor;
use hypeJunction\Scraper\Services\Linkify;

/**
 * Plugin hooks service
 */
class HookHandlers {

	private $config;
	private $router;
	private $resources;
	private $extractor;
	private $linkify;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Router $router
	 * @param Resources $resources
	 * @param Extractor $extractor
	 */
	public function __construct(Config $config, Router $router, Resources $resources, Extractor $extractor, Linkify $linkify) {
		$this->config = $config;
		$this->router = $router;
		$this->resources = $resources;
		$this->extractor = $extractor;
		$this->linkify = $linkify;
	}

	/**
	 * Output an embedded view of a URL
	 *
	 * @param string $hook   'format:src'
	 * @param string $type   'embed'
	 * @param string $return HTML
	 * @param array  $params Hook params
	 * @return string
	 */
	function formatEmbedView($hook, $type, $return, $params) {

		$src = elgg_extract('src', $params);
		unset($params['src']);

		$params['href'] = $src;

		return elgg_view('output/card', $params);
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
	function getEmbedMetadata($hook, $type, $return, $params) {

		$src = elgg_extract('src', $params);
		$handle = elgg_extract('handle', $params);

		return $this->resources->get($src, $handle);
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
	function extractQualifiers($hook, $type, $return, $params) {

		$source = elgg_extract('source', $params);

		$return['hashtags'] = $this->extractor->hashtags($source);
		$return['emails'] = $this->extractor->emails($source);
		$return['usernames'] = $this->extractor->usernames($source);
		$return['urls'] = $this->extractor->urls($source);

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
	function linkQualifiers($hook, $type, $return, $params) {
		//elgg_deprecated_notice("'link:qualifiers',\$type hook has been deprecated. Use 'output/linkify' view instead", $type);
		$source = elgg_extract('source', $params);
		return $this->linkify->all($source);
	}

}
