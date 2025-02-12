<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptRemoveLocationsApp extends SocialMaintenanceScript
{
	public static $title = 'Unpublish locations application.';

	public static $description = 'Unpublish locations app since it is no longer used.';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();

		// updating comment the element column
		$query = 'update `#__social_apps` set `state` = ' . $db->Quote(0);
		$query .= ' where `element` = ' . $db->Quote('locations');
		$query .= ' and `group` = ' . $db->Quote('user');
		$query .= ' and `type` = ' . $db->Quote('apps');

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}

}
