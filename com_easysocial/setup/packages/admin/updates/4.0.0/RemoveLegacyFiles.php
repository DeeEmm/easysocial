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

class SocialMaintenanceScriptRemoveLegacyFiles extends SocialMaintenanceScript
{
	public static $title = 'Remove Legacy Files';
	public static $description = 'Remove legacy files from 3.x as they are no longer needed in 4.x';

	public function main()
	{
		$files = [
			JPATH_ROOT . '/components/com_easysocial/themes/wireframe/profile/submitverification/default.php',
			JPATH_ROOT . '/components/com_easysocial/views/profile/tmpl/submitVerification.xml',
			JPATH_ADMINISTRATOR . '/components/com_easysocial/themes/default/settings/form/pages/layout/styling.php'
		];

		foreach ($files as $file) {
			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}

		return true;
	}
}
