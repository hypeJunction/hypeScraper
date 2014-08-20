<?php

namespace hypeJunction\Scraper;

class Validator {

	/**
	 * Validate URL format and accessibility
	 * @param string $url
	 * @return boolean
	 */
	public static function isValidURL($url = '') {
		if (!$url || !is_string($url) || !filter_var($url, FILTER_VALIDATE_URL) || !($fp = curl_init($url))) {
			return false;
		}
		return true;
	}

	/**
	 * Checks URL headers to determine whether the content type is image
	 * @param string $url
	 */
	public static function isImage($url = '') {
		if (!self::isValidURL($url)) {
			return false;
		}

		$headers = get_headers($url, 1);
		if (is_string($headers['Content-Type']) && substr($headers['Content-Type'], 0, 6) == 'image/') {
			return true;
		}
	}
}
