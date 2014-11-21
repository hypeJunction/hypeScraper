<?php

/**
 * Render an embed view for a scraped link
 * 
 * @uses $vars['src']    Originally embedded URL
 * @uses $vars['meta']   Scraped URL metadata
 * @link Embedder::getEmbedView()
 */

namespace hypeJunction\Scraper;

$meta = elgg_extract('meta', $vars);

if (!$meta instanceof MetaHandler) {
	echo elgg_view('framework/scraper/embed/default', $vars);
	return;
}

$module = elgg_extract('module', $vars, false);
$classes = array(elgg_extract('class', $vars));

$classes[] = 'embedder-block';
$classes[] = 'clearfix';

if ($meta->provider_name) {
	$classes[] = 'embedder-' . preg_replace('/[^a-z0-9\-]/i', '-', strtolower($meta->provider_name));
}

if ($meta->html) {
	$classes[] = 'flex-video';
	$body = str_replace('http://', '//', $meta->html);
} else {
	if (!$module) {
		$body .= '<h3>' . $meta->title . '</h3>';
		$body .= elgg_view('output/url', array(
			'text' => parse_url($meta->url, PHP_URL_HOST),
			'href' => $meta->url,
			'class' => 'embedder-link',
		));
	}
	$body .= elgg_view('output/longtext', array(
		'value' => elgg_get_excerpt($meta->description),
		'class' => 'embedder-description'
	));
	if ($meta->oembed_url && $meta->type == 'photo') {
		$icon_url = $meta->canonical;
	} else if ($meta->thumbnail_url) {
		$icon_url = $meta->thumbnail_url;
	}

	if ($icon_url) {
		$classes[] = 'embedder-has-icon';
		$icon = elgg_view('output/url', array(
			'class' => 'embedder-icon-bg',
			'text' => '<span></span>',
			'style' => 'background-image:url(' . $icon_url . ')',
			'href' => $meta->url
		));
	}
}

$body = elgg_view_image_block($icon, $body, array(
	'class' => implode(' ', array_filter($classes))
));

if ($module) {
	$footer = elgg_view('output/url', array(
		'href' => ($meta->canonical) ? $meta->canonical : $meta->url,
		'target' => '_blank',
	));

	$class = ($meta->type) ? "elgg-module-embedder-$meta->type" : '';

	echo elgg_view_module($module, $meta->title, $body, array(
		'class' => $class,
		'footer' => $footer,
	));
} else {
	echo elgg_view_module('embed', false, $body);
}