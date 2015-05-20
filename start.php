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
	register_error($ex->getMessage());
}
