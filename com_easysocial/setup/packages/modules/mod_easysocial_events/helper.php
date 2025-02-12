<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialModEventsHelper
{
	public static function getEvents(&$params)
	{
		$model = ES::model('Events');
		$date = ES::date();

		$filter = $params->get('filter');

		// Determine the ordering of the events
		$ordering = $params->get('ordering', 'start');

		$pastEventDays = $params->get('display_pastevent', 7);
		$multiplier = 24*60*60;
		$pastEvent = ES::date($date->toUnix() - ($pastEventDays * $multiplier))->toSql(true);

		// Default options
		$options = array();

		$my = ES::user();

		// Limit the number of events based on the params
		$options['limit'] = $params->get('display_limit', 5);
		$options['ordering'] = $ordering;
		$options['state'] = SOCIAL_STATE_PUBLISHED;
		$options['type'] = $my->isSiteAdmin() ? 'all' : 'user';

		// Set the start date of the event listed
		$options['start-after'] = $pastEvent;

		// Include any event inclusion
		$inclusion = trim($params->get('event_inclusion', ''));

		if ($inclusion) {
			$options['inclusion'] = explode(',', $inclusion);
		}

		$events = array();

		// Category filtering
		$category = $params->get('category');

		if ($category) {
			$options['category'] = $category;
		}

		// Filter featured event only
		if ($filter == 2) {
			$options['featured'] = true;
		}

		// Filter events participated by current logged in user only
		if ($filter == 3) {
			$my = ES::user();

			$options['type'] = 'user';
			$options['guestuid'] = $my->id;
		}

		// Filter by group
		if ($filter == 4) {
			$options['group_id'] = $params->get('groupId');
		}

		// Filter by page
		if ($filter == 5) {
			$options['page_id'] = $params->get('pageId');
		}

		if ($filter == 'upcoming') {
			$days = $params->get('upcoming_days', 14);
			$date = ES::date();
			$now = $date->toSql();
			$future = ES::date($date->toUnix() + ($days * 24*60*60))->toSql();

			$options['start-after'] = $now;
			$options['start-before'] = $future;
			$options['type'] = 'user';
		}

		// Get the events
		$events = $model->getEvents($options);

		return $events;
	}
}