<?php

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('admin:scraper:cache:domain'),
	'name' => 'domain',
	'value' => get_input('domain'),
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('admin:scraper:cache:find'),
]);

elgg_set_form_footer($footer);