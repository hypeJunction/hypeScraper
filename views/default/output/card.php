<?php

$href = elgg_extract('href', $vars);

$flush = elgg_extract('flush', $vars) && elgg_is_admin_logged_in();

$data = hypeapps_scrape($href, false, $flush);
if (!$data) {
	return;
}

if (empty($data) || empty($data['url'])) {
	return;
}

$meta = (object) $data;

$icon_url = elgg_extract('thumbnail_url', $data);
if (!$icon_url) {
	$icon_url = elgg_get_simplecache_url('framework/scraper/placeholder.png');
}

$module = elgg_extract('module', $vars, 'scraper-card');
$classes = array(elgg_extract('class', $vars));

$classes[] = 'scraper-card-block';
$classes[] = 'clearfix';

if ($meta->provider_name) {
	$classes[] = 'scraper-card-' . preg_replace('/[^a-z0-9\-]/i', '-', strtolower($meta->provider_name));
}

if (($meta->type == 'image' || $meta->type == 'photo') && $icon_url) {
	$vars['src'] = $icon_url;
	$vars['class'] = 'sraper-card-photo';
	$img = elgg_view('output/img', $vars);
	$body = elgg_view('output/url', array(
		'href' => $href,
		'text' => $img,
	));
} else {
	$body .= elgg_view_menu('scraper:card', [
		'href' => $href,
		'class' => 'elgg-menu-hz',
	]);
	
	$title = elgg_view('output/url', [
		'text' => $meta->title,
		'href' => $meta->url,
		'target' => '_blank',
	]);
	
	$body .= '<h3>' . $title . '</h3>';
	$body .= elgg_view('output/url', array(
		'text' => parse_url($meta->url, PHP_URL_HOST),
		'href' => $meta->url,
		'class' => 'scraper-card-link',
	));
	$body .= elgg_view('output/longtext', array(
		'value' => elgg_get_excerpt($meta->description),
		'class' => 'scraper-card-description'
	));

	if ($icon_url) {
		$classes[] = 'scraper-card-has-icon';
		$icon = elgg_view('output/url', array(
			'class' => 'scraper-card-icon-bg',
			'text' => '<span></span>',
			'style' => 'background-image:url(' . $icon_url . ')',
			'href' => $meta->url
		));
	}

	if ($meta->html && ($meta->type == 'rich' || $meta->type == 'video')) {
		$icon .= elgg_format_element('div', [
			'class' => 'scraper-play-button',
			'data-href' => elgg_http_add_url_query_elements(elgg_normalize_url('scraper/json'), array(
				'url' => $href,
				'm' => elgg_build_hmac($href)->getToken(),
			)),
		], elgg_view_icon('play'));
	}
}

$body = elgg_view_image_block($icon, $body, array(
	'class' => implode(' ', array_filter($classes))
));

if ($module) {
	$class = ($meta->type) ? " scraper-card-content-$meta->type" : '';
	echo elgg_view_module($module, false, $body, array(
		'class' => $class,
	));
} else {
	echo $body;
}

