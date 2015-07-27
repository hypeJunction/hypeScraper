<?php

$base_dir = dirname(dirname(dirname(dirname(__FILE__))));

require_once $base_dir . '/engine/settings.php';
require_once $base_dir . '/vendor/autoload.php';

global $CONFIG;
$server = new \hypeJunction\Scraper\Services\ThumbServer($CONFIG);
$server->serve();