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

ES::import('admin:/includes/model');

class EasySocialModelCalendar extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('calendar', $config);
	}

	/**
	 * Retrieves upcoming schedules based on the number of days
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getUpcomingSchedules($userId, $days = 14, $limit = 10)
	{
		$db = ES::db();
		$sql = $db->sql();
		$days = (int) $days;

		$query = array();
		$query[] = 'SELECT * FROM `#__social_apps_calendar`';
		$query[] = 'WHERE `user_id`=' . $db->Quote($userId);
		$query[] = 'AND `date_start` BETWEEN NOW()';
		$query[] = 'AND DATE_ADD(NOW() , INTERVAL ' . $days . ' DAY)';
		$query[] = 'ORDER BY `date_start` ASC';
		$query[] = 'LIMIT 0,' . (int) $limit;
		$query = implode(' ' , $query);

		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves a list of feeds created by a particular user.
	 *
	 * @since   1.0
	 * @access  public
	 * @param   int     $userId     The user's / creator's id.
	 *
	 * @return  Array               A list of notes item.
	 */
	public function getItems($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps_calendar');
		$sql->where('user_id' , $userId);
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$items = array();

		$privacy = ES::privacy(ES::user()->id);

		foreach ($result as $row) {
			if ($privacy->validate('apps.calendar', $row->id, 'calendar', $userId)) {
				$items[] = $row;
			}
		}

		return $items;
	}
}
