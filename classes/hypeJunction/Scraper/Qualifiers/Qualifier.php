<?php

namespace hypeJunction\Scraper\Qualifiers;

/**
 * Qualifier
 * Base class for working with hashtag, url, username and email entity Qualifiers
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Qualifier {

	const BASE_URI = "%s";
	const VIEW = 'output/url';
	
	const TYPE_HASHTAG = 'Hashtag';
	const TYPE_URL = 'Url';
	const TYPE_USERNAME = 'Username';
	const TYPE_EMAIL = 'EmailAddress';
	
	/**
	 * Descriptor
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
	 * Construct a new qualifier from type
	 * 
	 * @param string $type      Hashtag, Url, EmailAddress or Username
	 * @param string $qualifier Qualifier
	 * @param string $baseUri   Base URI
	 * @return Qualifier
	 */
	public static function constructFromType($type, $qualifier = '', $baseUri = '') {
		$class = __NAMESPACE__ . '\\' . $type;
		if (class_exists($class)) {
			return new $class($qualifier, $baseUri);
		}
		return new Qualifier($qualifier, $baseUri);
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
	public function getQualifier() {
		return $this->qualifier;
	}

	/**
	 * Get an attribute, e.g. a tag without # or username without @
	 * @return string Short qualifier/attribute
	 */
	public function getAttribute() {
		return $this->qualifier;
	}

	/**
	 * Prepare view $vars
	 * 
	 * @param array $vars Vars to pass by default
	 * @return array
	 */
	public function prepareVars(array $vars = array()) {
		$params = array(
			'text' => $this->getQualifier(),
			'href' => $this->getHref()
		);
		return array_merge($vars, $params);
	}

	/**
	 * Linkify and output a view
	 * 
	 * @param array $vars Vars to pass to the view
	 * @return string HTML
	 */
	public function output(array $vars = array()) {
		if (!is_callable('elgg_view')) {
			$text = $this->getQualifier();
			$href = $this->getHref();
			return "<a href=\"$href\">$text</a>";
		}
		
		$vars = $this->prepareVars($vars);
		return call_user_func('elgg_view', self::VIEW, $vars);
	}

}
