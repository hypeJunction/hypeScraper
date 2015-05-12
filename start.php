<?php

/**
 * A tool for extracting, interpreting, caching and embedding remote resources.
 * 
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */
try {
	require_once __DIR__ . '/lib/autoloader.php';
	hypeScraper()->boot();
} catch (Exception $ex) {
	$msg = "hypeScraper Error: {$ex->getMessage()})";
	elgg_log($msg, 'ERROR');
	if (elgg_is_admin_logged_in()) {
		register_error($msg);
	}
	hypeScraper()->deactivate();
}