<?php

namespace hypeJunction\Scraper;

use DOMDocument;

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

	/**
	 * Url
	 * @var UrlHandler 
	 */
	protected $url;

	/**
	 * Meta
	 * @var MetaHandler
	 */
	protected $meta;

	/**
	 * Constructor
	 * 
	 * @param string $url URL string
	 */
	function __construct($url = '') {
		$this->setUrl($url);
	}

	/**
	 * Set url
	 * 
	 * @param string $url URL
	 * @return Parser
	 */
	public function setUrl($url) {
		$this->url = new UrlHandler($url);
		return $this;
	}
	
	/**
	 * Scrape meta tags using the service specified in plugin settings
	 * @return array
	 */
	public function getMetadata() {
		return $this->parseMeta($this->getParsingService());
	}
	
	/**
	 * Scrape meta tags from a URL
	 * 
	 * @param string $url URL to scrape
	 * @return array
	 */
	public static function getMeta($url = '') {
		$parser = new Parser($url);
		return $parser->getMetadata();
	}

	/**
	 * Determine which approach to parsing to use
	 * @return string
	 */
	public function getParsingService() {
		$setting = $this->getPluginSetting('service');
		return $setting ?: self::SERVICE_DOM;
	}

	/**
	 * Parse metatags
	 * 
	 * @param string $service Parsing service
	 * @return array
	 */
	public function parseMeta($service = '') {
		switch ($service) {
			default :
			case self::SERVICE_DOM;
				return $this->dom();

			case self::SERVICE_IFRAMELY :
				return $this->iframely();

			case self::SERVICE_EMBEDLY :
				return $this->embedly();
		}
	}

	/**
	 * Scrape metatags from DOM
	 * @return array
	 */
	public function dom() {

		$url = $this->url->getURL();
		
		$meta['url'] = $url;
		$meta['title'] = parse_url($url, PHP_URL_HOST);
		$meta['description'] = '';

		$html = $this->url->getContent();
		
		$doc = new DOMDocument();
		@$doc->loadHTML($html);

		// Get document title
		$node = $doc->getElementsByTagName('title');
		$title = $node->item(0)->nodeValue;
		if ($title) {
			$meta['title'] = $title;
		}

		// Get oEmbed content and canonical URLs
		$nodes = $doc->getElementsByTagName('link');
		foreach ($nodes as $node) {
			$rel = $node->getAttribute('rel');
			$href = $node->getAttribute('href');

			switch ($rel) {

				case 'icon' :
					$meta['icons'][] = $href;
					break;

				case 'canonical' :
					$meta['canonical'] = $href;
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
			$oembed_handler = new UrlHandler($oembed_endpoint);
			$json = $oembed_handler->getContent();
			if ($json) {
				$oembed_params = json_decode($json, true);
				if ($oembed_params) {
					foreach ($oembed_params as $key => $value) {
						if (!$meta[$key]) {
							$meta[$key] = $value;
						}
						if ($key == 'url') {
							$meta['oembed_url'] = $value;
						}
						if ($key == 'thumbnail_url' && !$meta['oembed_url']) {
							$meta['oembed_url'] = $value;
						}
					}
				}
			}
		}

		if ($title) {
			$meta['title'] = $title;
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
						if ($name && !$meta[$name]) {
							$name = str_replace(':', '_', $name);
							$meta[$name] = $content;
						}
						break;

					case 'title' :
					case 'og:title' :
					case 'twitter:title' :
						if (!$meta['title']) {
							$meta['title'] = $content;
						}
						break;

					case 'description' :
					case 'og:description' :
					case 'twitter:description' :
						if (!$meta['description']) {
							$meta['description'] = $content;
						}
						break;

					case 'keywords' :
						$meta['keywords'] = $content;
						break;

					case 'og:site_name' :
					case 'twitter:site' :
						if (!$meta['provider_name']) {
							$meta['provider_name'] = $content;
						}
						break;

					case 'og:type' :
						$meta['og_type'] = $content;
						break;

					case 'og:image' :
					case 'twitter:image' :
						$meta['thumbnails'][] = $content;
						break;
				}
			}
		}

		if (count($meta['thumbnails'])) {
			// Display a thumbnail parsed from <meta> tags
			$meta['thumbnail_url'] = $meta['thumbnails'][0];
		} else if (count($meta['icons'])) {
			// Display an icon parsed from <link> tags
			$meta['thumbnail_url'] = $meta['icons'][0];
		} else {
			$max_width = 0;
			$nodes = $doc->getElementsByTagName('img');
			foreach ($nodes as $node) {
				$src = $node->getAttribute('src');
				$width = $node->getAttribute('width');
				if (!$meta['thumbnail_url'] || $width > $max_width) {
					$meta['thumbnail_url'] = $src;
					$max_width = $width;
				}
			}
		}

		/**
		 * @todo: figure out what to do with relative URLs in DOM
		 */
		if (!Validator::isImage($meta['thumbnail_url'])) {
			unset($meta['thumbnail_url']);
		}
		
		return $meta;
	}

	/**
	 * Get meta using iframe.ly service
	 * @return array
	 */
	public function iframely() {
		$json = $this->parseJson($this->getIframelyUrl());
		return ($json) ?: array();
	}

	/**
	 * Get endpoint for retrieving iframely meta
	 * @return string
	 */
	public function getIframelyUrl() {
		$apikey = $this->getPluginSetting('iframely_key');
		$endpoint = $this->getPluginSetting('iframely_endpoint');

		return elgg_http_add_url_query_elements($endpoint, array(
			'url' => $this->url->getUrl(),
			'api_key' => $apikey,
			'iframe' => true,
		));
	}
	
	/**
	 * Get meta using embedly service
	 * @return array
	 */
	public function embedly() {
		$json = $this->parseJson($this->getEmbedlyUrl());
		return ($json) ?: array();
	}

	/**
	 * Get endpoint for retrieving embed.ly meta
	 * @return string
	 */
	public function getEmbedlyUrl() {
		$apikey = $this->getPluginSetting('embedly_key');
		$endpoint = $this->getPluginSetting('embedly_endpoint');

		return elgg_http_add_url_query_elements($endpoint, array(
			'url' => $this->url->getUrl(),
			'api_key' => $apikey,
			'iframe' => true,
		));
	}
	
	/**
	 * Parse JSON from the URL
	 * 
	 * @param string  $endpoint Endpoint 
	 * @param boolean $as_array Decode as array
	 * @return boolean
	 */
	protected function parseJson($endpoint = '', $as_array = true) {
		$handler = new UrlHandler($endpoint);
		$json = $handler->getContent($endpoint);
		if ($json) {
			return json_decode($json, $as_array);
		}
		return false;
	}
	
	/**
	 * Get plugin setting
	 * 
	 * @param string $name Setting name
	 * @return mixed
	 */
	protected function getPluginSetting($name) {
		$plugin_id = basename(dirname(dirname(dirname(dirname(__FILE__)))));
		return elgg_get_plugin_setting($name, $plugin_id);
	}
}
