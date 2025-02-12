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

class SocialKunenaAdapterForum
{
	/**
	 * Determines if kunena 5.x is enabled
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function isEnabled()
	{
		static $exists = null;

		if (is_null($exists)) {

			$file = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
			$exists = false;

			if (!JFile::exists($file)) {
				return $exists;
			}

			$exists = true;

			// Load Kunena's api file
			require_once($file);

			// Load Kunena's language
			KunenaFactory::loadLanguage('com_kunena.libraries', 'admin');
		}

		return $exists;
	}

	/**
	 * Initialize Forum Kunena framework
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function forum()
	{
		$forum = new KunenaForum;

		return $forum;
	}

	/**
	 * Load language
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function loadLanguage($file, $client)
	{
		// Load language file from Kunena
		KunenaFactory::loadLanguage($file, $client);
	}

	/**
	 * Kunena framework for message
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function message($id)
	{
		$data = KunenaForumMessage::getInstance($id);

		return $data;
	}

	/**
	 * Kunena framework for parseBBCode
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function parseBBCode($content, $parent, $chars)
	{
		$data = KunenaHtmlParser::parseBBCode($content, $parent, $chars);

		return $data;
	}

	/**
	 * Kunena framework for topic
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getTopics($result)
	{
		$topics = KunenaForumTopicHelper::getTopics($result);

		return $topics;
	}

	/**
	 * Retrieve template
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getTemplate()
	{
		$template = KunenaFactory::getTemplate();

		return $template;
	}

	/**
	 * Retrieve user
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function user($id)
	{
		$user = KunenaUserHelper::get($id);

		return $user;
	}

	/**
	 * Retrieve datetime
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function date($timestamp)
	{
		$date = KunenaDate::getInstance($timestamp);

		return $date;
	}

	/**
	 * Kunena framework for message helper
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function messageHelper($id)
	{
		$message = KunenaForumMessageHelper::get($id);

		return $message;
	}

	/**
	 * Kunena framework for single topic
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getTopic($id)
	{
		$topic = KunenaForumTopicHelper::get($id);

		return $topic;
	}

	/**
	 * Retrieve the menu item id
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getItemId($url)
	{
		$menuItemId = KunenaRoute::getItemId($url);

		return $menuItemId;
	}
}
