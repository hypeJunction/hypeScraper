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
		'pagehandler_id' => 'url',
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


}
