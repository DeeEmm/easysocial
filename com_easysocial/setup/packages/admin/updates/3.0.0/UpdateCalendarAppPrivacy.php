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

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateCalendarAppPrivacy extends SocialMaintenanceScript
{
	public static $title = 'Update user calendar app privacy';
	public static $description = 'To update items privacy records that created using user calendar app';

	public function main()
	{
		// get apps.calendar privacy id

		$db = ES::db();

		$query = "select `id` from `#__social_privacy`";
		$query .= " where `type` = " . $db->Quote('apps');
		$query .= " and `rule` = " . $db->Quote('calendar');

		$db->setQuery($query);
		$pid = $db->loadResult();

		if ($pid) {

			// we need to get all the privacy_items that associated with calendars...
			$query = "select b.`id` as `calendar_id`, a.*";
			$query .= " from `#__social_privacy_items` as a";
			$query .= " inner join `#__social_apps_calendar` as b on a.`uid` = b.`id`";
			$query .= " where a.`type` = " . $db->Quote('view');
			$query .= " UNION ALL ";
			$query .= " select c.`context_id` as `calendar_id`, a.* ";
			$query .= " from `#__social_privacy_items` as a";
			$query .= " 	inner join `#__social_stream_item` as c on a.`uid` = c.`id`";
			$query .= " where c.`context_type` = " . $db->Quote('calendar');
			
			$db->setQuery($query);

			$results = $db->loadObjectList();

			if ($results) {

				$items = array();

				// manual grouping
				foreach ($results as $result) {
					$items[$result->calendar_id][$result->type][] = $result;
				}

				$tobeUpdate = array();
				$tobeRemove = array();

				foreach ($items as $id => $items) {

					$calendar_id = $id;

					if (isset($items['view']) && isset($items['activity'])) {
						foreach ($items['activity'] as $item) {
							$tobeRemove[] = $item->id;
						}
					}

					if (!isset($items['view']) && isset($items['activity'])) {

						$lastItem = array_pop($items['activity']);
						$tobeUpdate[] = $lastItem;

						if ($items['activity']) {
							foreach ($items['activity'] as $item) {
								$tobeRemove[] = $item->id;
							}
						}
					}

				}


				if ($tobeUpdate) {

					// we will use the Insert on duplicate trick so that we dont have to use loop to
					// execute update query for each items.
					$condition = array();

					$updateInsert = "insert into `#__social_privacy_items` (`id`, `privacy_id`, `user_id`, `uid`, `type`, `value`, `field_access`) VALUES ";

					foreach ($tobeUpdate as $item) {
						// lets build the item udpate query.
						$tmp = "(" . $db->Quote($item->id) . "," . $db->Quote($pid) . ", " . $db->Quote($item->user_id) . "," . $db->Quote($item->calendar_id) . "," . $db->Quote('calendar');
						$tmp .= ", " . $db->Quote($item->value) . "," . $db->Quote($item->field_access) . ")";
						$condition[] = $tmp;
					}

					$updateInsert .= implode(",", $condition);
					$updateInsert .= " ON DUPLICATE KEY UPDATE";
					$updateInsert .= " `privacy_id` = VALUES(`privacy_id`),";
					$updateInsert .= " `uid` = VALUES(`uid`),";
					$updateInsert .= " `type` = VALUES(`type`)";

					$db->setQuery($updateInsert);
					$db->query();
				}

				if ($tobeRemove) {
					// lets build the remove query.
					$remove = "delete from `#__social_privacy_items`";
					$remove .= " where `id` IN (" . implode(',', $tobeRemove) . ")";

					$db->setQuery($remove);
					$db->query();

				}
			}

			// finally, we update any existing view type in privacy items.
			$update = "update `#__social_privacy_items`";
			$update .= " set `type` = " . $db->Quote('calendar');
			$update .= " where `privacy_id` = " . $db->Quote($pid);
			$update .= " and `type` = " . $db->Quote('view');

			$db->setQuery($update);
			$db->query();
		}

		return true;
	}
}
