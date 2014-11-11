<?php

namespace hypeJunction\Scraper;

use DOMDocument;
use Exception;
use Guzzle\Http\Client;

/**
 * Parses URL metatags
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Parser {

	const SERVICE_DOM = 'dom_parser';
	const SERVICE_IFRAMELY = 'iframely';
	const SERVICE_EMBEDLY = 'embedly';

	protected $service;
	protected $url;
	protected $meta;
	static $cache;

	/**
	 * Constructor
	 * 
	 * @param string $url URL string
	 */
	function __construct($url = '') {

		$this->url = $url;

		if (isset(self::$cache[$this->url])) {
			$this->meta = self::$cache['url'];
		} else if (Validator::isValidURL($this->url)) {
			$meta = Hasher::getMetaFromURL($url);
			if ($meta) {
				$this->meta = $meta;
			} else {
				$service = elgg_get_plugin_setting('service', PLUGIN_ID);
				switch ($service) {
					default :
						$this->service = self::SERVICE_DOM;
						$this->dom();
						break;

					case self::SERVICE_IFRAMELY :
						$this->service = self::SERVICE_IFRAMELY;
						$this->iframely();
						break;

					case self::SERVICE_EMBEDLY :
						$this->service = self::SERVICE_EMBEDLY;
						$this->embedly();
						break;
				}
				Hasher::hash($this->url, $this->meta);
			}
		}
		
	}

	/**
	 * Scrape meta tags from a URL
	 * 
	 * @param string $url URL to scrape
	 * @return MetaHandler
	 */
	public static function getMeta($url = '') {
		try {
			$parser = new Parser($url);
			return $parser->meta;
		} catch (Exception $ex) {
			elgg_log($ex->getMessage(), 'ERROR');
			return new MetaHandler;
		}
	}

	/**
	 * Scrape metatags by parsing DOM
	 */
	private function dom() {

		if (!$this->meta) {
			$this->meta = new MetaHandler();
		}

		$this->meta->url = $this->url;
		$this->meta->title = parse_url($this->url, PHP_URL_HOST);
		$this->meta->description = '';

		$html = $this->getContents($this->url);

		$doc = new DOMDocument();
		@$doc->loadHTML($html);

		// Get document title
		$node = $doc->getElementsByTagName('title');
		$title = $node->item(0)->nodeValue;
		if ($title) {
			$this->meta->title = $title;
		}

		// Get oEmbed content and canonical URLs
		$nodes = $doc->getElementsByTagName('link');
		foreach ($nodes as $node) {
			$rel = $node->getAttribute('rel');
			$href = $node->getAttribute('href');

			switch ($rel) {

				case 'icon' :
					$this->meta->icons[] = $href;
					break;

				case 'canonical' :
					$this->meta->canonical = $href;
					break;

				case 'alternate' :
					$type = $node->getAttribute('type');
					if ($type == 'application/json+oembed' || $type == 'text/json+oembed') {
						$oembed_endpoint = $href;
					}
					break;
			}
		}

		if ($oembed_endpoint) {
			$json = $this->getContents($oembed_endpoint);
			if ($json) {
				$oembed_params = json_decode($json, true);
				if ($oembed_params) {
					foreach ($oembed_params as $key => $value) {
						if (!$this->meta->$key) {
							$this->meta->$key = $value;
						}
						if ($key == 'url') {
							$this->meta->oembed_url = $value;
						}
						if ($key == 'thumbnail_url' && !$this->meta->oembed_url) {
							$this->meta->oembed_url = $value;
						}
					}
				}
			}
		}

		if ($title) {
			$this->meta->title = $title;
		}

		$nodes = $doc->getElementsByTagName('meta');
		if (!empty($nodes)) {
			foreach ($nodes as $node) {
				$name = $node->getAttribute('name');
				if (!$name) {
					$name = $node->getAttribute('property');
				}
				if (!$name) {
					continue;
				}
				$name = strtolower($name);

				$content = $node->getAttribute('content');

				switch ($name) {

					default :
						if ($name && !$this->meta->$name) {
							$name = str_replace(':', '_', $name);
							$this->meta->$name = $content;
						}
						break;

					case 'title' :
					case 'og:title' :
					case 'twitter:title' :
						if (!$this->meta->title) {
							$this->meta->title = $content;
						}
						break;

					case 'description' :
					case 'og:description' :
					case 'twitter:description' :
						if (!$this->meta->description) {
							$this->meta->description = $content;
						}
						break;

					case 'keywords' :
						$this->meta->keywords = $content;
						break;

					case 'og:site_name' :
					case 'twitter:site' :
						if (!$this->meta->provider_name) {
							$this->meta->provider_name = $content;
						}
						break;

					case 'og:type' :
						$this->meta->og_type = $content;
						break;

					case 'og:image' :
					case 'twitter:image' :
						$this->meta->thumbnails[] = $content;
						break;
				}
			}
		}

		if (count($this->meta->thumbnails)) {
			// Display a thumbnail parsed from <meta> tags
			$this->meta->thumbnail_url = $this->meta->thumbnails[0];
		} else if (count($this->meta->icons)) {
			// Display an icon parsed from <link> tags
			$this->meta->thumbnail_url = $this->meta->icons[0];
		} else {
			$max_width = 0;
			$nodes = $doc->getElementsByTagName('img');
			foreach ($nodes as $node) {
				$src = $node->getAttribute('src');
				$width = $node->getAttribute('width');
				if (!$this->meta->thumbnail_url || $width > $max_width) {
					$this->meta->thumbnail_url = $src;
					$max_width = $width;
				}
			}
		}

		/**
		 * @todo: figure out what to do with relative URLs in DOM
		 */
		if (!Validator::isImage($this->meta->thumbnail_url)) {
			unset($this->meta->thumbnail_url);
		}
	}

	/**
	 * Get contents of a remote page
	 * 
	 * @param string $url URL string
	 * @return string 
	 */
	private function getContents($url = '') {

		if (!Validator::isValidURL($url)) {
			return false;
		}

		$client = new Client();
		$client->setDefaultOption('verify', false);
		$request = $client->createRequest('GET', $url);
		$response = $request->send();
		$result = $response->getBody();
		return $result;
	}

	/**
	 * Get tags using iframely service
	 * @throws Exception
	 */
	private function iframely() {

		$apikey = elgg_get_plugin_setting('iframely_key', PLUGIN_ID);
		$endpoint = elgg_get_plugin_setting('iframely_endpoint', PLUGIN_ID);

		if (!$endpoint) {
			throw new Exception("iframe.ly endpoint is not specified");
		}

		$url = elgg_http_add_url_query_elements($endpoint, array(
			'url' => $this->url,
			'api_key' => $apikey,
			'iframe' => true,
		));

		$json = $this->getContents($url);
		if ($json) {
			$this->meta = json_decode($json);
		}
	}

	/**
	 * Get tags using embedly service
	 * @throws Exception
	 */
	private function embedly() {

		$apikey = elgg_get_plugin_setting('embedly_key', PLUGIN_ID);
		$endpoint = elgg_get_plugin_setting('embedly_endpoint', PLUGIN_ID);

		if (!$endpoint) {
			throw new Exception("embed.ly endpoint is not specified");
		}

		$url = elgg_http_add_url_query_elements($endpoint, array(
			'url' => $this->url,
			'api_key' => $apikey,
			'iframe' => true,
		));

		$json = $this->getContents($url);
		if ($json) {
			$this->meta = json_decode($json);
		}
	}

}
