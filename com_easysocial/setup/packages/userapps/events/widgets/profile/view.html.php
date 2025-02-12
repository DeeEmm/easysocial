<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EventsWidgetsProfile extends SocialAppsWidgets
{
	public function sidebarBottom($user)
	{
		$params = $this->getParams();

		if (!$this->config->get('events.enabled') || !$params->get('widget_profile', true)) {
			return;
		}

		echo $this->getCreatedEvents($user, $params);
	}

	public function getCreatedEvents(SocialUser $user, $params)
	{
		$limit = $params->get('widget_profile_total', 5);
		$model = ES::model('Events');

		$now = ES::date()->toSql();

		$createdEvents = $model->getEvents(array(
			'creator_uid' => $user->id,
			'creator_type' => SOCIAL_TYPE_USER,
			'state' => SOCIAL_STATE_PUBLISHED,
			'ordering' => 'start',
			'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE),
			'limit' => $limit,
			'ongoing' => true,
			'upcoming' => true
		));

		$createdTotal = $model->getTotalEvents(array(
			'creator_uid' => $user->id,
			'creator_type' => SOCIAL_TYPE_USER,
			'state' => SOCIAL_STATE_PUBLISHED,
			'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE),
			'ongoing' => true,
			'upcoming' => true
		));

		$attendingEvents = $model->getEvents(array(
			'guestuid' => $user->id,
			'gueststate' => SOCIAL_EVENT_GUEST_GOING,
			'state' => SOCIAL_STATE_PUBLISHED,
			'ongoing' => true,
			'upcoming' => true,
			'ordering' => 'start',
			'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE),
			'limit' => $limit,
		));

		$attendingTotal = $model->getTotalEvents(array(
			'guestuid' => $user->id,
			'gueststate' => SOCIAL_EVENT_GUEST_GOING,
			'state' => SOCIAL_STATE_PUBLISHED,
			'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE),
			'ongoing' => true,
			'upcoming' => true
		));

		if (!$createdEvents && !$attendingEvents) {
			return;
		}

		$allowCreate = $user->isSiteAdmin() || $user->getAccess()->get('events.create');

		$total = $user->getTotalCreatedJoinedEvents();

		$viewAll = ESR::events(array('userid' => $user->getAlias()));

		if ($user->isViewer()) {
			$viewAll = ESR::events(array('filter' => 'mine'));
		}

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('total', $total);
		$theme->set('createdEvents', $createdEvents);
		$theme->set('createdTotal', $createdTotal);
		$theme->set('attendingEvents', $attendingEvents);
		$theme->set('attendingTotal', $attendingTotal);
		$theme->set('app', $this->app);
		$theme->set('allowCreate', $allowCreate);
		$theme->set('viewAll', $viewAll);

		return $theme->output('themes:/apps/user/events/widgets/profile/events');
	}
}
