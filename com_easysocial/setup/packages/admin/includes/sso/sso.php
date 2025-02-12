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

class SocialSSO extends EasySocial
{
	private $clients = array();
	private $allClients = array('facebook', 'twitter', 'linkedin', 'google', 'apple');

	public function getClient($client)
	{
		if (!isset($this->clients[$client])) {
			$consumer = ES::oauth($client);

			$this->clients[$client] = $consumer;
		}

		return $this->clients[$client];
	}

	/**
	 * Retrieves the auto login scripts
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getAutologinScripts()
	{
		$client = $this->getClient('facebook');

		return $client->getAutologinScripts();
	}

	/**
	 * Determines if auto login is possible
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function hasAutologin()
	{
		// If registrations is disabled, we shouldn't show anything
		if (!$this->config->get('registrations.enabled')) {
			return false;
		}

		// If the easysocial authentication plugin is disabled, they won't be able to sign in either.
		$pluginEnabled = JPluginHelper::isEnabled('authentication', 'easysocial');

		if (!$pluginEnabled) {
			return false;
		}

		if ($this->config->get('oauth.facebook.autologin') && $this->config->get('oauth.facebook.app') && $this->config->get('oauth.facebook.secret') && !$this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if a specific oauth client is enabled
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isEnabled($client)
	{
		// If registrations is disabled, we shouldn't show anything
		if (!$this->config->get('registrations.enabled')) {
			return false;
		}

		// If the easysocial authentication plugin is disabled, they won't be able to sign in either.
		$pluginEnabled = JPluginHelper::isEnabled('authentication', 'easysocial');

		if (!$pluginEnabled) {
			return false;
		}

		$key = 'oauth.' . strtolower($client) . '.registration.enabled';

		// If jfbconnect button is used, do not display twitter login button
		if ($client == 'twitter') {
			$jfbconnect = ES::jfbconnect();

			if ($jfbconnect->isEnabled()) {
				return false;
			}
		}

		if (!$this->config->get($key)) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the login button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLoginButton($client, $size = 'default', $text = '', $returnUrl = false, $isSharer = false)
	{
		$consumer = $this->getClient($client);

		$callbackOptions = array();
		$callbackOptions['layout'] = 'oauthDialog';
		$callbackOptions['client'] = $client;
		$callbackOptions['external'] = true;

		if ($returnUrl) {
			$callbackOptions['return'] = $returnUrl;
		} else {
			// Get default return url
			$return = ESR::getMenuLink($this->config->get('general.site.login'));
			$return = ES::getCallback($return);

			$menu = $this->app->getMenu();
			$activeMenu = $menu->getActive();

			// Retrieve the current menu login redirection URL
			if (is_object($activeMenu) && stristr($activeMenu->link, 'view=login') !== false) {
				if (isset($activeMenu->query) && isset($activeMenu->query['loginredirection'])) {
					$return = ESR::getMenuLink($activeMenu->query['loginredirection']);
				}
			}

			// If return value is empty, always redirect back to the dashboard
			if (!$return) {
				$return = ESR::dashboard(array(), false);
			}

			// Ensure that the return url is always encoded correctly.
			$return = base64_encode($return);
			$callbackOptions['return'] = $return;
		}

		if ($size == 'default') {
			$size = 'btn-sm btn-block';
		}

		$callback = ESR::registration($callbackOptions, false);

		$output = $consumer->getLoginButton($callback, array(), 'popup', $text, $size, $isSharer);

		return $output;
	}

	/**
	 * Determine if there are any social button enable on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasSocialButtons()
	{
		$items = $this->allClients;
		$enabled = false;

		foreach ($items as $item) {
			if ($this->isEnabled($item)) {
				$enabled = true;
				break;
			}
		}

		return $enabled;
	}

	/**
	 * Get all available social button on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getSocialButtons($size = 'default')
	{
		$items = $this->allClients;
		$buttons = array();

		foreach ($items as $item) {
			if ($this->isEnabled($item)) {
				$buttons[$item] = $this->getLoginButton($item, $size);
			}
		}

		return $buttons;
	}
}
