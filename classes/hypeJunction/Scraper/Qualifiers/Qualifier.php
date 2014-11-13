<?php

namespace hypeJunction\Scraper\Qualifiers;

/**
 * Abstract Qualifier
 * 
 * @abstract
 * @package    HypeJunction
 * @subpackage Scraper
 */
abstract class Qualifier {

	const BASE_URI = "%s";
	const VIEW = 'output/url';

	/**
	 * Qualifier
	 * @var string 
	 */
	protected $qualifier;

	/**
	 * Base URI
	 * @var string 
	 */
	protected $baseUri;

	/**
	 * Qualifier constructor
	 * 
	 * @param string $qualifier Qualifier
	 * @param string $baseUri   Base URI of a URL usable in sprintf()
	 */
	function __construct($qualifier = '', $baseUri = '') {
		$this->qualifier = trim($qualifier);
		$this->baseUri = ($baseUri) ? : static::BASE_URI;
	}

	/**
	 * Retrieve base URI
	 * @return string
	 */
	public function getBaseUri() {
		return $this->baseUri;
	}

	/**
	 * Get URL
	 * @return string
	 */
	public function getHref() {
		return sprintf($this->getBaseUri(), $this->getAttribute());
	}

	/**
	 * Get a normalized qualifier, e.g. a hashtag with # or username with @
	 * @return string Long qualifier
	 */
	abstract public function getQualifier();

	/**
	 * Get an attribute, e.g. a tag without # or username without @
	 * @return string Short qualifier/attribute
	 */
	abstract public function getAttribute();

	/**
	 * Linkify and output a view
	 * 
	 * @param array $vars Vars to pass to the view
	 * @return string HTML
	 */
	abstract public function output(array $vars = array());
}
