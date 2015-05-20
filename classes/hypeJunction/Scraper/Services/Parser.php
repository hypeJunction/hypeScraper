<?php

namespace hypeJunction\Scraper\Services;

use hypeJunction\Scraper\Config;
use hypeJunction\Scraper\Http\Resource;

class Parser {

	const SERVICE_DOM = 'dom_parser';
	const SERVICE_IFRAMELY = 'iframely';
	const SERVICE_EMBEDLY = 'embedly';

	private $config;
	private $httpResource;
	private $domParser;
	private $oEmbedParser;
	private $imageParser;
	private $iframelyParser;
	private $embedlyParser;

	public function __construct(Config $config, Resource $resource, domParser $domParser, oEmbedParser $oEmbedParser, imageParser $imageParser, iframelyParser $iframelyParser, embedlyParser $embedlyParser) {
		$this->config = $config;
		$this->httpResource = $resource;
		$this->domParser = $domParser;
		$this->oEmbedParser = $oEmbedParser;
		$this->imageParser = $imageParser;
		$this->iframelyParser = $iframelyParser;
		$this->embedlyParser = $embedlyParser;
	}

	public function parse($url = '', array $options = array()) {

		$service = $this->config->get('service');

		if ($this->httpResource->isImage($url, $options)) {
			$data = $this->imageParser->parse($url, $options);
		} else if ($this->httpResource->isJSON($url, $options) || $this->httpResource->isXML($url, $options)) {
			$data = $this->oEmbedParser->parse($url, $options);
		} else if ($this->httpResource->isHTML($url, $options)) {
			if ($service == self::SERVICE_IFRAMELY) {
				$data = $this->iframelyParser->parse($url, $options);
			} else if ($service == self::SERVICE_EMBEDLY) {
				$data = $this->embedlyParser->parse($url, $options);
			} else {
				$data = $this->domParser->parse($url, $options);
				if (!empty($data['oembed_url'])) {
					foreach ($data['oembed_url'] as $oembed_url) {
						$oembed_data = $this->parse($oembed_url, $options);
						$oembed_data['oembed_url'] = $oembed_data['url'];
						unset($oembed_data['url']);
						if (!empty($oembed_data) && is_array($oembed_data)) {
							$data = array_merge($data, $oembed_data);
						}
					}
				}
			}
		}

		if (!is_array($data)) {
			$data = array();
		}

		$data['__url'] = $url;

		return elgg_trigger_plugin_hook('parse', 'framework:scraper', array(
			'url' => $url,
			'options' => $options,
				), $data);
	}

}
