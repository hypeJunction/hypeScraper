<?php

/**
 * URL Class for dependency injection
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */

namespace hypeJunction\Scraper;

use ElggEntity;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use UFCOE\Elgg\Url as UrlSniffer;

class UrlHandler {

	/**
	 * URL
	 * @var string 
	 */
	protected $url;

	/**
	 * Results of URL analysis
	 * @var array 
	 */
	protected $analysis;
	
	/**
	 * Cache HTTP request results
	 * @staticvar array 
	 */
	static $cache;

	/**
	 * Construct a new object
	 * @param string $url URL
	 */
	function __construct($url = '') {
		$this->url = $url;
	}

	/**
	 * Get URL
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Determine if URL is valid
	 * @return boolean
	 */
	public function isValid() {
		if (!$this->url || !is_string($this->url) || !filter_var($this->url, FILTER_VALIDATE_URL)) {
			return false;
		}
		return true;
	}

	/**
	 * Determine if URL is an image file
	 * @return boolean
	 */
	public function isImageFile() {
		if (!$this->isValid()) {
			return false;
		}

		$mime = $this->getContentType();
		if ($mime) {
			list($simple) = explode('/', $mime);
			return ($simple == 'image');
		}

		return false;
	}

	/**
	 * Check if URL is reachable by making an HTTP request to retrieve header information
	 * @return boolean
	 */
	public function isReachable() {
		$response = $this->requestHead(new Client());
		if ($response instanceof Response) {
			return $response->isSuccessful();
		}
		return false;
	}

	/**
	 * Get useful meta information
	 * @return MetaHandler
	 */
	public function getMeta() {
		return Parser::getMeta($this->url);
	}

	/**
	 * Determine if the URL is in site with relation to the $siteUrl
	 * 
	 * @param string $siteUrl URL of the site, defaults to current site url
	 * @return boolean
	 */
	public function isInSite($siteUrl = null) {
		if (!isset($this->analysis)) {
			$this->analysis = $this->analyze($siteUrl);
		}
		return (isset($this->analysis['in_site']) && $this->analysis['in_site']);
	}

	/**
	 * Sniff URL to see if contains and entity GUID
	 * 
	 * @param string $siteUrl URL of the site, defaults to current site url
	 * @return void
	 */
	protected function analyze($siteUrl = null) {
		$sniffer = new UrlSniffer($siteUrl);
		return $sniffer->analyze($this->url);
	}

	/**
	 * Get an entity from a GUID if one is contained within the in site URL
	 * 
	 * @param string $siteUrl URL of the site, defaults to current site url
	 * @return ElggEntity|false
	 */
	public function getEntity($siteUrl = null) {
		if (!isset($this->analysis)) {
			$this->analysis = $this->analyze($siteUrl);
		}
		$guid = (isset($this->analysis['guid'])) ? $this->analysis['guid'] : 0;
		return get_entity($guid);
	}

	/**
	 * Get mime type of the URL content
	 * @return string|false
	 */
	public function getContentType() {
		$response = $this->requestHead(new Client());
		if ($response instanceof Response) {
			return $response->getContentType();
		}
		return false;
	}

	/**
	 * Request headers from URL
	 * 
	 * @param Client $client Guzzle client
	 * @return Response|false
	 */
	protected function requestHead(Client $client) {
		if (!isset(self::$cache[$this->url])) {
			try {
				$request = $client->head($this->url);
				$response = $request->send();
			} catch (BadResponseException $e) {
				$response = false;
			}

			self::$cache[$this->url] = $response;
		}
		return self::$cache[$this->url];
	}

}
