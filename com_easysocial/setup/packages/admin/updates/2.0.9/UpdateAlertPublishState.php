<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateAlertPublishState extends SocialMaintenanceScript
{
	public static $title = 'Update email and system notification publish state for notification alert rule';
	public static $description = 'Update email and system notification publish state for notification alert rule';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();

		$emailQuery = "UPDATE `#__social_alert` SET `email_published` = '1' WHERE `email_published` IS NULL";

		$sql->raw($emailQuery);
		$db->setQuery($sql);
		$db->query();

		$systemQuery = "UPDATE `#__social_alert` SET `system_published` = '1' WHERE `system_published` IS NULL";

		$sql->raw($systemQuery);
		$db->setQuery($sql);
		$db->query();

		return true;
	}
}