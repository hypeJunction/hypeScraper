<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Config;
use hypeJunction\Scraper\Http\Resource;

class embedlyParser {

	private $config;
	private $httpResource;

	public function __construct(Config $config, Resource $httpResource) {
		$this->config = $config;
		$this->httpResource = $httpResource;
	}

	public function parse($url = '', array $options = array()) {

		$meta = array(
			'url' => $url,
		);

		if (!$url) {
			return $meta;
		}

		$apikey = $this->config->get('embedly_key');
		$endpoint = $this->config->get('embedly_endpoint');

		$apiurl = elgg_http_add_url_query_elements($endpoint, array(
			'key' => $apikey,
			'url' => $url,
		));

		$json = $this->httpResource->read($apiurl, $options);
		return ($json) ? @json_decode($json, true) : $meta;
	}
}
