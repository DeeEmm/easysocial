<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialAppHookNotificationLikes extends SocialAppHooks
{
	/**
	 * Processes likes notifications
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function execute(&$item)
	{
		// Get likes participants
		$model = ES::model('Likes');
		$users = $model->getLikerIds($item->uid, $item->context_type);

		// Include the actor of the stream item as the recipient
		$users = array_merge(array($item->actor_id), $users);

		$users = $this->getUniqueUsers($users);

		$names = $this->getNames($users);

		// Get default title if the title is not provided
		if (!$item->title) {
			switch ($item->cmd) {
				case 'likes.involved':
					$langString = ES::string()->computeNoun('COM_EASYSOCIAL_LIKES_INVOLVED_SYSTEM_TITLE', count($users));
					$item->title = JText::sprintf($langString, $names);
					break;

				case 'likes.item':
				default:
					$langString = ES::string()->computeNoun('COM_EASYSOCIAL_LIKES_ITEM_SYSTEM_TITLE', count($users));
					$item->title = JText::sprintf($langString, $names);
					break;
			}
		}

		return;
	}
}