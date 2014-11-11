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
	const CONCRETE_CLASS = __CLASS__;

	public function getAttribute() {
		return $this->qualifier;
	}

	public function getQualifier() {
		return $this->qualifier;
	}

	public function output(array $vars = array()) {
		$params = array(
			'text' => $this->getQualifier(),
			'href' => $this->getHref(),
			'data-qualifier' => 'email-address',
			'data-value' => $this->getAttribute(),
		);
		$vars = array_merge($vars, $params);
		return elgg_view('framework/scraper/output/email', $vars);
	}

}
