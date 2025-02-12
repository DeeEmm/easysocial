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

class EasySocialControllerStore extends EasySocialController
{
	/**
	 * Find applications from stackideas.com
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function refresh()
	{
		ES::checkToken();

		$layout = $this->input->get('layout', '', 'string');

		$store = ES::store();
		$result = $store->refresh();

		if (!$result) {
			$this->view->setMessage($store->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_APPS_GENERATED_SUCCESS');
		return $this->view->call(__FUNCTION__, $layout);
	}

	/**
	 * Allows caller to purchase app
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function purchase()
	{
		ES::checkToken();

		// Get the app
		$id = $this->input->get('id', 0, 'int');
		$app = ES::store()->getApp($id);

		if (!$id || !$app->id) {
			return $this->view->exception('Invalid application id provided');
		}

		$store = ES::store();
		$paymentUrl = $store->purchase($app);

		ES::redirect($paymentUrl);
	}

	/**
	 * When user cancels the payment
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function success()
	{
		// Try to install the app now
		$appId = $this->input->get('app_id', 0, 'int');

		$table = ES::table('Store');
		$table->load(array('app_id' => $appId));

		return $this->install($table->id);
	}

	/**
	 * When user cancels the payment
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function fail()
	{
		$id = $this->input->get('app_id', '', 'int');

		$table = ES::table('Store');
		$table->load(array('app_id' => $id));

		$app = ES::store()->getApp($table);

		$this->view->call(__FUNCTION__, $app);
	}

	/**
	 * Downloads app from the directory
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function install($id = 0)
	{
		// Get the app that needs to be installed
		$id = $this->input->get('id', $id, 'int');
		$app = ES::store()->getApp($id);

		if (!$id || !$app->id) {
			return $this->view->exception('Invalid application id provided');
		}

		$store = ES::store();

		try {
			$path = $store->download($app);
		} catch (Exception $e) {
			$this->view->setMessage($e->getMessage(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $application);
		}

		if ($path === false) {
			return $this->view->exception('This application cannot be downloaded from the Application Store.');
		}

		// Once we have the path, try to install the app now.
		$application = $store->install($path);

		if ($application === false) {
			$this->view->setMessage($store->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $application);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_APPS_INSTALLED_SUCCESS', $application->_('title')));
		return $this->view->call(__FUNCTION__, $application);
	}
}
