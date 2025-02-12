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

class SocialMeta extends EasySocial
{
	public $properties = array();

	/**
	 * Factory method
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
	 * Method to set the meta data object on the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setMetaObj($data, $debug = null)
	{
		// Get all the available helpers
		$helpers = $this->getHelpers();

		// Format the data
		$this->formatData($data);

		// Let the helpers perform it's magic
		foreach ($helpers as $helper) {
			$helper->addMeta($data, $debug);
		}

		return $this;
	}

	/**
	 * Method to set meta data on the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setMeta($keyword, $content)
	{
		$obj = new stdClass();
		$obj->$keyword = $content;

		return $this->setMetaObj($obj);
	}

	/**
	 * Method to render the data on the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderMeta()
	{
		$helpers = $this->getHelpers();

		foreach ($helpers as $helper) {
			$helper->render();
		}

		return true;
	}

	/**
	 * Method to get helper file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getHelpers()
	{
		$files = JFolder::files(__DIR__ . '/helpers', '.php', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'opengraph.php'));

		if (!$files) {
			return false;
		}

		$helpers = array();

		foreach ($files as $file) {

			$path = __DIR__ . '/helpers/' . $file;

			require_once($path);

			// When item doesn't exist set it to false.
			$file = str_ireplace('.php', '', $file);
			$className = 'SocialMeta' . ucfirst($file);

			if (!class_exists($className)) {
				continue;
			}

			$args = func_get_args();

			// We do array_shift instead of unset($args[0]) to prevent using array_values to reset the index of the array, and also to maintain the reference
			array_shift($args);

			if (method_exists($className, 'getInstance')) {
				$obj = call_user_func_array(array($className, 'getInstance'), $args);
			} else {
				$obj = new $className();
			}

			$obj->className = $className;

			$helpers[] = $obj;
		}

		return $helpers;
	}

	/**
	 * Do a necessary formatting before output the data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function formatData(&$data)
	{
		// Remove any html tag in description
		if (isset($data->description) && $data->description) {

			$content = $data->description;

			// Remove html tags from the content
			$content = strip_tags($content);

			// We need to remove newlines from the content
			$content = str_ireplace("\r\n", "", $content);

			// We also need to replace html entity for space with proper spaces
			$content = ESJString::str_ireplace("&nbsp;", " ", $content);

			// We also need to trim the content to avoid trailing / leading spaces
			$content = trim($content);

			// Decode back the contents if there is any other html entities
			$content = html_entity_decode($content);

			// Remove any double quotes to avoid issues with escaping
			$content = ESJString::str_ireplace('"', '', $content);

			$data->description = $content;
		}
	}
}
