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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SocialLinks extends EasySocial
{
	public $url = '';
	public $hash = '';
	public $table = null;

	public function __construct($url = '')
	{
		parent::__construct();

		$this->table = ES::table('Link');

		if ($url) {
			$this->url = $url;
			$this->hash = md5($this->url);

			$this->table->load(array('hash' => $this->hash));
		}
	}

	public static function factory($url = '')
	{
		return new self($url);
	}

	/**
	 * Determines if a cache object exists for a particular link
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasCache()
	{
		return $this->table->id;
	}

	/**
	 * Creates a new cache for the current link
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cache($data)
	{
		if (!is_string($data)) {
			$data = json_encode($data);
		}

		$this->table->hash = $this->hash;
		$this->table->data = $data;
		$this->table->created = JFactory::getDate()->toSql();

		$state = $this->table->store();

		if (!$state) {
			$this->setError($this->table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Retrieves supported top level domains
	 *
	 * @since	2.1.9
	 * @access	public
	 */
	public function getSupportedTld($separator = '|')
	{
		static $tlds = null;

		if (is_null($tlds)) {
			$tlds = $this->config->get('links.parser.tld');
			$tlds = ESJString::trim($tlds, ',');

			// sort the value so that value with longer length will
			// stay at top.
			if ($tlds) {
				$tmpArr = explode(',', $tlds);
				$tmpArr = array_unique($tmpArr);

				usort($tmpArr, function($a, $b) {
					return strlen($b) - strlen($a);
				});
				$tlds = implode(',', $tmpArr);
			}

			// Since settings are stored as comma separated values
			if ($separator != ',') {
				$tlds = str_ireplace(',', $separator, $tlds);
			}
		}

		return $tlds;
	}

	/**
	 * Retrieves data about the links cache
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getData()
	{
		$data = $this->table->data;

		if (is_string($data)) {
			$data = json_decode($data);
		}

		return $data;
	}

	/**
	 * Fixes oembed links
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function fixOembedLinks($oembed)
	{
		// Fix video issues with youtube when site is on https
		if (isset($oembed->provider_url) && $oembed->provider_url == 'http://www.youtube.com/') {
			$oembed->html = ESJString::str_ireplace('http://', 'https://', $oembed->html);
			$oembed->thumbnail = str_ireplace('http://', 'https://', $oembed->thumbnail);
		}

		return $oembed;
	}

	/**
	 * Retrieves the correct image path
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getImageLink($assets, $params)
	{
		$uri = JURI::getInstance();

		// Get the image file
		$image = $assets->get('image');
		$cachePath = ltrim($this->config->get('links.cache.location'), '/');

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

			if ($exists) {
				$image = $linkImage->getUrl();
			} else {
				$fileName = basename($image);
				$image = rtrim(JURI::root(), '/') . '/' . $cachePath . '/' . $fileName;
			}
		}

		// If necessary, feed in our own proxy to avoid http over https issues.
		if ($params->get('stream_link_proxy', false) && ($assets->get('image')) && $uri->getScheme() == 'https') {

			if ($image && stristr($image, 'http://') !== false) {
				$image = ES::proxy($image);
			}
		}

		return $image;
	}

	/**
	 * Stores a given image link into the local cache
	 *
	 * @since	1.2.11
	 * @access	public
	 */
	public function cacheImage($imageLink)
	{
		// Check if settings is enabled
		if (!$this->config->get('links.cache.images')) {
			return false;
		}

		// Return false if the imagelink is empty.
		if (empty($imageLink)) {
			return false;
		}

		// Try to load any existing cached image from the db
		$linkImage = ES::table('LinkImage');
		$exists = $linkImage->load(array('source_url' => $imageLink));

		// If this already exists, skip this altogether
		if ($exists) {
			return $linkImage->internal_url;
		}

		// Generate a unique name for this file
		$fileName = md5($imageLink) . '.png';

		// Get the storage path
		$container = ES::cleanPath($this->config->get('links.cache.location'));
		$storage = JPATH_ROOT . '/' . $container . '/' . $fileName;

		// Check if the file already exists
		$exists = JFile::exists($storage);

		// If the file is already cached, delete it
		if ($exists) {
			JFile::delete($storage);
		}

		// Crawl the image now.
		$connector = ES::get('Connector');
		$connector->addUrl($imageLink);
		$connector->connect();

		// Get the result and parse them.
		$contents = $connector->getResult($imageLink);

		// Store the file to a temporary directory first
		$tmpFile = SOCIAL_TMP . '/' . $fileName;
		JFile::write($tmpFile, $contents);

		// Load the image now
		$image = ES::image();
		$image->load($tmpFile);

		// Ensure that image is valid
		if (!$image->isValid()) {
			JFile::delete($tmpFile);
			return false;
		}

		// Delete the temporary file.
		JFile::delete($tmpFile);

		// Unset the image now since we don't want to use asido to resize
		unset($image);

		// Store the file now into our cache storage.
		JFile::write($storage, $contents);

		$linkImage->source_url = $imageLink;
		$linkImage->internal_url = $fileName;
		$linkImage->store();

		return $fileName;
	}
}
