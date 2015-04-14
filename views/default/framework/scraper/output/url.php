<?php

/**
 * Output a link with a parse meta title
 * 
 * @uses $vars['text'] Title of the URL
 * @uses $vars['href'] URL to profile
 * @deprecated since 1.2
 */

if (isset($vars['class'])) {
	$vars['class'] = "scraper-url {$vars['class']}";
} else {
	$vars['class'] = "scraper-url";
}

echo elgg_view('output/url', $vars);
