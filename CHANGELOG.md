<a name="5.5.2"></a>
## [5.5.2](https://github.com/hypeJunction/hypeScraper/compare/5.5.1...v5.5.2) (2017-10-17)


### Bug Fixes

* **scraper:** set session cookie to allow scraping session-bound local resources ([dafe27c](https://github.com/hypeJunction/hypeScraper/commit/dafe27c))



<a name="5.5.1"></a>
## [5.5.1](https://github.com/hypeJunction/hypeScraper/compare/5.5.0...v5.5.1) (2017-10-17)


### Bug Fixes

* **oembed:** correctly split multiline oembed domain config ([8570b9b](https://github.com/hypeJunction/hypeScraper/commit/8570b9b)), closes [#52](https://github.com/hypeJunction/hypeScraper/issues/52)



<a name="5.5.0"></a>
# [5.5.0](https://github.com/hypeJunction/hypeScraper/compare/5.4.1...v5.5.0) (2017-06-26)


### Features

* **player:** make players responsive ([88fad4d](https://github.com/hypeJunction/hypeScraper/commit/88fad4d)), closes [hypeJunction/hypeEmbed#5](https://github.com/hypeJunction/hypeEmbed/issues/5)
* **preview:** add a setting to use player as a default preview type ([24bd9f4](https://github.com/hypeJunction/hypeScraper/commit/24bd9f4))



<a name="5.4.1"></a>
## [5.4.1](https://github.com/hypeJunction/hypeScraper/compare/5.4.0...v5.4.1) (2017-06-26)


### Bug Fixes

* **images:** add a hotfix to replace image URLs after datastore migration ([15a1885](https://github.com/hypeJunction/hypeScraper/commit/15a1885))



<a name="5.4.0"></a>
# [5.4.0](https://github.com/hypeJunction/hypeScraper/compare/5.3.0...v5.4.0) (2017-05-31)


### Features

* **admin:** add option to clear all cached URLs by domain/w ([2164593](https://github.com/hypeJunction/hypeScraper/commit/2164593))



<a name="5.3.0"></a>
# [5.3.0](https://github.com/hypeJunction/hypeScraper/compare/5.2.2...v5.3.0) (2017-05-12)


### Features

* **flush:** add an option to flush scraped URL data ([5930a12](https://github.com/hypeJunction/hypeScraper/commit/5930a12)), closes [#49](https://github.com/hypeJunction/hypeScraper/issues/49)



<a name="5.2.2"></a>
## [5.2.2](https://github.com/hypeJunction/hypeScraper/compare/5.2.0...v5.2.2) (2017-04-19)


### Bug Fixes

* **card:** avoid class name collision ([942b7a4](https://github.com/hypeJunction/hypeScraper/commit/942b7a4))



<a name="5.2.1"></a>
## [5.2.1](https://github.com/hypeJunction/hypeScraper/compare/5.2.0...v5.2.1) (2017-04-16)


### Bug Fixes

* **card:** avoid class name collision ([942b7a4](https://github.com/hypeJunction/hypeScraper/commit/942b7a4))



<a name="5.2.0"></a>
# [5.2.0](https://github.com/hypeJunction/hypeScraper/compare/5.1.7...v5.2.0) (2017-03-22)


### Features

* **card:** make card title an anchor link ([d20acff](https://github.com/hypeJunction/hypeScraper/commit/d20acff))
* **cards:** admins can now edit card title, desc, player and image ([1ee69de](https://github.com/hypeJunction/hypeScraper/commit/1ee69de))
* **css:** slightly reworks the card styles ([c7d0d94](https://github.com/hypeJunction/hypeScraper/commit/c7d0d94))
* **security:** implement a whitelist of oembed domains ([0608b91](https://github.com/hypeJunction/hypeScraper/commit/0608b91))



<a name="5.1.7"></a>
## [5.1.7](https://github.com/hypeJunction/hypeScraper/compare/5.1.6...v5.1.7) (2017-03-13)




<a name="5.1.6"></a>
## [5.1.6](https://github.com/hypeJunction/hypeScraper/compare/5.1.5...v5.1.6) (2017-02-28)


### Bug Fixes

* **css:** fix player button ([7aff092](https://github.com/hypeJunction/hypeScraper/commit/7aff092))



<a name="5.1.5"></a>
## [5.1.5](https://github.com/hypeJunction/hypeScraper/compare/5.1.4...v5.1.5) (2017-02-28)




<a name="5.1.4"></a>
## [5.1.4](https://github.com/hypeJunction/hypeScraper/compare/5.1.3...v5.1.4) (2017-02-26)


### Bug Fixes

* **cache:** no longer throw exception on invalid url ([50dbaec](https://github.com/hypeJunction/hypeScraper/commit/50dbaec))
* **parsing:** better url filtering, no longer throw exception on fail ([9968595](https://github.com/hypeJunction/hypeScraper/commit/9968595))



<a name="5.1.3"></a>
## [5.1.3](https://github.com/hypeJunction/hypeScraper/compare/5.1.2...v5.1.3) (2017-02-18)


### Bug Fixes

* **memory:** set upper threshold for parseable image width ([60ca20d](https://github.com/hypeJunction/hypeScraper/commit/60ca20d))



<a name="5.1.2"></a>
## [5.1.2](https://github.com/hypeJunction/hypeScraper/compare/5.1.1...v5.1.2) (2017-02-18)


### Bug Fixes

* **memory:** ensure thumbnails are images and resizable ([9e5ce74](https://github.com/hypeJunction/hypeScraper/commit/9e5ce74)), closes [#42](https://github.com/hypeJunction/hypeScraper/issues/42)



<a name="5.1.1"></a>
## [5.1.1](https://github.com/hypeJunction/hypeScraper/compare/5.1.0...v5.1.1) (2017-02-06)


### Bug Fixes

* **scraper:** minimize the risk of fatal impact ([0e7fdf7](https://github.com/hypeJunction/hypeScraper/commit/0e7fdf7))



<a name="5.1.0"></a>
# [5.1.0](https://github.com/hypeJunction/hypeScraper/compare/5.0.3...v5.1.0) (2017-02-03)


### Features

* **admin:** adds an admin interface for previewing cards ([e53f92e](https://github.com/hypeJunction/hypeScraper/commit/e53f92e))



<a name="5.0.3"></a>
## [5.0.3](https://github.com/hypeJunction/hypeScraper/compare/5.0.2...v5.0.3) (2016-12-08)




<a name="5.0.2"></a>
## [5.0.2](https://github.com/hypeJunction/hypeScraper/compare/5.0.1...v5.0.2) (2016-12-08)




<a name="5.0.1"></a>
## [5.0.1](https://github.com/hypeJunction/hypeScraper/compare/5.0.0...v5.0.1) (2016-12-08)


### Bug Fixes

* **linkify:** exclude HTML tag attribute names and values from token matches ([56caba3](https://github.com/hypeJunction/hypeScraper/commit/56caba3))



<a name="5.0.0"></a>
# [5.0.0](https://github.com/hypeJunction/hypeScraper/compare/4.2.2...v5.0.0) (2016-11-16)


### Bug Fixes

* **cache:** adds filesize sanity checks before generating thumbnails ([c7e5d0c](https://github.com/hypeJunction/hypeScraper/commit/c7e5d0c))
* **css:** make sure nested flex cards preserve their padding values ([e9cddb7](https://github.com/hypeJunction/hypeScraper/commit/e9cddb7))
* **embed:** only allow iframe, video and audio tags in embed html ([cd23401](https://github.com/hypeJunction/hypeScraper/commit/cd23401)), closes [#14](https://github.com/hypeJunction/hypeScraper/issues/14)
* **scraper:** ensure that urls are valid before scraping them ([bdf060a](https://github.com/hypeJunction/hypeScraper/commit/bdf060a))
* **thumbs:** fix class namespace ([e054447](https://github.com/hypeJunction/hypeScraper/commit/e054447))
* **upgrade:** normalize and filter URLs during upgrade ([0ec03f8](https://github.com/hypeJunction/hypeScraper/commit/0ec03f8))

### Features

* **cache:** add restrictions on parsing and caching of images ([5a6ec64](https://github.com/hypeJunction/hypeScraper/commit/5a6ec64))
* **core:** update requirements ([e339264](https://github.com/hypeJunction/hypeScraper/commit/e339264))
* **elgg:** now requires Elgg 2.3 ([a8f750c](https://github.com/hypeJunction/hypeScraper/commit/a8f750c))
* **images:** use new image resize library ([a458f1d](https://github.com/hypeJunction/hypeScraper/commit/a458f1d))
* **player:** add an option to disable player fallback ([e9cef19](https://github.com/hypeJunction/hypeScraper/commit/e9cef19))
* **release:** 5.0 RC release ([4fb1b5a](https://github.com/hypeJunction/hypeScraper/commit/4fb1b5a))
* **releases:** upgrade to latest Elgg, drop hypeApps requirement ([d9c5491](https://github.com/hypeJunction/hypeScraper/commit/d9c5491))
* **thumbs:** improve thumbs handling ([5d3f816](https://github.com/hypeJunction/hypeScraper/commit/5d3f816))
* **upgrades:** add a memory-safe upgrade script ([a19da0a](https://github.com/hypeJunction/hypeScraper/commit/a19da0a))


### BREAKING CHANGES

* elgg: Now requires Elgg 2.3
* release: Most of the API has been rewritten to reduce complexity.
The hooks will continue working, but hypeScraper() function has
been removed along side all of the DI services.
Support for embed.ly and iframely have been removed.
All the legacy and deprecated functions and views have been removed.
* releases: Now requires Elgg 2.2 or higher.
Drops requirement for hypeApps and subsequently affects all APIs
that rely on its functionality
* thumbs: Drops /server/thumbs.php
Drops \hypeJunction\Scraper\ThumbServer class
* core: Now requires Elgg 2.1



<a name="4.2.2"></a>
## [4.2.2](https://github.com/hypeJunction/hypeScraper/compare/4.2.1...v4.2.2) (2016-01-06)


### Bug Fixes

* **thumbs:** thumb URLs are now normalized to site root ([8105d17](https://github.com/hypeJunction/hypeScraper/commit/8105d17))



