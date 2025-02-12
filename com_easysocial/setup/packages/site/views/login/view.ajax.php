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

ES::import('site:/views/views');

class EasySocialViewLogin extends EasySocialSiteView
{
	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isLockDown()
	{
		return false;
	}

	/**
	 * Responsible to display the generic login form via ajax
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form($tpl = null)
	{
		// look like when we are testing againt Juser guest status, its best not to use the copy that are cached.
		// always get the new copy to test. #1915
		$my = ES::user();

		// If user is already logged in, they should not see this page.
		if (!$my->guest) {
			$this->setMessage('COM_EASYSOCIAL_LOGIN_ERROR_YOU_ARE_ALREADY_LOGGED_IN', SOCIAL_MSG_ERROR);
			return $this->ajax->reject($this->getMessage());
		}

		// Get any callback urls.
		$return = ES::getCallback();

		// If return value is empty, always redirect back to the dashboard
		if (!$return) {
			$return	= ESR::dashboard(array(), false);
		}

		// Determine if there's a login redirection
		$loginMenu = $this->config->get('general.site.login');

		if ($loginMenu != 'null' && !empty($loginMenu)) {
			$return = ESR::getMenuLink($loginMenu);
		}

		$return = base64_encode($return);

		// Dynamically determine the max height of the dialog. #1874
		$height = 350;
		$showRegistrations = true;
		$fields = false;

		// If registration is enabled, display the quick registration form
		if (!$this->config->get('registrations.enabled') || ($this->config->get('general.site.lockdown.enabled') && !$this->config->get('general.site.lockdown.registration'))) {
			$showRegistrations = false;
		}

		if ($showRegistrations && $this->config->get('registrations.mini.enabled')) {
			$model = ES::model('Fields');
			$profileId = $this->config->get('registrations.mini.profile', 'default');

			if ($profileId === 'default') {
				$profileId = ES::model('Profiles')->getDefaultProfile()->id;
			}

			// Get a list of custom fields for quick registration
			$fields = $model->getQuickRegistrationFields($profileId);

			// Set max height higher
			if (!empty($fields)) {
				$height = 550;
				$fields = true;
			}
		}

		// Check for social login button
		if (!$fields || empty($fields)) {
			$sso = ES::sso();

			if ($sso->hasSocialButtons()) {

				$social = array('facebook', 'twitter', 'linkedin');
				$i = 0;

				foreach ($social as $name) {
					if ($sso->isEnabled($name)) {
						$height = $height + 70;

						if ($i === 0) {
							$height = $height + 30;
						} else {
							$height = $height - 30;
						}

						$i++;
					}
				}
			}
		}

		$this->set('return', $return);
		$this->set('height', $height);
		$contents = parent::display('site/login/dialogs/login');

		return $this->ajax->resolve($contents);
	}
}
