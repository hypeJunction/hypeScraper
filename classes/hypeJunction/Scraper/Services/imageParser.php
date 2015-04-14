<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Http\Resource;

class imageParser {

	private $httpResource;

	public function __construct(Resource $resource) {
		$this->httpResource = $resource;
	}

	public function parse($url = '', array $options = array()) {
		return array(
			'type' => 'photo',
			'url' => $url,
			'thumbnails' => array($url),
		);
	}
}
