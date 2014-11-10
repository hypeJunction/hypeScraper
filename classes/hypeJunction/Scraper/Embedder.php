<?php

/**
 * Generate an embeddable view from a URL
 * 
 * @package    HypeJunction
 * @subpackage Scraper
 */

namespace hypeJunction\Scraper;

use ElggEntity;

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
	 * An object describing useful meta tags associated with the URL, 
	 * e.g. title, description, alternate links, Open Graph tags
	 * @var MetaHandler 
	 */
	protected $meta;

	/**
	 * Local entity this URL represents (if any)
	 * @var ElggEntity 
	 */
	protected $entity;

	/**
	 * Cached meta tags
	 * @var array 
	 */
	static $cache;

	/**
	 * Construct a new Embedder
	 * 
	 * @param UrlHandler|string $url URL to embed
	 * @return Embedder
	 */
	function __construct($url = '') {
		$this->setURL($url);
	}

	/**
	 * Get embed HTML view for an entity or URL
	 * 
	 * @param mixed  $asset  URL or entity to embed
	 * @param array  $params Rendering options
	 * @return string HTML
	 */
	public static function getEmbedView($asset = '', $params = array()) {

		$embedder = new Embedder;
		if ($asset instanceof ElggEntity) {
			$embedder->setEntity($asset);
		} else {
			$embedder->setUrl($asset);
		}
		return $embedder->getView($params);
	}

	/**
	 * Normalize and set URL
	 * 
	 * @param UrlHandler|string $url URL to embed
	 * @return UrlHandler
	 */
	public function setURL($url = '') {
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
		$this->entity = $entity;
		$this->setURL($entity->getURL());
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
		if (!isset($this->meta)) {
			$this->meta = $this->url->getMeta();
		}
		return $this->meta;
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
	 * @param string $url URL to embed
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
	 * Render embed HTML
	 * 
	 * @param array $params Params to pass to views and hooks
	 * @return string HTML
	 */
	public function getView(array $params = array()) {

		$type = $this->getType();

		$embed_params = array_filter(array(
			'src' => $this->url->getURL(),
			'entity' => $this->getEntity(),
			'meta' => $this->getMeta(),
		));

		$view = "framework/scraper/embed/$type";
		$params = array_merge($params, $embed_params);

		$output = elgg_view($view, $params);
		return elgg_trigger_plugin_hook("output:$type", 'embed', $params, $output);
	}

}
