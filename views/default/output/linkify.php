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
namespace hypeJunction\Scraper;

$value = elgg_extract('value', $vars, '');

$extractor = new Extractor();

if (elgg_extract('parse_urls', $vars, true)) {
	$value = $extractor->linkifyURLs($value);
}

if (elgg_extract('parse_hashtags', $vars, true)) {
	$value = $extractor->linkifyHashtags($value);
}

if (elgg_extract('parse_emails', $vars, true)) {
	$value = $extractor->linkifyEmails($value);
}

if (elgg_extract('parse_usernames', $vars, true)) {
	$value = $extractor->linkifyUsernames($value);
}

echo $value;