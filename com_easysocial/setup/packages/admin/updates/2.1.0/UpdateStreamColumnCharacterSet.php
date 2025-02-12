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

class SocialMaintenanceScriptUpdateStreamColumnCharacterSet extends SocialMaintenanceScript
{
	public static $title = 'Update stream column content character set to utf8mb4';
	public static $description = 'Update stream column content character set to utf8mb4';

	public function main()
	{
		$jConfig = JFactory::getConfig();
		$dbType = $jConfig->get('dbtype');
		$columnExist = true;

		if ($dbType == 'mysql' || $dbType == 'mysqli') {
			$db = ES::db();
			$sql = $db->sql();

			$dbversion = $db->getVersion();
			$dbversion = (float) $dbversion;

			$columns = $db->getTableColumns('#__social_stream');

			// Check if the column is in the table or not
			$columnExist = in_array('dummy', $columns);

			if (!$columnExist) {
				if ($dbversion >= '5.5') {
					$query = "ALTER TABLE `#__social_stream` MODIFY `content` TEXT CHARACTER SET utf8mb4 NOT NULL;";

					$sql->raw($query);
					$db->setQuery($sql);
					$db->query();
				}

				$query = "ALTER TABLE `#__social_stream` ADD COLUMN `dummy` tinyint(1) NULL default '1' AFTER `anywhere_id`;";

				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();
			}
		}

		return true;
	}
}
