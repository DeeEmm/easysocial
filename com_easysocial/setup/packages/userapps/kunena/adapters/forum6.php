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

use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopicHelper;
use Kunena\Forum\Libraries\User\KunenaUserHelper;
use Kunena\Forum\Libraries\Date\KunenaDate;
use Kunena\Forum\Libraries\Forum\Message\KunenaMessage;
use Kunena\Forum\Libraries\Forum\Message\KunenaMessageHelper;
use Kunena\Forum\Libraries\Html\KunenaParser;
use Kunena\Forum\Libraries\Route\KunenaRoute;

jimport('joomla.filesystem.file');

class SocialKunenaAdapterForum6
{
	/**
	 * Determines if Kunena 6.x is enabled
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isEnabled()
	{
		// Skip this because already validate this under construct function.
		return true;
	}

	/**
	 * Initialize Forum Kunena framework
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function forum()
	{
		$forum = new Kunena\Forum\Libraries\Forum\KunenaForum;

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
		$data = KunenaMessage::getInstance($id);
		return $data;
	}

	/**
	 * Kunena framework for parser
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function parseBBCode($content, $parent, $chars)
	{
		$data = KunenaParser::parseBBCode($content, $parent, $chars);

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
		$topics = KunenaTopicHelper::getTopics($result);

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
		$message = KunenaMessageHelper::get($id);

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
		$topic = KunenaTopicHelper::get($id);

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
