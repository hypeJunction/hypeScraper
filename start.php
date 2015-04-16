<?php

/**
 * A tool for extracting, interpreting, caching and embedding remote resources.
 * 
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

require_once __DIR__ . '/lib/autoloader.php';

elgg_register_event_handler('init', 'system', function() {

	hypeScraper()->events->init();
	hypeScraper()->hooks->init();
	hypeScraper()->router->init();

	elgg_extend_view('css/elgg', 'css/framework/scraper/stylesheet.css');

});
