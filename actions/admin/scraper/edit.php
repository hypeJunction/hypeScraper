<?php

$href = get_input('href');

$svc = hypeJunction\Scraper\ScraperService::getInstance();
$data = $svc->get($href);
$data['thumbnail_url'] = get_input('thumbnail_url', $data['thumbnail_url']);
$data['title'] = get_input('title', $data['title'], false);
$data['description'] = get_input('description', $data['description'], false);
$data['html'] = get_input('html', $data['html'], false);

$svc->save($href, $data);