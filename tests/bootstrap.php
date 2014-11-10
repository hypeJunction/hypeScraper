<?php

date_default_timezone_set('Europe/Berlin');

global $CONFIG;
$CONFIG = (object) array(
	'dbprefix' => 'elgg_',
	'boot_complete' => false,
	'wwwroot' => 'http://example.com/',
);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/engine/load.php";
require_once dirname(__DIR__) . "/vendors/autoload.php";