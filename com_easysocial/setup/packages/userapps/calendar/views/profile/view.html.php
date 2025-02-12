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

class CalendarViewProfile extends SocialAppsView
{
	public function display($userId = null, $docType = null)
	{
		// Require user to be logged in
		ES::requireLogin();

		$user = ES::user($userId);

		// Check for privacy
		$privacy = $this->my->getPrivacy();

		// Ensure that the viewer is really allowed to view the calendar
		if (!$privacy->validate('apps.calendar', $user->id, SOCIAL_TYPE_USER, $user->id)) {
			echo parent::display('canvas/calendar/restricted');
			return;
		}

		// Load css for app
		$this->app->loadCss();

		$model = ES::model('Calendar');
		$result = $model->getItems($user->id);

		$schedules 	= array();

		if ($user->isViewer()) {
			$title = JText::_('APP_CALENDAR_CANVAS_TITLE_OWNER');
		} else {
			$title = JText::sprintf('APP_CALENDAR_CANVAS_TITLE_VIEWER', $user->getName());
		}

		// Set the page title
		ES::document()->title($title);

		if ($result) {

			foreach ($result as $row) {
				$table = ES::table('Calendar');
				$table->bind($row);

				$schedules[] = $table;
			}
		}

		// Determines if the current page is on RTL mode.
		$doc = JFactory::getDocument();
		$direction = $doc->getDirection();
		$isRTL = $direction == 'rtl' ? true : false;

		// Get application params
		$params = $this->app->getParams();

		$this->set('params', $params);
		$this->set('isRTL', $isRTL);
		$this->set('user', $user);
		$this->set('schedules', $schedules);

		echo parent::display('canvas/calendar/default');
	}
}
