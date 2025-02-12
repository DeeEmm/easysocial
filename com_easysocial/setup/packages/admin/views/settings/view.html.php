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

ES::import('admin:/views/views');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class EasySocialViewSettings extends EasySocialAdminView
{
	/**
	 * Default method to generate the settings
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Disallow access
		if (!$this->authorise('easysocial.access.settings')) {
			return $this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$this->redirect('index.php?option=com_easysocial&view=settings&layout=form&page=general');

		$active = $this->input->get('active', 'general', 'cmd');

		$this->set('active', $active);

		return parent::display('admin/settings/default');
	}

	/**
	 * Main method to output the settings page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form()
	{
		// Get the current page.
		$page = $this->input->get('page', '', 'word');
		$page = strtolower($page);

		// Ensure that the page is valid
		if (!$page) {
			return $this->redirect('index.php?option=com_easysocial');
		}

		// Add Joomla toolbar buttons
		JToolbarHelper::apply();

		// Allow purging text based avatars
		if ($page == 'users' && $this->config->get('users.avatarUseName')) {
			JToolbarHelper::custom('purgeTextAvatars', 'purgeTextAvatars', '', JText::_('COM_ES_PURGE_TEXT_AVATARS'), false);
		}

		if ($this->config->get('general.environment') == 'development') {
			JToolbarHelper::custom('rebuildSearch', '', '', 'Rebuild Search Index', false);
		}

		// Set the heading
		$heading = 'COM_EASYSOCIAL_' . strtoupper($page) . '_SETTINGS_HEADER';
		$this->setHeading($heading);

		// Set the page to the class for other method to access
		$this->section = $page;

		// Try to get the tabs from this page
		$tabs = $this->getTabs($page);

		// Set the page variable.
		$active = $this->input->get('tab', '', 'cmd');

		$goto = $this->input->get('goto', '', 'cmd');

		$this->set('goto', $goto);
		$this->set('active', $active);
		$this->set('tabs', $tabs);
		$this->set('page', $page);

		echo parent::display('admin/settings/form/default');
	}

	/**
	 * Retrieves the tabs
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTabs($page)
	{
		$path = JPATH_ADMINISTRATOR . '/components/com_easysocial/themes/default/settings/form/pages/' . $page;

		if (!JFolder::exists($path)) {
			// redirect to general page.
			$this->setMessage(JText::sprintf('COM_ES_SETTINGS_PAGE_NOT_FOUND', $page), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());
			return $this->redirect('index.php?option=com_easysocial&view=settings&layout=form&page=general');
		}

		$files = JFolder::files($path, '.php');
		$tabs = array();

		// Get the current active tab
		$active = $this->input->get('tab', '', 'cmd');

		foreach ($files as $file) {

			$fileName = $file;
			$file = str_ireplace('.php', '', $file);

			$tab = new stdClass();
			$tab->id = str_ireplace(array(' ', '.', '#', '_'), '-', strtolower($file));
			$tab->title = JText::_('COM_EASYSOCIAL_' . strtoupper($page) . '_SETTINGS_' . strtoupper($file));
			$tab->file = $path . '/' . $fileName;
			$tab->active = ($file == 'general' && !$active) || $active === $tab->id;

			// Get the contents of the tab now
			$theme = ES::themes();
			$namespace = 'themes:/admin/settings/form/pages/' . strtolower($page) . '/' . $file;

			// if that is setting social page
			if ($page == 'social') {

				$selectedScopesPermission = $this->config->get('oauth.facebook.scopes');

				if ($selectedScopesPermission) {
					$selectedScopesPermission = explode(',', $selectedScopesPermission);
				}

				$oauthFacebookURIs = ES::oauth()->getOauthRedirectURI('facebook');
				$oauthTwitterURIs = ES::oauth()->getOauthRedirectURI('twitter');
				$oauthLinkedinURIs = ES::oauth()->getOauthRedirectURI('linkedin');
				$oauthAppleURIs = ES::oauth()->getOauthRedirectURI('apple');
				$oauthGoogleURIs = ES::oauth()->getOauthRedirectURI('google');

				$theme->set('selectedScopesPermission', $selectedScopesPermission);
				$theme->set('oauthFacebookURIs', $oauthFacebookURIs);
				$theme->set('oauthTwitterURIs', $oauthTwitterURIs);
				$theme->set('oauthLinkedinURIs', $oauthLinkedinURIs);
				$theme->set('oauthAppleURIs', $oauthAppleURIs);
				$theme->set('oauthGoogleURIs', $oauthGoogleURIs);
			}

			// if that is setting video page
			if ($page == 'videos') {

				$model = ES::model('OAuth');
				$oauthData = $model->isAssociated('twitch');

				$isAssociated = $oauthData ? $oauthData : false;

				if ($isAssociated && (isset($oauthData->expires) && $oauthData->expires)) {

					// Retrieve the Twitch app access token expired date
					$expires = ES::date($oauthData->expires)->format(JText::_('DATE_FORMAT_LC3'));

					$theme->set('expires', $expires);
				}

				$theme->set('isAssociated', $isAssociated);
			}

			if ($page == 'photos') {

				// Determine if imagick enable so we can load this option
				$imagickEnabled = extension_loaded('imagick');
				$theme->set('imagickEnabled', $imagickEnabled);
			}

			$tab->contents = $theme->output($namespace);

			$tabs[$tab->id] = $tab;
		}

		// Sort items manually. Always place "General" as the first item
		if (isset($tabs['general'])) {

			$general = $tabs['general'];

			unset($tabs['general']);

			array_unshift($tabs, $general);
		} else {
			// First tab should always be highlighted
			$firstIndex = array_keys($tabs);
			$firstIndex = $firstIndex[0];

			if ($active) {
				$tabs[$firstIndex]->active = $active === $tabs[$firstIndex]->id;
			} else {
				$tabs[$firstIndex]->active = true;
			}
		}

		return $tabs;
	}

	/**
	 * Rebuilds the search database
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function rebuildSearch()
	{
		$this->setHeading("Rebuild Search Results for Settings");

		$file = SOCIAL_ADMIN_DEFAULTS . '/sidebar/settings.json';
		$contents = file_get_contents($file);

		$settings = json_decode($contents);

		$items = array();

		foreach ($settings->childs as $child) {
			$items[] = $child->url->page;
		}

		$this->set('items', $items);

		return parent::display('admin/settings/rebuild.search');
	}

	/**
	 * Post process after settings is reset
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reset($page)
	{
		return $this->redirect('index.php?option=com_easysocial&view=settings&layout=form&page=' . $page);
	}

	/**
	 * Post process after settings is imported
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function import($page)
	{
		return $this->redirect('index.php?option=com_easysocial&view=settings&layout=form&page=' . $page);
	}

	/**
	 * Responsible to redirect to the appropriate page when a user clicks on the apply button.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function apply($page = '', $tab = '')
	{
		$redirect = 'index.php?option=com_easysocial&view=settings&layout=form&page=' . $page;

		if ($tab) {
			$redirect .= '&tab=' . $tab;
		}

		return $this->redirect($redirect);
	}
}
