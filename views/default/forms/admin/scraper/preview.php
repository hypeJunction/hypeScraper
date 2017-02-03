<?php

echo elgg_view_field([
	'#type' => 'url',
	'#label' => elgg_echo('admin:scraper:preview:url'),
	'name' => 'href',
	'value' => get_input('href'),
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('preview'),
]);

elgg_set_form_footer($footer);