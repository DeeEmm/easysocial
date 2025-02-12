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

class SocialMaintenanceScriptUpdateMobileNotificationsColumn extends SocialMaintenanceScript
{
	public static $title = 'Add new column for mobile notifications data' ;
	public static $description = 'Add new column for mobile notifications data to support notifications from multiple different mobile app';

	public function main()
	{
		$db = ES::db();
		$jConfig = ES::jconfig();
		$dbname = $jConfig->get('db');
		$dbprefix = $jConfig->get('dbprefix');

		$query = array();

		$query[] = 'SELECT count(*) FROM information_schema.tables';
		$query[] = 'where `table_schema` = ' . $db->Quote($dbname);
		$query[] = 'and TABLE_NAME = ' . $db->Quote($dbprefix . 'social_notifications_mobile');

		$query = implode(' ', $query);

		$db->setQuery($query);
		$exists = (bool) $db->loadResult();

		if (!$exists) {
			return true;
		}

		$query = 'ALTER TABLE `#__social_notifications_mobile` ADD `app_type` VARCHAR(255) NOT NULL';

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
