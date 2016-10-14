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
echo hypeapps_linkify_tokens($value, $vars);