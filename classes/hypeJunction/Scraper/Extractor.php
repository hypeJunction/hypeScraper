<?php

namespace hypeJunction\Scraper;

/**
 * @access private
 */
class Extractor {

	// non-greedy match of a single tag name and attributes
	// we need to exclude e.g. hex color codes when matchin hashes
	const REGEX_MATCH_TAG = '<.*?>';

	// character allowed before match
	const REGEX_CHAR_BACK = '(^|\s|\!|\.|\?|>|\G)+';
	
	const REGEX_HASHTAG = '(#\w+)';
	
	const REGEX_URL = '(h?[t|f]??tps*:\/\/[^\s\r\n\t<>"\'\)\(]+)';

	const REGEX_EMAIL = '([\w\-\.]+@[^\s\r\n\t<>"\'\)\(]+\.+[0-9a-z]{2,})';

	const REGEX_USERNAME = '(@[\p{L}\p{Nd}._-]+)';

	/**
	 * Extracts all qualifiers
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function all($text = '') {
		return array(
			'urls' => self::urls($text) ? : [],
			'hashtags' =>  self::hashtags($text) ? : [],
			'emails' => self::emails($text) ? : [],
			'usernames' => self::usernames($text) ? : [],
		);
	}

	/**
	 * Extract hashtags from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function hashtags($text = '') {
		$matches = [];
		$regex = '/' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK  . self::REGEX_HASHTAG . '/i';
		preg_match_all($regex, $text, $matches);
		$results = array_filter($matches[2]);
		return array_unique($results);
	}

	/**
	 * Extract URLs from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function urls($text = '') {
		$matches = array();
		$regex = '/' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK  . self::REGEX_URL . '/i';
		preg_match_all($regex, $text, $matches);
		$results = array_filter($matches[2]);
		return array_unique($results);
	}

	/**
	 * Extract usernames from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function usernames($text = '') {
		$matches = [];
		$regex = '/' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK  . self::REGEX_USERNAME . '/i';
		preg_match_all($regex, $text, $matches);
		$results = array_filter($matches[2]);
		return array_unique($results);
	}

	/**
	 * Extract emails from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function emails($text = '') {
		$matches = array();
		$regex = '/' . self::REGEX_MATCH_TAG . '|' . self::REGEX_CHAR_BACK  . self::REGEX_EMAIL . '/i';
		preg_match_all($regex, $text, $matches);
		$results = array_filter($matches[2]);
		return array_unique($results);
	}

}
