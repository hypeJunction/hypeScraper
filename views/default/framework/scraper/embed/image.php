<?php

/**
 * Render an embed view for an image
 * 
 * @uses $vars['src'] URL of an embedded image
 */

if (isset($vars['module'])) {
	$module = elgg_extract('module', $vars, 'embed');
	unset($vars['module']);
}

$body = elgg_view('output/img', $vars);

if ($module) {
	$footer = elgg_view('output/url', array(
		'href' => elgg_extract('src', $vars),
		'target' => '_blank',
	));
	$output = elgg_view_module($module, '', $body, array(
		'class' => 'elgg-module-embed-image',
		'footer' => $footer,
	));
} else {
	$output = $body;
}

echo $output;

