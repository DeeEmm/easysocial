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

class SocialMaintenanceScriptFixPhotoStreamLikes extends SocialMaintenanceScript
{
	public static $title = 'Fix the issue with missing likes for photo stream';
	public static $description = 'Fixed the issue with missing likes that are posted from the story form';

	public function main()
	{
		$types = array('user', 'group', 'page', 'event');

		$db = ES::db();
		$sql = $db->sql();

		foreach ($types as $type) {
			$query = 'UPDATE `#__social_likes` AS a';
			$query .= ' INNER JOIN `#__social_likes` AS b on a.`id` = b.`id`';
			$query .= ' SET a.`type` = ' . $db->Quote('stream.' . $type . '.upload') . ', a.`uid` = b.`stream_id`';
			$query .= ' where b.`type` = ' . $db->Quote('photos.' . $type . '.upload');

			$sql->raw($query);
			$db->setQuery($sql);
			$db->query();
		}

		return true;
	}
}
