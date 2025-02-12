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

class SocialMaintenanceScriptUpdateCommentColumnCharacterSet extends SocialMaintenanceScript
{
	public static $title = 'Update comments column comment character set to utf8mb4';
	public static $description = 'Update comments column comment character set to utf8mb4';

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

			if ($dbversion >= '5.5') {
				$query = "SHOW FULL COLUMNS FROM `#__social_comments` where field = 'comment'";

				$db->setQuery($query);
				$result = $db->loadObjectList();

				$collation = $result[0]->Collation;

				if (strpos($collation, 'utf8mb4') === false) {
					$query = "ALTER TABLE `#__social_comments` MODIFY `comment` TEXT CHARACTER SET utf8mb4 NOT NULL;";

					$sql->raw($query);
					$db->setQuery($sql);
					$db->query();
				}
			}
		}

		return true;
	}
}
