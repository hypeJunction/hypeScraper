<?php

namespace hypeJunction\Scraper\Qualifiers;

class Hashtag extends Qualifier {

	const BASE_URI = "search?search_type=tags&q=%s";
	const CONCRETE_CLASS = __CLASS__;
	
	public function getAttribute() {
		return str_replace('#', '', $this->qualifier);
	}

	public function getQualifier() {
		if (substr($this->qualifier, 0, 1) != '#') {
			return "#{$this->qualifier}";
		}
		return $this->qualifier;
	}

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
