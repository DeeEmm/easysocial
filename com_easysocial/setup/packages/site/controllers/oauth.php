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

class EasySocialControllerOAuth extends EasySocialController
{
	/**
	 * Revokes the access for the user that has already authenticated
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function revoke()
	{
		ES::checkToken();

		$client = $this->input->get('client', '', 'string');
		$callback = $this->input->get('callback', '', 'default');

		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Check if the client is valid.
		if (!$client || !in_array($client, $allowedClients)) {
			return $this->view->exception('Invalid client type provided.');
		}

		$table = $this->my->getOAuth($client);

		if (!$table) {
			die();
		}

		$oauth = ES::oauth($client);
		$oauth->setAccess($table->token, $table->secret);

		// Try to revoke now
		$result = $oauth->revoke();

		if (!$result) {
			$this->view->setError('COM_EASYSOCIAL_OAUTH_THERE_WAS_ERROR_REVOKING_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $callback);
		}

		// Once the remote site has de-authorized the access, we need to delete the table.
		$state = $table->delete();

		if (!$state) {
			$this->view->setError($table->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $callback);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_OAUTH_REVOKED_SUCCESSFULLY', ucfirst($client)), SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $callback);
	}

	/**
	 * Removes a permission from the oauth user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removePermissions()
	{
		ES::requireLogin();

		$client = $this->input->get('client', '', 'word');
		$permissions = $this->input->get('permissions', '', 'word');

		$oauth = ES::oauth($client);
		$oauth->removePermissions($permissions);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Once we have the permissions granted, we need to obtain the access tokens
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function grant()
	{
		$client = $this->input->get('client', '', 'word');
		$callback = $this->input->get('callback', '', 'default');
		$callback = urlencode($callback);

		// Check for oauth_callback as well
		if (!$client) {
			return $this->view->exception('COM_EASYSOCIAL_OAUTH_INVALID_CLIENT');
		}

		// Verifier codes used by some oauth clients
		$verifier = $this->input->get('oauth_verifier', '', 'default');

		// Get the consumer
		$consumer = ES::oauth($client);
		$access = $consumer->getAccess($verifier);

		// Get the necessary composite index
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'word');

		if (!$uid || !$type) {
			return $this->view->exception('Invalid composite keys provided');
		}

		$table = ES::table('OAuth');
		$table->load(array('uid' => $uid, 'type' => $type));

		$table->uid = $uid;
		$table->type = $type;
		$table->client = $client;
		$table->secret = $access->secret;
		$table->token = $access->token;
		$table->expires = $access->expires;
		$table->params = $access->params;

		// Try to store the access;
		$state = $table->store();

		$this->view->setMessage('COM_EASYSOCIAL_OAUTH_GRANTED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $callback);
	}


	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isLockDown()
	{
		if ($this->config->get('general.site.lockdown.registration')) {
			return false;
		}

		return true;
	}
}
