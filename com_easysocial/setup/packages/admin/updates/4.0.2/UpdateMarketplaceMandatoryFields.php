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

class SocialMaintenanceScriptUpdateMarketplaceMandatoryFields extends SocialMaintenanceScript
{
	public static $title = "Update stock and condition field";
	public static $description = 'Remove these fields from mandatory';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = array();

		// Set it to unique
		$query[] = "UPDATE `#__social_apps` SET `core` = " . $db->quote(0);
		$query[] = "WHERE `element` = 'stock' OR `element` = 'condition'";
		$query[] = "AND `group` = 'marketplace' AND `type` = 'fields'";

		$query = implode(' ', $query);

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}
}
