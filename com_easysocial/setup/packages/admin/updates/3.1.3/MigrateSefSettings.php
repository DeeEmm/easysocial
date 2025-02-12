<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptMigrateSefSettings extends SocialMaintenanceScript
{
	public static $title = 'Migrate SEO setting for user\'s media permalinks.';
	public static $description = 'Configure the SEO setting on user\'s media SEF links to use proper setting depending on which version system being upgraded from.';

	public function main()
	{
		jimport('joomla.filesystem.file');

		$filename = md5(SOCIAL_FILE_CACHE_FILENAME);
		$filepath = SOCIAL_FILE_CACHE_DIR . '/' . $filename . '-cache.php';

		$isUpgradeFrom30 = JFile::exists($filepath) ? false : true;

		if (!$isUpgradeFrom30) {
			$config = ES::config();
			$config->set('seo.mediasef', SOCIAL_MEDIA_SEF_WITHUSER);

			// Convert the config object to a json string.
			$jsonString = $config->toString();

			$configTable = ES::table('Config');
			if (!$configTable->load('site')) {
				$configTable->type  = 'site';
			}

			$configTable->set('value' , $jsonString);
			$state = $configTable->store();
		}

		return true;
	}
}
