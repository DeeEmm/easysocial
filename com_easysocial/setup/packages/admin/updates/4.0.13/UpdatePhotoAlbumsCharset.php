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

class SocialMaintenanceScriptUpdatePhotoAlbumsCharset extends SocialMaintenanceScript
{
	public static $title = 'Update Photos and Albums table to support utf8mb4 character set';
	public static $description = 'Update Photos and Albums table to support utf8mb4 character set';

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
				$query = "SHOW FULL COLUMNS FROM `#__social_albums` where `Field` = 'title'";

				$db->setQuery($query);
				$result = $db->loadObjectList();

				$collation = $result[0]->Collation;

				if (strpos($collation, 'utf8mb4') === false) {
					// update album table's charset to utf8mb4
					$query = "ALTER TABLE `#__social_albums` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
					$db->setQuery($query);
					$db->query();

					// update photos table's charset to utf8mb4
					$query = "ALTER TABLE `#__social_photos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
					$db->setQuery($query);
					$db->query();
				}
			}
		}

		return true;
	}
}
