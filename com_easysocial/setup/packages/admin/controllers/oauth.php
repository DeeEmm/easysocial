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
	 * Retrieve the Twitch app access token
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function requestTwitchAppAccessToken()
	{
		$twitchLib = ES::twitch();

		$isEnabled = $twitchLib->isEnabled();

		if (!$isEnabled) {
			$this->info->set(null, JText::_('COM_ES_OAUTH_AUTHENTICATION_ERROR'), SOCIAL_MSG_ERROR);

			echo '<script>window.opener.doneLogin();window.close();</script>';
			exit;
		}

		// Retrieve the access token from the Twitch
		$contents = $twitchLib->requestAccessToken();

		$response = json_decode($contents);

		// return the error message if there's an error
		if (!$response && $contents) {

			$this->info->set(null, $contents, SOCIAL_MSG_ERROR);

			echo '<script>window.opener.doneLogin();window.close();</script>';
			exit;
		}

		$user = ES::user();
		$table = ES::table('OAuth');
		$table->load(array('uid' => $user->id, 'type' => SOCIAL_TYPE_USER, 'client' => 'twitch'));

		$access = $twitchLib->normalizeAccessToken($response);

		$table->oauth_id = $user->id;
		$table->uid = $user->id;
		$table->type = SOCIAL_TYPE_USER;
		$table->client = 'twitch';
		$table->secret = $access->secret;
		$table->token = $access->token;
		$table->expires = $access->expires;
		$table->params = $access->params;

		// Try to store the access;
		$state = $table->store();

		$message = JText::_('COM_ES_OAUTH_GRANTED_SUCCESSFULLY');
		$this->info->set(null, $message, SOCIAL_MSG_SUCCESS);

		echo '<script>window.opener.doneLogin();window.close();</script>';
		exit;
	}

	/**
	 * Revokes the access for the user that has already authenticated
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function revoke()
	{
		ES::checkToken();

		$client = $this->input->get('client', '', 'string');
		$callback = $this->input->get('callback', '', 'default');
		$redirect = 'index.php?option=com_easysocial&view=settings&layout=form&page=videos&tab=integrations';

		// for now only allow Twitch client type proceed from backend
		if ($client != 'twitch') {
			return $this->view->exception('Invalid client type provided.');
		}

		$user = ES::user();
		$oauth = ES::table('OAuth');
		$exist = $oauth->load(array('client' => $client, 'uid' => $user->id, 'type' => SOCIAL_TYPE_USER));

		if (!$exist) {

			// check again which account was authenticated the app token last time
			$hasRecord = $oauth->load(array('client' => $client, 'type' => SOCIAL_TYPE_USER));

			if ($hasRecord && $oauth->uid) {
				$user = ES::user($oauth->uid);

				// if the authenticated user account still exist on the site, throw the error to tell them need to login from this account to revoke
				if ($user->id) {
					$msg = JText::sprintf('COM_ES_OAUTH_REVOKED_AUTHENTICATION_ERROR', $user->name);
					$this->info->set(null, $msg, SOCIAL_MSG_ERROR);
					return ES::redirect($redirect);
				}

				// if the authenticated user no longer exist on the site, just revoke the access directly.
				$state = $oauth->delete();

				if (!$state) {
					$this->info->set(null, $oauth->getError(), SOCIAL_MSG_ERROR);
					return ES::redirect($redirect);
				}

				$this->info->set(null, JText::_('COM_ES_OAUTH_REVOKED_SUCCESSFULLY'), SOCIAL_MSG_SUCCESS);
				return ES::redirect($redirect);
			}

			$this->info->set(null, 'No longer exist.', SOCIAL_MSG_ERROR);
			return ES::redirect($redirect);
		}

		$twitchLib = ES::twitch();
		$revokeState = $twitchLib->revoke($oauth->token);

		// Ensure the remote site has de-authorized the access, we need to delete the data from the oauth table
		// the 'forceRevokeAccess' mean some how the access token invalid so we force to delete the access token from oauth table
		if ($revokeState === true || $revokeState == 'forceRevokeAccess') {

			$state = $oauth->delete();

			if (!$state) {
				$this->info->set(null, $oauth->getError(), SOCIAL_MSG_ERROR);
				return ES::redirect($redirect);
			}

			$this->info->set(null, JText::_('COM_ES_OAUTH_REVOKED_SUCCESSFULLY'), SOCIAL_MSG_SUCCESS);
			return ES::redirect($redirect);
		}

		if (!is_null($revokeState) && is_string($revokeState)) {
			$this->info->set(null, $revokeState, SOCIAL_MSG_ERROR);
			return ES::redirect($redirect);
		}

		$this->info->set(null, JText::_('COM_ES_OAUTH_THERE_WAS_ERROR_REVOKING_ACCESS'), SOCIAL_MSG_ERROR);
		return ES::redirect($redirect);
	}
}
