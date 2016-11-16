<?php

use hypeJunction\Scraper\Router;
use hypeJunction\Scraper\Views;

/**
 * A tool for extracting, interpreting, caching and embedding remote resources.
 * 
 * @author Ismayil Khayredinov <info@hypejunction.com>
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', function() {

	elgg_register_page_handler('scraper', [Router::class, 'serveScraperPages']);

	elgg_register_plugin_hook_handler('format:src', 'embed', [Views::class, 'viewCard']);
	elgg_register_plugin_hook_handler('extract:meta', 'all', [Views::class, 'getCard']);
	elgg_register_plugin_hook_handler('extract:qualifiers', 'all', [Views::class, 'extractTokens']);
	elgg_register_plugin_hook_handler('link:qualifiers', 'all', [Views::class, 'linkTokens']);
	elgg_register_plugin_hook_handler('view', 'output/longtext', [Views::class, 'linkifyLongtext']);

	elgg_extend_view('elgg.css', 'framework/scraper/stylesheet.css');
	elgg_extend_view('elgg.js', 'framework/scraper/player.js');
	
	// Bookmark previews
	if (elgg_is_active_plugin('bookmarks')) {
		elgg_register_plugin_hook_handler('view_vars', 'river/elements/layout', [Views::class, 'addBookmarkRiverPreview']);
		elgg_register_plugin_hook_handler('view_vars', 'object/elements/full', [Views::class, 'addBookmarkProfilePreview']);
	}

	// Basis XSS protecteion
	elgg_register_plugin_hook_handler('parse', 'framework:scraper', [Views::class, 'cleanEmbedHTML']);

	// Upgrades
	elgg_register_action('upgrade/scraper/move_to_db', __DIR__ . '/actions/upgrade/scraper/move_to_db.php', 'admin');
});

elgg_register_event_handler('upgrade', 'system', function() {
	if (!elgg_is_admin_logged_in()) {
		return;
	}
	require __DIR__ . '/lib/upgrades.php';
});
