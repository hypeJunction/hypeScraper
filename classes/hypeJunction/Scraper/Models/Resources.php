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
			$uid = md5($url);
			$path = "scraper_cache/thumbs/{$uid}.{$handle}.jpg";
			$dir = elgg_get_site_entity()->guid;
			$dir_tc = elgg_get_site_entity()->time_created;
			$query = serialize(array(
				'uid' => $uid,
				'path' => $path,
				'd' => $dir,
				'dts' => $dir_tc,
				'ts' => $data['thumb_cache'],
				'mac' => hash_hmac('sha256', $uid . $path, get_site_secret()),
			));
			$icon_url = elgg_http_add_url_query_elements('/mod/hypeApps/servers/icon.php', array(
				'q' => base64_encode($query),
			));
		} else if (!empty($data['thumbnail_url'])) {
			$icon_url = $data['thumbnail_url'];
		} else {
			$icon_url = elgg_normalize_url('/mod/hypeScraper/graphics/placeholder.png');
		}
		return $icon_url;
	}

}
