<?php

if (!is_callable('hypeApps')) {
	throw new Exception("hypeScraper requires hypeApps");
}

$path = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

if (!file_exists("{$path}vendor/autoload.php")) {
	throw new Exception('hypeScraper can not resolve composer dependencies. Run composer install');
}

require_once "{$path}vendor/autoload.php";

/**
 * Plugin container
 * @return \hypeJunction\Scraper\Plugin
 */
function hypeScraper() {
	return \hypeJunction\Scraper\Plugin::factory();
}
