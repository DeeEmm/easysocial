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

class EasySocialModAppointmentsHelper
{
	public static function format($result)
	{
		$my = ES::user();
		$appointments = array();
		$app = self::getCalendarApp();

		foreach ($result as $row) {
			$appointment = ES::table('Calendar');
			$appointment->bind($row);

			$appointment->permalink = $app->getCanvasUrl(array(
				'schedule_id' => $appointment->id,
				'customView' => 'item',
				'uid' => $my->getAlias(),
				'type' => SOCIAL_TYPE_USER
			));

			$appointments[] = $appointment;
		}

		return $appointments;
	}

	public static function getCalendarApp()
	{
		static $app = null;

		if (is_null($app)) {
			$app = ES::table('App');
			$exists = $app->load(array('element' => 'calendar'));

			if (!$exists) {
				$app = false;
				return $app;
			}
		}

		return $app;
	}
}
