hypeScraper [![Build Status](https://travis-ci.org/hypeJunction/hypeScraper.svg?branch=master)](https://travis-ci.org/hypeJunction/hypeScraper) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hypeJunction/hypeScraper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hypeJunction/hypeScraper/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/hypeJunction/hypeScraper/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hypeJunction/hypeScraper/?branch=master)
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


