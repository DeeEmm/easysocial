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

class SocialMaintenanceScriptUpdateClusterAccess extends SocialMaintenanceScript
{
	public static $title = 'Update Group and Event default access for photos.upload';
	public static $description = 'Update the Group and Event access for photos.upload';

	public function main()
	{
		$db = ES::db();

		$clusters = array('group', 'event');

		foreach ($clusters as $cluster) {
			// lets check if we need to perform the column update or not.
			$query = "select id from `#__social_access_rules` where `name` = 'photos.upload' AND `group` = " . $db->quote($cluster);
			$db->setQuery($query);
			$id = $db->loadResult();

			if (!$id) {
				continue;
			}

			$accessRule = ES::table('AccessRules');
			$accessRule->load($id);

			$params = json_decode($accessRule->params);
			$params->default = 'members';

			$accessRule->params = json_encode($params);
			
			unset($accessRule->type);
			unset($accessRule->default);

			$accessRule->store();
		}

		return true;
		
	}
}