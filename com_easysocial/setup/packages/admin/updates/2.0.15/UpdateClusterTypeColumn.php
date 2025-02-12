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

class SocialMaintenanceScriptUpdateClusterTypeColumn extends SocialMaintenanceScript
{
	public static $title = 'Update ClusterType column type in stream table';
	public static $description = 'Ensure ClusterType column type in stream table is nullable';

	public function main()
	{
		$db = ES::db();

		// lets check if we need to perform the column update or not.
		$query = "select count(1) from `#__social_stream` where `cluster_type` = ''";
		$db->setQuery($query);
		$count = $db->loadResult();

		if ($count) {
			// make sure clustertype the column type is nullable.
			$query = "ALTER TABLE `#__social_stream` MODIFY COLUMN `cluster_type` varchar(64) null";
			$db->setQuery($query);
			$db->query();

			// now make sure the value in this column the empty value is a null value.
			$query = "UPDATE `#__social_stream` SET `cluster_type` = null WHERE `cluster_type` = ''";
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}
}
