<?php

/**
 * Output a username with link to user profile
 * 
 * @uses $vars['text'] Username
 * @uses $vars['href'] URL to profile
 * @deprecated since 1.2
 */

if (isset($vars['class'])) {
	$vars['class'] = "scraper-username {$vars['class']}";
} else {
	$vars['class'] = "scraper-username";
}

echo elgg_view('output/url', $vars);