<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Config\Config;
use hypeJunction\Scraper\Embedder;
use hypeJunction\Scraper\Hasher;

/**
 * Routing and page handling service
 */
class Router {

	protected $config;

	/**
	 * Constructor
	 * @param Config $config
	 */
	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * Perform tasks on system init
	 * @return void
	 */
	public function init() {
		elgg_register_page_handler($this->getPageHandlerId(), array($this, 'handlePages'));
	}

	/**
	 * Handles embedded URLs
	 *
	 * @param array $page URL segments
	 * @return boolean
	 */
	function handlePages($page) {

		$hash = elgg_extract(0, $page, '');
		$viewtype = elgg_extract(1, $page, 'iframe');

		$url = get_input('url');

		if (!$url) {
			$url = Hasher::getURLFromHash($hash);
		}
		if (!$url) {
			return false;
		}

		switch ($viewtype) {

			default :
				forward($url);
				break;

			case 'iframe' :
				$embedder = new Embedder($url);
				$meta = $embedder->extractMeta();
				$title = $meta->title;
				$layout = $embedder->getEmbedView($url);
				echo elgg_view_page($title, $layout, 'iframe');
				break;
		}

		return true;
	}

	/**
	 * Returns page handler ID
	 * @return string
	 */
	public function getPageHandlerId() {
		return hypeScraper()->config->get('pagehandler_id', 'categories');
	}

	/**
	 * Prefixes the URL with the page handler ID and normalizes it
	 *
	 * @param mixed $url   URL as string or array of segments
	 * @param array $query Query params to add to the URL
	 * @return string
	 */
	public function normalize($url = '', $query = array()) {

		if (is_array($url)) {
			$url = implode('/', $url);
		}

		$url = implode('/', array($this->getPageHandlerId(), $url));

		if (!empty($query)) {
			$url = elgg_http_add_url_query_elements($url, $query);
		}

		return elgg_normalize_url($url);
	}

}
