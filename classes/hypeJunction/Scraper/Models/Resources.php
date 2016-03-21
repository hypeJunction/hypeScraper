<?php

namespace hypeJunction\Scraper\Models;

use hypeJunction\Scraper\Config;
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
			$data = $this->resourceCache->put($url, $handle, $data);
		}
		return $data;
	}

	public function getThumbUrl($url = '', $handle = null) {
		$data = $this->resourceCache->get($url, $handle);
		if (!empty($data['thumb_cache'])) {
			$thumb = new \ElggFile();
			$thumb->owner_guid = elgg_get_site_entity()->guid;
			$thumb->setFilename("scraper_cache/thumbs/{$uid}.{$handle}.jpg");
			$icon_url = elgg_get_inline_url($thumb);
		} else if (!empty($data['thumbnail_url'])) {
			$icon_url = $data['thumbnail_url'];
		} else {
			$icon_url = elgg_get_simplecache_url('framework/scraper/placeholder.png');
		}
		return elgg_normalize_url($icon_url);
	}

}
