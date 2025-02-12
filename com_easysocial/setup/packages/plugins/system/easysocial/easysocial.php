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

jimport('joomla.filesystem.file');

$app = JFactory::getApplication();

// Do not proceed if this is on CLI environment #5308
if ($app->isClient('cli')) {
	return;
}

$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/plugins.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class PlgSystemEasySocial extends EasySocialPlugins
{
	/**
	 * Triggered upon Joomla application initialization
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onAfterInitialise()
	{
		// We only process on the front end.
		if (ES::isFromAdmin()) {
			return;
		}

		return true;
	}

	/**
	 * Executes before the router
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		// We only process on the front end.
		if (ES::isFromAdmin()) {
			return;
		}

		// If the site is offline, just use Joomla login.
		if ($this->jConfig->getValue('offline')) {
			return;
		}

		$doc = JFactory::getDocument();

		// Set the current url for non registration pages so that we can respect the profile type settings registration_success
		// and later redirect the user after their successful signup.
		$view = $this->input->get('view', '', 'cmd');
		$task = $this->input->get('task', '', 'cmd');

		if ($doc->getType() == 'html' && !$this->my->id && $view != 'registration' && $view != 'login' && $task != 'saveStep') {
			$currentUri = ES::getURI(true);
			$currentUri = base64_encode($currentUri);


			$session = JFactory::getSession();
			$session->set('easysocial.before_registration', $currentUri, SOCIAL_SESSION_NAMESPACE);
		}

		// Process redirection
		$this->processUsersRedirection();
	}

	/**
	 * Redirects users view to easysocial
	 *
	 * @since	1.0
	 * @access	public
	 * @updated 3.3.0
	 */
	public function processUsersRedirection()
	{
		$cron = $this->input->get('cron', false, 'bool');
		$doc = JFactory::getDocument();

		if ($cron) {
			return;
		}

		// Check if user is required to reset their password
		$jUser = JFactory::getUser();

		// If yes, we skip the redirection to avoid the infinite loop issue when accessing other page. #1851
		if (isset($jUser->requireReset) && $jUser->requireReset) {
			return;
		}

		if ($doc->getType() != 'html') {
			return;
		}

		// Check if the admin wants to enable this
		if (!$this->config->get('registrations.redirection', false)) {
			return;
		}

		// we need to check if joomla privacy consent plugin enabled or not.
		$joomlaConsentPlgExists = JPluginHelper::isEnabled('system', 'privacyconsent');
		if ($joomlaConsentPlgExists && !$this->isJoomlaUserConsented()) {
			// user need to perform joomla privacy consent first before user can access any page.
			return;
		}

		// If this is registration from com_users, redirect to the appropriate page.
		if ($this->isUserRegistration()) {
			$url = ESR::registration(array(), false);

			return ES::redirect($url);
		}

		// If this is username reminder, redirect to the appropriate page.
		if ($this->isUserRemind()) {
			$url = ESR::account(array('layout' => 'forgetUsername'), false);

			return ES::redirect($url);
		}

		// If this is password reset, redirect to the appropriate page.
		if ($this->isUserReset()) {
			$url = ESR::account(array('layout' => 'forgetPassword'), false);

			return ES::redirect($url);
		}

		// If this is logout, we should respect this.
		if ($this->isUserLogout()) {
			return;
		}

		// If this is password reset, redirect to the appropriate page.
		if ($this->isUserLogin()) {

			$return = $this->app->input->get('return', '', 'default');

			if ($return) {
				ES::setCallback(base64_decode($return));
			}

			// Redirect to EasySocial's registration
			$url = ESR::login(array(), false);

			return ES::redirect($url);
		}

		// If this is password reset, redirect to the appropriate page.
		if ($this->isUserProfile()) {
			$url = ESR::profile(array(), false);

			return ES::redirect($url);
		}
	}

	/**
	 * Method to check if the given user has consented yet
	 *
	 * @since	2.2.5
	 * @access	private
	 */
	private function isJoomlaUserConsented()
	{
		static $_cache = array();

		$userId = JFactory::getUser()->id;
		if ($userId == 0) {
			// nothing to consent. always return true
			return true;
		}

		if (! isset($_cache[$userId])) {

			$db = JFactory::getDBO();

			$query = $db->getQuery(true);

			$query->select('COUNT(*)')
				->from('#__privacy_consents')
				->where('user_id = ' . (int) $userId)
				->where('subject = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
				->where('state = 1');

			$db->setQuery($query);

			$_cache[$userId] = (int) $db->loadResult() > 0;
		}

		return $_cache[$userId];
	}

	/**
	 * Determines if the current access is for profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isUserProfile()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'cmd');

		if ($option == 'com_users' && $view == 'profile') {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current access is for logout
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isUserLogout()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'cmd');
		$layout = $this->input->get('layout', '', 'cmd');
		$task = $this->input->get('task', '', 'cmd');

		if ($option == 'com_users' && $view == 'login' && $layout == 'logout') {
			return true;
		}

		if ($option == 'com_users' && $task == 'user.logout') {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current access is for login
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isUserLogin()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'cmd');

		if ($option == 'com_users' && $view == 'login') {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current access is for reset password
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isUserReset()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'cmd');

		if ($option == 'com_users' && $view == 'reset') {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current access is for remind username
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isUserRemind()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'cmd');

		if ($option == 'com_users' && $view == 'remind') {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current access is for registration
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isUserRegistration()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'cmd');

		if ($option == 'com_users' && $view == 'registration') {
			return true;
		}

		return false;
	}


	/**
	 * Method to handle any logout logic and report back to the subject.
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (client, ...).
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   2.0
	 */
	public function onUserLogout($user, $options = array())
	{

		$pluginEnabled = JPluginHelper::isEnabled('system', 'languagefilter');
		$mode_sef = $this->app->get('sef', 0);
		$isMultilang = JLanguageMultilang::isEnabled();

		if (!ES::isFromAdmin() && $mode_sef && $isMultilang && $pluginEnabled) {

			$menu = $this->app->getMenu();

			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			$params = new JRegistry();
			$params->loadString(empty($plugin) ? '' : $plugin->params);
			$langFilterAutomaticUrlChange = is_null($params) ? 'null' : $params->get('automatic_change', 'null');

			// Try to get association from the current active menu item
			$active = $menu->getActive();


			if ($active && $langFilterAutomaticUrlChange) {

				$config = ES::config();

				$logoutMenuItemId = $config->get('general.site.logout');

				// If lagout redirection is pointing to a menu item, we need to check if there is any menu item association.
				if ($logoutMenuItemId != 'null') {

					$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
					$assoc = JLanguageAssociations::isEnabled();

					$logoutMenuItem = $menu->getItem($logoutMenuItemId);

					$lang_code = $logoutMenuItem->language;
					$current_lang = $active->language;

					if (!$lang_code || $lang_code == '*') {
						$lang_code = $default_lang;
					}

					if (!$current_lang || $current_lang == '*') {
						$current_lang = JFactory::getLanguage()->getTag();
					}

					if ($assoc && $lang_code != $current_lang) {
						JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

						// // lets get the associated menu item.
						$associations = MenusHelper::getAssociations($logoutMenuItem->id);

						if (isset($associations[$current_lang]) && $menu->getItem($associations[$current_lang])) {
							$associationItemid = $associations[$current_lang];

							// reset the return url
							$returnUrl = 'index.php?Itemid=' . $associationItemid;
							$returnUrl = base64_encode($returnUrl);
							$this->app->input->set('return', $returnUrl);
						}

					}
				}
			}
		}

		return true;
	}
}
