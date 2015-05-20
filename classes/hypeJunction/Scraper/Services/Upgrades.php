<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Config;
use hypeJunction\Scraper\Models\Resources;

class Upgrades {

	private $config;
	private $model;
	
	/**
	 * Constructor
	 * @param Config   $config   Config
	 */
	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * Returns an array of upgrade scripts
	 * @return array
	 */
	private function getUpgrades() {
		return array(
		);
	}

	/**
	 * Runs pending upgrades
	 * @return void
	 */
	public function runUpgrades() {
		$site = elgg_get_site_entity();
		$upgrades = $this->getUpgrades();
		foreach ($upgrades as $upgrade) {
			$upgradename = "hypeScraper_$upgrade";
			if (get_private_setting($site->guid, $upgradename)) {
				continue;
			}
			if (is_callable(array($this, $upgrade))) {
				call_user_func(array($this, $upgrade));
				set_private_setting($site->guid, $upgradename, time());
			}
		}
	}
}
