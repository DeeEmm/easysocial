<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialCronHooksConversations
{
	public function execute(&$states)
	{
		$states[] = $this->cleanupDeletedMessages();
	}

	/**
	 * Clean up old deleted messages
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cleanupDeletedMessages()
	{
		$model = ES::model('Conversations');
		$state = $model->cleanupDeletedMessages();

		if ($state) {
			return JText::_('COM_ES_CRONJOB_CONVERSATIONS_DELETED_CLEANUP_SUCCESS');
		}

		return JText::_('COM_ES_CRONJOB_CONVERSATIONS_DELETED_CLEANUP_EMPTY');
	}
}
