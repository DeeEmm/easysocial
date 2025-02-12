<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/base.php');
require_once(dirname(dirname(__DIR__))  . '/vendor/autoload.php');

use GuzzleHttp\Client;

class SocialConnectorGuzzle extends SocialConnectorAdapter
{
	private $client = null;
	private $response = null;

	public function __construct($url = '')
	{
		parent::__construct($url);

		$this->client = new Client();
	}

	/**
	 * Performs the request to the url
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function execute()
	{
		$options = array_merge($this->options, [
			'curl' => [
				CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem',
                CURLOPT_SSL_VERIFYPEER => false
			]
		]);

		$queries = $this->extractQueryString($this->url);

		if ($this->headers) {
			$options['headers'] = $this->headers;
		}

		if ($this->query) {
			$queries = array_merge($queries, $this->query);
		}

		$options['query'] = $queries;

		// Making request for head only
		if ($this->headerOnly) {
			$this->response = $this->client->head($this->url, $options);
			return $this;
		}

		// Standard request
		try {

			$this->response = $this->client->request($this->method, $this->url, $options);

		} catch (Exception $e) {
			$exception = ES::response($e->getMessage(), ES_ERROR);

			$this->response = $exception;
		}

		return $this;
	}

	/**
	 * Returns the result that has already been executed.
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function getResult($url = null, $withHeaders = false)
	{
		// Do not proceed this if fall into exception case
		if ($this->response instanceof SocialResponse) {

			if (isset($this->response->message) && $this->response->message) {
				return $this->response->message;
			}
		}

		$contents = (string) $this->response->getBody();

		if ($this->headerOnly) {
			$headers = $this->getResponseHeaders();

			return $headers;
		}

		if ($withHeaders) {
			$headers = $this->getResponseHeaders();

			return $headers . "\r\n\r\n" . $contents;
		}

		return $contents;
	}

	/**
	 * Formats the response headers from guzzle into standard string
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function getResponseHeaders()
	{
		// Do not proceed this if fall into exception case
		if ($this->response instanceof SocialResponse) {

			if (isset($this->response->message) && $this->response->message) {
				return $this->response->message;
			}
		}

		$headers = '';

		// Get all of the response headers.
		$data = $this->response->getHeaders();

		foreach ($data as $name => $values) {
			$headers .= $name . ': ' . implode(', ', $values) . "\r\n";
		}

		return $headers;
	}

	/**
	 * Determines if the connection failed
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function hasException()
	{
		return ($this->response instanceof SocialResponse);
	}

	/**
	 * Allows caller to set the options to be passed to Guzzle
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function setOptions($option, $value)
	{
		$this->options[$option] = $value;
	}
}
