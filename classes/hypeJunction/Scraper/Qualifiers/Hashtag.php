<?php

namespace hypeJunction\Scraper\Qualifiers;

/**
 * Helper class for linking hashtags
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Hashtag extends Qualifier {

	const BASE_URI = "search?search_type=tags&q=%s";
	const VIEW = 'framework/scraper/output/hashtag';
	
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
	 * Prepare view $vars
	 * 
	 * @param array $vars Vars to pass by default
	 * @return array
	 */
	public function prepareVars(array $vars = array()) {
		$params = array(
			'text' => $this->getQualifier(),
			'href' => $this->getHref(),
			'data-qualifier' => 'hashtag',
			'data-value' => $this->getAttribute(),
		);

		return array_merge($vars, $params);
	}

}
