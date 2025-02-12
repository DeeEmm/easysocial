<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialMetaMetadata extends EasySocial
{	
	public $properties = array();
	public $doc = null;

	/**
	 * Factory Method
	 *
	 * @since	2.0
	 * @access	public
	 */	
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
		if (!$data) {
			return $data;
		}

		foreach ($data as $key => $value) {

			$method = 'add' . ucfirst($key);

			// Only process when the method is exists
			if (method_exists('SocialMetaMetadata', $method)) {
				$this->$method($value);
			}
		}

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
					$this->doc->setMetaData($key, $value);
				}
			}
		}

		return true;
	}

	/**
	 * Add title meta
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addTitle($title)
	{
		$this->properties['title'] = $title;

	}

	/**
	 * Add url
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addKeywords($keywords)
	{
		$this->properties['keywords'] = $keywords;
	}

	/**
	 * Add description
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addDescription($description)
	{
		$this->properties['description'] = $description;
	}

	/**
	 * Add Author
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function addAuthor($author)
	{
		$this->properties['author'] = $author;
	}

	/**
	 * Add robots index
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function addRobots($content)
	{
		$this->properties['robots'] = $content;
	}
}