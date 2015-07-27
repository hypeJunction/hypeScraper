<?php

namespace hypeJunction\Scraper\Services;

use Elgg\Database\Config;
use Elgg\EntityDirLocator;
use hypeJunction\Servers\Server;

class ThumbServer extends Server {

	private $dbConfig;
	private $dbPrefix;
	private $url;
	private $handle;
	private $dir_guid;
	private $ts;
	private $hmac;

	public function __construct(Config $dbConfig, $dbPrefix = 'elgg_') {
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

		$locator = new EntityDirLocator($this->dir_guid);
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

}
