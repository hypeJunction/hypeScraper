<?php

/**
 * @uses $vars['href'] URL to be previewed
 * @uses $vars['fallback'] Fallback to card view if no HTML content is associated with the URL
 * @uses $vars['responsive'] Make the player responsive
 */
$href = elgg_extract('href', $vars);

$data = hypeapps_scrape($href);
if (!$data) {
	return;
}

if ($data['html']) {
	$preview = $data['html'];
	if (elgg_extract('responsive', $vars, true)) {
		$classes = ['scraper-card-flex', 'clearfix'];
		if ($data['provider_name']) {
			$classes[] = "scraper-card-{$data['provider_name']}";
		}
		$player = elgg_format_element('div', [
			'class' => $classes,
		], $preview);
		echo elgg_format_element('div', [
			'class' => "scraper-card-{$data['type']}",
		], $player);
	} else {
		echo $preview;
	}
} else if (elgg_extract('fallback', $vars, true)) {
	echo elgg_view('output/card', [
		'href' => $href,
	]);
}
