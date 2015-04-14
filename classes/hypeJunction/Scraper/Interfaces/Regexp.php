<?php

namespace hypeJunction\Scraper\Interfaces;

interface Regexp {

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

}
