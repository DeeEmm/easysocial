<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialGiphy extends EasySocial
{
	private $base = 'https://api.giphy.com/v1/';

	private $endpoints = [
		'search' => '/search',
		'trending' => '/trending'
	];

	public $items = null;
	public $key = null;
	public $limit = 0;

	public function __construct()
	{
		parent::__construct();

		$this->items = null;
		$this->base = $this->base;
		$this->key = $this->config->get('giphy.apikey');
		$this->limit = $this->config->get('giphy.limit') + 1;
	}

	/**
	 * Request data from the GIPHY API
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getData($query = false, $type = 'gifs', $options = [])
	{
		if (!$this->isEnabled() || !$this->key) {
			return false;
		}

		$result = $this->getItems($query, $type, $options);

		if (isset($result->data)) {
			$this->items = $result->data;

			return $this->items;
		}

		return [];
	}

	/**
	 * Get giphy items from the connector with the query given
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function getItems($query, $type, $options = [])
	{
		$url = $this->getUrl('trending', $type);
		$queryStrings = $this->trending();

		if ($query) {
			$url = $this->getUrl('search', $type);
			$queryStrings = $this->search($query);
		}

		$offset = ES::normalize($options, 'offset', null);

		if ($offset) {
			$queryStrings['offset'] = $offset;
		}

		$url .= '?' . http_build_query($queryStrings);

		$connector = ES::connector($url);
		$data = $connector
					->execute()
					->getResult();

		$items = json_decode($data);

		return $items;
	}

	/**
	 * Request data from the GIPHY API
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function toExportData()
	{
		$data = [];

		foreach ($this->items as $giphy) {
			$obj = new stdClass();
			$obj->preview = $giphy->images->fixed_width->url;
			$obj->original = $giphy->images->original->url;

			$data[] = $obj;
		}

		return $data;
	}

	/**
	 * Retrieve the url for a specific endpoint
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getUrl($endpoint, $type)
	{
		if (!isset($this->endpoints[$endpoint])) {
			return $this->base;
		}

		// NOTE: $type will be 'gifs' or 'stickers' only
		$url = $this->base . $type . $this->endpoints[$endpoint];

		return $url;
	}

	/**
	 * Search Endpoint
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function search($query)
	{
		$options = [];
		$options['api_key'] = $this->key;
		$options['q'] = $query;
		$options['limit'] = $this->limit;

		return $options;
	}

	/**
	 * Trending Endpoint
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function trending()
	{
		$options = [];
		$options['api_key'] = $this->key;
		$options['limit'] = $this->limit;

		return $options;
	}

	/**
	 * Determine whether is it enabled or not
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isEnabled()
	{
		if (!$this->config->get('giphy.enabled')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether is it enabled or not for conversations
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isEnabledForConversations()
	{
		if (!$this->isEnabled() || !$this->config->get('conversations.giphy')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether is it enabled or not for story
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isEnabledForStory()
	{
		if (!$this->isEnabled() || !$this->config->get('stream.story.giphy')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether is it enabled or not for comments
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isEnabledForComments()
	{
		if (!$this->isEnabled() || !$this->config->get('comments.giphy')) {
			return false;
		}

		return true;
	}

	/**
	 * Check for a valid GIPHY URL
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isValidUrl($url)
	{
		$pattern = "/(^|\\s)(https?:\\/\\/)?(([a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.([a-z]{2,6}))|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(:[0-9]{1,5})?(\\/.*)?/uism";

		$match = preg_match($pattern, $url);

		if (!$match) {
			return false;
		}

		// If it contains single or double quote, just return false
		if (stristr($url, '"') != false || stristr($url, "'") != false) {
			return false;
		}

		if (stristr($url, 'giphy.com') == false) {
			return false;
		}

		return true;
	}
}
