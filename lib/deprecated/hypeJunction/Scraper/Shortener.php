<?php

namespace hypeJunction\Scraper;

/**
 * Shortens URLs
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Shortener {

	protected $input;
	protected $shorten;

	const REGEX_URL = '@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@';

	/**
	 * Construct a new shortener
	 * 
	 * @param string  $input   HTML string
	 * @param boolean $shorten Enforce shortening of unknown URLs
	 */
	function __construct($input = '', $shorten = false) {
		$this->input = $input;
		$this->shorten = $shorten;
	}

	/**
	 * Rewrite URLs
	 * 
	 * @param string  $input   HTML string
	 * @param boolean $shorten Shorten all URLs
	 * @return string HTML string
	 */
	public static function shorten($input = '', $shorten = false) {
		$rw = new Shortener($input);
		return $rw->shortenURLs();
	}

	/**
	 * Rewrite URLs attributes within <img>,<iframe> tags
	 * @return string Filtered HTML
	 */
	protected function shortenURLs() {
		return preg_replace_callback(self::REGEX_URL, array($this, 'rewriteURL'), $this->input);
	}

	/**
	 * Rewrite URLs with shorter/secure alternative
	 * 
	 * @param array $matches Array of matches
	 * @return string
	 */
	protected function rewriteURL($matches) {

		$url = $matches[1];
		
		if (!Validator::isValidURL($url)) {
			return $url;
		}

		$site_url = elgg_get_site_url();
		$site_url_parts = parse_url($site_url);

		$url_parts = parse_url($url);

		// Do not rewrite urls on own host
		if ($url_parts['host'] == $site_url_parts['host']) {
			return $url;
		}

		return self::getShortURL($url);
	}

	/**
	 * Get a shortened URL
	 * 
	 * @param string $url URL
	 * @return string
	 */
	public static function getShortURL($url) {
		$hash = Hasher::getHashFromURL($url);
		if (!$hash && $this->shorten) {
			$hash = Hasher::hash($url);
		}
		if ($hash) {
			return elgg_normalize_url(implode('/', array(
				PAGEHANDLER,
				$hash
			)));
		}
		return $url;
	}

}
