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

class SocialMaintenanceScriptRemovePagePhotoAccess extends SocialMaintenanceScript
{
	public static $title = 'Remove Page access for photos.upload';
	public static $description = 'Remove the Page access for photos.upload';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "delete from `#__social_access_rules` where `name` = " . $db->Quote('photos.upload') . " AND `group` = " . $db->Quote(SOCIAL_TYPE_PAGE);
		$sql->raw($query);
		$db->setQuery($sql);

		return true;
		
	}
}