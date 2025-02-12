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

class SocialMaintenanceScriptFixStreamHistoryParams extends SocialMaintenanceScript
{
	public static $title = 'Fix params column in stream history table';
	public static $description = 'Fix params column in stream history table';

	public function main()
	{
		$db = ES::db();

		$query = 'SHOW FIELDS FROM ' . $db->nameQuote('#__social_stream_history');
		$db->setQuery($query);

		$rows = $db->loadObjectList();
		$fields	= array();

		if ($rows) {
			foreach ($rows as $row) {
				$fields[$row->Field] = $row->Type;
			}
		}

		if (isset($fields['params']) && $fields['params'] && $fields['params'] == 'text') {
			// we need to update the column data type to longtext
			$update = 'alter table ' . $db->nameQuote('#__social_stream_history') . ' modify `params` LONGTEXT';
			$db->setQuery($update);
			$db->query();
		}

		return true;
	}
}
