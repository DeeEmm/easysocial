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

class SocialMaintenanceScriptPurgeTmpFolder extends SocialMaintenanceScript
{
	public static $title = 'Purge unwanted files and folders from the temporary folder';
	public static $description = 'Removed files and folders from the temporary folder that are no longer in use';

	public function main()
	{
		$config = ES::config();
		$path = JPATH_ROOT . $config->get('uploader.storage.container');
		$files = JFolder::files($path, '.[p|P][h|H][pP]', true, true);

		if ($files) {
			foreach ($files as $file) {

				// Delete the file
				JFile::delete($file);
			}
		}

		// Purge any data that is older than 2 days
		$db = ES::db();

		$query = array(
			'DELETE FROM `#__social_uploader`',
			'WHERE `created` < NOW() - INTERVAL 2 DAY'
		);

		$db->setQuery($query);

		$db->Query();

		return true;
	}
}
