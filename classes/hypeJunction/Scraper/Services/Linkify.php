<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Config;
use hypeJunction\Scraper\Interfaces\Regexp;
use hypeJunction\Scraper\Qualifiers\Qualifier;

class Linkify implements Regexp {

	private $config;
	private $resources;

	/**
	 * Constructor
	 * @param Config $config
	 */
	public function __construct(Config $config, \hypeJunction\Scraper\Models\Resources $resources) {
		$this->config = $config;
		$this->resources = $resources;
	}

	/**
	 * Linkifies all qualifiers
	 * 
	 * @param string $text Source text
	 * @return string
	 */
	public function all($text = '') {
		$text = $this->hashtags($text);
		$text = $this->urls($text);
		$text = $this->emails($text);
		$text = $this->usernames($text);
		return $text;
	}

	/**
	 * Linkify hashtags that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public function hashtags($text = '', $callback = null) {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_HASHTAG;
		$regex = "/$a$q/i";
		$callback = $callback ? : array($this, 'callbackHashtag');
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Linkify urls that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public function urls($text = '', $callback = null) {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_URL;
		$regex = "/$a$q/i";
		$callback = $callback ? : array($this, 'callbackUrl');
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Linkify usernames that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public function usernames($text = '', $callback = null) {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_USERNAME;
		$regex = "/$a$q/i";
		$callback = $callback ? : array($this, 'callbackUsername');
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Linkify emails that are not wrapped in <a> tags
	 *
	 * @param string   $text     Source text
	 * @param callable $callback Callback
	 * @return string
	 */
	public function emails($text = '', $callback = null) {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_EMAIL;
		$regex = "/$a$q/i";
		$callback = $callback ? : array($this, 'callbackEmail');
		return preg_replace_callback($regex, $callback, $text);
	}

	/**
	 * Callback function for hashtag preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 * @return string
	 */
	public function callbackHashtag($matches) {
		if (substr($matches[0], 0, 1) != '#') {
			// Matched a hashtag in a URL
			return $matches[0];
		}

		$tag = str_replace('#', '', $matches[0]);
		$href = sprintf($this->config->get("hashtag_uri", "%s"), $tag);
		return elgg_format_element('a', array(
			'class' => 'scraper-hashtag',
			'href' => $href,
			'data-qualifier' => 'hashtag',
				), $matches[0]);
	}

	/**
	 * Callback function for url preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 * @return string
	 */
	public function callbackUrl($matches) {

		$text = $matches[0];
		if ($this->config->get('linkify_url_titles')) {
			$data = $this->resources->get($matches[0]);
			$text = (!empty($data['title'])) ? $data['title'] : $text;
		}
		
		return elgg_format_element('a', array(
			'class' => 'scraper-url',
			'href' => $matches[0],
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
	public function callbackUsername($matches) {
		if (substr($matches[0], 0, 1) != '@') {
			// Matched a username in a URL
			return $matches[0];
		}

		$username = str_replace('@', '', $matches[0]);
		$user = get_user_by_username($username);

		if (!$user) {
			return $matches[0];
		}

		$href = sprintf($this->config->get("username_uri", "%s"), $username);

		return elgg_format_element('a', array(
			'class' => 'scraper-username',
			'href' => $href,
			'data-qualifier' => 'username',
			'data-value' => $username,
			'data-guid' => $user->guid,
			//'data-icon' => $user->getIconURL('tiny')
				), $user->getDisplayName());
	}

	/**
	 * Callback function for username preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 * @return string
	 */
	public function callbackEmail($matches) {
		$href = sprintf($this->config->get("email_uri", "%s"), $matches[0]);
		return elgg_format_element('a', array(
			'class' => 'scraper-email',
			'href' => $href,
			'data-qualifier' => 'email',
				), $matches[0]);
	}

}
