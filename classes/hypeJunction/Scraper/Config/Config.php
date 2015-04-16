<?php

namespace hypeJunction\Scraper\Config;

use ElggPlugin;

/**
 * Config
 */
class Config {

	const PLUGIN_ID = 'hypeScraper';

	private $plugin;
	private $settings;
	private $config = array(
		'legacy_mode' => false,
		'pagehandler_id' => 'url',
		'cache_thumbnails' => true,
		'cache_thumb_size' => 500,
		'cache_thumb_size_lower_threshold' => 100,
		'hashtag_uri' => "search?search_type=tags&q=%s",
		'username_uri' => "profile\/%s",
		'email_uri' => "mailto:%s",
		'linkify_url_titles' => true,
		'defer_loading' => true,
	);

	/**
	 * Constructor
	 * @param ElggPlugin $plugin ElggPlugin
	 */
	public function __construct(ElggPlugin $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * Config factory
	 * @return Config
	 */
	public static function factory() {
		$plugin = elgg_get_plugin_from_id(self::PLUGIN_ID);
		return new Config($plugin);
	}

	/**
	 * Returns all plugin settings
	 * @return array
	 */
	public function all() {
		if (!isset($this->settings)) {
			$this->settings = array_merge($this->config, $this->plugin->getAllSettings());
		}
		return $this->settings;
	}

	/**
	 * Returns a plugin setting
	 *
	 * @param string $name Setting name
	 * @return mixed
	 */
	public function get($name, $default = null) {
		return elgg_extract($name, $this->all(), $default);
	}

	/**
	 * Returns plugin path
	 * @return string
	 */
	public function getPath() {
		return $this->plugin->getPath();
	}

	/**
	 * Returns default config for http requests
	 * @return array
	 */
	public function getHttpClientConfig() {
		$http_config = [
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
			],
			'allow_redirects' => [
				'max' => 3,
				'strict' => true,
				'referer' => true,
				'protocols' => ['http', 'https']
			],
			'timeout' => 5,
			'connect_timeout' => 5,
			'verify' => false,
		];

		return elgg_trigger_plugin_hook('http:config', 'framework:scraper', array('config' => $this), $http_config);
	}

}
