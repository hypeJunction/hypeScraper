<?php

namespace hypeJunction\Scraper;

/**
 * Scraper service provider
 *
 * @property-read \ElggPlugin                                   $plugin
 * @property-read \hypeJunction\Scraper\Config                  $config
 * @property-read \hypeJunction\Scraper\HookHandlers             $hooks
 * @property-read \hypeJunction\Scraper\Router                  $router
 * @property-read \Guzzle\Http\Client                           $httpClient
 * @property-read \hypeJunction\Scraper\Http\Cache              $httpCache
 * @property-read \hypeJunction\Scraper\Http\Resource           $httpResource
 * @property-read \hypeJunction\Scraper\Services\Parser         $parser
 * @property-read \hypeJunction\Scraper\Services\domParser      $domParser
 * @property-read \hypeJunction\Scraper\Services\oEmbedParser   $oEmbedParser
 * @property-read \hypeJunction\Scraper\Services\imageParser    $imageParser
 * @property-read \hypeJunction\Scraper\Services\embedlyParser  $embedlyParser
 * @property-read \hypeJunction\Scraper\Services\iframelyParser $iframelyParser
 * @property-read \hypeJunction\Scraper\Resources\Cache         $resourceCache
 * @property-read \hypeJunction\Scraper\Models\Resources        $resources
 * @property-read \hypeJunction\Scraper\Services\Extractor      $extractor
 * @property-read \hypeJunction\Scraper\Servivices\Linkify      $linkify
 */
final class Plugin extends \hypeJunction\Plugin {

	/**
	 * Instance
	 * @var self
	 */
	static $instance;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(\ElggPlugin $plugin) {

		$this->setValue('plugin', $plugin);
		
		$this->setFactory('config', function (Plugin $p) {
			return new \hypeJunction\Scraper\Config($p->plugin);
		});
		$this->setFactory('hooks', function (Plugin $p) {
			return new \hypeJunction\Scraper\HookHandlers($p->config, $p->router, $p->resources, $p->extractor, $p->linkify);
		});

		$this->setFactory('router', function (Plugin $p) {
			return new \hypeJunction\Scraper\Router($p->config, $p->resources);
		});

		$this->setClassName('httpCache', '\hypeJunction\Scraper\Http\Cache');

		$this->setFactory('httpClient', function(Plugin $p) {
			return new \Guzzle\Http\Client('', $p->config->getHttpClientConfig());
		});
		$this->setFactory('httpResource', function(Plugin $p) {
			return new \hypeJunction\Scraper\Http\Resource($p->httpClient, $p->httpCache);
		});

		$this->setFactory('parser', function(Plugin $p) {
			return new \hypeJunction\Scraper\Services\Parser($p->config, $p->httpResource, $p->domParser, $p->oEmbedParser, $p->imageParser, $p->iframelyParser, $p->embedlyParser);
		});

		$this->setFactory('domParser', function(Plugin $p) {
			return new \hypeJunction\Scraper\Services\domParser($p->httpResource);
		});
		$this->setFactory('oEmbedParser', function(Plugin $p) {
			return new \hypeJunction\Scraper\Services\oEmbedParser($p->httpResource);
		});
		$this->setFactory('imageParser', function(Plugin $p) {
			return new \hypeJunction\Scraper\Services\imageParser($p->httpResource);
		});

		$this->setFactory('iframelyParser', function(Plugin $p) {
			return new \hypeJunction\Scraper\Services\iframelyParser($p->config, $p->httpResource);
		});
		$this->setFactory('embedlyParser', function(Plugin $p) {
			return new \hypeJunction\Scraper\Services\embedlyParser($p->config, $p->httpResource);
		});

		$this->setFactory('resourceCache', function(Plugin $p) {
			return new \hypeJunction\Scraper\Resources\Cache($p->config);
		});

		$this->setFactory('resources', function(Plugin $p) {
			return new \hypeJunction\Scraper\Models\Resources($p->config, $p->parser, $p->resourceCache);
		});

		$this->setClassName('extractor', '\hypeJunction\Scraper\Services\Extractor');
		$this->setFactory('linkify', function(Plugin $p) {
			return new \hypeJunction\Scraper\Services\Linkify($p->config, $p->resources);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public static function factory() {
		if (null === self::$instance) {
			$plugin = elgg_get_plugin_from_id('hypeScraper');
			self::$instance = new self($plugin);
		}
		return self::$instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		elgg_register_event_handler('init', 'system', array($this, 'init'));
	}

	/**
	 * 'init','system' callback
	 */
	public function init() {

		elgg_register_page_handler($this->config->get('pagehandler_id'), array($this->router, 'handlePages'));

		elgg_register_plugin_hook_handler('format:src', 'embed', array($this->hooks, 'formatEmbedView'));
		elgg_register_plugin_hook_handler('extract:meta', 'all', array($this->hooks, 'getEmbedMetadata'));
		elgg_register_plugin_hook_handler('extract:qualifiers', 'all', array($this->hooks, 'extractQualifiers'));
		elgg_register_plugin_hook_handler('link:qualifiers', 'all', array($this->hooks, 'linkQualifiers'));

		elgg_extend_view('css/elgg', 'css/framework/scraper/stylesheet');

		if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
			elgg_register_simplecache_view('js/scraper/legacy/play');
			elgg_register_js('scraper/play', elgg_get_simplecache_url('js', 'scraper/legacy/play'), 'footer');
		}
	}

}
