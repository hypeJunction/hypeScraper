<?php

namespace hypeJunction\Scraper;

use Hashids\Hashids;

/**
 * Assigns unique hashes to URLs
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Hasher {

	static $cache;
	protected $id;
	protected $hash;
	protected $url;
	protected $meta = array();
	protected $time_created;

	/**
	 * Constructor
	 * 
	 * @param string $url URL
	 */
	public function __construct($url = '') {
		$this->dbprefix = elgg_get_config('dbprefix');

		$this->time_created = time();
		$this->setUrl($url);
		if (Validator::isValidURL($url)) {
			$this->load();
		}
	}

	/**
	 * Load data from static or DB cache
	 * @return Hasher
	 */
	public function load() {
		if (isset(self::$cache[$this->url])) {
			$this->id = self::$cache[$this->url]['id'];
			$this->hash = self::$cache[$this->url]['hash'];
			$this->meta = self::$cache[$this->url]['meta'];
			$this->time_created = self::$cache[$this->url]['time_created'];
		} else {
			$query = "SELECT * FROM {$this->dbprefix}url_meta_cache
					WHERE long_url = '{$this->url}'";
			$data = get_data($query);

			if ($data) {
				$this->id = $data[0]->id;
				$this->hash = $data[0]->hash;
				$this->meta = json_decode($data[0]->meta, true);
				$this->time_created = $data[0]->time_created;
				self::$cache[$this->url] = array(
					'id' => $this->id,
					'hash' => $this->hash,
					'meta' => $this->meta,
					'time_created' > $this->time_created,
				);
			}
		}
		return $this;
	}

	/**
	 * Sanitize and set URL
	 * 
	 * @param string $url URL
	 * @return Hasher
	 */
	public function setUrl($url = '') {
		$this->url = sanitize_string($url);
		return $this;
	}

	/**
	 * Set generated hash
	 * 
	 * @param string $hash Hash
	 * @return Hasher
	 */
	public function setHash($hash = '') {
		if ($hash) {
			$this->hash = $hash;
			self::$cache[$this->url]['hash'] = $hash;
		}
		return $this;
	}

	/**
	 * Set metadata and update database records
	 * 
	 * @param array $meta Metadata
	 * @return Hasher
	 */
	public function setMetadata(array $meta = array()) {
		if ($this->meta !== $meta) {
			$this->meta = $meta;
			self::$cache[$this->url]['meta'] = $meta;
			if ($this->hash) {
				$this->save();
			}
		}
		return $this;
	}

	/**
	 * Get database ID
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get unique hash associated with this URL
	 * @return string|false
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * Get metadata
	 * @return array|null
	 */
	public function getMetadata() {
		return $this->meta;
	}

	/**
	 * Get created time
	 * @return int|null
	 */
	public function getTimeCreated() {
		return $this->time_created;
	}

	/**
	 * Create unqiue hash and store it in the database
	 * @return string Hash
	 */
	public function save() {

		$url = sanitize_string($this->url);
		$hash = sanitize_string($this->hash);
		$time_created = sanitize_int($this->time_created);

		$meta = (!is_string($this->meta)) ? json_encode($this->meta) : $this->meta;
		$meta = sanitize_string($meta);

		if (!$this->id) {
			$query = "INSERT INTO {$this->dbprefix}url_meta_cache (long_url, hash, meta, time_created)
					VALUES ('{$url}','{$hash}','{$meta}',{$time_created})
						ON DUPLICATE KEY UPDATE long_url='{$url}',meta='{$meta}'";

			$id = insert_data($query);
			$this->id = $id;
			$hashids = new Hashids(get_site_secret());
			$hash = $hashids->encode($id, $this->time_created);

			$query = "UPDATE LOW_PRIORITY {$this->dbprefix}url_meta_cache
					SET hash = '{$hash}' WHERE id = $id";

			update_data($query);
		} else {
			$query = "UPDATE {$this->dbprefix}url_meta_cache SET meta='{$meta}'
						WHERE id='{$this->id}'";
		}

		self::$cache[$this->url] = array(
			'id' => $this->id,
			'hash' => $this->hash,
			'meta' => $this->meta,
			'time_created' > $this->time_created,
		);

		return $hash;
	}

	/**
	 * Hash a URL
	 * 
	 * @param string $url  URL
	 * @param array  $meta Metadata describing URL to store in the DB
	 * @return string Hash
	 */
	public static function hash($url, array $meta = array()) {
		$hasher = new Hasher($url);
		$hasher->setMetadata($meta);
		return $hasher->save();
	}

	/**
	 * Get a unique hash that is associated with this URL
	 * 
	 * @param string $url URL
	 * @return string|false
	 */
	public static function getHashFromURL($url) {
		$hasher = new Hasher($url);
		return $hasher->getHash();
	}

	/**
	 * Get cached URL meta
	 * 
	 * @param string $url URL
	 * @return array|boolean
	 */
	public static function getMetaFromURL($url) {

		$hasher = new Hasher($url);
		return $hasher->meta;
	}

	/**
	 * Get URL from its hash
	 * 
	 * @param string $hash Hash
	 * @return string|boolean
	 */
	public static function getURLFromHash($hash) {

		if (empty($hash)) {
			return false;
		}

		$hash = sanitize_string($hash);

		if (isset(self::$cache)) {
			foreach (self::$cache as $url => $cache) {
				if ($cache['hash'] == $hash) {
					return $url;
				}
			}
		}

		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT long_url FROM {$dbprefix}url_meta_cache
					WHERE hash = '{$hash}'";
		$short = get_data($query);

		if ($short && count($short)) {
			$url = $short[0]->long_url;
			return $url;
		}

		return false;
	}

}
