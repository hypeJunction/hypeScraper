<?php

namespace hypeJunction\Scraper;

use Hashids\Hashids;

class Hasher {

	static $hashes;
	static $metas;

	public static function hash($url, $metadata = null) {

		$dbprefix = elgg_get_config('dbprefix');
		$url = sanitize_string($url);
		if (is_null($metadata)) {
			$metadata = Parser::getMeta($url);
		}
		$metadata = (!is_string($metadata)) ? json_encode($metadata) : $metadata;
		$metadata = sanitize_string($metadata);
		$hash = sanitize_string(self::getHashFromURL($url));

		$time = time();
		$query = "INSERT INTO {$dbprefix}url_meta_cache (long_url, hash, meta, time_created)
					VALUES ('{$url}','{$hash}','{$metadata}',{$time})
						ON DUPLICATE KEY UPDATE long_url='{$url}',meta='{$metadata}'";

		$id = insert_data($query);

		if (!$hash) {
			$hashids = new Hashids(get_site_secret());
			$hash = $hashids->encrypt($id, $time);

			$query = "UPDATE LOW_PRIORITY {$dbprefix}url_meta_cache
					SET hash = '{$hash}' WHERE id = $id";

			if (update_data($query)) {
				self::$hashes[$url] = $hash;
			}
		}

		self::$metas[$url] = json_decode($metadata);

		return $hash;
	}

	/**
	 * Get a unique hash that are associated with this URL
	 * @param string $url
	 * @return string
	 */
	public static function getHashFromURL($url) {

		$url = sanitize_string($url);

		if (!Validator::isValidURL($url)) {
			return false;
		}

		if (!isset(self::$hashes[$url])) {
			$dbprefix = elgg_get_config('dbprefix');
			$query = "SELECT hash FROM {$dbprefix}url_meta_cache
					WHERE long_url = '{$url}'";
			$short = get_data($query);

			if ($short && count($short)) {
				self::$hashes[$url] = $short[0]->hash;
			} else {
				return false;
			}
		}

		return self::$hashes[$url];
	}

	public static function getURLFromHash($hash) {

		if (empty($hash)) {
			return false;
		}

		$hash = sanitize_string($hash);

		if (isset(self::$hashes)) {
			$url = array_search($hash, self::$hashes);
			if ($url !== false) {
				return $url;
			}
		}

		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT long_url FROM {$dbprefix}url_meta_cache
					WHERE hash = '{$hash}'";
		$short = get_data($query);

		if ($short && count($short)) {
			$url = $short[0]->long_url;
			self::$hashes[$url] = $hash;
			return $url;
		} else {
			return false;
		}
	}

	public static function getMetaFromURL($url) {

		if (empty($url)) {
			return false;
		}

		$url = sanitize_string($url);

		if (isset(self::$metas)) {
			$meta = array_search($meta, self::$metas);
			if ($meta !== false) {
				return $meta;
			}
		}

		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT meta FROM {$dbprefix}url_meta_cache
					WHERE long_url = '{$url}'";
		$short = get_data($query);

		if ($short && count($short)) {
			$meta = json_decode($short[0]->meta);
			if ($meta) {
				self::$metas[$url] = $meta;
				return $meta;
			}
		}

		return false;
	}

}
