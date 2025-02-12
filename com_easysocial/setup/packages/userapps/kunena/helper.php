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

class KunenaHelper
{
	private $adapter = null;

	public function __construct()
	{
		$kunena6Exists = $this->kunena6Exists();
		$extensionName = 'forum';

		// Kunena version 6
		if ($kunena6Exists) {
			$extensionName = 'forum6';
		}

		$file = __DIR__ . '/adapters/' . $extensionName . '.php';
		require_once($file);

		$className = 'SocialKunenaAdapter' . ucfirst($extensionName);

		$this->adapter = new $className();
	}

	/**
	 * Determines if Kunena6 is enabled
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function kunena6Exists()
	{
		static $exists = null;

		if (is_null($exists)) {

			// Do not load if Kunena version is not supported or Kunena is offline
			if (!class_exists('Kunena\Forum\Libraries\Forum\KunenaForum')) {
				$exists = false;
				return $exists;
			}

			if (!Kunena\Forum\Libraries\Forum\KunenaForum::isCompatible('6.0') || !Kunena\Forum\Libraries\Forum\KunenaForum::enabled()) {
				$exists = false;
				return $exists;
			}

			JFactory::getLanguage()->load('com_kunena.libraries', JPATH_ADMINISTRATOR);
			JFactory::getLanguage()->load('com_kunena');

			$exists = true;
		}

		return $exists;
	}

	/**
	 * Determines what is the current kunena version
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isKunena6()
	{
		static $isKunena6 = null;

		if (is_null($isKunena6)) {

			$isKunena6 = true;

			if (!class_exists('Kunena\Forum\Libraries\Forum\KunenaForum')) {
				$isKunena6 = false;
			}
		}

		return $isKunena6;
	}

	/**
	 * Determines if Kunena is enabled
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isEnabled()
	{
		return $this->adapter->isEnabled();
	}

	/**
	 * Initialize Forum Kunena framework
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function forum()
	{
		return $this->adapter->forum();
	}

	/**
	 * Load language
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function loadLanguage($file, $client)
	{
		return $this->adapter->loadLanguage($file, $client);	
	}

	/**
	 * Kunena framework for message
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function message($id)
	{
		return $this->adapter->message($id);
	}

	/**
	 * Kunena framework for parseBBCode
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function parseBBCode($content, $parent, $chars)
	{
		return $this->adapter->parseBBCode($content, $parent, $chars);
	}

	/**
	 * Retrieve topics
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getTopics($result)
	{
		return $this->adapter->getTopics($result);
	}

	/**
	 * Retrieve template
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getTemplate()
	{
		return $this->adapter->getTemplate();
	}

	/**
	 * Retrieve user
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function user($id)
	{
		return $this->adapter->user($id);
	}

	/**
	 * Retrieve datetime
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function date($data)
	{
		return $this->adapter->date($data);
	}

	/**
	 * Kunena framework for message helper
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function messageHelper($id)
	{
		return $this->adapter->messageHelper($id);
	}

	/**
	 * Kunena framework for single topic
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getTopic($id)
	{
		return $this->adapter->getTopic($id);
	}

	/**
	 * Retrieve the menu item id
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getItemId($url)
	{
		return $this->adapter->getItemId($url);
	}	
}