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

class SocialMetaTwitter extends EasySocial
{
	public $properties = array();
	public $doc = null;

	public static function getInstance()
	{
		static $obj = null;

		if (!$obj) {
			$obj = new self();
		}

		return $obj;
	}

	/**
	 * Add cards data for cluster items
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addMeta($data)
	{
		// Only proceed when opengraph is enabled
		if (!$this->config->get('oauth.twitter.card.enabled')) {
			return;
		}

		if (!$data) {
			return $data;
		}

		foreach ($data as $key => $value) {

			$method = 'add' . ucfirst($key);

			// Only process when the method is exists
			if (method_exists('SocialMetaTwitter', $method)) {
				$this->$method($value);
			}
		}

		$this->addCard();

		return $this;
	}

	/**
	 * Adds the cards tags on a page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function render()
	{
		if (!$this->properties) {
			return;
		}

		// To prevent duplication
		static $tags = array();

		foreach ($this->properties as $key => $value) {
			if ($value) {
				if (!isset($tags[$key]) && $this->doc->getType() == 'html') {
					$tags[$key] = $value;
					$this->doc->addCustomTag('<meta name="twitter:'. $key .'" content="' . $value . '" />');
				}
			}
		}

		return true;
	}

	/**
	 * Add twitter card meta
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addCard()
	{
		$cardType = $this->config->get('oauth.twitter.card.type');

		$this->properties['card'] = $cardType;

	}

	/**
	 * Add url card
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addUrl($permalink)
	{
		$this->properties['url'] = $permalink;
	}

	/**
	 * Add description card
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addDescription($description)
	{
		$this->properties['description'] = ES::string()->escape($description);
	}

	/**
	 * Add title card
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addTitle($title)
	{
		$this->properties['title'] = ES::string()->escape($title);
	}

	/**
	 * Add image card
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addImage($image)
	{
		if (!isset($this->properties['image'])) {
			// Get the current site http/https append to image URL
			$uri = JURI::getInstance();
			$scheme = $uri->toString(array('scheme'));
			$scheme = str_replace('://', ':', $scheme);

			$obj = new stdClass();

			if (is_object($image) && isset($image->url)) {
				$image = $image->url;
			}

			// some of the image path pass inside here which got contained http:// and https://
			// have to re-structure it again, first remove http:// or https://
			if (strpos($image, 'https://') !== false) {
				$image = ltrim($image, 'https:');
			}

			if (strpos($image, 'http://') !== false) {
				$image = ltrim($image, 'http:');
			}

			$this->properties['image'] = $scheme . $image;
		}
	}
}
