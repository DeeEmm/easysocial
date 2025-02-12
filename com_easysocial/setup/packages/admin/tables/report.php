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

// Import main table
ES::import('admin:/tables/table');

class SocialTableReport extends SocialTable
{
	public $id = null;
	public $title = null;
	public $message = null;
	public $extension = null;
	public $uid = null;
	public $type = null;
	public $created_by = null;
	public $ip = null;
	public $created = null;
	public $state = null;
	public $url = null;

	public function __construct($db)
	{
		parent::__construct('#__social_reports', 'id', $db);
	}

	/**
	 * Retrieves the user object for the current reporter.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUser()
	{
		$user = ES::user($this->created_by);

		return $user;
	}

	/**
	 * Processes email notifications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function notify()
	{
		// Set all properties from this table into the mail template
		$params = ES::makeArray($this);

		//remove this _tbl_keys
		unset($params['_tbl_keys']);
		unset($params['input']);

		// Additional parameters.
		$user = ES::user($this->created_by);
		$params['reporter'] = $user->getName();
		$params['reporterLink'] = $user->getPermalink(true, true);
		$params['item'] = $this->title;
		$params['actorName'] = '';
		$params['actorPermalink'] = '';

		// report item actor user name
		if ($this->actor_id) {
			$actor = ES::user($this->actor_id);

			if ($actor) {
				$params['actorName'] = $actor->getName();
				$params['actorPermalink'] = $actor->getPermalink(true, true);
			}
		}

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');

		// We need to merge admin and custom emails
		$admins = $usersModel->getSystemEmailReceiver();
		$config = ES::config();
		$custom = $config->get('reports.notifications.emails', '');
		$recipients = array();

		foreach ($admins as $user) {
			$recipients[] = $user->email;
		}

		if (!empty($custom)) {
			$custom = explode(',', $custom);

			foreach ($custom as $email) {
				$recipients[] = $email;
			}
		}

		// Ensure for uniqueness here.
		$recipients = array_unique($recipients);

		if ($recipients) {

			// Get mailer object
			$mailer = ES::mailer();
			$templates = array();

			foreach ($recipients as $recipient) {
				$template = $mailer->getTemplate();

				// Set recipient
				$template->setRecipient('', $recipient);

				// Set title of email
				$template->setTitle('COM_EASYSOCIAL_EMAILS_NEW_REPORT_SUBJECT');

				// Set template file.
				$template->setTemplate('site/reports/moderator', $params);

				$mailer->create($template);
			}
		}
	}
}
