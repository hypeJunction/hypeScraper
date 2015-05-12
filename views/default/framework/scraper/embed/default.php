<?php

/**
 * Render a view for a URL
 * 
 * @uses $vars['class'] Class for a formatted tag
 * @uses $vars['src']   URL
 * @deprecated since 1.2
 */

$vars['href'] = $vars['src'];
echo elgg_view('output/card', $vars);
