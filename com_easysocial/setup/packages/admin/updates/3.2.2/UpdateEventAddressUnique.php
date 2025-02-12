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

class SocialMaintenanceScriptUpdateEventAddressUnique extends SocialMaintenanceScript
{
	public static $title = 'Update apps table for event address field' ;
	public static $description = 'Fixed on an event should only have one address field';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();

		$query[] = "SELECT `unique` FROM `#__social_apps`";
		$query[] = "WHERE `element` = 'address'";
		$query[] = "AND `group` = 'event' AND `type` = 'fields'";

		$query = implode(' ', $query);
		$sql->raw($query);
		$db->setQuery($sql);

		$column = $db->loadColumn();

		// If the event address field is unique already, do not need to proceed
		// $column[0] since only select 1 column which is `unique`
		if ($column[0] == 1) {
			return true;
		}

		$query = array();

		// Set it to unique
		$query[] = "UPDATE `#__social_apps` SET `unique` = " . $db->quote(1);
		$query[] = "WHERE `element` = 'address'";
		$query[] = "AND `group` = 'event' AND `type` = 'fields'";

		$query = implode(' ', $query);

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}
}