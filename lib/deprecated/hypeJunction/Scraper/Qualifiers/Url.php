<?php

namespace hypeJunction\Scraper\Qualifiers;

use hypeJunction\Scraper\UrlHandler;

/**
 * Helper class for linking URLs
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Url extends Qualifier {

	const VIEW = 'framework/scraper/output/url';

	/**
	 * Url handler
	 * @var UrlHandler
	 */
	protected $url;

	/**
	 * Construct a new helper
	 * 
	 * @param string $qualifier URL string
	 * @param string $baseUri   Base URL for replacement
	 */
	function __construct($qualifier = '', $baseUri = '') {
		parent::__construct($qualifier, $baseUri);
		$this->url = new UrlHandler($qualifier);
	}

	/**
	 * Get normalized URL
	 * @return string
	 */
	public function getAttribute() {
		return $this->url->getUrl();
	}

	/**
	 * Get URL page title
	 * @return string
	 */
	public function getQualifier() {
		$meta = $this->url->getMeta();
		return ($meta->title) ? $meta->title : $this->url->getUrl();
	}

	/**
	 * Get favicon URL
	 * @return string
	 */
	public function getIcon() {
		return "http://g.etfv.co/{$this->getHref()}";
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
			'href' => $this->getHref(),
			'data-qualifier' => 'url',
			'data-value' => $this->getAttribute(),
		);
		if ($this->user) {
			$params['data-icon'] = $this->getIcon();
		}

		return array_merge($vars, $params);
	}

}
