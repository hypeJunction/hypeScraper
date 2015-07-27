<?php

namespace hypeJunction\Scraper;

/**
 * Config
 */
class Config extends \hypeJunction\Config {

	/**
	 * {@inheritdoc}
	 */
	public function getDefaults() {
		return array(
			'legacy_mode' => true,
			'pagehandler_id' => 'url',
			'cache_thumbnails' => true,
			'cache_thumb_size' => 500,
			'cache_thumb_size_lower_threshold' => 100,
			'hashtag_uri' => "search?search_type=tags&q=%s",
			'username_uri' => "profile/%s",
			'email_uri' => "mailto:%s",
			'linkify_url_titles' => true,
			'defer_loading' => true,
		);
	}

	/**
	 * Returns default config for http requests
	 * @return array
	 */
	public function getHttpClientConfig() {
		$http_config = [
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
			],
			'allow_redirects' => [
				'max' => 3,
				'strict' => true,
				'referer' => true,
				'protocols' => ['http', 'https']
			],
			'timeout' => 5,
			'connect_timeout' => 5,
			'verify' => false,
		];

		return elgg_trigger_plugin_hook('http:config', 'framework:scraper', array('config' => $this), $http_config);
	}

}
