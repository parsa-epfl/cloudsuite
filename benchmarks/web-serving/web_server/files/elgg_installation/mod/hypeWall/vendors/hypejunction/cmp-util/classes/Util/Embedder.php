<?php

/**
 * Generate an embeddable view from a URL
 */

namespace hypeJunction\Util;

use ElggEntity;
use ElggFile;
use Exception;
use UFCOE\Elgg\Url;

class Embedder {

	const IframelyGateway = 'http://iframely.com/';

	protected $url;
	protected $guid;
	protected $entity;
	protected $view;
	static $cache;

	function __construct($url = '') {

		if (!self::isValidURL($url)) {
			throw new Exception("Embedder class expects a valid URL");
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
	 * Validate URL format and accessibility
	 * @param string $url
	 * @return boolean
	 */
	public static function isValidURL($url = '') {
		if (!$url || !is_string($url) || !filter_var($url, FILTER_VALIDATE_URL) || !($fp = curl_init($url))) {
			return false;
		}
		return true;
	}

	/**
	 * Checks URL headers to determine whether the content type is image
	 * @param string $url
	 */
	public static function isImage($url = '') {
		if (!self::isValidURL($url)) {
			return false;
		}

		$headers = get_headers($url, 1);
		if (is_string($headers['Content-Type']) && substr($headers['Content-Type'], 0, 6) == 'image/') {
			return true;
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
			return elgg_view('output/longtext', array(
				'value' => $url
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
		} else if (self::isImage($this->url)) {
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
	 * @return string
	 */
	private function getSrcView($params = array()) {

		$meta = $this->extractMeta('oembed');

		$title = $meta->title;

		if ($meta->provider_name) {
			$class = 'embed-' . preg_replace('/[^a-z0-9\-]/i', '-', strtolower($meta->provider_name));
		}

		switch ($meta->type) {

			default :
				$link = elgg_view('output/url', array(
					'href' => $this->url
				));
				$body = elgg_view('output/longtext', array(
					'value' => $link,
				));
				break;

			case 'link' :

				if ($meta->thumbnail_url) {
					$icon = elgg_view('output/img', array(
						'src' => $meta->thumbnail_url,
						'width' => 100,
					));
				}

				$description = elgg_view('output/longtext', array(
					'value' => $meta->description,
				));

				$footer = elgg_view('output/url', array(
					'href' => ($meta->canonical) ? $meta->canonical : $meta->url,
					'target' => '_blank',
				));

				$body = elgg_view_image_block($icon, $description);
				break;

			case 'photo' :

				$body = elgg_view('output/url', array(
					'text' => elgg_view('output/img', array(
						'src' => $meta->url,
						'alt' => $meta->title,
					)),
					'href' => $meta->canonical,
					'target' => '_blank',
				));

				$footer = elgg_view('output/url', array(
					'href' => ($meta->canonical) ? $meta->canonical : $meta->url,
					'target' => '_blank',
				));
				break;

			case 'rich' :
			case 'video' :

				$title = $meta->title;
				$footer = elgg_view('output/url', array(
					'href' => ($meta->canonical) ? $meta->canonical : $meta->url,
					'target' => '_blank',
				));
				$body = $meta->html;
				break;
		}

		$output = elgg_view_module('embed', $title, $body, array(
			'class' => $class,
			'footer' => $footer,
		));

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
	 * Extract page oembed/iframely tags
	 * @param string $endpoint
	 * @return array
	 */
	public function extractMeta($endpoint = '') {

		if (isset(self::$cache[$this->url][$endpoint])) {
			return self::$cache[$this->url][$endpoint];
		}

		switch ($endpoint) {
			case 'oembed' :
				$gateway = $this->getGateway() . 'oembed?url=' . $this->url;
				break;

			default :
			case 'iframely' :
				$gateway = $this->getGateway() . 'iframely?uri=' . $this->url;
				break;
		}

		$ch = curl_init($gateway);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, false);
		$json = curl_exec($ch);
		curl_close($ch);

		$meta = json_decode($json);
		self::$cache[$this->url][$endpoint] = $meta;
		return $meta;
	}

	/**
	 * Get Iframely server base URL
	 * @return string
	 */
	private function getGateway() {
		return elgg_trigger_plugin_hook('iframely.gateway', 'embed', null, self::IframelyGateway);
	}

}
