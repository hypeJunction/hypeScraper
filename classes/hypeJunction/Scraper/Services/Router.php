<?php

namespace hypeJunction\Scraper\Services;

use ElggFile;
use hypeJunction\Scraper\Config\Config;
use hypeJunction\Scraper\MetaHandler;
use hypeJunction\Scraper\Models\Resources;

/**
 * Routing and page handling service
 */
class Router {

	private $config;
	private $model;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Resources  $model
	 */
	public function __construct(Config $config, Resources $model) {
		$this->config = $config;
		$this->model = $model;
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

		$url = get_input('url');
		$handle = get_input('handle');

		$iframe = get_input('iframe', false);
		
		$site = elgg_get_site_entity();
		if (!$handle) {
			$handle = $site->guid;
		}

		if (!$url || !$handle) {
			return false;
		}
		
		$parse = elgg_is_logged_in();

		switch ($page[0]) {

			default :
				$data = $this->model->get($url, $handle, $parse);
				$layout = elgg_view('output/card', array(
					'href' => $url,
					'handle' => $handle,
				));
				$shell = ($iframe) ? 'iframe' : 'default';
				echo elgg_view_page($data['title'], $layout, $shell);
				break;

			case 'json' :
				$data = $this->model->get($url, $handle, $parse);
				header('Content-Type: application/json');
				echo json_encode($data);
				exit;

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
