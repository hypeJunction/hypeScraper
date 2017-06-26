<?php

$title = elgg_echo('admin:scraper:timestamp_images');
$body = elgg_format_element('p', [], elgg_echo('admin:scraper:timestamp_images:help'));
$footer = elgg_view('output/url', [
	'text' => elgg_echo('admin:scraper:hotfix'),
	'href' => 'action/admin/scraper/timestamp_images',
	'is_action' => true,
	'class' => 'elgg-button elgg-button-action',
]);

echo elgg_view_module('info', $title, $body, [
	'footer' => $footer,
]);