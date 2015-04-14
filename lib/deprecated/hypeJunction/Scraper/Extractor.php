<?php

namespace hypeJunction\Scraper;

use hypeJunction\Scraper\Qualifiers\Qualifier;

/**
 * Extracts qualifier (hashtags, urls, emails and usernames) from text
 * Renders an HTML with linkified qualifiers
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Extractor {

	/**
	 * Negative lookead ahead regex to exclude matches found within <a> tags
	 */
	const REGEX_ANCHOR_NEGATIVE = '(?!(?:[^<]+>|[^>]+<\/a[^\w]*>))';

	/**
	 * Hashtag regex
	 * Uses noncapturing group to match URLs with hashtags, which we will remove from results
	 */
	const REGEX_HASHTAG = '((?:h?[t|f]??tps*:\/\/[^\s\r\n\t<>"\'\)\(]+)?(?=[^\w]|\G)#\b\w+\b)';

	/**
	 * URL regex that matches URLs in http,https,ftp schemes
	 */
	const REGEX_URL = '(h?[t|f]??tps*:\/\/[^\s\r\n\t<>"\'\)\(]+)';

	/**
	 * Email regex
	 */
	const REGEX_EMAIL = '(\b[\w\-\.]+@[^\s\r\n\t<>"\'\)\(]+\.+[0-9a-z]{2,}\b)';

	/**
	 * Username regex
	 * Uses noncapturing group to match URLs with hashtags, which we will remove from results
	 */
	const REGEX_USERNAME = '((?:h?[t|f]??tps*:\/\/[^\s\r\n\t<>"\'\)\(]+)?(?<=[^\w]|\G)@\b[\p{L}\p{Nd}._]+\b)';

	protected $text;
	public $html = '';
	public $hashtags = array();
	public $urls = array();
	public $emails = array();
	public $usernames = array();

	/**
	 * Contruct a new Extractor
	 * 
	 * @param string $text Source text
	 */
	function __construct($text = '') {
		$this->setText($text);
	}

	/**
	 * Extract all qualifiers
	 * @return Extractor
	 */
	public function extractAll() {
		$this->hashtags = $this->extractHashtags($this->text);
		$this->urls = $this->extractURLs($this->text);
		$this->emails = $this->extractEmails($this->text);
		$this->usernames = $this->extractUsernames($this->text);
		return $this;
	}

	/**
	 * Linkify all qualifiers
	 * @return Extractor
	 */
	public function linkifyAll() {
		$text = ($this->html) ? $this->html : $this->text;
		$text = $this->linkifyHashtags($text);
		$text = $this->linkifyURLs($text);
		$text = $this->linkifyUsernames($text);
		$text = $this->linkifyEmails($text);
		$this->html = $text;
		return $this;
	}

	/**
	 * Change text
	 * 
	 * @param string $text Source text
	 * @return Extractor
	 */
	public function setText($text = '') {
		$this->text = $text;
		return $this;
	}

	/**
	 * Get text
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * Get HTML
	 * @return type
	 */
	public function getHTML() {
		return $this->html;
	}

	/**
	 * Extract URLs, emails, usernames and hashtags from text
	 * 
	 * @param string $text Source text
	 * @return Extractor
	 * @deprecated 1.1
	 */
	public static function extract($text = '') {
		$extractor = new Extractor($text);
		return $extractor->extractAll();
	}

	/**
	 * Substitute URLs, emails, usernames and hashtags with html <a> tags
	 * 
	 * @param string $text Source text
	 * @return string Rendered HTML
	 * @deprecated 1.1
	 */
	public static function render($text = '') {
		$extractor = new Extractor($text);
		return $extractor->linkifyAll()->getHTML();
	}

	/**
	 * Extract hashtags from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function extractHashtags($text = '') {
		$matches = $results = array();
		preg_match_all(self::REGEX_HASHTAG, $text, $matches);
		foreach ($matches[0] as $match) {
			// remove hashtags that are part of URL
			if (substr($match, 0, 1) == '#') {
				$results[] = $match;
			}
		}
		return $results;
	}
	
	/**
	 * Extract URLs from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function extractURLs($text = '') {
		$matches = array();
		preg_match_all(self::REGEX_URL, $text, $matches);
		return $matches[0];
	}

	/**
	 * Extract usernames from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function extractUsernames($text = '') {
		$matches = $results = array();
		preg_match_all(self::REGEX_USERNAME, $text, $matches);
		foreach ($matches[0] as $match) {
			// remove usernames that are part of URL
			if (substr($match, 0, 1) == '@') {
				$results[] = $match;
			}
		}
		return $results;
	}

	/**
	 * Extract emails from text
	 *
	 * @param string $text Source text
	 * @return array
	 */
	public static function extractEmails($text = '') {
		$matches = array();
		preg_match_all(self::REGEX_EMAIL, $text, $matches);
		return $matches[0];
	}

	/**
	 * Linkify hashtags that are not wrapped in <a> tags
	 * 
	 * @param string $text Source text
	 * @return string
	 */
	public function linkifyHashtags($text = '') {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_HASHTAG;
		$regex = "/$a$q/i";
		return preg_replace_callback($regex, array($this, 'pregReplaceHashtagCallback'), $text);
	}

	/**
	 * Callback function for hashtag preg_replace_callback
	 * 
	 * @param array $matches An array of matches
	 * @return string
	 */
	public function pregReplaceHashtagCallback($matches) {
		if (substr($matches[0], 0, 1) != '#') {
			// Matched a hashtag in a URL
			return $matches[0];
		}
		return $this->linkifyQualifier(Qualifier::TYPE_HASHTAG, $matches[0]);
	}

	/**
	 * Linkify urls that are not wrapped in <a> tags
	 * 
	 * @param string $text Source text
	 * @return string
	 */
	public function linkifyURLs($text = '') {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_URL;
		$regex = "/$a$q/i";
		return preg_replace_callback($regex, array($this, 'pregReplaceUrlCallback'), $text);
	}

	/**
	 * Callback function for url preg_replace_callback
	 * 
	 * @param array $matches An array of matches
	 * @return string
	 */
	public function pregReplaceUrlCallback($matches) {
		return $this->linkifyQualifier(Qualifier::TYPE_URL, $matches[0]);
	}

	/**
	 * Linkify usernames that are not wrapped in <a> tags
	 * 
	 * @param string $text Source text
	 * @return string
	 */
	public function linkifyUsernames($text = '') {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_USERNAME;
		$regex = "/$a$q/i";
		return preg_replace_callback($regex, array($this, 'pregReplaceUsernameCallback'), $text);
	}

	/**
	 * Callback function for username preg_replace_callback
	 * 
	 * @param array $matches An array of matches
	 * @return string
	 */
	public function pregReplaceUsernameCallback($matches) {
		if (substr($matches[0], 0, 1) != '@') {
			// Matched a username in a URL
			return $matches[0];
		}
		return $this->linkifyQualifier(Qualifier::TYPE_USERNAME, $matches[0]);
	}

	/**
	 * Linkify emails that are not wrapped in <a> tags
	 * 
	 * @param string $text Source text
	 * @return string
	 */
	public function linkifyEmails($text = '') {
		$a = self::REGEX_ANCHOR_NEGATIVE;
		$q = self::REGEX_EMAIL;
		$regex = "/$a$q/i";
		return preg_replace_callback($regex, array($this, 'pregReplaceEmailCallback'), $text);
	}

	/**
	 * Callback function for username preg_replace_callback
	 * 
	 * @param array $matches An array of matches
	 * @return string
	 */
	public function pregReplaceEmailCallback($matches) {
		return $this->linkifyQualifier(Qualifier::TYPE_EMAIL, $matches[0]);
	}

	/**
	 * Render anchor markup view for a hashtag
	 * 
	 * @param string $hashtag  Hashtag
	 * @param string $url_base Base URI
	 * @param array  $vars     View params
	 * @return string HTML
	 * @deprecated 1.1.
	 */
	public static function renderHashtagHTML($hashtag, $url_base = '', $vars = array()) {
		$vars['class'] = 'extractor-hashtag';
		$extractor = new Extractor;
		return $extractor->linkifyQualifier(Qualifier::TYPE_HASHTAG, $hashtag, $url_base, $vars);
	}

	/**
	 * Render a view wrapped in <a> tag
	 * 
	 * @param mixed  $url      URL string or array of preg matches
	 * @param string $url_base Base URI
	 * @param array  $vars     View params
	 * @return string HTML
	 * @deprecated 1.1.
	 */
	public static function renderURLHTML($url, $url_base = '', $vars = array()) {
		$vars['class'] = 'extractor-link';
		$extractor = new Extractor;
		return $extractor->linkifyQualifier(Qualifier::TYPE_URL, $url, $url_base, $vars);
	}

	/**
	 * Render a view wrapped in <a> tag
	 * 
	 * @param mixed  $username Username or an array of preg matches
	 * @param string $url_base Base URI
	 * @param array  $vars     View params
	 * @return string HTML
	 * @deprecated 1.1.
	 */
	public static function renderUsernameHTML($username, $url_base = '', $vars = array()) {
		$vars['class'] = 'extractor-username';
		$extractor = new Extractor;
		return $extractor->linkifyQualifier(Qualifier::TYPE_USERNAME, $username, $url_base, $vars);
	}

	/**
	 * Render a view wrapped in <a> tag
	 * 
	 * @param mixed  $email    Email or an array of preg matches
	 * @param string $url_base Base URI
	 * @param array  $vars     View params
	 * @return string HTML
	 * @deprecated 1.1.
	 */
	public static function renderEmailHTML($email, $url_base = '', $vars = array()) {
		$vars['class'] = 'extractor-email';
		$extractor = new Extractor;
		return $extractor->linkifyQualifier(Qualifier::TYPE_EMAIL, $email, $url_base, $vars);
	}

	/**
	 * Render a qualifier link
	 * 
	 * @param string $type      Qualifier type
	 * @param string $qualifier Qualifier value
	 * @param string $url_base  Base url
	 * @param array  $vars      View vars
	 * @return string
	 */
	public function linkifyQualifier($type, $qualifier, $url_base = '', $vars = array()) {
		$qualifier = Qualifier::constructFromType($type, $qualifier, $url_base);
		return $qualifier->output($vars);
	}

}
