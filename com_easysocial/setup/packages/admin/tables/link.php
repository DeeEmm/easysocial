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

ES::import('admin:/tables/table');

class SocialTableLink extends SocialTable
{
	/**
	 * The unique id
	 * @var int
	 */
	public $id = null;

	/**
	 * The unique url string (MD5-ed)
	 * @var string
	 */
	public $hash = null;

	/**
	 * Stores the data about the URL in JSON format
	 * @var string
	 */
	public $data = null;

	/**
	 * The time this url was logged
	 * @var datetime
	 */
	public $created = null;

	public function __construct($db)
	{
		parent::__construct('#__social_links', 'id', $db);
	}

	/**
	 * Loads a given record given the link
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadByLink($link)
	{
		$hash = md5($link);

		return $this->load(array('hash' => $hash));
	}

	/**
	 * Converts the data into a proper object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getData()
	{
		static $objects = array();

		if (!isset($objects[$this->id])) {
			$data = json_decode($this->data);

			$objects[$this->id]	= $data;
		}

		return $objects[$this->id];
	}

	/**
	 * Retrieves the image for the link object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getImage($assets)
	{
		$config = ES::config();

		// Get the image file
		$image = $assets->get('image');
		$cachePath = ltrim($config->get('links.cache.location'), '/');

		// @since 1.3.8
		// This block of code should be removed later.
		// FIX Older images where 'cached' state is not stored.
		// Check if the image string contains the cached storage path
		if (!$assets->get('cached') && (stristr($image, '/media/com_easysocial/cache') !== false)) {
			$assets->set('cached', true);
		}

		// Dirty way of checking
		// If the image is cached, we need to get the correct path
		if ($assets->get('cached')) {

			// First we try to load the image from the image link table
			$linkImage = ES::table('LinkImage');
			$exists = $linkImage->load(array('internal_url' => $image));

			// Default
			$image = rtrim(JURI::root(), '/') . '/' . $cachePath . '/' . basename($image);

			if ($exists) {
				$image = $linkImage->getUrl();
			}
		}

		return $image;
	}

	/**
	 * Retrieve the oembed object from the data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOembed()
	{
		$data = $this->getData();

		$oembed = isset($data->oembed) ? $data->oembed : false;

		if (!$oembed) {
			return false;
		}

		if (!is_object($oembed)) {
			return false;
		}

		if (!isset($oembed->isArticle)) {
			$oembed->isArticle = false;
		}

		// NYTimes.com uses asset_type:article
		if (isset($oembed->asset_type) && $oembed->asset_type == 'article') {
			$oembed->isArticle = true;
		}

		// For facebook, to determine whether or not the oembed is video,
		// Check for https://www.facebook.com/plugins/video in oembed->html
		if (isset($oembed->provider_name) && $oembed->provider_name == 'Facebook' && strpos($oembed->html, '://www.facebook.com/plugins/video') === false) {
			$oembed->isArticle = true;
		}

		// check if this is facebook video or not. #3256
		if (isset($data->opengraph) && isset($data->opengraph->type) && strpos($data->opengraph->type, 'video') !== false && (isset($oembed->html) && strpos($oembed->html, '://www.facebook.com/plugins/video') !== false)) {
			$oembed->type = $data->opengraph->type;
			$oembed->provider_url = $data->opengraph->url;
			$oembed->video_type = $data->opengraph->video_type;
			$oembed->width = $data->opengraph->video_width;
			$oembed->height = $data->opengraph->video_height;
		}

		if (isset($oembed->provider_name) && $oembed->provider_name == 'Twitter') {
			$config = ES::config();

			// If user is using dark theme, we should render dark theme for twitter embed
			if ($config->get('theme.site') == 'dark') {
				$oembed->html = str_replace('class="twitter-tweet"', 'class="twitter-tweet" data-theme="dark"', $oembed->html);
			}
		}

		// Fix video issues with youtube when site is on https
		if (isset($oembed->provider_url) && $oembed->provider_url == 'http://www.youtube.com/') {
			$oembed->html = ESJString::str_ireplace('http://', 'https://', $oembed->html);
			$oembed->thumbnail = str_ireplace('http://', 'https://', $oembed->thumbnail);
		}

		if (isset($oembed->html) && ES::config()->get('youtube.nocookie', false)) {
			$oembed->html = str_replace('youtube.com/', 'youtube-nocookie.com/', $oembed->html);
		}

		return $oembed;
	}

	/**
	 * function to determine if this embed is a facebook video or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isFacebookEmbed()
	{
		$oembed = $this->getOembed();

		if ($oembed !== false) {
			if (isset($oembed->provider_url) && stristr($oembed->provider_url, '://www.facebook.com') !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * function to determine if this embed is a twiter link or not.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isTwitterEmbed()
	{
		$oembed = $this->getOembed();

		if ($oembed !== false) {
			if (isset($oembed->provider_url) && stristr($oembed->provider_url, '://twitter.com') !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * function to determine if this embed is a gfycat link or not.
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isGfycatEmbed()
	{
		$oembed = $this->getOembed();

		if ($oembed !== false) {
			if (isset($oembed->provider_url) && stristr($oembed->provider_url, '://gfycat.com') !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * function to determine if this embed is a twitch link or not.
	 *
	 * @since	3.2.21
	 * @access	public
	 */
	public function isTwitchLink($link)
	{
		if (stristr($link, 'twitch.tv') !== false || stristr($link, 'clips.twitch.tv') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if we should render this video link using embedded code
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function renderEmbedExternalVideoLink()
	{
		if ($this->isTwitterEmbed()) {
			return true;
		}

		if ($this->isGfycatEmbed()) {
			return true;
		}

		return false;
	}

	public function isSquareRatio()
	{
		$oembed = $this->getOembed();

		$width = $oembed->width;
		$height = $oembed->height;

		return $width == $height;
	}

	public function getRatioString()
	{
		$oembed = $this->getOembed();

		$width = $oembed->width;
		$height = $oembed->height;

		if ($width == $height) {
			return 'is-1by1';
		}

		// for now we assume if height is greatr than width, its 9x16 format.
		if ($height > $width) {
			return 'is-9by16';
		}

		return '';
	}

	/**
	 * Function to determine if this embed is a wordpress oembed
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isWordpressEmbed()
	{
		$oembed = $this->getOembed();

		if (isset($oembed->isWordpress) && $oembed->isWordpress) {
			return true;
		}

		return false;
	}

	/**
	 * Exports audio data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $assets = null)
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$data = $this->getData();

		$result = array(
			'id' => $this->id,
			'created' => $this->created,
			'image' => isset($data->images[0]) ? $data->images[0] : '',
			'title' => $data->title,
			'description' => $data->description,
			'url' => isset($data->url) ? $data->url : ''
		);

		// Override the value from given assets
		if ($assets instanceof SocialRegistry) {
			$result['title'] = $assets->get('title');
			$result['description'] = $assets->get('content');
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}
}
