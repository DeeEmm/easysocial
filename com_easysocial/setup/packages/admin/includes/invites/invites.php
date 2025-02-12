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

class SocialInvites
{
	/**
	 * Triggered when an invited user id registers on the site
	 *
	 * @since	5.0.0
	 * @access	public
	 */
	public function registered($inviterUserId, $invitedUserId)
	{
		$model = ES::model('Friends');
		$state = $model->request($inviterUserId, $invitedUserId, SOCIAL_FRIENDS_STATE_FRIENDS);

		if (!$state) {
			return false;
		}

		// Assign points to the user that created this invite because the invitee registered on the site
		$points = ES::points();
		$points->assign('friends.registered', 'com_easysocial' , $inviterUserId);

		// @badge: friends.registered
		// Assign badge for the person that invited friend already registered on the site.
		$badge = ES::badges();
		$badge->log('com_easysocial', 'friends.registered', $inviterUserId, JText::_('COM_EASYSOCIAL_FRIENDS_BADGE_INVITED_FRIEND_REGISTERED'));

		return true;
	}
}
