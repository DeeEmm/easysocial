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

class SocialMaintenanceScriptRemoveUserBirthdayApp extends SocialMaintenanceScript
{
	public static $title = 'Remove User Birthday App';
	public static $description = 'Uninstall and remove user upcoming birthday app as this app no longer being used.';

	public function main()
	{
		$app = ES::table('App');
		$exists = $app->loadByElement('birthday' , SOCIAL_TYPE_USER , SOCIAL_APPS_TYPE_APPS);

		if ($exists) {
			$state = $app->uninstall();

			if ($state) {
				$model = ES::model('Apps');
				$model->removeUserApp($app->id);
			}
		}

		return true;
	}
}
