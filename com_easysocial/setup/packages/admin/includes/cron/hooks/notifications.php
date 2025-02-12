<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialCronHooksNotifications
{
	public function execute(&$states)
	{
		$states[] = $this->cleanupNotifications();
	}

	/**
	 * Truncate notification items
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cleanupNotifications()
	{
		$config = ES::config();

		if (!$config->get('notifications.cleanup.enabled')) {
			return JText::_('COM_EASYSOCIAL_CRONJOB_NOTIFICATIONS_CLEANUP_DISABLED');
		}

		$months = $config->get('notifications.cleanup.duration', '6');
		$includeUnreadNotifications = $config->get('notifications.cleanup.unread', false);

		$model = ES::model('Notifications');
		$state = $model->cleanup($months, $includeUnreadNotifications);

		return JText::_('COM_EASYSOCIAL_CRONJOB_NOTIFICATIONS_CLEANUP_PROCESSED');
	}
}
