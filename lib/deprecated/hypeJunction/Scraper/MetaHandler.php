<?php

namespace hypeJunction\Scraper;

/**
 * Handles URL metatags
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class MetaHandler {

	public $type;
	public $url;
	public $canonical;
	public $oembed_url;
	public $thumnail_url;
	public $title;
	public $description;
	public $thumbnails = array();
	public $icons = array();
	public $provider_name;
	public $og_type;
	static $cache;

	/**
	 * Constructor
	 * 
	 * @param string $url URL
	 */
	function __construct($url = '') {
		$this->url = $url;
	}

	/**
	 * Build MetaHandler from array
	 * 
	 * @param array $array Source array (DB cache)
	 * @return MetaHandler
	 */
	public static function fromArray($array = array()) {
		$meta = new MetaHandler();
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$meta->$key = $value;
			}
		}
		return $meta;
	}

	/**
	 * Build metahandler from URL
	 * 
	 * @param string $url URL
	 * @return MetaHandler
	 */
	public static function fromUrl($url = '') {
		$handler = new UrlHandler($url);
		$meta = $handler->getMeta();
		return MetaHandler::fromArray($meta);
	}
	
	/**
	 * Get metatags as array
	 * @return array
	 */
	public function toArray() {
		return json_decode(json_encode($this), true);
	}
}
