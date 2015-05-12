<?php

if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
	elgg_load_js('scraper/play');
} else {
	elgg_require_js('scraper/play');
}

$href = elgg_extract('href', $vars);
$handle = elgg_extract('handle', $vars);
if (!$handle) {
	$handle = elgg_get_site_entity()->guid;
}

$parse = elgg_extract('parse', $vars, elgg_is_logged_in());

$data = hypeScraper()->resources->get($href, $handle, $parse);
if (empty($data) || empty($data['url'])) {
	echo elgg_view('output/url', array(
		'href' => $href,
	));
	return;
}

$meta = (object) $data;

$icon_url = hypeScraper()->resources->getThumbUrl($href, $handle);

$module = elgg_extract('module', $vars, 'scraper-card');
$classes = array(elgg_extract('class', $vars));

$classes[] = 'scraper-card-block';
$classes[] = 'clearfix';

if ($meta->provider_name) {
	$classes[] = 'scraper-card-' . preg_replace('/[^a-z0-9\-]/i', '-', strtolower($meta->provider_name));
}

if ($meta->type == 'image' || $meta->type == 'photo') {
	$vars['src'] = $icon_url;
	$vars['class'] = 'sraper-card-photo';
	$img = elgg_view('output/img', $vars);
	$body = elgg_view('output/url', array(
		'href' => $href,
		'text' => $img,
	));
} else {
	$body .= '<h3>' . $meta->title . '</h3>';
	$body .= elgg_view('output/url', array(
		'text' => parse_url($meta->url, PHP_URL_HOST),
		'href' => $meta->url,
		'class' => 'scraper-card-link',
	));
	$body .= elgg_view('output/longtext', array(
		'value' => elgg_get_excerpt($meta->description),
		'class' => 'scraper-card-description'
	));

	$classes[] = 'scraper-card-has-icon';
	$icon = elgg_view('output/url', array(
		'class' => 'scraper-card-icon-bg',
		'text' => '<span></span>',
		'style' => 'background-image:url(' . $icon_url . ')',
		'href' => $meta->url
	));

	if ($meta->html && ($meta->type == 'rich' || $meta->type == 'video')) {
		$icon .= elgg_format_element('div', array(
			'class' => 'scraper-play-button',
			'data-href' => hypeScraper()->router->normalize('json', array(
				'url' => $href,
				'handle' => $handle,
			))
		));
	}
}

$body = elgg_view_image_block($icon, $body, array(
	'class' => implode(' ', array_filter($classes))
		));


if ($module) {

	$class = ($meta->type) ? " scraper-card-$meta->type" : '';
	echo elgg_view_module($module, false, $body, array(
		'class' => $class,
	));
} else {
	echo $body;
}

