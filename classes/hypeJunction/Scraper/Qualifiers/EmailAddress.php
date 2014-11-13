<?php

namespace hypeJunction\Scraper\Qualifiers;

/**
 * Helper class for linkifying email addresses
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class EmailAddress extends Qualifier {

	const BASE_URI = "mailto:%s";
	const VIEW = 'framework/scraper/output/email';

	/**
	 * Get email address
	 * @return string
	 */
	public function getAttribute() {
		return $this->qualifier;
	}

	/**
	 * Get normalized email address
	 * @return string
	 */
	public function getQualifier() {
		return $this->qualifier;
	}

	/**
	 * Get an <a> tag
	 * 
	 * @param array $vars Vars to pass to the view
	 * @return string|array HTML or array of vars
	 */
	public function output(array $vars = array()) {

		$params = array(
			'text' => $this->getQualifier(),
			'href' => $this->getHref(),
			'data-qualifier' => 'email-address',
			'data-value' => $this->getAttribute(),
		);
		$vars = array_merge($vars, $params);

		if (!is_callable('elgg_view')) {
			return $vars;
		}
		return call_user_func('elgg_view', self::VIEW, $vars);
	}

}
