<?php

namespace hypeJunction\Scraper\Listeners;

use hypeJunction\Scraper\Config\Config;
use hypeJunction\Scraper\Embedder;
use hypeJunction\Scraper\Extractor;
use hypeJunction\Scraper\Parser;
use hypeJunction\Scraper\Services\Router;

/**
 * Plugin hooks service
 */
class PluginHooks {

	private $config;
	private $router;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Router $router
	 */
	public function __construct(Config $config, Router $router) {
		$this->config = $config;
		$this->router = $router;
	}

	/**
	 * Perform tasks on system init
	 * @return void
	 */
	public function init() {

		elgg_register_plugin_hook_handler('format:src', 'embed', array($this, 'formatEmbedView'));
		elgg_register_plugin_hook_handler('extract:meta', 'all', array($this, 'getEmbedMetadata'));
		elgg_register_plugin_hook_handler('extract:qualifiers', 'all', array($this, 'extractQualifiers'));
		elgg_register_plugin_hook_handler('link:qualifiers', 'all', array($this, 'linkQualifiers'));
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
	function getEmbedMetadata($hook, $type, $return, $params) {

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
	function extractQualifiers($hook, $type, $return, $params) {

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
	function linkQualifiers($hook, $type, $return, $params) {
		//elgg_deprecated_notice("'link:qualifiers',\$type hook has been deprecated. Use 'output/linkify' view instead", $type);
		$source = elgg_extract('source', $params);
		return Extractor::render($source);
	}

}
