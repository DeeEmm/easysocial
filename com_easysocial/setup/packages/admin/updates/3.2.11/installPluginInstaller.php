<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptInstallPluginInstaller extends SocialMaintenanceScript
{
	public static $title = 'Install EasySocial Installer plugin';
	public static $description = 'To ensure system install the EasySocial installer plugin.';

	public function main()
	{

		$pluginName = 'easysocial';
		$groupName = 'installer';
		$pluginPath = JPATH_ROOT . '/plugins/installer/easysocial';

		$folderExists = JFolder::exists($pluginPath);
		$xmlExists = JFile::exists($pluginPath . '/' . $pluginName . '.xml');

		// We need to try to load the plugin first to determine if it really exists
		$plugin = JTable::getInstance('extension');
		$options = array('folder' => strtolower($groupName), 'element' => strtolower($pluginName));
		$plgExists = $plugin->load($options);

		if ($folderExists && $xmlExists) {

			if (!$plgExists) {

				$now = ES::date()->toMySQL();

				// copy folder to Joomla tmp folder
				$tmpRootPath = JPATH_ROOT . '/tmp/' . md5($now);
				$tmpPath = $tmpRootPath . '/' . $pluginName;
				JFolder::copy($pluginPath, $tmpPath);
				
				// Get Joomla's installer instance
				$installer = new JInstaller();

				// Allow overwriting existing plugins
				$installer->setOverwrite(true);
				$state = $installer->install($tmpPath);

				// delete tmp folder
				JFolder::delete($tmpRootPath);

				$plugin->load($options);
			}

			// Load the plugin and ensure that it's published
			$plugin->enabled = true;
			$plugin->store();
		}

		return true;
	}
}
