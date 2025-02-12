<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewOauth extends EasySocialSiteView
{
	/**
	 * Post processing once a user's access has been revoked
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function revoke()
	{
		$this->info->set($this->getMessage());

		$url = ESR::profile(array('layout' => 'edit'), false);

		return $this->redirect($url);
	}

	/**
	 * This is the first entry point when the social site redirects back to this callback.
	 * It is responsible to close the popup and redirect to the appropriate url.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removePermissions()
	{
		$config = ES::config();

		// Get allowed clients
		$allowedClients	= array_keys((array) $config->get('oauth'));

		// Get the current client.
		$oauthClient = $this->input->get('client', '', 'word');

		if (!in_array($oauthClient, $allowedClients)) {
			
			ES::info()->set(false, JText::sprintf('COM_EASYSOCIAL_OAUTH_INVALID_OAUTH_CLIENT_PROVIDED', $oauthClient), SOCIAL_MSG_ERROR);
			$this->redirect('index.php?option=com_easysocial&view=login');
			$this->close();

			return;
		}

		parent::display('site/oauth/remove.permissions');
	}

	/**
	 * This is the first entry point when the social site redirects back to this callback.
	 * It is responsible to close the popup and redirect to the appropriate url.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function requestPermissions()
	{
		$config = ES::config();

		// Get allowed clients
		$allowedClients	= array_keys((array) $config->get('oauth'));

		// Get the current client.
		$oauthClient = $this->input->get('client', '', 'word');

		if (!in_array($oauthClient, $allowedClients)) {

			ES::info()->set(false, JText::sprintf('COM_EASYSOCIAL_OAUTH_INVALID_OAUTH_CLIENT_PROVIDED', $oauthClient), SOCIAL_MSG_ERROR);
			$this->redirect('index.php?option=com_easysocial&view=login' );
			$this->close();

			return;
		}

		$consumer = ES::OAuth(SOCIAL_TYPE_FACEBOOK);
		$permissions = $this->input->get('permissions', '', 'default');

		// Add permissions for this client
		$consumer->addPermission($permissions);

		// Get the return url
		$return = $this->input->get('return_to', '', 'base64');
		$return = base64_decode($return);

		$this->set('redirect', $return);

		parent::display('site/registration/oauth.popup');
	}
}
