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

class CalendarControllerCalendar extends SocialAppsController
{
	/**
	 * Displays the create new schedule form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form()
	{
		ES::checkToken();
		ES::requireLogin();

		// Determines if item is being edited
		$id = $this->input->get('id', 0, 'int');

		$calendar = ES::table('Calendar');
		$calendar->load($id);

		if ($id && $calendar->id && $calendar->user_id != $this->my->id) {
			return $this->ajax->reject();
		}

		// Get the start and end date
		$start = $this->input->get('start', '', 'default');
		$end = $this->input->get('end', '', 'default');
		$calendar->all_day = $this->input->get('allday', false, 'bool');

		$format = 'YYYY-MM-DD HH:mm:ss';

		// The date values are already populated with the timezone based on the browser.
		// We need to remove the offset before saving later
		if ($start) {
			$startDate = ES::date($start);
			$calendar->date_start = $startDate->toMySQL();
		}

		if ($end) {
			$endDate = ES::date($end);
			$calendar->date_end = $endDate->toMySQL();
		}

		// Get the app params
		$params = $this->getParams();

		// Load up the theme
		$theme = ES::themes();
		$theme->set('params', $params);
		$theme->set('calendar', $calendar);
		$theme->set('format', $format);

		$output	= $theme->output('apps/user/calendar/canvas/dialog.create');

		return $this->ajax->resolve($output);
	}

	/**
	 * Deletes an item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		// Check for request forgeries
		ES::checkToken();

		// Ensure that the user is logged in
		ES::requireLogin();

		// Load up the table
		$calendar = ES::table('Calendar');

		// Get current logged in user
		$my = ES::user();

		// Determines if the calendar is being edited
		$id = $this->input->get('id', 0, 'int');
		$calendar->load($id);

		if (!$id) {
			return $this->ajax->reject();
		}

		if ($calendar->user_id != $my->id) {
			return $this->ajax->reject();
		}

		$state = $calendar->delete();

		return $this->ajax->resolve();
	}

	/**
	 * Renders a single appointment item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Check for request forgeries
		ES::checkToken();

		// Ensure that the user is logged in
		ES::requireLogin();

		// Load up the table
		$calendar = ES::table('Calendar');

		// Get current logged in user
		$my = ES::user();

		// Determines if the calendar is being edited
		$id = $this->input->get('id', 0, 'int');
		$calendar->load($id);

		if (!$id) {
			return $this->ajax->reject();
		}

		if ($calendar->user_id != $my->id) {
			return $this->ajax->reject();
		}

		// Load up the theme
		$theme = ES::themes();
		$theme->set('calendar', $calendar);

		$output	= $theme->output('apps/user/calendar/canvas/dialog.delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders a single appointment item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function view()
	{
		// Check for request forgeries
		ES::checkToken();

		// Ensure that the user is logged in
		ES::requireLogin();

		// Load up the table
		$calendar = ES::table('Calendar');

		// Get current logged in user
		$my = ES::user();

		// Determines if the calendar is being edited
		$id = $this->input->get('id', 0, 'int');
		$calendar->load($id);

		if (!$id) {
			return $this->ajax->reject();
		}

		$user = ES::user($calendar->user_id);
		$app = $this->getApp();

		$params = $this->getParams();

		$stream = ES::table('StreamItem');
		$options = array('context_type' => 'calendar', 'context_id' => $calendar->id);
		$stream->load($options);

		// Load up the theme
		$theme = ES::themes();
		$theme->set('calendar', $calendar);
		$theme->set('user', $user);
		$theme->set('app', $app);
		$theme->set('params', $params);
		$theme->set('streamId', $stream->uid);

		$output	= $theme->output('apps/user/calendar/canvas/dialog.view');

		return $this->ajax->resolve($output);
	}

	/**
	 * Saves the schedule
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store()
	{
		// Check for request forgeries
		ES::checkToken();

		// Ensure that the user is logged in
		ES::requireLogin();

		// Load up the table
		$table = ES::table('Calendar');

		// Get current logged in user
		$my = ES::user();

		// Determines if the calendar is being edited
		$id = $this->input->get('id', 0, 'int');
		$table->load($id);

		// If this is being edited, double check the permissions
		if ($id && $table->id) {

			if ($table->user_id != $my->id) {
				return $this->ajax->reject(JText::_('APP_CALENDAR_NOT_ALLOWED_TO_EDIT'), SOCIAL_MSG_ERROR);
			}
		}

		// Get the starting and ending date
		$start = $this->input->get('startVal', '', 'default');
		$end = $this->input->get('endVal', '', 'default');

		$startDate = ES::date($start, false);
		$endDate = ES::date($end, false);

		// Get the posted data
		$post = $this->input->getArray('post');

		// Bind the posted data
		$table->bind($post);

		$table->date_start = $startDate->toMySQL();
		$table->date_end = $endDate->toMySQL();
		$table->user_id = ES::user()->id;

		// Determines if we should publish this on the stream
		$publishStream = $this->input->get('stream', true, 'bool');

		$state = $table->store();

		if (!$state) {
			return $this->ajax->reject($table->getError(), SOCIAL_MSG_ERROR);
		}

		if ($publishStream) {
			$verb = $id ? 'update' : 'create';
			$table->createStream($verb);
		}

		return $this->ajax->resolve($table->id);
	}
}
