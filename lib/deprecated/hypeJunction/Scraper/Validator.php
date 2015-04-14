<?php

namespace hypeJunction\Scraper;

/**
 * Validates URLs
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Validator {

	/**
	 * Injectable URL object
	 * @var UrlHandler 
	 */
	protected $url;

	/**
	 * Validator constructor
	 * 
	 * @param string $url URL string
	 */
	function __construct($url = '') {
		$this->url = new UrlHandler($url);
	}

	/**
	 * Validate URL format and accessibility
	 * 
	 * @param string  $url  URL to validate
	 * @param boolean $ping Test if URL is reachable
	 * @return boolean
	 */
	public static function isValidURL($url = '', $ping = false) {
		$validator = new Validator($url);
		if (!$validator->url->isValid()) {
			return false;
		}
		if ($ping && !$validator->url->isReachable($url)) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if URL is an image file
	 * 
	 * @param string $url URL to check
	 * @return boolean
	 */
	public static function isImage($url = '') {
		$validator = new Validator($url);
		return $validator->url->isImageFile();
	}

}
