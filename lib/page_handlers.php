<?php

namespace hypeJunction\Scraper;

function page_handler($page) {

	$hash = elgg_extract(0, $page, '');
	$viewtype = elgg_extract(1, $page, 'iframe');

	$url = get_input('url');

	if (!$url) {
		$url = Hasher::getURLFromHash($hash);
	}
	if (!$url) {
		return false;
	}

	switch ($viewtype) {

		default :
			forward($url);
			break;

		case 'iframe' :
			$embedder = new Embedder($url);
			$meta = $embedder->extractMeta();
			$title = $meta->title;
			$layout = $embedder->getEmbedView($url);
			echo elgg_view_page($title, $layout, 'iframe');
			break;
	}

	return true;
}
