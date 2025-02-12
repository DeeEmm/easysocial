<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialConnectorAdapter
{
	protected $proxy = null;
	protected $proxyEnabled = null;
	protected $proxyAuth = '';
	protected $proxyUrl = '';

	// Headers
	protected $headers = array();

	// Query string
	protected $query = array();

	// Curl options
	protected $options = [];

	// Connection method
	protected $method = 'GET';

	// Determines if the connection is for headers only
	protected $headerOnly = false;

	public function __construct($url = '')
	{
		if ($url) {
			$this->url = $url;
		}

		$jConfig = ES::jConfig();

		$this->proxy = array(
			'enable' => $jConfig->getValue('proxy_enable'),
			'host' => $jConfig->getValue('proxy_host'),
			'port' => $jConfig->getValue('proxy_port'),
			'user' => $jConfig->getValue('proxy_port'),
			'pass' => $jConfig->getValue('proxy_pass')
		);

		if ($this->isProxyEnabled()) {
			$this->proxyUrl = $this->proxy['host'] . ':' . $this->proxy['port'];
			$this->proxyAuth = $this->proxy['user'] . ':' . $this->proxy['pass'];
		}
	}

	/**
	 * Determines the method used to connect
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function setMethod($method = 'GET')
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Determines if proxy is enabled
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function isProxyEnabled()
	{
		if (is_null($this->proxyEnabled)) {
			$this->proxyEnabled = false;

			if ($this->proxy['enable'] && $this->proxy['host'] && $this->proxy['port'] && $this->proxy['user'] && $this->proxy['pass']) {
				$this->proxyEnabled = true;
			}
		}

		return $this->proxyEnabled;
	}

	/**
	 * Adds a url to query
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function addUrl($url)
	{
		$this->url = $url;
		$this->urls[$url] = $url;

		return true;
	}

	/**
	 * Adds to the query string
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function addQuery($key, $value)
	{
		$this->query[$key] = $value;

		return $this;
	}

	/**
	 * Sets value in the header
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function addHeader($key, $value)
	{
		$this->headers[$key] = $value;

		return $this;
	}

	/**
	 * Deprecated. Use setReferer
	 *
	 * @deprecated	3.2.11
	 */
	public function addReferrer($url)
	{
		return $this->setReferer($url);
	}

	/**
	 * Extracts query string data into an associative array
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function extractQueryString($url)
	{
		// We need to merge the queries from the query string
		$tmp = parse_url($url, PHP_URL_QUERY);

		if (!$tmp) {
			return array();
		}

		parse_str($tmp, $queries);

		return $queries;
	}

	/**
	 * Sets the referer in the request
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function setReferer($referer)
	{
		$this->headers['Referer'] = $referer;

		return $this;
	}

	/**
	 * Sets the user agent for the request
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function setUserAgent($userAgent)
	{
		$this->headers['User-Agent'] = $userAgent;
		return $this;
	}

	/**
	 * Determins if we should only be requesting for head data only
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function useHeadersOnly()
	{
		$this->headerOnly = true;
		// $this->options[CURLOPT_HEADER]	= true;
		// $this->options[CURLOPT_NOBODY]	= true;

		return $this;
	}
}
