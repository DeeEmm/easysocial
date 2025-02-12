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

class SocialMaintenanceScriptFixAlbumPhotoUserId extends SocialMaintenanceScript
{
	public static $title = 'Migrate user id in albums and photos table';

	public static $description = 'Migrating user id from column uid into user_id in albums and photos tables.';

	public function main()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		// updating albums table.
		$query = 'update `#__social_albums` set `user_id` = `uid` where `type` = ' . $db->Quote( 'user' );
		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();

		// updating photos table.
		$query = 'update `#__social_photos` set `user_id` = `uid` where `type` = ' . $db->Quote( 'user' );
		$sql->clear();
		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();

		return true;
	}
}
