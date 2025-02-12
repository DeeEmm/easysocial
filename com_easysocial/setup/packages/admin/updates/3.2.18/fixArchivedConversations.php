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

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptFixArchivedConversations extends SocialMaintenanceScript
{
	public static $title = 'Fix archived conversations';
	public static $description = 'To make sure all archived conversations of a user is being archieved correctly.';

	public function main()
	{
		$db = ES::db();
		$max = 50;

		// first we try to get archieved conversation and 
		// check if that particular conversation has unarchived message or not.

		$query = "SELECT a.`conversation_id`, a.`user_id`";
		$query .= " FROM `#__social_conversations_message_maps` as a";
		$query .= " INNER JOIN (";
		$query .= " 	SELECT DISTINCT `conversation_id`, `user_id`";
		$query .= " 	FROM `#__social_conversations_message_maps`";
		$query .= " 	WHERE `state` = 0";
		$query .= " ) as x on a.`conversation_id` = x.`conversation_id` AND a.`user_id` = x.`user_id`";
		$query .= " AND a.`state` = 1";
		$query .= " ORDER BY a.`conversation_id` DESC";
		$query .= " LIMIT $max";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results) {
			foreach ($results as $row) {

				$cid = $row->conversation_id;
				$uid = $row->user_id;

				$query = "UPDATE `#__social_conversations_message_maps` SET `state` = 0";
				$query .= " WHERE `conversation_id` = " . $db->Quote($cid);
				$query .= " AND `user_id` = " . $db->Quote($uid);

				$db->setQuery($query);
				$db->query();
			}
		}

		return true;
	}
}
