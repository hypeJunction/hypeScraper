hypeScraper
===========
![Elgg 1.8](https://img.shields.io/badge/Elgg-1.8.x-orange.svg?style=flat-square)
![Elgg 1.9](https://img.shields.io/badge/Elgg-1.9.x-orange.svg?style=flat-square)
![Elgg 1.10](https://img.shields.io/badge/Elgg-1.10.x-orange.svg?style=flat-square)
![Elgg 1.11](https://img.shields.io/badge/Elgg-1.11.x-orange.svg?style=flat-square)
![Elgg 1.12](https://img.shields.io/badge/Elgg-1.12.x-orange.svg?style=flat-square)

A tool for extracting, interpreting, caching and embedding remote resources.

## Features

* Convert URLs to embeddable content using native parser, iframe.ly or embed.ly
* Parse #hashtags, @usernames, links and emails

## Developer Notes

### Cache

The plugin caches URL metadata and thumbnails on the Elgg file store. You can use custom handles to
maintain multiple instances of the same URL.

### Linkify

To linkify all URLs, usernames, emails and hashtags that are not wrapped in html tags, use ```output/linkify``` view.
Pass your text in a ```value``` parameter. You can use ```parse_``` flags to skip certain qualifiers.

```php
$text = '@someone needs to #linkify this article http://example.com and email it to someone@example.com';
if (elgg_view_exists('output/linkify')) {
	$text = elgg_view('output/linkify', array(
		'value' => $text,
		//'parse_urls' => false,
		//'parse_hashtags' => false,
		//'parse_usernames' => false,
		//'parse_emails' => false,
	));
}
```

### Previews/Embeds

To generate a preview/card, use ```output/card``` view. Pass your URL as ```href``` parameter;

```php
echo elgg_view('output/card', array(
	'href' => 'https://www.youtube.com/watch?v=Dlf1_vuIR4I',
));
```

To generate a preview for multiple URLs extracted from text, use ```output/url_preview``` view.
Pass your text as a	```value``` parameter. The view will parse all URLs and generate previews.

```php
$text = 'This video is really cool https://vimeo.com/channels/staffpicks/116498390';
if (elgg_view_exists('output/url_preview')) {
	$text = elgg_view('output/url_preview', array(
		'value' => $text,
	));
}
```

To generate a preview for bookmarks in the river, override ```views/default/river/object/bookmarks/create```

```php

$object = $vars['item']->getObjectEntity();
$excerpt = elgg_get_excerpt($object->description);

echo elgg_view('river/elements/layout', array(
	'item' => $vars['item'],
	'message' => $excerpt,
	'attachments' => elgg_view('output/card', array('href' => $object->address)),
));

```
