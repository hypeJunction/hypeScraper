<?php

namespace hypeJunction\Scraper\Qualifiers;

/**
 * Helper class for linking email addresses
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class EmailAddress extends Qualifier {

	const BASE_URI = "mailto:%s";
	const VIEW = 'framework/scraper/output/email';

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
			'data-qualifier' => 'email-address',
			'data-value' => $this->getAttribute(),
		);
		return array_merge($vars, $params);
	}

}
