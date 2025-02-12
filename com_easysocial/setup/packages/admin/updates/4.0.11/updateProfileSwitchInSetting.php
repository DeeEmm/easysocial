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

class SocialMaintenanceScriptUpdateProfileSwitchInSetting extends SocialMaintenanceScript
{
	public static $title = "Update Profile Switch-In Setting";
	public static $description = 'Configure the existings profile type on profile switch in setting.';

	public function main()
	{
		$db = ES::db();

		// the switchable_in should follow the registration setting in profile type
		$query = "UPDATE `#__social_profiles` SET `switchable_in` = `registration`";

		$db->setQuery($query);
		$db->query();

		return true;
	}
}