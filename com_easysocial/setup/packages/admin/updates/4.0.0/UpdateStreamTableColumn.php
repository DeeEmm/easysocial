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

class SocialMaintenanceScriptUpdateStreamTableColumn extends SocialMaintenanceScript
{
	public static $title = "Update streams table to support Joomla 4.";
	public static $description = 'Making the necessary changes for the tables for Joomla 4\'s strict SQL mode';

	public function main()
	{
		$db = ES::db();

		$defaultDateValue = $this->isMySQL56() ? 'CURRENT_TIMESTAMP' : $db->Quote('0000-00-00 00:00:00');

		$queries = [];

		$queries[] = "ALTER TABLE `#__social_stream` 
						MODIFY `created` datetime NOT NULL DEFAULT $defaultDateValue,
						MODIFY `modified` datetime NOT NULL DEFAULT $defaultDateValue,
						MODIFY `edited` DATETIME NULL,
						MODIFY `last_action_date` DATETIME NULL
					";

		$queries[] = "ALTER TABLE `#__social_stream_history` 
						MODIFY `created` datetime NOT NULL DEFAULT $defaultDateValue,
						MODIFY `modified` datetime NOT NULL DEFAULT $defaultDateValue,
						MODIFY `edited` DATETIME NULL
					";

		if ($this->isMySQL56()) {
			$queries[] = "ALTER TABLE `#__social_stream_item` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";

			$queries[] = "ALTER TABLE `#__social_stream_item_history` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";

			$queries[] = "ALTER TABLE `#__social_stream_sticky` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";
		}

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
