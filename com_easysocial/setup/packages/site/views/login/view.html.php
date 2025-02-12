<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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
		if ($this->config->get('site.general.lockdown.registration')) {
			return true;
		}

		return false;
	}

	/**
	 * Responsible to display the generic login form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// If user is already logged in, they should not see this page.
		if (!$this->my->guest) {
			$url = ESR::dashboard(array(), false);

			return $this->redirect($url);
		}

		ES::setMeta();

		// Add page title
		$this->page->title(JText::_('COM_EASYSOCIAL_LOGIN_PAGE_TITLE'));

		// Add breadcrumb
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_LOGIN_PAGE_BREADCRUMB'));

		// Facebook codes.
		$facebook = ES::oauth('Facebook');

		$loginMenu = $this->config->get('general.site.login');

		$menu = $this->app->getMenu();
		$activeMenu = $menu->getActive();

		// Retrieve the current menu login redirection URL
		if (is_object($activeMenu) && stristr($activeMenu->link, 'view=login') !== false) {

			if (isset($activeMenu->query) && isset($activeMenu->query['loginredirection'])) {
				$loginMenu = $activeMenu->query['loginredirection'];
			}
		}

		$b64encoded = false;

		// Get any callback urls.
		$return = ES::getCallback();

		// Determine if there's a login redirection if the `return` is empty
		if ($loginMenu != 'null' && !empty($loginMenu)) {
			$return = ESR::getMenuLink($loginMenu);
		}

		if (!$return) {
			// Determine if there's a login redirection
			$return = $this->input->getVar('return', '');

			// There is a case where url passed is already base64 encode
			if ($return && base64_encode(base64_decode($return, true)) === $return){
				$b64encoded = true;
			}
		}

		// If return value is empty, always redirect back to the dashboard
		if (!$return) {
			$return	= ESR::dashboard(array(), false);
		}

		if (!$b64encoded) {
			$return = base64_encode($return);
		}

		$this->set('return', $return);
		$this->set('facebook', $facebook);

		return parent::display('site/login/default/default');
	}

	/**
	 * Responsible to log the user out from the system.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function logout()
	{
		// If the user is not logged in on the first place
		if ($this->my->guest) {
			$redirect = ESR::login(array(), false);
			return $this->redirect($redirect);
		}

		ES::setMeta();

		$redirect = $this->config->get('general.site.logout');

		$menu = $this->app->getMenu();
		$activeMenu = $menu->getActive();

		// Retrieve the current menu logout redirection URL
		if (is_object($activeMenu) && stristr($activeMenu->link, 'view=login&layout=logout') !== false) {

			if (isset($activeMenu->query) && isset($activeMenu->query['logoutredirection'])) {
				$redirect = $activeMenu->query['logoutredirection'];

				$menuItem = $menu->getItem($redirect);
				if ($menuItem && $menuItem->type === 'url') {
					// Perform the log out.
					$this->app->logout();

					// now redirect user to the custom url link.
					return $this->app->redirect($menuItem->link);
				}
			}
		}

		// Stay on same page
		if (!$redirect || $redirect == null || $redirect == 'null') {
			// Get the previous url before user accessing this view
			$redirect = ESR::referer();
		} else {
			$redirect = ESR::getMenuLink($redirect);
		}

		// Perform the log out.
		$this->app->logout();

		// Route the non sef urls
		$redirect = ESR::_($redirect);

		return $this->redirect($redirect);
	}
}