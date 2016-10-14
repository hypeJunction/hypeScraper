<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_input('select', [
	'name' => 'params[linkify]',
	'value' => $entity->linkify,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('scraper:settings:linkify'),
	'help' => elgg_echo('scraper:settings:linkify:help'),
]);

if (elgg_is_active_plugin('bookmarks')) {
	echo elgg_view_input('select', [
		'name' => 'params[bookmarks]',
		'value' => $entity->bookmarks,
		'options_values' => [
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		],
		'label' => elgg_echo('scraper:settings:bookmarks'),
		'help' => elgg_echo('scraper:settings:bookmarks:help'),
	]);
}