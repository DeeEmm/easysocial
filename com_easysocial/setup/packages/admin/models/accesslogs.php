<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelAccessLogs extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('accesslogs', $config);
	}

	public function initStates()
	{
		parent::initStates();
	}

	public function getUsage($rule, $userId, $intervalMode = SOCIAL_ACCESS_LIMIT_INTERVAL_NO)
	{
		$db = ES::db();
		$sql = $db->sql();

		$isEvent = $rule == 'events.limit' ? true : false;


		$query = "select count(1) from `#__social_access_logs` as a";

		if ($isEvent) {
			$now = ES::date()->toMySQL();

			$parts = explode(' ', $now);
			$start = $parts[0] . ' 00:00:00';
			$end = $parts[0] . ' 23:59:59';
			// if this is an event limit checking, we need to join with event meta table as we only want to count
			// ongoing event and upcoming event.
			$query .= " inner join `#__social_events_meta` as b on a.`uid` = b.`cluster_id` and (b.`start` >= " . $db->Quote($start) . " OR";
			$query .= " (b.`start` <= " . $db->Quote($now) . " AND b.`end` >= " . $db->Quote($now) . "))";
		}


		$query .= " where a.`rule` = " . $db->Quote($rule);
		$query .= " and a.`user_id` = " . $db->Quote($userId);

		if ($intervalMode) {
			$dates = $this->getStartEndDates($intervalMode);

			$query .= " and (a.`created` >= " . $db->Quote($dates->start);
			$query .= " and a.`created` <= " . $db->Quote($dates->end) . ")";
		}

		// echo $query . '<br /><br />';

		$sql->raw($query);
		$db->setQuery($sql);

		$usage = $db->loadResult();

		return $usage ? $usage : 0;
	}

	private function getStartEndDates($intervalMode)
	{
		$obj = new stdClass();

		$obj->start = "";
		$obj->end = "";

		switch($intervalMode) {
			case SOCIAL_ACCESS_LIMIT_INTERVAL_DAILY:
				$today = ES::date()->toMySQL();
				$parts = explode(" ", $today);

				$obj->start = $parts[0] . ' 00:00:01';
				$obj->end = $parts[0] . ' 23:59:59';

				break;
			case SOCIAL_ACCESS_LIMIT_INTERVAL_WEEKLY:
				// reference: http://stackoverflow.com/questions/1897727/get-first-day-of-week-in-php

				$today = ES::date();
				$day = $today->toFormat('w');

				$obj->start = ES::date('-'.$day.' days')->toMySQL();
				$obj->end = ES::date('+'.(6-$day).' days')->toMySQL();


				break;
			case SOCIAL_ACCESS_LIMIT_INTERVAL_MONTHLY:

				$today = ES::date();
				$yearMonth = $today->toFormat('Y-m');

				// Retrieve the last day of the current month
				$dayStartOfMonth = $yearMonth . '-01';
				$lastDayOfCurrentMonth = ES::getLastDayOfCurrentMonth($dayStartOfMonth);

				$obj->start = $dayStartOfMonth . ' 00:00:01';
				$obj->end = $lastDayOfCurrentMonth . ' 23:59:59';

				break;
			case SOCIAL_ACCESS_LIMIT_INTERVAL_YEARLY:

				$today = ES::date();
				$year = $today->toFormat('Y');

				$obj->start = $year .'-01-01 00:00:01';
				$obj->end = $year . '-12-31 23:59:59';

				break;
		}

		return $obj;
	}
}
