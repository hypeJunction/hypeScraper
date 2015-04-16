<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

// Elgg 1.8 autoloader does not recurse into folders
//elgg_register_classes(dirname(dirname(__FILE__)) . '/classes/');

/**
 * Plugin DI Container
 * @staticvar \hypeJunction\Scraper\Di\PluginContainer $provider
 * @return \hypeJunction\Scraper\Di\PluginContainer
 */
function hypeScraper() {
	static $provider;
	if (null === $provider) {
		$provider = \hypeJunction\Scraper\Di\PluginContainer::create();
	}
	return $provider;
}