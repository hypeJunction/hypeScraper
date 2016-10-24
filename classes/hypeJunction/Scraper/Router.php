<?php

namespace hypeJunction\Scraper;

/**
 * Routing and page handling service
 *
 * @access private
 */
class Router {

	/**
	 * Handles scraper pages
	 *
	 * @param array $segments URL segments
	 * @return bool
	 */
	public static function serveScraperPages($segments) {

		$url = get_input('url');

		if (!elgg_is_logged_in()) {
			$m = get_input('m');
			if (!$m || !elgg_build_hmac($url)->matchesToken($m)) {
				return false;
			}
		}

		$viewtype = array_shift($segments);
		if (!$viewtype || !elgg_is_registered_viewtype($viewtype)) {
			$viewtype = 'default';
		}

		elgg_set_viewtype($viewtype);

		echo elgg_view_resource('scraper/card', [
			'href' => $url,
			'iframe' => get_input('iframe', false),
		]);

		return true;
	}

}
