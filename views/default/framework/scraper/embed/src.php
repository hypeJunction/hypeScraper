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

$classes = array(elgg_extract('class', $vars));

if ($meta->provider_name) {
	$classes[] = 'embed-' . preg_replace('/[^a-z0-9\-]/i', '-', strtolower($meta->provider_name));
}

if ($meta->html) {
	$body = str_replace('http://', '//', $meta->html);
} else {
	if ($meta->oembed_url && $meta->type == 'photo') {
		$icon = elgg_view('output/url', array(
			'text' => elgg_view('output/img', array(
				'src' => str_replace('http://', '//', $meta->oembed_url),
				'alt' => $meta->title,
			)),
			'href' => $meta->canonical,
			'class' => 'embedder-photo',
			'target' => '_blank',
		));
	}
	if ($meta->thumbnail_url) {
		$icon = elgg_view('output/img', array(
			'src' => $meta->thumbnail_url,
			'width' => 100,
			'class' => 'embedder-thumbnail',
		));
	}
}

if (!$body && $meta->description) {
	$body .= elgg_view('output/longtext', array(
		'value' => elgg_get_excerpt($meta->description),
		'class' => 'embedder-description'
	));
}

if ($icon) {
	$body = elgg_view_image_block($icon, $body, array(
		'class' => 'embedder-image-block',
	));
}

if ($module = elgg_extract('module', $vars, 'embed')) {
	$footer = elgg_view('output/url', array(
		'href' => ($meta->canonical) ? $meta->canonical : $meta->url,
		'target' => '_blank',
	));

	if (!$body) {
		$body = $footer;
		$footer = false;
	}

	$classes[] = ($meta->type) ? "elgg-module-embed-$meta->type" : '';

	$output = elgg_view_module($module, $meta->title, $body, array(
		'class' => implode(' ', array_filter($classes)),
		'footer' => $footer,
	));
} else {
	$output = $body;
}

echo $output;