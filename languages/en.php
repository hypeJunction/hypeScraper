<?php

$english = array(
	'scraper:settings:service' => 'Scraper Service',
	'scraper:settings:service:help' => 'Specify which scraper service to use. If using anything other than native DOM parser, '
	. 'provide necessary configuration details below',
	'scraper:settings:service:dom_parser' => 'Native DOM parser',
	'scraper:settings:service:iframely' => 'iframe.ly',
	'scraper:settings:service:embedly' => 'embed.ly',
	'scraper:settings:iframely:endpoint' => 'iframe.ly oEmbed endpoint',
	'scraper:settings:iframely:endpoint:help' => 'e.g. http://iframe.ly/api/oembed',
	'scraper:settings:iframely:key' => 'iframe.ly API key',
	'scraper:settings:iframely:key:help' => 'Valid iframe.ly API key',
	'scraper:settings:embedly:endpoint' => 'embed.ly oEmbed endpoint',
	'scraper:settings:embedly:endpoint:help' => 'e.g. http://api.embed.ly/1/oembed',
	'scraper:settings:embedly' => 'Embed.ly API key',
	'scraper:settings:embedly:help' => 'Valid embed.ly API key',
);

add_translation('en', $english);
