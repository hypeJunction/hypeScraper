<?php

namespace hypeJunction\Scraper\Di;

use Elgg\Di\DiContainer;
use hypeJunction\Scraper\Config\Config;
use hypeJunction\Scraper\Controllers\Actions;
use hypeJunction\Scraper\Listeners\Events;
use hypeJunction\Scraper\Listeners\PluginHooks;
use hypeJunction\Scraper\Models\Model;
use hypeJunction\Scraper\Services\Navigation;
use hypeJunction\Scraper\Services\Router;
use hypeJunction\Scraper\Services\Upgrades;

/**
 * Scraper service provider
 *
 * @property-read Config      $config
 * @property-read Events      $events
 * @property-read PluginHooks $hooks
 * @property-read Router      $router
 * @property-read Actions     $actions
 * @property-read Navigation  $navigation
 * @property-read Model       $model
 * @property-read Upgrades    $upgrades
 */
final class PluginContainer extends DiContainer {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		$this->setFactory('config', '\hypeJunction\Scraper\Config\Config::factory');
		
		$this->setFactory('events', function (PluginContainer $c) {
			return new Events($c->config, $c->router, $c->upgrades);
		});

		$this->setFactory('hooks', function (PluginContainer $c) {
			return new PluginHooks($c->config, $c->router);
		});
		
		$this->setFactory('router', function (PluginContainer $c) {
			return new Router($c->config);
		});

		$this->setFactory('upgrades', function (PluginContainer $c) {
			return new Upgrades($c->config);
		});

	}

	/**
	 * Creates a new  ServiceProvider instance
	 * @return PluginContainer
	 */
	public static function create() {
		return new PluginContainer();
	}

}
