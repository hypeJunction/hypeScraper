<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Interfaces\Regexp;

/**
 * Extracts qualifier (hashtags, urls, emails and usernames) from text
 * Renders an HTML with linkified qualifiers
 */
class Extractor implements Regexp {

	/**
	 * Extracts all qualifiers
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public function all($text = '') {
		return array(
			'urls' => $this->urls($text),
			'hashtags' => $this->hashtags($text),
			'emails' => $this->emails($text),
			'usernames' => $this->usernames($text),
		);
	}

	/**
	 * Extract hashtags from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function hashtags($text = '') {
		$matches = $results = array();
		preg_match_all(self::REGEX_HASHTAG, $text, $matches);
		foreach ($matches[0] as $match) {
			// remove hashtags that are part of URL
			if (substr($match, 0, 1) == '#') {
				$results[] = $match;
			}
		}
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
		preg_match_all(self::REGEX_URL, $text, $matches);
		return array_unique($matches[0]);
	}

	/**
	 * Extract usernames from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function usernames($text = '') {
		$matches = $results = array();
		preg_match_all(self::REGEX_USERNAME, $text, $matches);
		foreach ($matches[0] as $match) {
			// remove usernames that are part of URL
			if (substr($match, 0, 1) == '@') {
				$results[] = $match;
			}
		}
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
		preg_match_all(self::REGEX_EMAIL, $text, $matches);
		return array_unique($matches[0]);
	}

}
