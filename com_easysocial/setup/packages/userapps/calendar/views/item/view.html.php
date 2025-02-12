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

class CalendarViewItem extends SocialAppsView
{
	public function display($userId = null, $docType = null)
	{
		ES::requireLogin();

		$user = ES::user($userId);
		$id = $this->input->get('schedule_id', 0, 'int');

		$calendar = ES::table('Calendar');
		$calendar->load($id);

		if (!$calendar->id || !$id) {
			ES::info()->set(false, 'APP_CALENDAR_CANVAS_INVALID_SCHEDULE_ID', SOCIAL_MSG_ERROR);

			$redirect = $user->getPermalink(false, true);
			return $this->redirect($redirect);
		}

		$privacy = $this->my->getPrivacy();
		$result = $privacy->validate('apps.calendar', $calendar->id, 'calendar', $user->id);

		if (!$result) {
			ES::info()->set(false, 'APP_CALENDAR_NO_ACCESS', SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::dashboard(array(), false));
		}

		ES::document()->title($calendar->title);

		// Render the comments and likes
		$likes = ES::likes();
		$likes->get($id, 'calendar', 'create', SOCIAL_APPS_GROUP_USER);

		$comments = ES::comments($id, 'calendar' , 'create', SOCIAL_APPS_GROUP_USER);

		$params = $this->app->getParams();

		$this->set('params', $params);
		$this->set('likes', $likes);
		$this->set('comments', $comments);
		$this->set('calendar', $calendar);
		$this->set('user', $user);

		echo parent::display('canvas/item/default');
	}
}
