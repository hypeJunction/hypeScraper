<?php

namespace hypeJunction\Scraper\Resources;

use ElggFile;
use hypeJunction\Scraper\Config;

class Cache {

	private $config;

	public function __construct(Config $config) {
		$this->config = $config;
	}

	function get($url = '', $handle = null) {
		$hash = md5($url);

		$site = elgg_get_site_entity();
		if (!$handle) {
			$handle = $site->guid;
		}

		$file = new ElggFile();
		$file->owner_guid = $site->guid;
		$file->setFilename("scraper_cache/resources/{$hash}.{$handle}.json");

		if (!file_exists($file->getFilenameOnFilestore())) {
			return false;
		}

		$file->open('read');
		$json = $file->grabFile();
		$file->close();

		return json_decode($json, true);
	}

	function invalidate($url = '', $handle = null) {
		$hash = md5($url);

		$site = elgg_get_site_entity();
		if (!$handle) {
			$handle = $site->guid;
		}
		$file = new ElggFile();
		$file->owner_guid = $site->guid;
		$file->setFilename("scraper_cache/resources/{$hash}.{$handle}.json");
		$file->delete();

		$file->setFilename("scraper_cache/thumbs/{$hash}.{$handle}.jpg");
		$file->delete();
	}

	function put($url = '', $handle = null, $data = array()) {
		if (!$url) {
			return false;
		}

		$hash = md5($url);

		$site = elgg_get_site_entity();
		if (!$handle) {
			$handle = $site->guid;
		}

		if (!empty($data)) {
			$thumbnails = !empty($data['thumbnails']) ? $data['thumbnails'] : array();
			$icons = !empty($data['icons']) ? $data['icons'] : array();

			$thumbnails = array_merge($thumbnails, $icons);

			if (!empty($thumbnails) && $this->config->get('cache_thumbnails')) {
				foreach ($thumbnails as $thumbnail_url) {
					$imagesize = getimagesize($thumbnail_url);
					if (empty($imagesize) || $imagesize[0] < $this->config->get('cache_thumb_size_lower_threshold')) {
						continue;
					}

					$size = $this->config->get('cache_thumb_size');
					$thumb = get_resized_image_from_existing_file($thumbnail_url, $size, $size, false, 0, 0, 0, 0, true);
					if ($thumb) {
						$file = new ElggFile();
						$file->owner_guid = $site->guid;
						$file->setFilename("scraper_cache/thumbs/{$hash}.{$handle}.jpg");
						$file->open('write');
						$file->write($thumb);
						$file->close();

						$data['thumb_cache'] = time();

						break;
					}
				}
			}
		}

		$file = new ElggFile();
		$file->owner_guid = $site->guid;
		$file->setFilename("scraper_cache/resources/{$hash}.{$handle}.json");
		$file->open('write');
		$file->write(json_encode($data));
		$file->close();

		return $data;
	}

}
