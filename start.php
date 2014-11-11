<?php

/**
 * A tool for extracting, interpreting, caching and embedding remote resources.
 * 
 * @package HypeJunction
 * @subpackage Scraper
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

namespace hypeJunction\Scraper;

const PLUGIN_ID = 'hypeScraper';
const PAGEHANDLER = 'url';

require_once __DIR__ . '/vendors/autoload.php';

require_once __DIR__ . '/lib/page_handlers.php';
require_once __DIR__ . '/lib/hooks.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * Initialize the plugin
 * @return void
 */
function init() {

	elgg_register_viewtype('embed');
	elgg_register_viewtype_fallback('embed');
	
	elgg_register_page_handler(PAGEHANDLER, __NAMESPACE__ . '\\page_handler');

	elgg_register_plugin_hook_handler('format:src', 'embed', __NAMESPACE__ . '\\format_embed_view');

	elgg_register_plugin_hook_handler('extract:meta', 'all', __NAMESPACE__ . '\\get_embed_metatags');
	elgg_register_plugin_hook_handler('extract:qualifiers', 'all', __NAMESPACE__ . '\\extract_qualifiers');
	elgg_register_plugin_hook_handler('link:qualifiers', 'all', __NAMESPACE__ . '\\link_qualifiers');

	elgg_extend_view('css/elgg', 'css/framework/scraper/css');

	//elgg_register_plugin_hook_handler('unit_test', 'system', __NAMESPACE__ . '\\unit_test');
}
