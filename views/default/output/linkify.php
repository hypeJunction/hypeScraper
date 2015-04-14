<?php

/**
 * View that can be used to linkify URLs, hashtags, usernames and emails
 * This can be used as an alternative to output/longtext, if you don't need
 * all the tag filtering and paragraphs
 * 
 * @uses $vars['value']           STR  Text to linkify
 * @uses $vars['parse_urls']      BOOL Disable URL linkification
 * @uses $vars['parse_hashtags']  BOOL Disable hashtag linkification
 * @uses $vars['parse_emails']    BOOL Disable email linkification
 * @uses $vars['parse_usernames'] BOOL Disable usernames linkification
 */

$value = elgg_extract('value', $vars, '');

if (elgg_extract('parse_urls', $vars, true)) {
	$value = hypeScraper()->linkify->urls($value);
}

if (elgg_extract('parse_hashtags', $vars, true)) {
	$value = hypeScraper()->linkify->hashtags($value);
}

if (elgg_extract('parse_emails', $vars, true)) {
	$value = hypeScraper()->linkify->emails($value);
}

if (elgg_extract('parse_usernames', $vars, true)) {
	$value = hypeScraper()->linkify->usernames($value);
}

echo $value;