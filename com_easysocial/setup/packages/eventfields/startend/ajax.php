<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventStartend extends SocialFieldItem
{
	public function getDatetime()
	{
		$userid = $this->input->get('userid', 0, 'int');
		$user = ES::user($userid);

		$tz = $this->input->get('tz', '', 'string');

		$datetime = $this->input->get('datetime', '', 'string');

		$date = ES::date($datetime, false);

		if ($tz === 'local') {
			// If the user timezone is set, then we'll use it.
			// However, if the user didn't set the timezone, then we'll us the browser timezone.
	        // Check: /media/com_easysocial/scripts/apps/fields/event/startend/display.js
			if (!is_null($user->getParam('timezone'))) {
				$dtz = new DateTimeZone($user->getParam('timezone'));

				$date->setTimezone($dtz);

			} else {
				$local = $this->input->get('local', 0, 'int');

				$seconds = $local * 60 * 60;

				$date = ES::date($date->toUnix() + $seconds, false);
			}
		} else {
			$dtz = new DateTimeZone($tz);

			$date->setTimezone($dtz);
		}

		$dateFormat = $this->params->get('date_format');

		if ($this->params->get('allow_time')) {
			$dateFormat .= ' ' . $this->params->get('time_format');
		}

		$string = $date->format(JText::_(ES::config()->get('events.timeformat') == '12h' ? 'COM_EASYSOCIAL_DATE_DMY12H' : 'COM_EASYSOCIAL_DATE_DMY24H'));

		ES::ajax()->resolve($string);
	}
}
