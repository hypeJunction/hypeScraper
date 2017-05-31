<?php

$domain = get_input('domain');

echo elgg_view_form('admin/scraper/cache', [
	'method' => 'GET',
	'action' => current_page_url(),
	'disable_security' => true,
], [
	'domain' => $domain,
]);

if (!$domain) {
	return;
}

$svc = \hypeJunction\Scraper\ScraperService::getInstance();
$urls = $svc->find($domain);
if (empty($urls)) {
	echo elgg_format_element('p', [
		'class' => 'elgg-no-results',
	], elgg_echo('admin:scraper:cache:no_results'));

	return;
}

foreach ($urls as $url) {
	$card = elgg_view('output/card', [
		'href' => $url,
	]);
	echo elgg_format_element('div', [], $card);
}

echo elgg_view('output/url', [
	'class' => 'elgg-button elgg-button-delete',
	'text' => elgg_echo('admin:scraper:cache:clear'),
	'href' => elgg_http_add_url_query_elements('action/admin/scraper/clear', [
		'domain' => $domain,
	]),
	'is_action' => true,
]);