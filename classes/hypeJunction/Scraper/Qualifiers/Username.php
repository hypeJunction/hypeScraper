<?php

namespace hypeJunction\Scraper\Qualifiers;

use ElggUser;

class Username extends Qualifier {

	const BASE_URI = "search?search_type=user&q=%s";
	const CONCRETE_CLASS = __CLASS__;

	/**
	 * User that owns the username
	 * @var ElggUser
	 */
	protected $user;

	function __construct($qualifier = '', $baseUri = '') {
		error_log($qualifier);
		parent::__construct($qualifier, $baseUri);
		$this->user = $this->getUser();
	}

	/**
	 * Get user by username
	 * @return ElggUser|false
	 */
	public function getUser() {
		return get_user_by_username($this->getAttribute());
	}

	public function getAttribute() {
		return str_replace('@', '', $this->qualifier);
	}

	public function getQualifier() {
		if (substr($this->qualifier, 0, 1) != '@') {
			return "@{$this->qualifier}";
		}
		return $this->qualifier;
	}

	public function getHref() {
		if (!$this->user) {
			return parent::getHref();
		}
		return $this->user->getURL();
	}

	/**
	 * Get user icon URL
	 * @return string
	 */
	public function getIcon() {
		return ($this->user) ? $this->user->getURL('tiny') : false;
	}

	public function output(array $vars = array()) {
		$params = array(
			'text' => $this->getQualifier(),
			'href' => $this->getHref(),
			'data-qualifier' => 'username',
			'data-value' => $this->getAttribute(),
		);
		if ($this->user) {
			$params['data-guid'] = $this->user->guid;
			$params['data-name'] = $this->user->name;
			$params['data-icon'] = $this->getIcon();
		}

		$vars = array_merge($vars, $params);
		return elgg_view('framework/scraper/output/username', $vars);
	}

}
