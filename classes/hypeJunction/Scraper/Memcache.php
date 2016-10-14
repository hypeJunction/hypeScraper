<?php

namespace hypeJunction\Scraper;

use Elgg\Cache\Pool;

/**
 * @access private
 */
class Memcache implements Pool {

	/**
	 * @var \ElggMemcache
	 */
	private $memcache;
	
	public function __construct() {
		$this->memcache = new \ElggMemcache('scraper_cache');
	}

	public function get($key, callable $callback = null, $default = null) {
		$value = $this->memcache->load($key);
		if (!isset($value)) {
			$value = $default;
		}
		if (is_callable($callback)) {
			return call_user_func($callback, $value);
		}
		return $value;
	}

	public function invalidate($key) {
		$this->memcache->delete($key);
	}

	public function put($key, $value) {
		$this->memcache->save($key, $value, 0);
	}

}
