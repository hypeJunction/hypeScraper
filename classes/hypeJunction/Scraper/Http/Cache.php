<?php

namespace hypeJunction\Scraper\Http;

class Cache {

	private $values = array();

	public function get($key = '') {
		if ($key && isset($this->values[$key])) {
			return $this->values[$key];
		}
		return;
	}

	public function invalidate($key = '') {
		if ($key && isset($this->values[$key])) {
			unset($this->values[$key]);
			return true;
		}
		return false;
	}

	public function put($key = '', $value = null) {
		if (!$key || !is_scalar($key)) {
			return false;
		}
		$this->values[$key] = $value;
		return true;
	}

}
