<?php

/**
 * Render an embed view for an entity
 * 
 * @uses $vars['entity'] Entity whose view to render
 * @uses $vars['src']    Originally embedded URL
 */

$entity = elgg_extract('entity', $vars);

if (isset($vars['module'])) {
	$module = elgg_extract('module', $vars, false);
	unset($vars['module']);
}

if ($entity instanceof ElggFile && $entity->simpletype == 'image') {
	$body = elgg_view_entity_icon($entity, 'large', $vars);
} else {
	$defaults = array(
		'format' => 'embed',
		'full_view' => false,
	);
	$vars = array_merge($defaults, $vars);
	$body = elgg_view_entity($entity, $vars);
}

if ($module) {
	$output = elgg_view_module($module, '', $body, array(
		'class' => 'elgg-module-embed-entity',
	));
} else {
	$output = $body;
}

echo $output;