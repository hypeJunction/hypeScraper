hypeScraper
===========

A tool for extracting, interpreting, caching and embedding remote resources.

## Features

* Convert URLs to embeddable content using native parser, iframe.ly or embed.ly
* Parse #hashtags, @usernames, links and emails
* API for hashing and shortening URLs

## Notes

The plugin creates a new MySQL table ```prefix_url_meta_cache``` for caching
URL metatags and corresponding them to a unique hash.


