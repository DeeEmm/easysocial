<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptRemoveMaintenanceScriptFile extends SocialMaintenanceScript
{
	public static $title = 'Remove Maintenance Script File';
	public static $description = 'Remove one of the maintenance script which used the same class name with previous script file.';

	public function main()
	{
		$files = array(SOCIAL_ADMIN . '/updates/3.0.4/RemoveUnusedFiles.php');

		foreach ($files as $file) {
			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}

		return true;
	}
}
