<?php

namespace hypeJunction\Scraper;

use Elgg\Cache\Pool;
use ElggFile;
use hypeJunction\Parser;

/**
 * @access private
 */
class ScraperService {

	/**
	 * @var self
	 */
	static $_instance;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var Pool
	 */
	private $cache;

	/**
	 * Constructor
	 *
	 * @param Parser $parser Parser
	 * @param Pool     $cache Cache
	 */
	public function __construct(Parser $parser, Pool $cache) {
		$this->parser = $parser;
		$this->cache = $cache;
	}

	/**
	 * Returns a singleton
	 * @return self
	 */
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			$conf = self::getHttpClientConfig();
			$client = new \GuzzleHttp\Client($conf);
			$parser = new \hypeJunction\Parser($client);
			$cache = $routes_cache = is_memcache_available() ? new Memcache() : new FileCache();
			self::$_instance = new self($parser, $cache);
		}
		return self::$_instance;
	}

	/**
	 * Get scraped data
	 * 
	 * @param string $url URL
	 * @return array|false
	 * @throws \InvalidArgumentException
	 */
	public function get($url) {
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException(__METHOD__ . ' expects a valid URL');
		}

		$data = $this->cache->get(sha1($url));
		if ($data) {
			return $data;
		}

		$dbprefix = elgg_get_config('dbprefix');
		$row = get_data_row("
			SELECT * FROM {$dbprefix}scraper_data
			WHERE url = :url
		", null, [
			':url' => $url,
		]);

		return $row ? unserialize($row->data) : false;
	}

	/**
	 * Parse and scrape a URL
	 *
	 * @param string $url   URL
	 * @param bool   $flush Flush existing URL data
	 * @return array|false
	 * @throws \InvalidArgumentException
	 */
	public function parse($url, $flush = false) {
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException(__METHOD__ . ' expects a valid URL');
		}

		if ($flush) {
			$this->delete($url);
		} else {
			$data = $this->get($url);
			if (isset($data)) {
				return $data;
			}
		}

		if (!$this->parser->exists($url)) {
			$this->save($url, false);
			return false;
		}

		$post_max_size = elgg_get_ini_setting_in_bytes('post_max_size');
		$upload_max_filesize = elgg_get_ini_setting_in_bytes('upload_max_filesize');
		$max_upload = $upload_max_filesize > $post_max_size ? $post_max_size : $upload_max_filesize;

		if ((int) $response->getHeader('Content-Length') > $max_upload) {
			// Large images eat up memory
			$this->save($url, false);
			return false;
		}

		$data = $this->parser->parse($url);
		if (!$data) {
			$this->save($url, false);
			return false;
		}

		$type = elgg_extract('type', $data);

		switch ($type) {
			case 'photo' :
			case 'image' :
				$image = $this->saveImageFromUrl($url);
				if ($image instanceof ElggFile) {
					$data['width'] = $image->natural_width;
					$data['height'] = $image->natural_height;
					$data['filename'] = $image->getFilename();
					$data['owner_guid'] = $image->owner_guid;
					$data['thumbnail_url'] = elgg_get_inline_url($image);
				}
				break;

			default :
				$assets = [];
				$thumbnails = (array) elgg_extract('thumbnails', $data, []);
				$icons = (array) elgg_extract('icons', $data, []);

				// Try 3 images and choose the one with highest dimensions
				$thumbnails = $thumbnails + $icons;
				$thumbs_parse = 0;
				foreach ($thumbnails as $thumbnail) {
					if ($thumbnail == $url) {
						continue;
					}
					$thumbnail = elgg_normalize_url($thumbnail);
					if (filter_var($thumbnail, FILTER_VALIDATE_URL)) {
						$asset = $this->parse($thumbnail, $flush);
						if ($asset) {
							$thumbs_parsed++;
							$assets[] = $asset;
						}
					}
					if ($thubms_parsed == 3) {
						break;
					}
				}

				$data['assets'] = array_values(array_filter($assets));
				usort($data['assets'], function ($a, $b) {
					if ($a['width'] == $b['width'] && $a['height'] == $b['height']) {
						return 0;
					}
					return ($a['width'] > $b['width'] || $a['height'] > $b['height']) ? -1 : 1;
				});

				if (isset($data['assets'][0]['thumbnail_url'])) {
					$data['thumbnail_url'] = $data['assets'][0]['thumbnail_url'];
				}

				break;
		}

		$data = elgg_trigger_plugin_hook('parse', 'framework:scraper', array(
			'url' => $url,
				), $data);

		$this->save($url, $data);
		return $data;
	}

	/**
	 * Save URL data to the database
	 *
	 * @param string $url  URL
	 * @param array  $data Data
	 * @return boolean
	 */
	public function save($url, $data = false) {
		if (!$url) {
			return false;
		}

		$dbprefix = elgg_get_config('dbprefix');
		$result = insert_data("
			INSERT INTO {$dbprefix}scraper_data
			SET url = :url,
			    hash = :hash,
				data = :data
			ON DUPLICATE KEY UPDATE
			    data = :data
		", [
			':url' => (string) $url,
			':data' => serialize($data),
			':hash' => sha1($url),
		]);

		if ($result) {
			$this->cache->put(sha1($url), $data);
			return true;
		}

		return false;
	}

	/**
	 * Delete URL data from DB and cache
	 *
	 * @param string $url URL
	 * @return bool
	 */
	public function delete($url) {
		$this->cache->invalidate(sha1($url));

		$dbprefix = elgg_get_config('dbprefix');
		$result = delete_data("
				DELETE FROM {$dbprefix}scraper_data
				WHERE url = :url
		", [
			':url' => (string) $url,
		]);

		return (bool) $result;
	}

	/**
	 * Saves an image on Elgg's filestore
	 *
	 * @param string $url URL of the image
	 * @return \ElggFile|false
	 */
	public function saveImageFromUrl($url) {

		$mime = $this->parser->getContentType($url);
		switch ($mime) {
			case 'image/jpeg' :
			case 'image/jpg' :
				$ext = 'jpg';
				break;
			case 'image/gif' :
				$ext = 'gif';
				break;
			case 'image/png' :
				$ext = 'png';
				break;
			default :
				return false;
		}

		$basename = sha1($url);
		$this->parser;
		$raw_bytes = $this->parser->read($url);
		if (empty($raw_bytes)) {
			return;
		}

		$site = elgg_get_site_entity();
		$tmp = new \ElggFile();
		$tmp->owner_guid = $site->guid;
		$tmp->setFilename("scraper_cache/tmp/$basename.$ext");
		$tmp->open('write');
		$tmp->write($raw_bytes);
		$tmp->close();
		unset($raw_bytes);

		$threshold = elgg_get_plugin_setting('cache_thumb_size_lower_threshold', 'hypeScraper', 100);
		$imagesize = getimagesize($tmp->getFilenameOnFilestore());
		if (!$imagesize || $imagesize[0] < $threshold) {
			$tmp->delete();
			return false;
		}

		$image = new \ElggFile();
		$image->owner_guid = $site->guid;
		$image->setFilename("scraper_cache/thumbs/$basename.jpg");

		$image->natural_width = $imagesize[0];
		$image->natural_height = $imagesize[1];

		$size = elgg_get_plugin_setting('cache_thumb_size', 'hypeScraper', 500);
		$thumb = get_resized_image_from_existing_file($tmp->getFilenameOnFilestore(), $size, $size);

		$image->open('write');
		$image->write($thumb);
		$image->close();

		unset($thumb);
		$tmp->delete();

		return $image;
	}

	/**
	 * Returns default config for http requests
	 * @return array
	 */
	public static function getHttpClientConfig() {
		$config = [
			'headers' => [
				'User-Agent' => implode(' ', [
					'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12)',
					'Gecko/20101026',
					'Firefox/3.6.12'
				]),
			],
			'allow_redirects' => [
				'max' => 10,
				'strict' => true,
				'referer' => true,
				'protocols' => ['http', 'https']
			],
			'timeout' => 5,
			'connect_timeout' => 5,
			'verify' => false,
			'cookies' => true,
		];

		return elgg_trigger_plugin_hook('http:config', 'framework:scraper', null, $config);
	}

}
