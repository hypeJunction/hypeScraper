<?php

/**
 * Output an email link
 * 
 * @uses $vars['text'] Email address
 * @uses $vars['href'] Mailto href
 * @deprecated since 1.2
 */

if (isset($vars['class'])) {
	$vars['class'] = "scraper-email {$vars['class']}";
} else {
	$vars['class'] = "scraper-email";
}

echo elgg_view('output/url', $vars);
