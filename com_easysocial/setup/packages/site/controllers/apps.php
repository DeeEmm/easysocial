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

class EasySocialControllerApps extends EasySocialController
{
	/**
	 * Allows user to save settings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveSettings()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', '', 'int');
		$app = ES::app($id);

		if (!$id || !$app->id) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call( __FUNCTION__ );
		}

		// Ensure that the user can really access this app settings.
		if (!$app->isInstalled()) {
			$message = $this->view->setMessage('COM_EASYSOCIAL_APPS_SETTINGS_NOT_INSTALLED', ES_ERROR);
			return $this->ajax->reject($message);
		}

		// The data is in json format
		$data = $this->input->get('data', '', 'raw');

		$map = ES::table('AppsMap');
		$map->load(array('uid' => $this->my->id, 'app_id' => $app->id));
		$map->params = $data;
		$map->store();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows apps to process a controller
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function controller()
	{
		ES::checkToken();

		// Get the app
		$id = $this->input->get('appId', 0, 'int');

		$app = ES::app($id);

		// Allow app to specify their own controller and task
		$controller = $this->input->get('appController', '', 'cmd');
		$task = $this->input->get('appTask', '', 'cmd');

		// Process the app's controller
		$lib = ES::apps();
		$lib->renderController($controller, $task, $app);
	}

	/**
	 * Renders the terms and conditions for apps
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTnc()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$app = ES::app($id);

		$config = $app->getManifest();

		$tnc = JText::_('COM_EASYSOCIAL_APPS_TNC');

		if (is_object($config) && property_exists($config, 'tnc')) {
			$tnc = JText::_($config->tnc);
		}

		$this->view->call( __FUNCTION__, $tnc);
	}

	/**
	 * Allows caller to install applications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function installApp()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$app = ES::app($id);

		if (!$app) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_APP_ID_INVALID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if ($app->isInstalled()) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_APP_ID_INVALID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try to install the app now.
		$result = $app->install($this->my->id);

		if (!$result) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_INSTALL_ERROR_OCCURED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to uninstall an application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function uninstall()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get app id.
		$id = $this->input->get('id', 0, 'int');

		// Check if app is a valid app
		$app = ES::app($id);

		if (!$app || !$id) {
			return $this->view->exception('COM_EASYSOCIAL_APPS_UNINSTALL_ERROR_OCCURED');
		}

		// Try to uninstall the app.
		$result = $app->uninstallUserApp();

		if (!$result) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_APPS_UNINSTALL_ERROR_OCCURED'), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__);
	}
}
