<?php

namespace hypeJunction\Scraper\Services;

class ThumbServer {

	private $dbConfig;
	private $dbPrefix;
	private $dbLink;
	private $url;
	private $handle;
	private $dir_guid;
	private $ts;
	private $hmac;

	public function __construct(\Elgg\Database\Config $dbConfig, $dbPrefix = 'elgg_') {
		$this->dbConfig = $dbConfig;
		$this->dbPrefix = $dbPrefix;

		$this->url = $this->get('url');
		$this->handle = $this->get('handle');
		$this->dir_guid = $this->get('dir_guid');
		$this->ts = $this->get('ts');
		$this->hmac = $this->get('mac');
	}

	/**
	 * Serves an icon
	 * Terminates the script and sends headers on error
	 * @return void
	 */
	public function serve() {

		if (headers_sent()) {
			return;
		}

		if (!$this->url || !$this->handle || !$this->dir_guid || !$this->hmac) {
			header("HTTP/1.1 400 Bad request");
			exit;
		}

		$etag = md5($this->url . $this->handle . $this->ts);
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == "\"$etag\"") {
			header("HTTP/1.1 304 Not Modified");
			exit;
		}

		$this->openDbLink();
		$values = $this->getDatalistValue(array('dataroot', '__site_secret__'));
		$this->closeDbLink();

		if (empty($values)) {
			header("HTTP/1.1 404 Not Found");
			exit;
		}

		$data_root = $values['dataroot'];
		$key = $values['__site_secret__'];

		$hmac = hash_hmac('sha256', $this->url . $this->handle, $key);

		if ($this->hmac != $hmac) {
			header("HTTP/1.1 403 Forbidden");
			exit;
		}

		$locator = new \Elgg\EntityDirLocator($this->dir_guid);
		$filename = $data_root . $locator->getPath() . 'scraper_cache/thumbs/' . md5($this->url) . '.' . $this->handle . '.jpg';

		error_log($this->url);
		error_log($filename);

		if (!file_exists($filename)) {
			header("HTTP/1.1 404 Not Found");
			exit;
		}

		$filesize = filesize($filename);

		header("Content-type: image/jpeg");
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+6 months")), true);
		header("Pragma: public");
		header("Cache-Control: public");
		header("Content-Length: $filesize");
		header("ETag: \"$etag\"");
		readfile($filename);
		exit;
	}

	/**
	 * Returns DB config
	 * @return array
	 */
	protected function getDbConfig() {
		if ($this->dbConfig->isDatabaseSplit()) {
			return $this->dbConfig->getConnectionConfig(\Elgg\Database\Config::READ);
		}
		return $this->dbConfig->getConnectionConfig(\Elgg\Database\Config::READ_WRITE);
	}

	/**
	 * Connects to DB
	 * @return void
	 */
	protected function openDbLink() {
		$dbConfig = $this->getDbConfig();
		$this->dbLink = @mysql_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], true);
	}

	/**
	 * Closes DB connection
	 * @return void
	 */
	protected function closeDbLink() {
		if ($this->dbLink) {
			mysql_close($this->dbLink);
		}
	}

	/**
	 * Retreive values from datalists table
	 * 
	 * @param array $names Parameter names to retreive
	 * @return array
	 */
	protected function getDatalistValue(array $names = array()) {

		if (!$this->dbLink) {
			return array();
		}

		$dbConfig = $this->getDbConfig();
		if (!mysql_select_db($dbConfig['database'], $this->dbLink)) {
			return array();
		}

		if (empty($names)) {
			return array();
		}
		$names_in = array();
		foreach ($names as $name) {
			$name = mysql_real_escape_string($name);
			$names_in[] = "'$name'";
		}
		$names_in = implode(',', $names_in);

		$values = array();

		$q = "SELECT name, value
				FROM {$this->dbPrefix}datalists
				WHERE name IN ({$names_in})";

		$result = mysql_query($q, $this->dbLink);
		if ($result) {
			$row = mysql_fetch_object($result);
			while ($row) {
				$values[$row->name] = $row->value;
				$row = mysql_fetch_object($result);
			}
		}

		return $values;
	}

	/**
	 * Returns request query value
	 *
	 * @param string $name    Query name
	 * @param mixed  $default Default value
	 * @return mixed
	 */
	protected function get($name, $default = null) {
		if (isset($_GET[$name])) {
			return $_GET[$name];
		}
		return $default;
	}

}
