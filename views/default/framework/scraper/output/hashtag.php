<?php

/**
 * Output a hashtag link
 * 
 * @uses $vars['text'] Hashtag text
 * @uses $vars['href'] Hashtag destination URL
 * @deprecated since 1.2
 */

if (isset($vars['class'])) {
	$vars['class'] = "scraper-hashtag {$vars['class']}";
} else {
	$vars['class'] = "scraper-hashtag";
}

echo elgg_view('output/url', $vars);
