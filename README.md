hypeScraper
===========
![Elgg 2.3](https://img.shields.io/badge/Elgg-2.3-orange.svg?style=flat-square)

A tool for scraping, caching and embedding remote resources.

## Features

* Scrapes URLs and turns them in responsive preview cards
* Aggressive caching of scraped resources for enhanced performance
* Linkifies #hashtags, @usernames, links and emails

![Card view](https://raw.github.com/hypeJunction/hypeScraper/master/screenshots/scraper-card.png "Card")
![Card mobile](https://raw.github.com/hypeJunction/hypeScraper/master/screenshots/scraper-card-mobile.png "Responsive Card")
![Player](https://raw.github.com/hypeJunction/hypeScraper/master/screenshots/scraper-player.png "Player")

## Developer notes

### Card

To display a URL card with an image preview, title and brief description, use ``output/card`` view:

```php
echo elgg_view('output/card', array(
	'href' => 'https://www.youtube.com/watch?v=Dlf1_vuIR4I',
));
```

### Player

To dipslay a rich media player use ``output/player`` view:

```php
echo elgg_view('output/player', array(
	'href' => 'https://www.youtube.com/watch?v=Dlf1_vuIR4I',
));
```

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
