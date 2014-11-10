<?php

/**
 * Render a view for a URL
 * 
 * @uses $vars['class'] Class for a formatted tag
 * @uses $vars['src']   URL
 */

namespace hypeJunction\Scraper;

$url = elgg_extract('src', $vars, '');
unset($vars['src']);

$vars['value'] = $url;

$classes = array('scraper-default-url');
if ($class = elgg_extract('class', $vars)) {
	$classes[] = $class;
}
$vars['class'] = implode(' ', $classes);

echo elgg_view('output/url', $vars);
