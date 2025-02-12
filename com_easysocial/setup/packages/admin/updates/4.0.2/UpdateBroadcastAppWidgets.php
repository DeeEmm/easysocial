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

class SocialMaintenanceScriptUpdateBroadcastAppWidgets extends SocialMaintenanceScript
{
	public static $title = "Update broadcast app to support widget view.";
	public static $description = 'Update broadcast app so that stream widget will work correctly on frontend.';

	public function main()
	{
		$db = ES::db();
		$q = array();

		// Set it to unique
		$q[] = "UPDATE `#__social_apps` SET `widget` = " . $db->quote(1);
		$q[] = "WHERE `element` = 'broadcast'";
		$q[] = "AND `type` = 'apps'";
		$q[] = "AND `group` = 'user'";

		$query = implode(' ', $q);

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
