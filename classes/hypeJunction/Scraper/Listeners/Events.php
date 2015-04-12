<?php

namespace hypeJunction\Scraper\Listeners;

use hypeJunction\Scraper\Config\Config;
use hypeJunction\Scraper\Services\Router;
use hypeJunction\Scraper\Services\Upgrades;

/**
 * Events service
 */
class Events {

	/**
	 * Scripts to require on system upgrade
	 * @var array
	 */
	private $upgradeScripts = array(
		'activate.php',
	);
	
	private $config;
	private $router;
	private $model;
	private $upgrades;
	private $queue;

	/**
	 * Constructor
	 * @param Config   $config   Config
	 * @param Router   $router   Router
	 * @param Upgrades $upgrades Upgrades
	 */
	public function __construct(Config $config, Router $router, Upgrades $upgrades) {
		$this->config = $config;
		$this->router = $router;
		$this->upgrades = $upgrades;
	}

	/**
	 * Run tasks on system init
	 * @return void
	 */
	public function init() {
		elgg_register_event_handler('upgrade', 'system', array($this, 'upgrade'));
	}

	/**
	 * Runs upgrade scripts
	 * @return bool
	 */
	protected function upgrade() {
		if (elgg_is_admin_logged_in()) {
			foreach ($this->upgradeScripts as $script) {
				$path = $this->plugin->getPath() . $script;
				if (file_exists($path)) {
					require_once $path;
				}
			}
			$this->upgrades->runUpgrades();
		}
		return true;
	}

}
