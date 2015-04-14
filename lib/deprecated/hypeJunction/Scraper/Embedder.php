<?php

namespace hypeJunction\Scraper;

use ElggEntity;

/**
 * Generate an embeddable view from a URL
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */
class Embedder {

	const TYPE_ENTITY = 'entity';
	const TYPE_IMAGE = 'image';
	const TYPE_SRC = 'src';
	const TYPE_DEFAULT = 'default';

	/**
	 * URL to be embedded
	 * @var UrlHandler 
	 */
	protected $url;

	/**
	 * Local entity this URL represents (if any)
	 * @var ElggEntity 
	 */
	protected $entity;

	/**
	 * Singleton
	 * @var Embedder 
	 */
	static $instance;
	
	/**
	 * Construct a new Embedder
	 * 
	 * @param UrlHandler|string $url URL to embed
	 * @return Embedder
	 */
	function __construct($url = '') {
		$this->setURL($url);
		self::$instance = $this;
	}

	/**
	 * Get embed HTML view for an entity or URL
	 * 
	 * @param mixed $resource URL or entity to embed
	 * @param array $params   Rendering options
	 * @return string HTML
	 */
	public static function getEmbedView($resource = '', $params = array()) {

		$embedder = self::getInstance();
		if ($resource instanceof ElggEntity) {
			$embedder->setEntity($resource);
		} else {
			$embedder->setURL($resource);
		}
		return $embedder->getView($params);
	}

	/**
	 * Get singleton
	 * @return Embedder
	 */
	public static function getInstance() {
		if (isset(self::$instance)) {
			return self::$instance;
		}
		return new Embedder;
	}
	
	/**
	 * Normalize and set URL
	 * 
	 * @param UrlHandler|string $url URL to embed
	 * @return UrlHandler
	 */
	public function setURL($url = '') {
		unset($this->entity);
		if ($url instanceof UrlHandler) {
			$this->url = $url;
		} else {
			$this->url = new UrlHandler($url);
		}
		return $this->url;
	}

	/**
	 * Get normalized URL
	 * @return UrlHandler
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Set an entity to embed
	 * Overwrites the URL with that of an entity
	 * 
	 * @param ElggEntity $entity Entity
	 * @return ElggEntity
	 */
	public function setEntity(ElggEntity $entity) {
		$this->setURL($entity->getURL());
		$this->entity = $entity;
	}

	/**
	 * Get entity sniffed from URL
	 * @return ElggEntity|false
	 */
	public function getEntity() {
		if (isset($this->entity)) {
			return $this->entity;
		}
		return $this->url->getEntity();
	}

	/**
	 * Get meta tags parsed from URL
	 * @return MetaHandler
	 */
	public function getMeta() {
		return $this->url->getMeta();
	}

	/**
	 * Get cached page metadata for a URL
	 * @return MetaHandler
	 * @deprecated 1.1
	 */
	public function extractMeta() {
		return $this->getMeta();
	}

	/**
	 * Determine what type of embed to use
	 * Returns one of the following values:
	 *   'entity' - URL represents and entity
	 *   'image' - URL points to an image file
	 *   'src' - URL points to a parseable resource/page
	 *   'default' - URL has no meaningful meta tags
	 *
	 * @return string
	 */
	public function getType() {
		if ($this->getEntity()) {
			return self::TYPE_ENTITY;
		} else if ($this->url->isValid() && $this->url->isReachable()) {
			if ($this->url->isImageFile()) {
				return self::TYPE_IMAGE;
			} else if ($this->getMeta()) {
				return self::TYPE_SRC;
			}
		}
		return self::TYPE_DEFAULT;
	}

	/**
	 * Prepare view/hook params
	 * 
	 * @param array $params Params to pass by default
	 * @return array
	 */
	public function prepareParams(array $params = array()) {
		
		$embed_params = array_filter(array(
			'src' => $this->url->getURL(),
			'entity' => $this->getEntity(),
			'meta' => $this->getMeta(),
		));
				
		$params = array_merge($params, $embed_params);
		return $params;
	}
	
	/**
	 * Render embed HTML
	 * 
	 * @param array $params Params to pass to views and hooks
	 * @return string HTML
	 */
	public function getView(array $params = array()) {
		
		$output = '';
		
		$type = $this->getType();

		$view = "framework/scraper/embed/$type";
		$params = $this->prepareParams($params);
			
		if (is_callable('elgg_view')) {
			$output = elgg_view($view, $params);
		}
		
		return elgg_trigger_plugin_hook("output:$type", 'embed', $params, $output);
	}

}
