<?php

namespace hypeJunction\Scraper\Services;

use DOMDocument;
use hypeJunction\Scraper\Http\Resource;

class domParser extends Parser {

	private $httpResource;

	public function __construct(Resource $resource) {
		$this->httpResource = $resource;
	}

	public function parse($url = '', array $options = array()) {

		$doc = $this->getDOM($url, $options);

		$defaults = array(
			'url' => $url,
		);
		
		$link_tags = $this->parseLinkTags($doc, $options);
		$meta_tags = $this->parseMetaTags($doc);
		$img_tags = $this->parseImgTags($doc);

		$meta = array_merge_recursive($defaults, $link_tags, $meta_tags, $img_tags);

		if (empty($meta['title'])) {
			$meta['title'] = $this->parseTitle($doc);
		}

		foreach ($meta as $key => $value) {
			if (is_array($value)) {
				$meta[$key] = array_unique(array_filter($value));
			}
		}
		if (!empty($meta['thumbnails'])) {
			$meta['thumbnail_url'] = $meta['thumbnails'][0];
		} else if (!empty($meta['icons'])) {
			$meta['thumbnail_url'] = $meta['icons'][0];
		}
		
		return $meta;
	}

	public function getDOM($url = '', array $options = array()) {
		$html = $this->httpResource->html($url, $options);
		$doc = new DOMDocument();
		$doc->documentURI = $url;
		@$doc->loadHTML($html);
		return $doc;
	}

	/**
	 * Parses document title
	 *
	 * @param DOMDocument $doc Document
	 * @return type
	 */
	public function parseTitle(DOMDocument $doc) {
		$node = $doc->getElementsByTagName('title');
		$title = $node->item(0)->nodeValue;
		return ($title) ? : '';
	}

	public function parseLinkTags(DOMDocument $doc, array $options = array()) {

		$meta = array();

		// Get oEmbed content and canonical URLs
		$nodes = $doc->getElementsByTagName('link');
		foreach ($nodes as $node) {
			$rel = $node->getAttribute('rel');
			$href = $node->getAttribute('href');

			switch ($rel) {

				case 'icon' :
					$meta['icons'][] = $this->getAbsoluteURL($doc, $href);
					break;

				case 'canonical' :
					$meta['canonical'] = $this->getAbsoluteURL($doc, $href);
					break;

				case 'alternate' :
					$type = $node->getAttribute('type');
					if (in_array($type, array(
								'application/json+oembed',
								'text/json+oembed',
								'application/xml+oembed',
								'text/xml+oembed'
							))) {
						$meta['oembed_url'][] = $this->getAbsoluteURL($doc, $href);
					}
					break;
			}
		}

		return $meta;
	}

	public function parseMetaTags(DOMDocument $doc) {

		$meta = array();

		$nodes = $doc->getElementsByTagName('meta');
		if (!empty($nodes)) {
			foreach ($nodes as $node) {
				$name = $node->getAttribute('name');
				if (!$name) {
					$name = $node->getAttribute('property');
				}
				if (!$name) {
					continue;
				}
				$name = strtolower($name);

				$content = $node->getAttribute('content');

				$meta['metatags'][$name] = $content;

				switch ($name) {

					case 'title' :
					case 'og:title' :
					case 'twitter:title' :
						if (empty($meta['title'])) {
							$meta['title'] = $content;
						}
						break;

					case 'description' :
					case 'og:description' :
					case 'twitter:description' :
						if (empty($meta['description'])) {
							$meta['description'] = $content;
						}
						break;

					case 'keywords' :
						$meta['tags'] = string_to_tag_array($content);
						break;

					case 'og:site_name' :
					case 'twitter:site' :
						if (empty($meta['provider_name'])) {
							$meta['provider_name'] = $content;
						}
						break;

					case 'og:type':
						$meta['resource_type'] = $content;
						break;

					case 'og:image' :
					case 'twitter:image' :
						$meta['thumbnails'][] = $this->getAbsoluteURL($doc, $content);
						break;
				}
			}
		}

		return $meta;
	}

	public function parseImgTags(DOMDocument $doc) {

		$meta = array();

		$nodes = $doc->getElementsByTagName('img');
		foreach ($nodes as $node) {
			$src = $node->getAttribute('src');
			$meta['thumbnails'][] = $this->getAbsoluteURL($doc, $src);
		}

		return $meta;
	}

	public function getAbsoluteURL(DOMDocument $doc, $href = '') {

		// Check if $url is absolute
		if (parse_url($href, PHP_URL_HOST)) {
			return $href;
		}

		$uri = trim($doc->documentURI ? : '', '/');

		// Check if $url is relative to root
		if (substr($href, 0, 1) === "/") {
			$scheme = parse_url($uri, PHP_URL_SCHEME);
			$host = parse_url($uri, PHP_URL_HOST);
			return "$scheme$host$href";
		}

		// $url is relative to page
		return "$uri$href";
	}

}
