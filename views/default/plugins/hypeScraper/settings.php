<?php

use hypeJunction\Scraper\Services\Parser;


$entity = elgg_extract('entity', $vars);
?>
<div>
	<label><?php echo elgg_echo('scraper:settings:service') ?></label>
	<div class="elgg-text-help"><?php echo elgg_echo('scraper:settings:service:help') ?></div>
	<?php
	echo elgg_view('input/dropdown', array(
		'name' => 'params[service]',
		'value' => $entity->service,
		'options_values' => array(
			Parser::SERVICE_DOM => elgg_echo('scraper:settings:service:dom_parser'),
			Parser::SERVICE_IFRAMELY => elgg_echo('scraper:settings:service:iframely'),
			Parser::SERVICE_EMBEDLY => elgg_echo('scraper:settings:service:embedly'),
		)
	));
	?>
</div>
<div>
	<label><?php echo elgg_echo('scraper:settings:iframely:endpoint') ?></label>
	<div class="elgg-text-help"><?php echo elgg_echo('scraper:settings:iframely:endpoint:help') ?></div>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[iframely_endpoint]',
		'value' => $entity->iframely_endpoint,
	));
	?>
	<label><?php echo elgg_echo('scraper:settings:iframely:key') ?></label>
	<div class="elgg-text-help"><?php echo elgg_echo('scraper:settings:iframely:key:help') ?></div>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[iframely_key]',
		'value' => $entity->iframely_key,
	));
	?>
</div>
<div>
	<label><?php echo elgg_echo('scraper:settings:embedly:endpoint') ?></label>
	<div class="elgg-text-help"><?php echo elgg_echo('scraper:settings:embedly:endpoint:help') ?></div>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[embedly_endpoint]',
		'value' => $entity->embedly_endpoint,
	));
	?>
	<label><?php echo elgg_echo('scraper:settings:embedly') ?></label>
	<div class="elgg-text-help"><?php echo elgg_echo('scraper:settings:embedly:help') ?></div>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[embedly_key]',
		'value' => $entity->embedly_key,
	));
	?>
</div>