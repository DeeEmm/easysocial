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

class SocialTwitch extends EasySocial
{
	public function isEnabled()
	{
		$clientKey = $this->config->get('video.twitch.clientId');
		$clientSecret = $this->config->get('video.twitch.clientSecret');

		if (!$clientKey || !$clientSecret) {
			return false;
		}

		return true;
	}

	/**
	 * Revokes the twitch application access token
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function revoke($token)
	{
		if (!$this->isEnabled()) {
			// In case the user remove the key first before revoke
			return 'forceRevokeAccess';
		}

		$clientKey = $this->config->get('video.twitch.clientId');
		$endpoint = "https://id.twitch.tv/oauth2/revoke";

		$connector = ES::connector($endpoint);
		$result = $connector
						->setMethod('POST')
						->addQuery('client_id', $clientKey)
						->addQuery('token', $token)
						->execute()
						->getResult();

		$response = json_decode($result);

		if ($connector->hasException()) {

			if (is_string($result)) {

				if (strpos($result, 'Invalid token') !== false) {
					return 'forceRevokeAccess';
				}

				return $result;
			}

			return $response;
		}

		// return empty mean revoke successfully.
		return true;
	}

	/**
	 * Retrieves the access token from Facebook
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function normalizeAccessToken($response = '')
	{
		if (!$response) {
			return false;
		}

		$obj = new stdClass();

		$obj->token = $response->access_token;
		$obj->secret = true;
		$obj->params = '';
		$obj->expires = ES::date();

		// If the expiry date is given
		if (isset($response->expires_in)) {
			$expires = $response->expires_in;

			// Set the expiry date with proper date data
			$obj->expires = ES::date(strtotime('now') + $expires)->toSql();
		}

		return $obj;
	}

	/**
	 * Request Twitch application access token
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function requestAccessToken()
	{
		if (!$this->isEnabled()) {
			return false;
		}

		$clientKey = $this->config->get('video.twitch.clientId');
		$clientSecret = $this->config->get('video.twitch.clientSecret');

		$endpoint = "https://id.twitch.tv/oauth2/token";

		$connector = ES::connector($endpoint);
		$contents = $connector
						->setMethod('POST')
						->addQuery('client_id', $clientKey)
						->addQuery('client_secret', $clientSecret)
						->addQuery('grant_type', 'client_credentials')
						->execute()
						->getResult();

		return $contents;
	}

	/**
	 * Renders a revoke button
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function getRevokeButton($callback)
	{
		$theme = ES::themes();
		$theme->set('callback', $callback);
		$output = $theme->output('site/twitch/revoke');

		return $output;
	}

	/**
	 * Renders a login button.
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function getLoginButton()
	{
		$text = 'COM_ES_OAUTH_SIGN_IN_WITH_TWITCH';
		$authorizeURL = rtrim(JURI::root(), '/') . '/administrator/index.php?option=com_easysocial&controller=oauth&task=requestTwitchAppAccessToken';

		$theme = ES::themes();
		$theme->set('text', $text);
		$theme->set('authorizeURL', $authorizeURL);

		$output = $theme->output('site/twitch/button');

		return $output;
	}
}
