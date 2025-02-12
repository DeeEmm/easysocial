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

class EasySocialControllerModules extends EasySocialController
{
	/**
	 * Discover modules list from services repository
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function discover()
	{
		ES::checkToken();

		$model = ES::model('Modules');
		$manifest = $model->discoverManifest($this->config->get('general.key'));

		// Do something if we are unable to retrieve the full manifest list
		if (!$manifest) {
			return $this->ajax->reject($result->message);
		}

		// Decode the result
		if ($manifest->state != 200) {
			$return = base64_encode('index.php?option=com_easysocial&view=modules');

			return $this->ajax->reject($result->error);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Installs a module
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function install()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', [], 'int');

		foreach ($ids as $id) {
			$package = ES::table('Package');
			$package->load((int) $id);

			$state = $package->install();

			if (!$state) {
				$this->view->setMessage($package->getError(), 'error');
				return $this->redirectToView('modules');
			}

			$this->actionlog->log('COM_ES_ACTION_LOG_MODULES_PACKAGE_INSTALL_MODULE', 'modules', [
				'link' => 'index.php?option=com_easysocial&view=modules',
				'moduleName' => $package->title
			]);
		}

		// Rediscover the modules
		$model = ES::model('Modules');
		$manifest = $model->discoverManifest($this->config->get('general.key'));

		$this->view->setMessage('Selected modules is now installed on the site');
		$this->redirectToView('modules');
	}

	/**
	 * Uninstalls a module from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function uninstall()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', [], 'int');

		foreach ($ids as $id) {
			$package = ES::table('Package');
			$package->load((int) $id);

			$package->uninstall();

			$this->actionlog->log('COM_ES_ACTION_LOG_MODULES_PACKAGE_UNINSTALL_MODULE', 'modules', [
				'link' => 'index.php?option=com_easysocial&view=modules',
				'moduleName' => $package->title
			]);
		}

		// Rediscover the modules
		$model = ES::model('Modules');
		$manifest = $model->discoverManifest($this->config->get('general.key'));

		$this->view->setMessage('Selected modules is now uninstalled from the site');
		$this->redirectToView('modules');
	}
}
