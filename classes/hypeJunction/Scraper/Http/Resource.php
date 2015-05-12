<?php

namespace hypeJunction\Scraper\Http;

use Exception;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

class Resource {

	private $client;
	private $cache;

	/**
	 * Constructor
	 *
	 * @param Client $client
	 * @param Cache  $cache
	 */
	public function __construct(Client $client, Cache $cache) {
		$this->client = $client;
		$this->cache = $cache;
	}

	/**
	 * Check if URL exists and is reachable by making an HTTP request to retrieve header information
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return boolean
	 */
	public function exists($url = '', array $options = array()) {
		$response = $this->head($url, $options);
		if ($response instanceof Response) {
			return $response->isSuccessful();
		}
		return false;
	}

	/**
	 * Returns head of the resource
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return Response|false
	 */
	public function head($url = '', array $options = array()) {
		if (!$url || !is_string($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}
		$head = $this->cache->get($url);
		if (is_null($head)) {
			try {
				$head = $this->client->head($url, $options)->send();
			} catch (Exception $e) {
				$head = false;
				elgg_log($e->getMessage(), 'ERROR');
			}
			$this->cache->put($url, $head);
		}
		return $head;
	}

	/**
	 * Get contents of the page
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return string
	 */
	public function read($url = '', $options = array()) {
		$body = '';
		if (!$this->exists($url)) {
			return $body;
		}
		try {
			$response = $this->client->get($url, $options)->send();
			$body = $response->getBody(true);
		} catch (Exception $ex) {
			elgg_log($ex->getMessage(), 'ERROR');
		}
		return $body;
	}

	/**
	 * Checks if resource is an html page
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return boolean
	 */
	public function isHTML($url = '', array $options = array()) {
		$mime = $this->getContentType($url, $options);
		return strpos($mime, 'text/html') !== false;
	}

	/**
	 * Checks if resource is JSON
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return boolean
	 */
	public function isJSON($url = '', array $options = array()) {
		$mime = $this->getContentType($url, $options);
		return strpos($mime, 'json') !== false;
	}

	/**
	 * Checks if resource is XML
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return boolean
	 */
	public function isXML($url = '', array $options = array()) {
		$mime = $this->getContentType($url, $options);
		return strpos($mime, 'xml') !== false;
	}

	/**
	 * Checks if resource is an image
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return boolean
	 */
	public function isImage($url = '', array $options = array()) {
		$mime = $this->getContentType($url, $options);
		if ($mime) {
			list($simple, ) = explode('/', $mime);
			return ($simple == 'image');
		}

		return false;
	}

	/**
	 * Get mime type of the URL content
	 *
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return string|false
	 */
	public function getContentType($url = '', array $options = array()) {
		$response = $this->head($url, $options);
		if ($response instanceof Response) {
			return $response->getContentType();
		}
		return false;
	}

	/**
	 * Returns HTML contents of the page
	 * 
	 * @param string $url     URL of the resource
	 * @param array  $options HTTP request options
	 * @return string
	 */
	public function html($url = '', $options = array()) {
		if (!$this->isHTML($url, $options)) {
			return '';
		}
		return $this->read($url, $options);
	}
	
}
