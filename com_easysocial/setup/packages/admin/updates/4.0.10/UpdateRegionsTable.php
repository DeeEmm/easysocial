<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateRegionsTable extends SocialMaintenanceScript
{
	public static $title = "Update Regions Table for Joomla 4.";
	public static $description = 'Making the necessary changes for the tables for Joomla 4\'s strict SQL mode';

	public function main()
	{
		$db = ES::db();

		$query = "ALTER TABLE `#__social_regions` ALTER `parent_uid` SET DEFAULT 0, ALTER `parent_type` SET DEFAULT '' ";

		$db->setQuery($query);
		$db->execute();

		return true;
	}
}