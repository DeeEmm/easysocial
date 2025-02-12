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

class ThemesHelperEvent extends ThemesHelperAbstract
{
	/**
	 * Renders the group type label
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function type(SocialEvent $event, $tooltipPlacement = 'bottom', $eventView = false, $showIcon = true)
	{
		$theme = ES::themes();
		$theme->set('showIcon', $showIcon);
		$theme->set('placement', $tooltipPlacement);
		$theme->set('event', $event);
		$theme->set('cluster', $event->getCluster());

		$output = $theme->output('site/helpers/event/type');

		return $output;
	}

	/**
	 * Renders social share button for events
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bookmark(SocialEvent $event)
	{
		if ($event->isDraft()) {
			return;
		}

		$options = array();
		$options['url'] = $event->getPermalink(false, true);
		$options['display'] = 'dialog';

		$title = strip_tags($event->getTitle());

		if (ESJString::strlen($title) >= 50) {
			$title = ESJString::substr($title, 0, 50) . JText::_('COM_EASYSOCIAL_ELLIPSIS');
		}

		$options['title'] = $title;

		$sharing = ES::sharing($options);

		$output = $sharing->button();

		return $output;
	}

	/**
	 * Renders the event's admin button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function adminActions(SocialEvent $event, $returnUrl = '')
	{
		// Check for privileges
		if (!$this->my->isSiteAdmin() && !$event->isOwner() && !$event->isAdmin()) {
			return;
		}

		if (!$returnUrl) {
			$returnUrl = base64_encode(ES::getURI(true));
		}

		$eventAdminStart = false;
		$eventAdminEnd = false;
		$showAdminAction = false;

		if (($this->my->isSiteAdmin() || $event->isOwner() || $event->isAdmin()) && !$event->isDraft()) {
			// Check whether the action is exists.
			$eventAdminStart = ES::themes()->render('widgets', 'event', 'events', 'eventAdminStart', array($event));
			$eventAdminEnd = ES::themes()->render('widgets', 'event', 'events', 'eventAdminEnd' , array($event));

			if (!empty($eventAdminStart) || !empty($eventAdminEnd)) {
				$showAdminAction = true;
			}
		}

		$theme = ES::themes();
		$theme->set('event', $event);
		$theme->set('eventAdminStart', $eventAdminStart);
		$theme->set('eventAdminEnd', $eventAdminEnd);
		$theme->set('showAdminAction', $showAdminAction);
		$theme->set('returnUrl', $returnUrl);

		$output = $theme->output('site/helpers/event/admin.actions');

		return $output;
	}

	/**
	 * Generates a report link for an event
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function report(SocialEvent $event, $wrapper = 'list')
	{
		static $output = array();

		$index = $event->id . $wrapper;

		if (!isset($output[$index])) {

			// Ensure that the user is allowed to report objects on the site
			if ($event->isOwner() || !$this->config->get('reports.enabled') || !$this->access->allowed('reports.submit')) {
				return;
			}

			$reports = ES::reports();

			// Reporting options
			$options = [
				'dialogTitle' => 'COM_EASYSOCIAL_EVENTS_REPORT_EVENT',
				'dialogContent' => 'COM_EASYSOCIAL_EVENTS_REPORT_EVENT_DESC',
				'title' => $event->getTitle(),
				'permalink' => $event->getPermalink(true, true),
				'type' => 'link',
				'showIcon' => false,
				'actorid' => $event->creator_uid
			];

			$output[$index] = $reports->form(SOCIAL_TYPE_EVENT, $event->id, $options);
		}

		return $output[$index];
	}


	/**
	 * Renders the event's action button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function action(SocialEvent $event, $dropdownPlacement = 'right', $forceReload = false, $buttonSize = 'sm')
	{
		// Event states
		$isOver = $event->isOver();
		$seatsAvailable = $event->seatsLeft() === 0 ? false : true;

		// Guest States
		$guest = $event->getGuest();

		$isParticipant = $guest->isParticipant();
		$isGroupMember = false;
		$isAttending = $guest->isGoing();
		$isNotAttending = $guest->isNotGoing();
		$isPending = $guest->isPending();
		$isMaybeAttending = $guest->isMaybe();
		$isInvited = $guest->isInvited();
		$isInvitedByAttendee = $guest->isInvitedByAttendee();

		// Check cluster permission
		$isClusterAllowed = false;

		// initiate the variable here so that at later part we can access.
		$eventClusterType = null;
		$eventClusterName = null;

		if ($event->isClusterEvent()) {
			$eventCluster = $event->getCluster();

			$eventClusterType = $eventCluster->getType();
			$eventClusterName = $eventCluster->getTitle();

			// Check whether the current viewer whether is a member/follower of the group/page or not
			// #5219 - page's event will always allow user to join.
			if ($eventCluster->isMember() || $eventClusterType === SOCIAL_TYPE_PAGE) {
				$isClusterAllowed = true;
			}
		}

		$showRSVPButton = false;

		// Always show the RSVP button as long as that member is invited
		if ($event->isOwner() && $this->my->getAccess()->get('events.allow.join')) {
			$showRSVPButton = true;

		// if this is non-cluster events is is being invited.
		} elseif ((!$event->isClusterEvent() && $isInvitedByAttendee)) {
			$showRSVPButton = true;

		// if this is non-cluster event, ensure that it is standalone public event
		} elseif (!$event->isClusterEvent() && ($event->isOpen() || $isParticipant) && !$isPending && !$event->isDraft() && $this->my->getAccess()->get('events.allow.join')) {
			$showRSVPButton = true;

		// if this is a cluster events is is being invited or the user is already a cluster member
		} elseif ($event->isClusterEvent() && $isClusterAllowed && ($isInvitedByAttendee || $this->my->getAccess()->get('events.allow.join'))) {
			$showRSVPButton = true;
		}

		// detemine if we should show the requeset to attend button or not
		$showRequestToAttend = false;

		if (!$isPending && !$event->isOwner() && $this->my->getAccess()->get('events.allow.join')) {
			if (!$event->isClusterEvent() && $event->isClosed() && !$isInvitedByAttendee) {
				$showRequestToAttend = true;
			}

			// if this is a cluster event and user is a member.
			if ($event->isClusterEvent() && $isClusterAllowed) {
				$showRequestToAttend = true;
			}
		}

		// Do not force reload if non-logged in user are trying to rsvp the event
		if (!$this->my->id) {
			$forceReload = false;
		}

		$theme = ES::themes();
		$theme->set('buttonSize', $buttonSize);
		$theme->set('isOver', $isOver);
		$theme->set('isClusterAllowed', $isClusterAllowed);
		$theme->set('isParticipant', $isParticipant);
		$theme->set('isAttending', $isAttending);
		$theme->set('isNotAttending', $isNotAttending);
		$theme->set('isInvited', $isInvited);
		$theme->set('isPending', $isPending);
		$theme->set('isMaybeAttending', $isMaybeAttending);
		$theme->set('dropdownPlacement', $dropdownPlacement);
		$theme->set('seatsAvailable', $seatsAvailable);
		$theme->set('forceReload', $forceReload);
		$theme->set('event', $event);
		$theme->set('eventClusterType', $eventClusterType);
		$theme->set('eventClusterName', $eventClusterName);
		$theme->set('showRSVPButton', $showRSVPButton);
		$theme->set('showRequestToAttend', $showRequestToAttend);

		$output = $theme->output('site/helpers/event/action');

		return $output;
	}
}
