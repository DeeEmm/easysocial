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

class SocialMaintenanceScriptMigrateAppendUseridSetting extends SocialMaintenanceScript
{
	public static $title = 'Migrate deprecated setting - Append User ID';
	public static $description = 'Migrate the deprecated setting - Append User ID into new SEO setting - SEF Urls To Use IDs';

	public function main()
	{
		// users.appenduserid
		// to
		// seo.useid

		$state = true;
		$config = ES::config();
		$appendId = $config->get('users.appenduserid', null);

		if (!is_null($appendId) && !$appendId) {

			// append user id disabled. let update the seo use id to false.
			$config->set('seo.useid', "0");

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
