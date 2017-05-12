<?php

echo elgg_view_field([
	'#type' => 'url',
	'#label' => elgg_echo('admin:scraper:preview:url'),
	'name' => 'href',
	'value' => get_input('href'),
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'label' => elgg_echo('scraper:refetch'),
	'name' => 'flush',
	'value' => 1,
	'default' => false,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('preview'),
]);

elgg_set_form_footer($footer);