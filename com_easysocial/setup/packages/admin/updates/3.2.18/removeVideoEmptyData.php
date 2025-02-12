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

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptRemoveVideoEmptyData extends SocialMaintenanceScript
{
	public static $title = 'Remove video invalid data' ;
	public static $description = 'Remove those video data which stored empty data from database';

	public function main()
	{
		$db = ES::db();
		$query = array();

		$query[] = 'DELETE FROM `#__social_videos`';
		$query[] = 'WHERE `source` = "link"';
		$query[] = 'AND `user_id` = 0';
		$query[] = 'AND `uid` = 0';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
