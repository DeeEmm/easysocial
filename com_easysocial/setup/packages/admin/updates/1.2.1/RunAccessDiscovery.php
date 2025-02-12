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

class SocialMaintenanceScriptRunAccessDiscovery extends SocialMaintenanceScript
{
	public static $title = 'Runs access rules discovery';

	public static $description = 'Initiaite the access rules for version 1.2 to add all the rules into the database';

	public function main()
	{
		// Only need to discover access rules in admin because prior to 1.2, there are no access rules in other location

		$model = ES::model('accessrules');

		$files = $model->scan('admin');

		foreach ($files as $file)
		{
			$model->install($file);
		}

		return true;
	}
}
