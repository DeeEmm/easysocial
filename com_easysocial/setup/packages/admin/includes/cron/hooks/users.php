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

class SocialCronHooksUsers
{
	public function execute(&$states)
	{
		// Initiate the process to get videos that are pending to be processed.
		$states[] = $this->removeExpiredRestLogs();
	}

	/**
	 * Method to remove expired rest login logs.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function removeExpiredRestLogs()
	{
		$model = ES::model('Users');
		$model->removeRestSessionLogs();

		return JText::_('COM_ES_USERS_CRONJOB_REST_LOGS_CLEARED');
	}
}
