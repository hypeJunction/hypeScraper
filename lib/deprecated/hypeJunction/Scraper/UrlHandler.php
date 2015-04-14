<?php

namespace hypeJunction\Scraper;

use ElggEntity;
use Exception;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use UFCOE\Elgg\Url as UrlSniffer;

/**
 * URL handler for injection
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
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
	 * Guzzle client
	 * @var Client 
	 */
	protected $client;

	/**
	 * Cache HTTP request results
	 * @staticvar array 
	 */
	static $cache;

	/**
	 * Construct a new object
	 * 
	 * @param string $url URL
	 */
	function __construct($url = '') {
		$this->setURL($url);
	}

	/**
	 * Set URL
	 * 
	 * @param string $url URL
	 * @return void
	 */
	public function setURL($url = '') {
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
	 * Set client
	 * 
	 * @param Client $client Guzzle client
	 * @return void
	 */
	public function setClient(Client $client) {
		$this->client = $client;
	}

	/**
	 * Get guzzle client
	 * @return Client
	 */
	public function getClient() {
		if ($this->client instanceof Client) {
			return $this->client;
		}
		$client = new Client();
		$client->setDefaultOption('verify', false);
		return $client;
	}

	/**
	 * Get hasher service
	 * @return Hasher
	 */
	public function getHasher() {
		return new Hasher($this->url);
	}

	/**
	 * Get parser service
	 * @return Parser
	 */
	public function getParser() {
		return new Parser($this->url);
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
		$response = $this->getHead();
		if ($response instanceof Response) {
			return $response->isSuccessful();
		}
		return false;
	}

	/**
	 * Get mime type of the URL content
	 * @return string|false
	 */
	public function getContentType() {
		$response = $this->getHead();
		if ($response instanceof Response) {
			return $response->getContentType();
		}
		return false;
	}

	/**
	 * Get contents of the page
	 * @return string
	 */
	public function getContent() {
		$response = $this->getBody();
		if ($response instanceof Response) {
			return $response->getBody(true);
		}
		return '';
	}

	/**
	 * Get useful meta information
	 * 
	 * @param boolean $enable_cache Retrieve/store meta from cache
	 * @return MetaHandler
	 */
	public function getMeta($enable_cache = true) {
		if (isset(self::$cache[$this->url]['meta'])) {
			$meta = self::$cache[$this->url]['meta'];
		} else {
			if (!$enable_cache) {
				$meta = $this->getParser()->getMetadata();
			} else {
				$hasher = $this->getHasher();
				$meta = $hasher->getMetadata();
				if (!$meta) {
					$meta = $this->getParser()->getMetadata();
					$hasher->setMetadata($meta);
				}
			}
			self::$cache[$this->url]['meta'] = $meta;
		}

		return MetaHandler::fromArray($meta);
	}

	/**
	 * Determine if the URL is in site with relation to the $siteUrl
	 * 
	 * @param string $siteUrl URL of the site, defaults to current site url
	 * @return boolean
	 */
	public function isInSite($siteUrl = null) {
		$this->analysis = $this->analyze($siteUrl);
		return (isset($this->analysis['in_site']) && $this->analysis['in_site']);
	}

	/**
	 * Retrieve a guid from the URL
	 * 
	 * @param string $siteUrl URL of the site, default to current site url
	 * @return int GUID or false
	 */
	public function getGuid($siteUrl = null) {
		$this->analysis = $this->analyze($siteUrl);
		return (isset($this->analysis['guid'])) ? $this->analysis['guid'] : 0;
	}

	/**
	 * Get an entity from a GUID if one is contained within the in site URL
	 * 
	 * @param string $siteUrl URL of the site, defaults to current site url
	 * @return ElggEntity|false
	 */
	public function getEntity($siteUrl = null) {
		$guid = $this->getGuid($siteUrl);
		return ($guid) ? get_entity($guid) : false;
	}

	/**
	 * Sniff URL to see if contains and entity GUID
	 * 
	 * @param string $siteUrl URL of the site, defaults to current site url
	 * @return array
	 */
	public function analyze($siteUrl = null) {
		$sniffer = new UrlSniffer($siteUrl);
		return $sniffer->analyze($this->url);
	}

	/**
	 * Get head of the HTTP request
	 * @return Response|false
	 */
	public function getHead() {
		if (!isset(self::$cache[$this->url]['head'])) {
			$head = false;
			try {
				$head = $this->getClient()->head($this->url)->send();
			} catch (Exception $e) {
				
			}
			self::$cache[$this->url]['head'] = $head;
		}
		return self::$cache[$this->url]['head'];
	}

	/**
	 * Get body of the HTTP request
	 * @return Response|false
	 */
	public function getBody() {
		if (!isset(self::$cache[$this->url]['body'])) {
			$body = false;
			try {
				$body = $this->getClient()->get($this->url)->send();
			} catch (Exception $e) {
				
			}
			self::$cache[$this->url]['body'] = $body;
		}
		return self::$cache[$this->url]['body'];
	}

}
