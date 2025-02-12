<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialConverseKit extends EasySocial
{
	public static function factory()
	{
		$obj = new self();

		return $obj;
	}

	/**
	 * Determines if Conversekit is enabled
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function exists($currentView = null)
	{
		$exists = $this->hasActivateLinkableCK();

		if ($exists) {

			if ($currentView && $currentView != 'conversations') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if Conversekit is enabled
	 *
	 * @since	3.2.2
	 * @access	public
	 */
	public function hasActivateLinkableCK()
	{
		static $state = null;

		if (is_null($state)) {
			$exists = JPluginHelper::isEnabled('system', 'conversekit');

			$hasActivateLinkableCK = $this->config->get('conversations.conversekit.links');

			if ($exists && $hasActivateLinkableCK) {
				$state = true;

				if (!$this->isMobile()) {
					return $state;
				}

				// On mobile device, ensure that ConverseKit is also enabled on mobile
				$ck = JPluginHelper::getPlugin('system', 'conversekit');

				$params = new JRegistry($ck->params);
				$renderOnMobile = $params->get('render_mobile', true);

				if (!$renderOnMobile) {
					$state = false;
				}
			}
		}

		return $state;
	}
}
