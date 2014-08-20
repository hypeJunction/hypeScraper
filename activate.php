<?php

// Create a new table that will hold long urls with their ids
$prefix = elgg_get_config('dbprefix');
$tables = get_db_tables();

if (!in_array("{$prefix}url_meta_cache", $tables)) {
	set_time_limit(0);

	run_sql_script(__DIR__ . '/sql/create_table.sql');
}
