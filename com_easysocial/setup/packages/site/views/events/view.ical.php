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

ES::import('site:/views/views');

class EasySocialViewEvents extends EasySocialSiteView
{
	/**
	 * Adding Lockdown exception on event ical view.
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isLockDown()
	{
		return false;
	}

	public function display($tpl = null)
	{
		return $this->export();
	}

	/**
	 * Allows caller to export event items into downloadable ics file
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function export()
	{
		// Get the event object
		$event = ES::event($this->input->get('id', 0, 'int'));

		$description = strip_tags($event->description, '<br>');
		$description = ESJString::str_ireplace(array('<br>', '<br />', '<br/>'), '\n', $description);
		$description = preg_replace("/\r\n/", '\n', $description);

		$theme = ES::themes();
		$theme->set('event', $event);
		$theme->set('description', $description);
		$output = $theme->output('site/events/ical');

		$ts = substr(md5(rand(0,100)), 0, 5);
		$fileName = 'calendar_' . $ts . '.ics';


		// Allow caller to download the file
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename=' . $fileName);
		echo $output;

		exit;
	}
}
