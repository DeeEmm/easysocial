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

class SocialMaintenanceScriptRemoveDuplicateFiles extends SocialMaintenanceScript
{
	public static $title = 'Removing the duplicate forget username and password file';
	public static $description = 'Remove the duplicate forget username and password file which are forgetusername.xml and forgetpassword.xml';

	public function main()
	{
		$path = JPATH_ROOT . '/components/com_easysocial/views/account/tmpl';
		$files = glob($path . '/*', GLOB_NOSORT);

		foreach($files as $file) {
			$name = basename($file);

			if ($name == 'forgetusername.xml' || $name == 'forgetpassword.xml') {
				JFile::delete($file);
			}
		}

		return true;
	}
}
