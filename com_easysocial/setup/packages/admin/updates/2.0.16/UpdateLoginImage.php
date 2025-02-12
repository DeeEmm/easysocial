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

class SocialMaintenanceScriptUpdateLoginImage extends SocialMaintenanceScript
{
	public static $title = 'Update login image to use images folder instead of template override';
	public static $description = 'Update login image to use images folder instead of template override to ensure that the image is showing correctly in mobile template';

	public function main()
	{
		$assets = ES::assets();
		$template = $assets->getJoomlaTemplate();
		$fileName = 'login_background.png';

		$overridePath = JPATH_ROOT . '/templates/' . $template . '/html/com_easysocial/images/' . $fileName;
		$exists = JFile::exists($overridePath);

		// Move the image to new destionation
		if ($exists) {

			$newPath = JPATH_ROOT . '/images/easysocial_login';

			// Try to create the folder
			if (!JFolder::create($newPath)) {
				return;
			}

			$newPath = $newPath . '/' . $fileName;
			$state = JFile::move($overridePath, $newPath);
		}

		return true;
	}
}