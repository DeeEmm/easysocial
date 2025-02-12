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

class SocialMaintenanceScriptFixListFieldData extends SocialMaintenanceScript
{
	public static $title = 'Fix list field data';

	// public static $title = 'COM_EASYSOCIAL_MAINTENANCE_SCRIPT_FIXDATETIMEFIELDDATA_TITLE';

	public static $description = 'Store plain text for list fiedls. E.g. checkbox, dropdown, country and etc.';

	// public static $description = 'COM_EASYSOCIAL_MAINTENANCE_SCRIPT_FIXDATETIMEFIELDDATA_DESCRIPTION';

	public function main()
	{
		$json	= ES::json();
		$db		= ES::db();
		$sql	= $db->sql();

		// update [""] to empty string.
		$query = 'update `#__social_fields_data` set `raw` = \'\' where `raw` = ' . $db->Quote( '[""]' );
		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();


		//now let retrieve data that the raw data is json string.
		$query = 'select id, raw from `#__social_fields_data`';
		$query .= ' where `raw` like ' . $db->Quote('["%');
		$sql->raw($query);

		$db->setQuery($sql);
		$results = $db->loadObjectList();

		if ($results) {
			foreach ($results as $row) {
				if ($json->isJsonString($row->raw)) {
					$raw = $json->decode($row->raw);
					if (is_array($raw)) {
						$raw = implode(',', $raw);
					}

					if (! empty($raw)) {
						//update the row.
						$update = "update `#__social_fields_data` set raw = '$raw' where id = '$row->id'";
						$sql->clear();
						$sql->raw($update);
						$db->setQuery($sql);
						$db->query();
					}
				}//if
			}//foreach
		}//if

		return true;
	}
}
