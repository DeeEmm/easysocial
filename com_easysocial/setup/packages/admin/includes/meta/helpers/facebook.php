<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialMetaFacebook extends EasySocial
{
	public $properties 	= array();

	public static function getInstance()
	{
		static $obj = null;

		if (!$obj) {
			$obj = new self();
		}

		return $obj;
	}

	/**
	 * Add opengraph tag
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addMeta($data)
	{
		if (!$data) {
			return $data;
		}

		foreach ($data as $key => $value) {

			$method = 'add' . ucfirst($key);

			// Only process when the method is exists
			if (method_exists('SocialMetaFacebook', $method)) {
				$this->$method($value);
			}
		}

		return $this;
	}

	/**
	 * Adds the open graph tags on a page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function render()
	{
		// Only proceed when opengraph is enabled
		if (!$this->config->get('oauth.facebook.opengraph.enabled')) {
			return;
		}

		require_once(dirname(__FILE__) . '/opengraph.php');

		foreach ($this->properties as $property => $data) {
			if ($data) {		
				if (method_exists('OpengraphRenderer', $property)) {
					OpengraphRenderer::$property($data);
				}
			}
		}

		return true;
	}

	/**
	 * Inserts an image into the opengraph headers
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function addImage($image, $width = null, $height = null)
	{
		// Get the current site http/https append to image URL
		$uri = JURI::getInstance();
		$scheme = $uri->toString(array('scheme'));
		$scheme = str_replace('://', ':', $scheme);

		$obj = new stdClass();

		$imageUrl = $image;

		if (is_object($image) && isset($image->url)) {
			$imageUrl = $image->url;

			if (isset($image->width) && !$width) {
				$width = $image->width;
			}

			if (isset($image->height) && !$height) {
				$height = $image->height;
			}
		}

		// some of the image path pass inside here which got contained http:// and https://
		// have to re-structure it again, first remove http:// or https://
		if (strpos($imageUrl, 'https://') !== false) {
			$imageUrl = ltrim($imageUrl, 'https:');
		}

		if (strpos($imageUrl, 'http://') !== false) {
			$imageUrl = ltrim($imageUrl, 'http:');
		}

		$obj->url = $scheme . $imageUrl;
		$obj->width = $width;
		$obj->height = $height;

		if (!isset($this->properties['image'])) {
			$this->properties['image'] = array();
		}

		$this->properties['image'][] = $obj;

		return $this;
	}

	/**
	 * Inserts the video into the opengraph headers
	 *
	 * @since	2.0.11
	 * @access	public
	 */
	public function addVideo(SocialVideo $video)
	{
		$this->addType('video');

		$obj = new stdClass();

		if ($video->isLink()) {
			$obj->url = $video->path;
		} else {
			$obj->url = $video->getFile();
		}

		$obj->type = 'text/html';
		$obj->width = 1280;
		$obj->height = 720;

		$this->properties['video'][] = $obj;

		return $this;
	}

	/**
	 * Inserts the description of the page
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function addDescription($content)
	{
		$this->properties['description'] = $content;

		return $this;
	}

	/**
	 * Adds the url attribute
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function addUrl($url)
	{
		$this->properties['url'] = $url;

		return $this;
	}

	public function addType($type)
	{
		$this->properties['type'] = $type;

		return $this;
	}

	public function addTitle($title )
	{
		$this->properties['title'] = $title;

		return $this;
	}

	/**
	 * Add datetime for event type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addStart_Time($startTime)
	{
		$this->properties['start_time'] = $startTime;

		return $this;
	}

	/**
	 * Add endtime for event type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addEnd_Time($endTime)
	{
		$this->properties['end_time'] = $endTime;

		return $this;
	}	
}