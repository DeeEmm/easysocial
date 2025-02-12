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

class EasySocialControllerInstallationApps extends EasySocialSetupController
{
	/**
	 * Responsible to install apps
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		// Get the group of apps to install.
		$group = $this->input->get('group', '', 'cmd');

		// Get the temporary path to the archive
		$tmpPath = $this->input->get('path', '', 'default');

		// Get the archive path
		$archivePath = $tmpPath . '/' . $group . 'apps.zip';

		// Where the extracted items should reside.
		$path = $tmpPath . '/' . $group . 'apps';

		// Detect if the target folder exists
		$target = JPATH_ROOT . '/media/com_easysocial/apps/' . $group;

		// Try to extract the archive first
		$state = $this->extractArchive($archivePath, $path);

		if (!$state) {
			$result = new stdClass();
			$result->state = false;
			$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_ERROR_EXTRACT_APPS', $group);

			return $this->output($result);
		}

		// If the apps folder does not exist, create it first.
		$exists = JFolder::exists($target);

		if (!$exists) {
			$state = JFolder::create($target);

			if (!$state) {
				$result = new stdClass();
				$result->state = false;
				$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_ERROR_CREATE_APPS_FOLDER', $target);

				return $this->output($result);
			}
		}

		// Get a list of apps within this folder.
		$apps = JFolder::folders($path, '.', false, true);
		$totalApps 	= 0;

		// If there are no apps to install, just silently continue
		if (!$apps) {
			$result = new stdClass();
			$result->state = true;
			$result->message = JText::_('COM_EASYSOCIAL_INSTALLATION_APPS_NO_APPS');

			return $this->output($result);
		}

		$results = array();

		// Go through the list of apps on the site and try to install them.
		foreach ($apps as $app) {
			$results[] = $this->installApp($app, $target, $group);
			$totalApps += 1;
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class = $obj->state ? 'success' : 'error';
			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}

	/**
	 * Installs Single Application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function installApp($appArchivePath, $target, $group = 'user')
	{
		// Get the element of the app
		$element = basename($appArchivePath);
		$element = str_ireplace('.zip', '' , $element);

		// Get the installation source folder.
		$path = dirname($appArchivePath) . '/' . $element;

		// Include core library
		$this->engine();

		// Get installer library
		$installer = ES::get('Installer');

		// Try to load the installation from path.
		$state = $installer->load($path);

		// Try to load and see if the previous app already has a record
		$oldApp = ES::table('App');
		$appExists = $oldApp->load(array('type' => SOCIAL_TYPE_APPS, 'element' => $element, 'group' => $group));

		// If there's an error with this app, we should silently continue
		if (!$state) {
			$result = new stdClass();
			$result->state = false;
			$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_ERROR_LOADING_APP', $element);

			return $result;
		}

		// Let's try to install the app.
		$app = $installer->install();

		// If there's an error with this app, we should silently continue
		if ($app === false) {
			$result = new stdClass();
			$result->state = false;
			$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_ERROR_INSTALLING_APP', $element);

			return $result;
		}

		// If application already exist, use the previous title.
		if ($appExists) {
			$app->title = $oldApp->title;
			$app->alias = $oldApp->alias;
		}

		$app->state = $appExists ? $oldApp->state : SOCIAL_STATE_PUBLISHED;
		$app->store();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_APPS_INSTALLED_APP_SUCCESS', $element);

		return $result;
	}

}
