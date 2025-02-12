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

class SocialMaintenanceScriptUpdateAssignedDate extends SocialMaintenanceScript
{
	public static $title = "Update Assigned Date in videos and audios table.";
	public static $description = 'Add default assigned_date using created date in videos and audios table.';

	public function main()
	{
		$db = ES::db();

		$q = [];

		// videos
		$q[] = "UPDATE `#__social_videos` SET `assigned_date` = `created` WHERE (`assigned_date` IS NULL OR `assigned_date` = '0000-00-00 00:00:00')";

		// audios
		$q[] = "UPDATE `#__social_audios` SET `assigned_date` = `created` WHERE (`assigned_date` IS NULL OR `assigned_date` = '0000-00-00 00:00:00')";

		foreach ($q as $query) {
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
}