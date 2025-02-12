<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateFileAppInstallableState extends SocialMaintenanceScript
{
	public static $title = 'Update User File Apps to be installable by user';
	public static $description = 'By allowing user to install file app they will be able to manage all of their uploaded files on the site.';

	public function main()
	{
		$app = ES::table('App');
		$exists = $app->loadByElement('files' , SOCIAL_TYPE_USER , SOCIAL_APPS_TYPE_APPS);

		if ($exists) {
			$app->installable = 1;
			$app->store();
		}

		return true;
	}
}
