<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialModRecentPollsHelper
{
    public static function getPolls(&$params)
    {
		$model = ES::model('Polls');

		// Determine filter type
		$filter = $params->get('filter');

		// Determine the ordering of the events
		$ordering = $params->get('ordering', 'start');

		$options = array();

		if ($filter == 'mine') {
			$my = ES::user();
			$options['user_id'] = $my->id;
		}

		// Limit the number of events based on the params
		$options['limit'] = $params->get('display_limit', 5);

		$result = $model->getPolls($options);

		$polls = array();

		if ($result) {
			// Format the polls
			foreach ($result as $row) {

				$poll = ES::table('Polls');
				$poll->bind($row);

				$polls[] = $poll;				
			}
		}

		return $polls;
    }
}
