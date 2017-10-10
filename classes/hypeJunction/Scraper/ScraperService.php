<?php

namespace hypeJunction\Scraper;

use Elgg\Cache\Pool;
use ElggFile;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
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
	 * @param Pool   $cache  Cache
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
	 *
	 * @return array|void
	 */
	public function get($url) {
		if (!$this->parser->isValidUrl($url)) {
			elgg_log(__METHOD__ . ' expects a valid URL: ' . $url);

			return null;
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

		return $row ? unserialize($row->data) : null;
	}

	/**
	 * Find scraped resourced
	 *
	 * @param string $query Query to match against
	 *
	 * @return string[]
	 */
	public function find($query) {

		$query = sanitize_string($query);

		$dbprefix = elgg_get_config('dbprefix');
		$rows = get_data("
			SELECT url FROM {$dbprefix}scraper_data
			WHERE url LIKE '%$query%'
		");

		return array_map(function ($elem) {
			return $elem->url;
		}, $rows);
	}

	/**
	 * Parse and scrape a URL
	 *
	 * @param string $url     URL
	 * @param bool   $flush   Flush existing URL data
	 * @param bool   $recurse Recurse into subresources
	 *
	 * @return array|false
	 */
	public function parse($url, $flush = false, $recurse = true) {

		elgg_log("Attempting to parse URL: $url");

		if (!$this->parser->isValidUrl($url)) {
			elgg_log("Invalid URL: $url");

			return false;
		}

		if ($flush) {
			$this->delete($url);
		} else {
			$data = $this->get($url);
			if (isset($data)) {
				return $data;
			}
		}

		try {
			$response = $this->parser->request($url);
		} catch (\Exception $ex) {
			elgg_log($ex->getMessage(), 'ERROR');
			$data = false;
		}

		if (!$response instanceof \GuzzleHttp\Psr7\Response || $response->getStatusCode() != 200) {
			$this->save($url, false);

			return false;
		}

		$post_max_size = elgg_get_ini_setting_in_bytes('post_max_size');
		$upload_max_filesize = elgg_get_ini_setting_in_bytes('upload_max_filesize');
		$max_upload = $upload_max_filesize > $post_max_size ? $post_max_size : $upload_max_filesize;

		$content_length = $response->getHeader('Content-Length');
		if (is_array($content_length)) {
			$content_length = array_shift($content_length);
		}

		if ((int) $content_length > $max_upload) {
			// Large images eat up memory
			$this->save($url, false);

			return false;
		}

		try {
			$data = $this->parser->parse($url);
		} catch (\Exception $ex) {
			// There is an issue with the DOM markup and we are unable to
			// scrape the data. Giving up.
			elgg_log($ex->getMessage(), 'ERROR');
			$data = false;
		}

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
				if ($recurse) {
					$data = $this->parseThumbs($data);
				}
				break;
		}

		$data = elgg_trigger_plugin_hook('parse', 'framework:scraper', [
			'url' => $url,
		], $data);

		elgg_log("URL data parsed: " . print_r($data, true));

		$this->save($url, $data);

		return $data;
	}

	/**
	 * Save URL data to the database
	 *
	 * @param string $url  URL
	 * @param array  $data Data
	 *
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
	 *
	 * @return bool
	 */
	public function delete($url) {

		$parse = $this->parse($url);

		if (!empty($parse['assets'])) {
			foreach ($parse['assets'] as $asset) {
				if (!empty($asset['filename'])) {
					$file = new ElggFile();
					$file->owner_guid = elgg_get_site_entity()->guid;
					$file->setFilename($asset['filename']);
					$file->delete();
				}
			}
		}

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
	 *
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

		//@Todo - looks like we need some way to check this in core
		// instead of elgg_save_resized_image() OOMing
		if (!$this->hasMemoryToResize($tmp->getFilenameOnFilestore())) {
			$tmp->delete();

			return false;
		}

		$lower_threashold = elgg_get_plugin_setting('cache_thumb_size_lower_threshold', 'hypeScraper', 100);
		$upper_threshold = elgg_get_plugin_setting('cache_thumb_size_upper_threshold', 'hypeScraper', 1500);
		$imagesize = getimagesize($tmp->getFilenameOnFilestore());
		if (!$imagesize || $imagesize[0] < $lower_threashold || $imagesize[0] > $upper_threshold) {
			$tmp->delete();

			return false;
		}

		$image = new \ElggFile();
		$image->owner_guid = $site->guid;
		$image->setFilename("scraper_cache/thumbs/$basename.jpg");

		$image->natural_width = $imagesize[0];
		$image->natural_height = $imagesize[1];

		$image->open('write');
		$image->close();

		$size = elgg_get_plugin_setting('cache_thumb_size', 'hypeScraper', 500);
		$thumb = elgg_save_resized_image($tmp->getFilenameOnFilestore(), $image->getFilenameOnFilestore(), [
			'w' => $size,
			'h' => $size,
			'upscale' => false,
			'square' => false,
		]);

		unset($thumb);
		$tmp->delete();

		return $image;
	}

	/**
	 * Parse thumbnails from scraped data
	 *
	 * @param array $data Data
	 *
	 * @return array
	 */
	public function parseThumbs(array $data = []) {
		$assets = [];
		$thumbnails = (array) elgg_extract('thumbnails', $data, []);
		$icons = (array) elgg_extract('icons', $data, []);

		// Try 3 images and choose the one with highest dimensions
		$thumbnails = array_filter(array_unique(array_merge($thumbnails, $icons)));
		$thumbs_parsed = 0;
		foreach ($thumbnails as $thumbnail) {
			$thumbnail = elgg_normalize_url($thumbnail);
			$asset = $this->parse($thumbnail, false, false);

			if ($asset) {
				$thumbs_parsed++;
				$assets[] = $asset;
			}

			if ($thumbs_parsed == 5) {
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

		return $data;
	}

	/**
	 * Returns default config for http requests
	 * @return array
	 */
	public static function getHttpClientConfig() {
		$jar = new CookieJar();
		$jar->setCookie(new SetCookie([
			'Name' => 'Elgg',
			'Value' => elgg_get_session()->getId(),
			'Domain' => parse_url(elgg_get_site_url(), PHP_URL_HOST),
		]));

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
			'cookies' => $jar,
		];

		return elgg_trigger_plugin_hook('http:config', 'framework:scraper', null, $config);
	}

	/**
	 * Do we estimate that we have enough memory available to resize an image?
	 *
	 * @param string $source - the source path of the file
	 *
	 * @return bool
	 */
	public function hasMemoryToResize($source) {
		$imginfo = getimagesize($source);
		$requiredMemory1 = ceil($imginfo[0] * $imginfo[1] * 5.35);
		$requiredMemory2 = ceil($imginfo[0] * $imginfo[1] * ($imginfo['bits'] / 8) * $imginfo['channels'] * 2.5);
		$requiredMemory = (int) max($requiredMemory1, $requiredMemory2);

		$mem_avail = elgg_get_ini_setting_in_bytes('memory_limit');
		$mem_used = memory_get_usage();

		$mem_avail = $mem_avail - $mem_used - 20971520; // 20 MB buffer, yeah arbitrary but necessary

		return $mem_avail > $requiredMemory;
	}
}
