<?php

/**
 * Generate an embeddable view from a URL
 */

namespace hypeJunction\Scraper;

use ElggEntity;
use ElggFile;
use Exception;
use UFCOE\Elgg\Url;

class Embedder {

	protected $url;
	protected $guid;
	protected $entity;
	protected $view;
	protected $meta;
	static $cache;

	function __construct($url = '') {

		if (!Validator::isValidURL($url)) {
			throw new Exception(get_class()  . " expects a valid URL ($url)");
		}

		$this->url = $url;

		$sniffer = new Url();
		$guid = $sniffer->getGuid($this->url);
		if ($guid) {
			$this->guid = $guid;
			$this->entity = get_entity($guid);
		}
	}

	/**
	 * Get an embeddable representation of a URL
	 * @param string $url	URL to embed
	 * @param array $params	Additional params
	 * @return string		HTML
	 */
	public static function getEmbedView($url = '', $params = array()) {

		try {
			if ($url instanceof ElggEntity) {
				$url = $url->getURL();
			}
			$embedder = new Embedder($url);
			return $embedder->getView($params);
		} catch (Exception $ex) {
			elgg_log($ex->getMessage(), 'ERROR');
			return elgg_view('output/longtext', array(
				'value' => $url,
				'class' => 'embedder-invalid-url',
			));
		}
	}

	/**
	 * Determine what view to return
	 * @return string
	 */
	private function getView($params = array()) {

		if (elgg_instanceof($this->entity)) {
			return $this->getEntityView($params = array());
		} else if (Validator::isImage($this->url)) {
			return $this->getImageView($params = array());
		}

		return $this->getSrcView($params = array());
	}

	/**
	 * Render a uniform view for embedded entities
	 * Use 'output:entity', 'embed' hook to override the output
	 * @return string
	 */
	private function getEntityView($params = array()) {

		$entity = $this->entity;

		if ($entity instanceof ElggFile) {

			$size = ($entity->simpletype == 'image') ? 'large' : 'small';
			$output = elgg_view_entity_icon($entity, $size);
		} else {

			elgg_push_context('widgets');
			if (!isset($params['full_view'])) {
				$params['full_view'] = false;
			}
			$output = elgg_view_entity($entity, $params);
			elgg_pop_context();
		}

		$params['entity'] = $this->entity;
		$params['src'] = $this->url;

		return elgg_trigger_plugin_hook('output:entity', 'embed', $params, $output);
	}

	/**
	 * Render a uniform view for embedded links
	 * Use 'output:src', 'embed' hook to override the output
	 * @param array $params		Additional params to pass to the hook
	 * @uses mixed $params['module']	Name of the module to wrap the output in, or false
	 * @return string
	 */
	private function getSrcView($params = array()) {

		$meta = $this->extractMeta();

		$class = array();
		
		if ($meta->provider_name) {
			$class[] = 'embed-' . preg_replace('/[^a-z0-9\-]/i', '-', strtolower($meta->provider_name));
		}

		if ($meta->html) {
			$body = str_replace('http://', '//', $meta->html);
		} else {
			if ($meta->oembed_url && $meta->type == 'photo') {
				$icon = elgg_view('output/url', array(
					'text' => elgg_view('output/img', array(
						'src' => str_replace('http://', '//', $meta->oembed_url),
						'alt' => $meta->title,
					)),
					'href' => $meta->canonical,
					'class' => 'embedder-photo',
					'target' => '_blank',
				));
			}
			if ($meta->thumbnail_url) {
				$icon = elgg_view('output/img', array(
					'src' => $meta->thumbnail_url,
					'width' => 100,
					'class' => 'embedder-thumbnail',
				));
			}
		}

		if (!$body && $meta->description) {
			$body .= elgg_view('output/longtext', array(
				'value' => elgg_get_excerpt($meta->description),
				'class' => 'embedder-description'
			));
		}

		$footer = elgg_view('output/url', array(
			'href' => ($meta->canonical) ? $meta->canonical : $meta->url,
			'target' => '_blank',
		));

		if (!$body) {
			$body = $footer;
			$footer = false;
		}

		if ($icon) {
			$body = elgg_view_image_block($icon, $body, array(
				'class' => 'embedder-image-block',
			));
		}

		if ($module = elgg_extract('module', $params, 'embed')) {
			$class[] = ($meta->type) ? "elgg-module-embed-$meta->type" : '';
			$output = elgg_view_module($module, $meta->title, $body, array(
				'class' => implode(' ', array_filter($class)),
				'footer' => $footer,
			));
		} else {
			$output = $body;
		}

		$params['src'] = $this->url;
		$params['meta'] = $meta;

		return elgg_trigger_plugin_hook('output:src', 'embed', $params, $output);
	}

	/**
	 * Wrap an image url into a params tag
	 * @param type $params
	 */
	public function getImageView($params = array()) {

		$body = elgg_view('output/img', array(
			'src' => $this->url,
		));

		$output = elgg_view_module('embed', false, $body, array(
			'footer' => elgg_view('output/url', array(
				'href' => $this->url,
			))
		));
		return elgg_trigger_plugin_hook('output:image', 'embed', $params, $output);
	}

	/**
	 * Extract page meta tags
	 * @return array
	 */
	public function extractMeta() {

		if (isset(self::$cache[$this->url])) {
			return self::$cache[$this->url];
		}

		$this->prepareMeta();

		self::$cache[$this->url] = $this->meta;
		return $this->meta;
	}

	/**
	 * Extract meta tags and oEmbed embed code from the remote URL
	 * @return void
	 */
	private function prepareMeta() {
		$this->meta = Parser::getMeta($this->url);
	}

}
