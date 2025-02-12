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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationPlugins extends EasySocialSetupController
{
	/**
	 * Installation of plugins on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$this->engine();

		// Get the path to the current installer archive
		$tmpPath = $this->input->get('path', '', 'default');

		// Path to the archive
		$archivePath = $tmpPath . '/plugins.zip';

		// Where should the archive be extrated to
		$path = $tmpPath . '/plugins';

		$state = $this->extractArchive($archivePath, $path);

		if (!$state) {
			return $this->output($this->getResultObj(JText::_('COM_EASYSOCIAL_INSTALLATION_ERROR_EXTRACT_PLUGINS'), false));
		}

		// Get a list of apps we should install.
		$groups = JFolder::folders($path, '.', false, true);

		// Get Joomla's installer instance
		$installer = JInstaller::getInstance();

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($groups as $group) {

			// Now we find the plugin info
			$plugins = JFolder::folders( $group , '.' , false , true );
			$groupName = basename($group);
			$groupName = ucfirst($groupName);

			foreach ($plugins as $pluginPath) {

				$pluginName = basename($pluginPath);
				$pluginName = ucfirst($pluginName);

				// We need to try to load the plugin first to determine if it really exists
				$plugin = JTable::getInstance('extension');
				$options = array('folder' => strtolower($groupName), 'element' => strtolower($pluginName));
				$exists = $plugin->load($options);

				// Allow overwriting existing plugins
				$installer->setOverwrite(true);
				$state = $installer->install($pluginPath);

				if (!$exists) {
					$plugin->load($options);
				}


				// Load the plugin and ensure that it's published
				if ($state) {

					// If the plugin was previously disabled, do not turn this on.
					if (($exists && $plugin->enabled) || !$exists) {
						$plugin->enabled = true;
					}

					$plugin->store();
				}

				$message = $state ? JText::sprintf('COM_EASYSOCIAL_INSTALLATION_SUCCESS_PLUGIN', $groupName, $pluginName) : JText::sprintf('COM_EASYSOCIAL_INSTALLATION_ERROR_PLUGIN', $groupName, $pluginName);
				$class = $state ? 'success' : 'error';

				$result->message .= '<div class="text-' . $class . '">' . $message . '</div>';
			}
		}

		return $this->output($result);
	}
}
