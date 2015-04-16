<?php

namespace hypeJunction\Scraper\Di;

use Guzzle\Http\Client;
use hypeJunction\Scraper\Config\Config;
use hypeJunction\Scraper\Di\DiContainer;
use hypeJunction\Scraper\Http\Cache;
use hypeJunction\Scraper\Http\Resource;
use hypeJunction\Scraper\Listeners\Events;
use hypeJunction\Scraper\Listeners\PluginHooks;
use hypeJunction\Scraper\Models\Resources;
use hypeJunction\Scraper\Resources\Cache as ResourceCache;
use hypeJunction\Scraper\Services\domParser;
use hypeJunction\Scraper\Services\embedlyParser;
use hypeJunction\Scraper\Services\Extractor;
use hypeJunction\Scraper\Services\iframelyParser;
use hypeJunction\Scraper\Services\imageParser;
use hypeJunction\Scraper\Services\Linkify;
use hypeJunction\Scraper\Services\oEmbedParser;
use hypeJunction\Scraper\Services\Parser;
use hypeJunction\Scraper\Services\Router;
use hypeJunction\Scraper\Services\Upgrades;

/**
 * Scraper service provider
 *
 * @property-read Config          $config
 * @property-read Events          $events
 * @property-read PluginHooks     $hooks
 * @property-read Router          $router
 * @property-read Upgrades        $upgrades
 * @property-read Client          $httpClient
 * @property-read Cache           $httpCache
 * @property-read Resource        $httpResource
 * @property-read Parser          $parser
 * @property-read domParser       $domParser
 * @property-read oEmbedParser    $oEmbedParser
 * @property-read imageParser     $imageParser
 * @property-read embedlyParser   $embedlyParser
 * @property-read iframelyParser  $iframelyParser
 * @property-read ResourceCache   $resourceCache
 * @property-read Resources       $resources
 * @property-read Extractor       $extractor
 * @property-read Linkify         $linkify
 *
 */
final class PluginContainer extends DiContainer {

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->setFactory('config', '\hypeJunction\Scraper\Config\Config::factory');

		$this->setFactory('events', function (PluginContainer $c) {
			return new Events($c->config, $c->router, $c->upgrades);
		});

		$this->setFactory('hooks', function (PluginContainer $c) {
			return new PluginHooks($c->config, $c->router, $c->resources, $c->extractor, $c->linkify);
		});

		$this->setFactory('router', function (PluginContainer $c) {
			return new Router($c->config, $c->resources);
		});

		$this->setFactory('upgrades', function (PluginContainer $c) {
			return new Upgrades($c->config);
		});

		$this->setClassName('httpCache', '\hypeJunction\Scraper\Http\Cache');

		$this->setFactory('httpClient', function(PluginContainer $c) {
			return new Client('', $c->config->getHttpClientConfig());
		});
		$this->setFactory('httpResource', function(PluginContainer $c) {
			return new Resource($c->httpClient, $c->httpCache);
		});

		$this->setFactory('parser', function(PluginContainer $c) {
			return new Parser($c->config, $c->httpResource, $c->domParser, $c->oEmbedParser, $c->imageParser, $c->iframelyParser, $c->embedlyParser);
		});

		$this->setFactory('domParser', function(PluginContainer $c) {
			return new domParser($c->httpResource);
		});
		$this->setFactory('oEmbedParser', function(PluginContainer $c) {
			return new oEmbedParser($c->httpResource);
		});
		$this->setFactory('imageParser', function(PluginContainer $c) {
			return new imageParser($c->httpResource);
		});
		
		$this->setFactory('iframelyParser', function(PluginContainer $c) {
			return new iframelyParser($c->config, $c->httpResource);
		});
		$this->setFactory('embedlyParser', function(PluginContainer $c) {
			return new embedlyParser($c->config, $c->httpResource);
		});

		$this->setFactory('resourceCache', function(PluginContainer $c) {
			return new ResourceCache($c->config);
		});

		$this->setFactory('resources', function(PluginContainer $c) {
			return new Resources($c->config, $c->parser, $c->resourceCache);
		});

		$this->setClassName('extractor', '\hypeJunction\Scraper\Services\Extractor');
		$this->setFactory('linkify', function(PluginContainer $c) {
			return new Linkify($c->config, $c->resources);
		});

	}

	/**
	 * Creates a new  ServiceProvider instance
	 * @return PluginContainer
	 */
	public static function create() {
		return new PluginContainer();
	}

}
