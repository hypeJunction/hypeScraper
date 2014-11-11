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
		foreach ($array as $key => $value) {
			$meta->$key = $value;
		}
		return $meta;
	}

}
