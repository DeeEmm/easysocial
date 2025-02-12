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

class SocialMaintenanceScriptFixNotificationContext extends SocialMaintenanceScript
{
	public static $title = 'Migrate type context in notifications';

	public static $description = 'Migrate context type in notifications table to use proper context with verb.';

	public function main()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$queries = array();

		// photos
		$query = "update `#__social_notifications`";
		$query .= " set `context_type` = 'photos.create'";
		$query .= " where `context_type` = 'photos'";
		$query .= " and `cmd` = 'likes.likes'";
		$queries[] = $query;

		// albums
		$query = "update `#__social_notifications`";
		$query .= " set `context_type` = 'albums.create'";
		$query .= " where `context_type` = 'albums'";
		$query .= " and `cmd` = 'likes.likes'";
		$queries[] = $query;

		foreach ($queries as $query) {

			$sql->clear();
			$sql->raw( $query );
			$db->setQuery( $sql );
			$db->query();
		}

		return true;
	}

}
