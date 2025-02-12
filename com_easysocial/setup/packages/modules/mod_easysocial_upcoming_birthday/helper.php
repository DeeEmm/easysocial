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

class EasySocialModBirthdaysHelper
{
	public static function format($result, $params)
	{
		$ids = array();
		$dateToday = ES::date()->toFormat('md');
		$displayYear = $params->get('display_year', false);

		$today = array();
		$otherDays = array();

		// Get the current user's privacy
		$my = ES::user();
		$privacy = $my->getPrivacy();

		foreach ($result as $row) {
			$ids[] = $row->uid;
		}

		// Preload list of users
		ES::user($ids);

		$items = array();

		foreach ($result as $row) {

			$obj = new stdClass();
			$obj->user = ES::user($row->uid);
			$obj->birthday = $row->displayday;

			//Checking to display year here
			if ($displayYear) {

				$dateFormat = JText::_('COM_EASYSOCIAL_DATE_DMY');

				//check birtday the year privacy
				if (!$privacy->validate('field.birthday.year', $row->field_id, 'birthday.year', $row->uid)) {
					$dateFormat = JText::_('COM_EASYSOCIAL_DATE_DM');
				}

			} else {
				$dateFormat = JText::_('COM_EASYSOCIAL_DATE_DM');
			}

			// It should not apply any timezone on the birthday
			$obj->display = JFactory::getDate($obj->birthday)->format($dateFormat);

			$items[] = $obj;
		}

		return $items;
	}
}
