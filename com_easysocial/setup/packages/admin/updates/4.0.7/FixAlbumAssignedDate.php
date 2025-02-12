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

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptFixAlbumAssignedDate extends SocialMaintenanceScript
{
	public static $title = 'Fix Asssigned Date in photo albums';
	public static $description = 'Make sure assigned date has value for all standard photos albums';

	public function main()
	{
		$db = ES::db();

		$query = "update `#__social_albums` set `assigned_date` = `created` where `core` = 0 and `assigned_date` = " . $db->Quote('0000-00-00 00:00:00');
		$db->setQuery($query);
		$db->query();

		return true;
	}
}
