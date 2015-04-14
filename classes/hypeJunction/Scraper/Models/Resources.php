<?php

namespace hypeJunction\Scraper\Models;

use hypeJunction\Scraper\Config\Config;
use hypeJunction\Scraper\Resources\Cache;
use hypeJunction\Scraper\Services\Parser;

class Resources {

	private $config;
	private $parser;
	private $resourceCache;

	public function __construct(Config $config, Parser $parser, Cache $resourceCache) {
		$this->config = $config;
		$this->parser = $parser;
		$this->resourceCache = $resourceCache;
	}

	public function get($url = '', $handle = null, $parse = false) {
		$data = $this->resourceCache->get($url, $handle);
		if ($data == false && $parse) {
			$data = $this->parser->parse($url);
			$data =$this->resourceCache->put($url, $handle, $data);
		}
		return $data;
	}
	
}
