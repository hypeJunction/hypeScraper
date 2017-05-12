<?php

namespace hypeJunction\Scraper;

use ElggMenuItem;

class Menus {
	
	/**
	 * Setup menu
	 * 
	 * @param string         $hook   "register"
	 * @param string         $type   "menu:scraper:card"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem[]
	 */
	public static function setupCardMenu($hook, $type, $return, $params) {

		if (!elgg_is_admin_logged_in()) {
			return;
		}

		$href = elgg_extract('href', $params);
		if (!$href) {
			return;
		}

		$return[] = ElggMenuItem::factory([
			'name' => 'edit',
			'href' => elgg_http_add_url_query_elements('admin/scraper/edit', [
				'href' => $href,
			]),
			'text' => elgg_view_icon('pencil'),
			'title' => elgg_echo('edit'),
		]);

		$return[] = ElggMenuItem::factory([
			'name' => 'refetch',
			'href' => elgg_http_add_url_query_elements('action/admin/scraper/refetch', [
				'href' => $href,
			]),
			'text' => elgg_view_icon('refresh'),
			'title' => elgg_echo('scraper:refetch'),
			'is_action' => true,
			'confirm' => elgg_echo('scraper:refetch:confirm'),
		]);

		return $return;
	}
}
