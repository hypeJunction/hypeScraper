hypeScraper
===========

A tool for extracting, interpreting, caching and embedding remote resources.

## Features

* Convert URLs to embeddable content using native parser, iframe.ly or embed.ly
* Parse #hashtags, @usernames, links and emails
* API for hashing and shortening URLs


## Developer Notes

### Database Table

The plugin creates a new MySQL table ```prefix_url_meta_cache``` for caching URL metatags and corresponding them to a unique hash.
You can safely deactivate the plugin and drop the table at any time.

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

To generate a preview (or in some cases an embed) for a URL (or multiple URLs), use ```output/url_preview``` view.
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
	'attachments' => elgg_view('output/url_preview', array('value' => $object->address)),
));

```