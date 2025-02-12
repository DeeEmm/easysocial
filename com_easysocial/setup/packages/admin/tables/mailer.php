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

class SocialTableMailer extends SocialTable
{
	public $id = null;
	public $sid = null;
	public $sender_name = null;
	public $replyto_email = null;
	public $sender_email = null;
	public $recipient_name = null;
	public $recipient_email = null;
	public $title = null;
	public $content = null;
	public $template = null;
	public $html = null;
	public $state = null;
	public $created = null;
	public $params = null;
	public $priority = null;
	public $language = null;

	public function __construct($db)
	{
		parent::__construct('#__social_mailer', 'id', $db);
	}

	public function bind($data , $ignore = array())
	{
		$state = parent::bind($data);
		$jConfig = ES::jconfig();
		$config = ES::config();

		$configFromName = $config->get('notifications.general.fromname', '');
		$configFromEmail = $config->get('notifications.general.fromemail', '');

		// @TODO: Admin can create multiple emails from in the future.
		if (is_null($this->sender_name)) {
			$this->sender_name = $configFromName ? $configFromName : $jConfig->getValue('fromname');
		}

		if (is_null($this->sender_email)) {
			$this->sender_email = $configFromEmail ? $configFromEmail : $jConfig->getValue('mailfrom');
		}

		if (is_null($this->created)) {
			$this->created  = ES::get('Date')->toMySQL();
		}

		if (is_null($this->priority)) {
			$this->priority = SOCIAL_MAILER_PRIORITY_NORMAL;
		}

		return $state;
	}

	public function getParams()
	{
		if (empty($this->params) || is_null($this->params)) {
			return false;
		}

		return ES::get('Parameter' , $this->params);
	}

	/**
	 * Retrieves the absolute path to the icon for the current state
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getIcon()
	{
		$uri = SOCIAL_MEDIA_URI . '/assets/images/icons/mailer_priority_' . $this->priority . '.png';

		return $uri;
	}

	/**
	 * Formats the content for preview.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preview()
	{
		$mailer = ES::mailer();
		return $mailer->getEmailContents($this);
	}

	public function loadLanguage($respectEmailLang = false)
	{
		$lang = $respectEmailLang ? $this->language : null;
		$reload = $respectEmailLang ? true : false;

		if (!empty($this->template)) {

			$parts = explode('/', $this->template);

			$location = array_shift($parts);

			if ($location === 'site' || $location == 'apps') {
				ES::language()->loadSite($lang, $reload);
			}

			if ($location === 'admin' || $location == 'apps') {
				ES::language()->loadAdmin($lang, $reload);
			}
		}
	}

	/**
	 * Publishing the scheduled emails.
	 * This is used mainly for scheduled posting.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function publishScheduledEmail()
	{
		// Change the state to unread.
		$this->state = SOCIAL_NOTIFICATION_STATE_UNREAD;

		// We'll need to update the created date.
		$this->created = ES::date()->toSql();

		$state = $this->store();

		return $state;
	}
}
