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

class SocialMaintenanceScriptUpdateDateDefaultValue extends SocialMaintenanceScript
{
	public static $title = "Update datetime column default value for Joomla 4.";
	public static $description = 'Making the necessary changes on datetime columns for Joomla 4\'s strict SQL mode';

	public function main()
	{
		$db = ES::db();

		$defaultDateValue = $this->isMySQL56() ? 'CURRENT_TIMESTAMP' : $db->Quote('0000-00-00 00:00:00');

		$queries = [];

		// update default value on datetime columns.
		if ($this->isMySQL56()) {
			$queries[] = "ALTER TABLE `#__social_alert` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";

			$queries[] = "ALTER TABLE `#__social_likes` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";

			$queries[] = "ALTER TABLE `#__social_comments` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";

			$queries[] = "ALTER TABLE `#__social_search_filter` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";
		}

		$queries[] = "ALTER TABLE `#__social_polls` 
						MODIFY `created` datetime NOT NULL DEFAULT $defaultDateValue,
						MODIFY `expiry_date` DATETIME NULL
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
