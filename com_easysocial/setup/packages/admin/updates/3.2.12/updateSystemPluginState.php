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

class SocialMaintenanceScriptUpdateSystemPluginState extends SocialMaintenanceScript
{
	public static $title = 'Update EasySocial system plugins.';
	public static $description = 'Updating EasySocial system plugins so that these plugin can be found from Joomla installer in manage listing.';

	public function main()
	{
		$db = ES::db();

		$query = "UPDATE `#__extensions` SET `state` = 0 WHERE `type` = 'plugin' AND `element` LIKE 'easysocial%' and `state` = 1";
		$db->setQuery($query);
		$db->query();

		return true;
	}
}
