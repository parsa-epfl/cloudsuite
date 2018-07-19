<?php

/**
 * Url Sniffer by Steve Clay (ufcoe)
 * @link https://gist.github.com/mrclay/5016415
 */
namespace UFCOE\Elgg;

class Url {

	protected $host;
	protected $path;
	protected $scheme;

	/**
	 * @param string $siteUrl if not given, will retrieve from elgg_get_site_url()
	 * @throws \InvalidArgumentException
	 */
	public function __construct($siteUrl = null) {
		if (!$siteUrl && is_callable('elgg_get_site_url')) {
			$siteUrl = elgg_get_site_url();
		}
		if (!preg_match('~^(https?)\\://([^/]+)(/.*)~', $siteUrl, $m)) {
			throw new \InvalidArgumentException('$siteUrl must be full URL');
		}
		$this->scheme = $m[1];
		$this->host = $m[2];
		$this->path = $m[3];
	}

	/**
	 * @param string $url
	 * @return int 0 if no GUID found
	 */
	public function getGuid($url) {
		$url = $this->analyze($url);
		return ($url && $url['guid']) ? $url['guid'] : 0;
	}

	/**
	 * @param string $url
	 * @return array|bool
	 */
	public function analyze($url) {
		$url = trim($url);
		if (!preg_match('~^(https?)\\://([^/]+)(/[^\\?]*)~', $url, $m)) {
			return false;
		}
		list (, $scheme, $host, $path) = $m;
		$ret = array(
			'scheme_matches' => ($scheme === $this->scheme),
			'host_matches' => ($host === $this->host),
			'guid' => null,
			'action' => null,
			'handler' => null,
			'handler_segments' => array(),
		);
		$ret['in_site'] = ($ret['host_matches'] && (0 === strpos($path, $this->path)));
		if ($ret['in_site']) {
			$sitePath = substr($path, strlen($this->path));
			if (preg_match('~^action/(.*)~', $sitePath, $m)) {
				if (preg_match('~^[^/]~', $m[1])) {
					$ret['action'] = $m[1];
				}
			} else {
				$segments = explode('/', $sitePath);
				if (!empty($segments[0])) {
					$ret['handler'] = $segments[0];
					$ret['handler_segments'] = array_slice($segments, 1);
					if ($segments[0] !== 'profile') {
						if ((count($segments) >= 3)
								&& in_array($segments[1], array('view', 'read'))
								&& preg_match('~^[1-9]\\d*$~', $segments[2])) {
							$ret['guid'] = (int) $segments[2];

							// less-reliable guessing
						} elseif (preg_match('~^(?:[^/]+/)+([1-9]\\d*)(?:$|/)~', $sitePath, $m)) {
							$ret['guid'] = (int) $m[1];
						}
					}
				}
			}
		}
		return $ret;
	}
}
