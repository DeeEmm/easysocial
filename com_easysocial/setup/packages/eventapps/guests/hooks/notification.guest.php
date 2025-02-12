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

class SocialEventAppGuestsHookNotificationGuest
{
	/**
	 * Processes likes notifications
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function execute(&$item)
	{
		// makeadmin, revokeadmin, reject, approve, remove, invite is 1 target only

		// going, maybe, notgoing, request, withdraw is targetting admins, multiple target

		// events.guest.invite
		// events.guest.makeadmin
		// events.guest.revokeadmin
		// events.guest.reject
		// events.guest.approve
		// events.guest.remove
		// events.guest.going
		// events.guest.maybe
		// events.guest.notgoing
		// events.guest.request
		// events.guest.withdraw

		// APP_EVENT_GUESTS_NOTIFICATION_MAKEADMIN_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_REVOKEADMIN_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_REJECT_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_APPROVE_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_REMOVE_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_GOING_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_MAYBE_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_NOTGOING_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_REQUEST_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_WITHDRAW_TITLE
		// APP_EVENT_GUESTS_NOTIFICATION_INVITE_TITLE

		$segments = explode('.', $item->cmd);

		$action = $segments[2];

		$actor = ES::user($item->actor_id);

		$guest = ES::table('EventGuest');
		$guest->load($item->uid);

		$eventId = $item->getParams()->get('eventId');

		if (!$eventId) {
			return;
		}

		$event = ES::event($eventId);

		$item->title = JText::sprintf('APP_EVENT_GUESTS_NOTIFICATION_' . strtoupper($action) . '_TITLE', $actor->getName(), $event->getName());

		$item->image = $event->getAvatar();

		return;
	}
}
