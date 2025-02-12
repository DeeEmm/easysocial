<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateFieldsDataColumn extends SocialMaintenanceScript
{
	public static $title = 'Update fields data column to support utf8mb4 character set';
	public static $description = 'Update fields data column to support utf8mb4 character set';

	public function main()
	{
		$jConfig = JFactory::getConfig();
		$dbType = $jConfig->get('dbtype');
		$columnExist = true;

		if ($dbType == 'mysql' || $dbType == 'mysqli') {
			$db = ES::db();

			$dbversion = $db->getVersion();
			$dbversion = (float) $dbversion;

			if ($dbversion >= '5.5') {
				$query = "SHOW FULL COLUMNS FROM `#__social_fields_data` where `Field` = 'data'";

				$db->setQuery($query);
				$result = $db->loadObjectList();

				$collation = $result[0]->Collation;

				if (strpos($collation, 'utf8mb4') === false) {
					$query = "ALTER TABLE `#__social_fields_data` MODIFY `data` TEXT CHARACTER SET utf8mb4 NOT NULL;";
					$db->setQuery($query);
					$db->query();

					// we need to drop the indexes that involved this raw column first.
					$query = "ALTER TABLE `#__social_fields_data` DROP INDEX `idx_type_key_raw`, DROP INDEX `idx_type_raw`";
					$db->setQuery($query);
					$db->query();

					$query = "ALTER TABLE `#__social_fields_data` MODIFY `raw` TEXT CHARACTER SET utf8mb4;";
					$db->setQuery($query);
					$db->query();

					// last we re-add the indexes.
					$query = "ALTER TABLE `#__social_fields_data` ADD INDEX `idx_type_raw` (`type` (25), `raw` (175))";
					$query .= ", ADD INDEX `idx_type_key_raw` (`type` (25), `datakey` (50), `raw` (125))";
					$db->setQuery($query);
					$db->query();
				}
			}
		}

		return true;
	}
}
