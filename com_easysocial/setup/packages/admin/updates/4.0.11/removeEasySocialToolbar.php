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

class SocialMaintenanceScriptRemoveEasySocialToolbar extends SocialMaintenanceScript
{
	public static $title = "Removing EasySocial Toolbar";
	public static $description = 'Removing EasySocial Toolbar in favour of Stackideas Toolbar';

	public function main()
	{
		$db = ES::db();
		$query = 'DELETE FROM `#__social_packages` WHERE `element` = ' . $db->Quote('mod_easysocial_toolbar');

		$db->setQuery($query);
		$db->execute();

		$folders = [
			JPATH_ROOT . '/administrator/components/com_easysocial/includes/toolbar',
			JPATH_ROOT . '/components/com_easysocial/themes/wireframe/styles/toolbar',
			JPATH_ROOT . '/components/com_easysocial/themes/wireframe/toolbar',
			JPATH_ROOT . '/components/com_easysocial/themes/breeze/toolbar',
			JPATH_ROOT . '/components/com_easysocial/themes/elegant/toolbar',
			JPATH_ROOT . '/components/com_easysocial/themes/frosty/toolbar',
			JPATH_ROOT . '/components/com_easysocial/themes/vortex/toolbar',
			JPATH_ROOT . '/modules/mod_easysocial_toolbar'
		];

		$files = [
			JPATH_ROOT . '/administrator/components/com_easysocial/themes/default/settings/form/pages/layout/toolbars.php',
			JPATH_ROOT . '/components/com_easysocial/themes/wireframe/styles/toolbar.less'
		];

		foreach ($folders as $folder) {
			if (JFolder::exists($folder)) {
				JFolder::delete($folder);
			}
		}

		foreach ($files as $file) {
			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}

		return true;
	}
}