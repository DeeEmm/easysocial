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

class SocialMaintenanceScriptFixPhotosOrderingConfig extends SocialMaintenanceScript
{
	public static $title = 'Fixed the configuration of the photos ordering';
	public static $description = 'Fixed the configuration of the photos ordering';

	public function main()
	{
		$db = ES::db();

		$query = 'SHOW COLUMNS FROM `#__social_photos` LIKE ' . $db->Quote('orderingfix');
		$db->setQuery($query);
		$exists = $db->loadColumn();

		if (!$exists) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__social_config');
			$sql->column('value');
			$sql->where('type', 'site');

			$db->setQuery($sql);
			$value = $db->loadResult();

			$obj = ES::makeObject($value);

			$current = $obj->photos->layout->ordering;;
			$obj->photos->layout->ordering = $current == 'desc' ? 'asc' : 'desc';

			$string = ES::makeJSON($obj);

			$sql->clear();
			$sql->update('#__social_config');
			$sql->set('value', $string);
			$sql->where('type', 'site');

			$db->setQuery($sql);
			$db->query();

			$query = 'ALTER TABLE `#__social_photos` add column `orderingfix` tinyint(1) default 1';
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}
}
