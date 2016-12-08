<?php

namespace hypeJunction\Scraper;

/**
 * @access private
 */
class Linkify extends Extractor {

	// match entire anchor <a></a> so we can exclude it from matches
	const REGEX_MATCH_ANCHOR = "<a[^>]*?>.*?<\/a>";

	// non-greedy match of a single tag name and attributes
	// we need to exclude e.g. hex color codes when matchin hashes
	const REGEX_MATCH_TAG = '<.*?>';

	// we want at least one non space or punctuation character before the match
	const REGEX_CHAR_BACK = '(^|\s|\!|\.|\?|>|\G)+';

	const REGEX_HASHTAG = '(#\w+)';

	const REGEX_URL = '(h?[t|f]??tps*:\/\/[^\s\r\n\t<>"\'\)\(]+)';

	const REGEX_EMAIL = '([\w\-\.]+@[^\s\r\n\t<>"\'\)\(]+\.+[0-9a-z]{2,})';

	const REGEX_USERNAME = '(@[\p{L}\p{Nd}._-]+)';
	
	/**
	 * Linkifies all qualifiers
	 * 
	 * @param string $text Source text
	 * @return string
	 */
	public static function all($text = '') {
		$text = html_entity_decode($text);
		$text = self::emails($text);
		$text = self::usernames($text);
		$text = self::hashtags($text);
		$text = self::urls($text);
		return $text;
	}

	/**
	 * Linkify hashtags that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public static function hashtags($text = '', callable $callback = null) {
		$callback = $callback ? : array(__CLASS__, 'callbackHashtag');
		$regex = '/' . self::REGEX_MATCH_ANCHOR . '|' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK . self::REGEX_HASHTAG . '/i';
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Linkify urls that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public static function urls($text = '', callable $callback = null) {
		$callback = $callback ?: array(__CLASS__, 'callbackUrl');
		$regex = '/' . self::REGEX_MATCH_ANCHOR . '|' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK . self::REGEX_URL . '/i';
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Linkify usernames that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public static function usernames($text = '', callable $callback = null) {
		$callback = $callback ?: array(__CLASS__, 'callbackUsername');
		$regex = '/' . self::REGEX_MATCH_ANCHOR . '|' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK . self::REGEX_USERNAME . '/i';
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Linkify emails that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public static function emails($text = '', callable $callback = null) {
		$callback = $callback ?: array(__CLASS__, 'callbackEmail');
		$regex = '/' . self::REGEX_MATCH_ANCHOR . '|' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK . self::REGEX_EMAIL . '/i';
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Callback function for hashtag preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 * @return string
	 */
	
	public static function callbackHashtag($matches) {
		
		if (empty($matches[2])) {
			return $matches[0];
		}

		$tag = str_replace('#', '', $matches[2]);
		$uri = elgg_get_plugin_setting("hashtag_uri", 'hypeScraper', "search?search_type=tags&q=%s");
		$href = sprintf($uri, $tag);
		return $matches[1] . elgg_format_element('a', array(
			'class' => 'scraper-hashtag',
			'href' => elgg_normalize_url($href),
			'data-qualifier' => 'hashtag',
		), $matches[2]);
	}

	/**
	 * Callback function for url preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 * @return string
	 */
	public static function callbackUrl($matches) {

		if (empty($matches[2])) {
			return $matches[0];
		}
		
		$text = $matches[2];
		if (elgg_get_plugin_setting('linkify_url_titles', 'hypeScraper', true)) {
			$data = hypeapps_scrape($text, true);
			$text = (!empty($data['title'])) ? $data['title'] : $text;
		}

		return $matches[1] . elgg_format_element('a', array(
			'class' => 'scraper-url',
			'href' => $matches[2],
			'data-qualifier' => 'url',
			'rel' => 'nofollow',
		), $text);
	}

	/**
	 * Callback function for username preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 * @return string
	 */
	public static function callbackUsername($matches) {
		
		if (empty($matches[2])) {
			return $matches[0];
		}

		$username = str_replace('@', '', $matches[2]);
		$user = get_user_by_username($username);
		
		if (!$user) {
			return $matches[0];
		}

		return $matches[1] . elgg_format_element('a', array(
			'class' => 'scraper-username',
			'href' => $user->getURL(),
			'data-qualifier' => 'username',
			'data-value' => $user->username,
			'data-guid' => $user->guid,
		), $user->getDisplayName());
	}

	/**
	 * Callback function for username preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 * @return string
	 */
	public static function callbackEmail($matches) {

		if (empty($matches[2])) {
			return $matches[0];
		}
		
		return $matches[1] . elgg_format_element('a', array(
			'class' => 'scraper-email',
			'href' => "mailto:{$matches[2]}",
			'data-qualifier' => 'email',
		), $matches[2]);
	}

}
