<?php

/**
 * Render an embed view for a scraped link
 *
 * @uses $vars['src']    Originally embedded URL
 */

$vars['href'] = $vars['src'];
echo elgg_view('output/card', $vars);
