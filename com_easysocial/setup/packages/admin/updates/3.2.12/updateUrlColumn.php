<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateUrlColumn extends SocialMaintenanceScript
{
	public static $title = 'Update column for Social Url Table';
	public static $description = 'Updating certain columns for social url table to fix long url not being able to save properly';

	public function main()
	{
		$db = ES::db();

		$query = 'ALTER TABLE `#__social_urls` MODIFY `sefurl` TEXT CHARACTER SET utf8mb4 NOT NULL';
		$db->setQuery($query);
		$db->query();

		$query = 'ALTER TABLE `#__social_urls` MODIFY `rawurl` TEXT CHARACTER SET utf8mb4 NOT NULL';
		$db->setQuery($query);
		$db->query();

		// Modify the existing index to ensure the size is not too big.
		$existingKeys = array('sefurl', 'rawurl');

		foreach ($existingKeys as $key) {
			$query = 'SHOW INDEX FROM `#__social_urls` WHERE `Key_name` = ' . $db->Quote($key);

			$db->setQuery($query);
			$result = $db->loadResult();

			if ($result) {
				$query = 'ALTER TABLE `#__social_urls` DROP KEY ' . $db->nameQuote($key);
				$db->setQuery($query);
				$db->query();
			}

			$query = 'ALTER TABLE `#__social_urls` ADD KEY ' . $db->nameQuote($key) . ' (' . $db->nameQuote($key) . ' (255))';
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}
}
