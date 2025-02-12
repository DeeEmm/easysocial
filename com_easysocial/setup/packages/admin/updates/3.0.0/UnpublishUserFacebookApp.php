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

class SocialMaintenanceScriptUnpublishUserFacebookApp extends SocialMaintenanceScript
{
	public static $title = 'Unpublish User Facebook App';
	public static $description = 'Unpublish user Facebook app as Facebook no longer allow to autopost.';

	public function main()
	{
		$app = ES::table('App');
		$exists = $app->loadByElement('facebook' , SOCIAL_TYPE_USER , SOCIAL_APPS_TYPE_APPS);

		if ($exists) {

			// we need to unset the core state of this app so that in case user want to uninstall this app,
			// they still can do it.
			$app->core = 0;
			$app->system = 0;

			// setting default to true to have the similar effect of core app.
			$app->default = 1;

			// lastly, unpublish this app.
			$app->state = 0;

			$state = $app->store();
		}

		return true;
	}
}
