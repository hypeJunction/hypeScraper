<?php

namespace hypeJunction\Scraper\Qualifiers;

use hypeJunction\Scraper\UrlHandler;

class Url extends Qualifier {

	const CONCRETE_CLASS = __CLASS__;
	
	/**
	 * Url handler
	 * @var UrlHandler
	 */
	protected $url;

	function __construct($qualifier = '', $baseUri = '') {
		parent::__construct($qualifier, $baseUri);
		$this->url = new UrlHandler($qualifier);
	}

	public function getAttribute() {
		return $this->url->getUrl();
	}

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

	public function output(array $vars = array()) {
		$params = array(
			'text' => $this->getQualifier(),
			'href' => $this->getHref(),
			'data-qualifier' => 'url',
			'data-value' => $this->getAttribute(),
		);
		if ($this->user) {
			$params['data-icon'] = $this->getIcon();
		}

		$vars = array_merge($vars, $params);
		return elgg_view('framework/scraper/output/url', $vars);
	}

}
