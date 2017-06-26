<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('scraper:settings:linkify'),
	'#help' => elgg_echo('scraper:settings:linkify:help'),
	'name' => 'params[linkify]',
	'value' => $entity->linkify,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
]);

if (elgg_is_active_plugin('bookmarks')) {
	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('scraper:settings:bookmarks'),
		'#help' => elgg_echo('scraper:settings:bookmarks:help'),
		'name' => 'params[bookmarks]',
		'value' => $entity->bookmarks,
		'options_values' => [
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		],
	]);
}

$domains = \hypeJunction\Scraper\Views::getoEmbedDomains();
echo elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('scraper:settings:oembed_domains'),
	'#help' => elgg_echo('scraper:settings:oembed_domains:help'),
	'name' => 'params[oembed_domains]',
	'value' => implode(PHP_EOL, $domains),
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('scraper:settings:preview_type'),
	'name' => 'params[preview_type]',
	'value' => $entity->preview_type ?: 'card',
	'options_values' => [
		'card' => elgg_echo('scraper:settings:preview_type:card'),
		'player' => elgg_echo('scraper:settings:preview_type:player'),
	],
]);