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

class SocialMaintenanceScriptUpdateStrictSQLMode extends SocialMaintenanceScript
{
	public static $title = "Update strict SQL MODE for Joomla 4.";
	public static $description = 'Making the necessary changes for the tables for Joomla 4\'s strict SQL mode';

	public function main()
	{
		$db = ES::db();

		$defaultDateValue = $this->isMySQL56() ? 'CURRENT_TIMESTAMP' : $db->Quote('0000-00-00 00:00:00');

		$queries = [];
		$queries[] = "ALTER TABLE `#__social_fields_data` MODIFY `params` TEXT";

		$queries[] = "ALTER TABLE `#__social_photos` 
					MODIFY `assigned_date` datetime NOT NULL DEFAULT $defaultDateValue,
					MODIFY `caption` text NULL,
					ALTER `featured` SET DEFAULT 0,
					ALTER `ordering` SET DEFAULT 0,
					ALTER `state` SET DEFAULT 0
				";

		$queries[] = "ALTER TABLE `#__social_covers` 
					ALTER `photo_id` SET DEFAULT 0,
					ALTER `cover_id` SET DEFAULT 0
				";

		$queries[] = "ALTER TABLE `#__social_photos_meta` ALTER `photo_id` SET DEFAULT 0";

		$queries[] = "ALTER TABLE `#__social_albums`
					MODIFY `caption` TEXT,
					MODIFY `assigned_date` datetime NOT NULL DEFAULT $defaultDateValue,
					ALTER `cover_id` SET DEFAULT 0,
					ALTER `core` SET DEFAULT 0,
					ALTER `notified` SET DEFAULT 0,
					ALTER `hits` SET DEFAULT 0
				";

		foreach ($queries as $query) {
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	private function isMySQL56()
	{
		static $_cache = null;

		if (is_null($_cache)) {
			$db = JFactory::getDBO();
			// we check the server version 1st
			$server_version = $db->getVersion();
			$_cache = version_compare($server_version, '5.6.0', '>=');
		}

		return $_cache;
	}
}
