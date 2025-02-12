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

class SocialMaintenanceScriptMigrateStoryPhotosComments extends SocialMaintenanceScript
{
	public static $title = 'Migrate photos comments from story post';
	public static $description = 'Migrate the comments of the photos posted from the story form with the correct verb';

	public function main()
	{
		$types = array('user', 'group', 'page', 'event');

		$db = ES::db();
		$sql = $db->sql();

		foreach ($types as $type) {
			$query = 'UPDATE `#__social_comments` AS a';
			$query .= ' INNER JOIN `#__social_comments` AS b on a.`id` = b.`id`';
			$query .= ' SET a.`element` = ' . $db->Quote('stream.' . $type . '.upload') . ', a.`uid` = b.`stream_id`';
			$query .= ' where b.`element` = ' . $db->Quote('photos.' . $type . '.upload');

			$sql->raw($query);
			$db->setQuery($sql);
			$db->query();
		}

		return true;
	}
}
