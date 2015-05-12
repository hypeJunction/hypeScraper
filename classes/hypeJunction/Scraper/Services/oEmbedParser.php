<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Http\Resource;

class oEmbedParser {

	private $props = array(
		'type',
		'version',
		'title',
		'author_name',
		'author_url',
		'provider_name',
		'provider_url',
		'cache_age',
		'thumbnail_url',
		'thumbnail_width',
		'thumbnail_height',
		'width',
		'height',
		'html',
	);
	private $httpResource;

	public function __construct(Resource $resource) {
		$this->httpResource = $resource;
	}

	public function parse($url = '', array $options = array()) {

		$meta = array(
			'url' => $url,
		);

		$content = $this->httpResource->read($url);
		if (!$content) {
			return $meta;
		}

		if ($this->httpResource->isJSON($url, $options)) {
			$data = @json_decode($content);
		} else if ($this->httpResource->isXML($url, $options)) {
			$data = @simplexml_load_string($content);
		}

		foreach ($this->props as $key) {
			if (!empty($data->$key)) {
				$meta[$key] = (string) $data->$key;
			}
		}
		return $meta;
	}

}
