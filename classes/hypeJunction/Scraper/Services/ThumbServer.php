<?php

namespace hypeJunction\Scraper\Services;

class ThumbServer {

	const READ = 'read';
	const WRITE = 'write';
	const READ_WRITE = 'readwrite';

	private $config;
	private $dbPrefix;
	private $dbLink;
	private $url;
	private $handle;
	private $dir;
	private $dir_tc;
	private $ts;
	private $hmac;

	public function __construct($config) {
		$this->config = $config;
		$this->dbPrefix = $config->dbprefix;

		$this->url = $this->get('url');
		$this->handle = $this->get('handle');
		$this->dir = $this->get('dir');
		$this->dir_tc = $this->get('dir_tc');
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

		if (!$this->url || !$this->handle || !$this->dir || !$this->hmac) {
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

		$hmac = hash_hmac('sha256', $this->url . $this->handle . $this->dir . $this->dir_tc, $key);

		if ($this->hmac != $hmac) {
			header("HTTP/1.1 403 Forbidden");
			exit;
		}

		//$locator = new \Elgg\EntityDirLocator($this->dir);
		//$filename = $data_root . $locator->getPath() . 'scraper_cache/thumbs/' . md5($this->url) . '.' . $this->handle . '.jpg';

		$md = md5($this->url);
		$time_created = date('Y/m/d', $this->dir_tc);
		$filename = "{$data_root}{$time_created}/{$this->dir}/scraper_cache/thumbs/{$md}.{$this->handle}.jpg";

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
		if ($this->isDatabaseSplit()) {
			return $this->getConnectionConfig(self::READ);
		}
		return $this->getConnectionConfig(self::READ_WRITE);
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

	/**
	 * Are the read and write connections separate?
	 *
	 * @return bool
	 */
	public function isDatabaseSplit() {
		if (isset($this->config->db) && isset($this->config->db['split'])) {
			return $this->config->db['split'];
		}
		// this was the recommend structure from Elgg 1.0 to 1.8
		if (isset($this->config->db) && isset($this->config->db->split)) {
			return $this->config->db->split;
		}
		return false;
	}

	/**
	 * Get the connection configuration
	 *
	 * The parameters are in an array like this:
	 * array(
	 * 	'host' => 'xxx',
	 *  'user' => 'xxx',
	 *  'password' => 'xxx',
	 *  'database' => 'xxx',
	 * )
	 *
	 * @param int $type The connection type: READ, WRITE, READ_WRITE
	 * @return array
	 */
	public function getConnectionConfig($type = self::READ_WRITE) {
		$config = array();
		switch ($type) {
			case self::READ:
			case self::WRITE:
				$config = $this->getParticularConnectionConfig($type);
				break;
			default:
				$config = $this->getGeneralConnectionConfig();
				break;
		}
		return $config;
	}

	/**
	 * Get the read/write database connection information
	 *
	 * @return array
	 */
	protected function getGeneralConnectionConfig() {
		return array(
			'host' => $this->config->dbhost,
			'user' => $this->config->dbuser,
			'password' => $this->config->dbpass,
			'database' => $this->config->dbname,
		);
	}

	/**
	 * Get connection information for reading or writing
	 *
	 * @param string $type Connection type: 'write' or 'read'
	 * @return array
	 */
	protected function getParticularConnectionConfig($type) {
		if (is_object($this->config->db[$type])) {
			// old style single connection (Elgg < 1.9)
			$config = array(
				'host' => $this->config->db[$type]->dbhost,
				'user' => $this->config->db[$type]->dbuser,
				'password' => $this->config->db[$type]->dbpass,
				'database' => $this->config->db[$type]->dbname,
			);
		} else if (array_key_exists('dbhost', $this->config->db[$type])) {
			// new style single connection
			$config = array(
				'host' => $this->config->db[$type]['dbhost'],
				'user' => $this->config->db[$type]['dbuser'],
				'password' => $this->config->db[$type]['dbpass'],
				'database' => $this->config->db[$type]['dbname'],
			);
		} else if (is_object(current($this->config->db[$type]))) {
			// old style multiple connections
			$index = array_rand($this->config->db[$type]);
			$config = array(
				'host' => $this->config->db[$type][$index]->dbhost,
				'user' => $this->config->db[$type][$index]->dbuser,
				'password' => $this->config->db[$type][$index]->dbpass,
				'database' => $this->config->db[$type][$index]->dbname,
			);
		} else {
			// new style multiple connections
			$index = array_rand($this->config->db[$type]);
			$config = array(
				'host' => $this->config->db[$type][$index]['dbhost'],
				'user' => $this->config->db[$type][$index]['dbuser'],
				'password' => $this->config->db[$type][$index]['dbpass'],
				'database' => $this->config->db[$type][$index]['dbname'],
			);
		}
		return $config;
	}

}
