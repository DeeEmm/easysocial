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

class SocialMaintenanceScriptFixPhotosMetaPath extends SocialMaintenanceScript
{
	public static $title = 'Fix absolute path which is stored in the photos meta table';

	public static $description = 'Fix absolute path which is stored in the photos meta table';

	public function main()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_photos_meta');
		$sql->column('value');
		$sql->where('group', 'path');

		$db->setQuery($sql);
		$result 	= $db->loadResult();

		// Prepare the relative path
		$config 	= ES::config();
		$relative	= $config->get('photos.storage.container');
		$pos 		= strpos($result, $relative);
		$path		= substr($result, 0, $pos);

		$query 	= 'UPDATE `#__social_photos_meta` SET `value` = replace(`value`,' . $db->Quote($path) . ',' . $db->Quote('') . ')';
		$query	.= ' WHERE `group`=' . $db->Quote('path');

		$sql->clear();
		$sql->raw($query);

		$db->setQuery($sql);
		$db->Query();

		return true;
	}
}
