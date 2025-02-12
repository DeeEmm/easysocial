<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptFixedDuplicateReactions extends SocialMaintenanceScript
{
	public static $title = 'Fixed Duplicate Reactions On Photos' ;
	public static $description = 'Fixed possible duplicate reactions on photos';

	public function main()
	{
		$db = ES::db();

		// Change the verb to a proper verb
		$type = array(
			'photos.group.create' => 'photos.group.add',
			'photos.page.create' => 'photos.page.add',
			'photos.event.create' => 'photos.event.add',
			'photos.user.create' => 'photos.user.add'
		);

		foreach ($type as $key => $value) {
			$query = 'UPDATE `#__social_likes` SET `type` = ' . $db->Quote($value) . 'WHERE `type` = ' . $db->Quote($key);

			$db->setQuery($query);
			$db->query();
		}

		// Delete duplicate reactions
		$query = array();
		$query[] = 'DELETE a FROM `#__social_likes` as a';
		$query[] = 'INNER JOIN (';
		$query[] = 'SELECT max(id) as max_id, `type`, `uid`, `stream_id`, `created_by`, `react_as` FROM `#__social_likes`';
		$query[] = 'GROUP BY `type`, `uid`, `stream_id`, `created_by`, `react_as`';
		$query[] = 'having count(id) > 1) as b';
		$query[] = 'ON a.`type` = b.`type` and a.`uid` = b.`uid` and a.`stream_id` = b.`stream_id` and a.`created_by` = b.`created_by` and a.`react_as` = b.`react_as` and a.`id` != b.`max_id`';
		$query[] = 'WHERE a.`type` IN("photos.group.add", "photos.event.add", "photos.page.add", "photos.user_add")';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
