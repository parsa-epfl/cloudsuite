<?php

/**
 * Extract hashtags, urls, emails and usernames from text
 * Render an html view of the text
 */

namespace hypeJunction\Util;

use UFCOE\Elgg\Url;

class Extractor {

	const REGEX_HASHTAG = '/(^|[^\w])#(\w*[^\s\d!-\/:-@]+\w*)/';
	const REGEX_URL = '/(?<![=\/"\'])((ht|f)tps?:\/\/[^\s\r\n\t<>"\']+)/i';
	const REGEX_EMAIL = '/(^|[^\w])([\w\-\.]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})/i';
	const REGEX_USERNAME = '/(^|[^\w])@([\p{L}\p{Nd}._]+)/u';
	const URL_BASE_HASHTAG = 'search?search_type=tags&q={$hashtag}';
	const URL_BASE_EMAIL = 'mailto:{$email}';

	protected $text;
	public $html;
	public $hashtags;
	public $urls;
	public $emails;
	public $usernames;

	function __construct($text = '') {

		$this->text = $text;
		$this->hashtags = self::extractHashtags($text);
		$this->urls = self::extractURLs($text);
		$this->emails = self::extractEmails($text);
		$this->usernames = self::extractUsernames($text);
		$this->html = $text;
		$this->renderHTML();
	}

	public static function extract($text = '') {
		$extractor = new Extractor($text);
		return $extractor;
	}

	public static function render($text = '') {
		$extractor = new Extractor($text);
		return $extractor->html;
	}

	public static function extractHashtags($text = '') {
		$matches = array();
		preg_match_all(self::REGEX_HASHTAG, $text, $matches);
		return $matches[2];
	}

	public static function renderHashtagHTML($hashtag, $url_base = '') {
		if (!$url_base) {
			$url_base = self::URL_BASE_HASHTAG;
		}
		$url = str_replace('{$hashtag}', $hashtag, $url_base);
		return elgg_view('output/url', array(
			'text' => "#{$hashtag}",
			'href' => $url,
			'class' => 'extractor-hashtag',
		));
	}

	public static function extractURLs($text = '') {
		$matches = array();
		preg_match_all(self::REGEX_URL, $text, $matches);
		return $matches[1];
	}

	public static function renderURLHTML($url) {

		$favicon = "http://g.etfv.co/{$url}";

		if (class_exists('UFCOE\\Elgg\\Url')) {
			$sniffer = new Url();
			$guid = $sniffer->getGuid($url);
			if ($entity = get_entity($guid)) {
				$favicon = $entity->getIconURL('tiny');
				if (elgg_instanceof($entity->user)) {
					$text = "@{$entity->username}";
				} else {
					$text = (isset($entity->name)) ? $entity->name : $entity->title;
				}
			}
		}

		if (!$text) {
			$embedder = new Embedder($url);
			$meta = $embedder->extractMeta('oembed');
			if ($meta->title) {
				$text = $meta->title;
			} else {
				$text = elgg_get_excerpt($url, 35);
			}
		}

		return elgg_view('output/url', array(
			'text' => "<span class=\"favicon\" style=\"background-image:url($favicon)\"></span><span class=\"link\">$text</span>",
			'href' => $url,
			'class' => 'extractor-link',
		));
	}

	public static function extractEmails($text = '') {
		$matches = array();
		preg_match_all(self::REGEX_EMAIL, $text, $matches);
		return $matches[2];
	}

	public static function renderEmailHTML($email, $url_base = '') {
		if (!$url_base) {
			$url_base = self::URL_BASE_EMAIL;
		}
		$url = str_replace('{$email}', $email, $url_base);
		return elgg_view('output/url', array(
			'text' => $email,
			'href' => $url,
			'class' => 'extractor-email'
		));
	}

	public static function extractUsernames($text = '') {
		$matches = array();
		preg_match_all(self::REGEX_USERNAME, $text, $matches);
		return $matches[2];
	}

	public static function renderUsernameHTML($username) {
		$user = get_user_by_username($username);
		if (!elgg_instanceof($user)) {
			return "@$username";
		}
		$favicon = $user->getIconURL('tiny');
		return elgg_view('output/url', array(
			'text' => "<span class=\"favicon\" style=\"background-image:url($favicon)\"></span><span class=\"link\">@{$user->username}</span>",
			'title' => $user->name,
			'href' => $user->getURL(),
			'class' => 'extractor-username'
		));
	}

	protected function renderHTML() {

		if (count($this->hashtags)) {
			foreach ($this->hashtags as $hashtag) {
				$atag = self::renderHashtagHTML($hashtag);
				$this->html = str_replace("#{$hashtag}", $atag, $this->html);
			}
		}
		if (count($this->urls)) {
			foreach ($this->urls as $url) {
				$atag = self::renderURLHTML($url);
				$this->html = str_replace($url, $atag, $this->html);
			}
		}
		if (count($this->emails)) {
			foreach ($this->emails as $email) {
				$atag = self::renderEmailHTML($email);
				$this->html = str_replace($email, $atag, $this->html);
			}
		}
		if (count($this->usernames)) {
			foreach ($this->usernames as $username) {
				$atag = self::renderUsernameHTML($username);
				$this->html = str_replace("@{$username}", $atag, $this->html);
			}
		}

		return $this->html;
	}

}
