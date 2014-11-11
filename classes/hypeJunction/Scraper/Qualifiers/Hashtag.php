<?php

namespace hypeJunction\Scraper\Qualifiers;

/**
 * Helper class for linkifying hashtags
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Hashtag extends Qualifier {

	const BASE_URI = "search?search_type=tags&q=%s";
	const CONCRETE_CLASS = __CLASS__;
	
	/**
	 * Get a tag without #
	 * @return string
	 */
	public function getAttribute() {
		return str_replace('#', '', $this->qualifier);
	}

	/**
	 * Get a full tag
	 * @return string
	 */
	public function getQualifier() {
		if (substr($this->qualifier, 0, 1) != '#') {
			return "#{$this->qualifier}";
		}
		return $this->qualifier;
	}

	/**
	 * Get an <a> tag
	 * 
	 * @param array $vars Vars to pass to the view
	 * @return string
	 */
	public function output(array $vars = array()) {
		$params = array(
			'text' => $this->getQualifier(),
			'href' => $this->getHref(),
			'data-qualifier' => 'hashtag',
			'data-value' => $this->getAttribute(),
		);
		$vars = array_merge($vars, $params);
		return elgg_view('framework/scraper/output/hashtag', $vars);
	}

}
