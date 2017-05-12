<?php

$href = get_input('href');

$svc = hypeJunction\Scraper\ScraperService::getInstance();

if ($data = $svc->parse($href, true)) {
	return elgg_ok_response($data, elgg_echo('scraper:refetch:success'));
} else {
	return elgg_error_response(elgg_echo('scraper:refetch:error'));
}