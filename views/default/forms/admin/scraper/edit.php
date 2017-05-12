<?php

$href = elgg_extract('href', $vars);

echo elgg_view('output/card', [
	'href' => $href,
]);

$data = hypeapps_scrape($href);
if (!$data) {
	return;
}

//echo elgg_format_element('pre', [], var_export($data, true));

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('title'),
	'name' => 'title',
	'value' => $data['title'],
]);

echo elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('description'),
	'name' => 'description',
	'value' => $data['description'],
]);

echo elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('scraper:player:html'),
	'name' => 'html',
	'value' => $data['html'],
]);

$assets = $data['assets'];
if ($assets) {
	$options = [];
	foreach ($assets as $asset) {
		$url = $asset['thumbnail_url'];
		$options[$url] = elgg_view('output/img', [
			'src' => $url,
			'width' => 200,
		]);
	}
	
	echo elgg_view_field([
		'#type' => 'radio',
		'align' => 'horizontal',
		'name' => 'thumbnail_url',
		'value' => $data['thumbnail_url'],
		'options' => array_flip($options),
	]);
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'href',
	'value' => $href,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);