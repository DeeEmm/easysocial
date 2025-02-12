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

ES::import('site:/views/views');

class EasySocialViewApps extends EasySocialSiteView
{
	/**
	 * Post processing after filtering apps
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function filter()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		// Default properties
		$options = array('type' => SOCIAL_APPS_TYPE_APPS, 'installable' => true, 'group' => SOCIAL_APPS_GROUP_USER, 'uid' => $this->my->id, 'state' => SOCIAL_STATE_PUBLISHED);

		// default sorting
		$options['sort'] = 'a.title';
		$options['order'] = 'asc';

		// See if filter is provided
		$filter = $this->input->get('filter', '', 'word');

		// Currently the only filter type is 'mine'
		if (!empty($filter) && $filter != 'all') {
			$options['uid'] = $this->my->id;
			$options['key'] = SOCIAL_TYPE_USER;
			$options['includedefault'] = true; // this flag used only in my apps page and use conjunction with 'installable'; #1657
		}

		// Get apps model
		$model = ES::model('Apps');
		$apps = $model->getApps($options);

		$theme = ES::themes();
		$theme->set('filter', $filter);
		$theme->set('apps', $apps);
		$output = $theme->output('site/apps/default/items');

		return $this->ajax->resolve($output);
	}

	/**
	 * Retrieves the terms and conditions for the app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTnc()
	{
		// User's need to be logged in
		ES::requireLogin();
		ES::language()->loadAdmin();

		$theme = ES::themes();
		$output = $theme->output('site/apps/dialogs/install');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after an app is installed.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function installApp()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$theme = ES::themes();
		$html = $theme->output('site/apps/dialogs/installed');

		return $this->ajax->resolve($html);
	}

	/**
	 * Post processing after uninstalling app
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function uninstall()
	{
		$theme = ES::themes();
		$html = $theme->output('site/apps/dialogs/uninstalled');

		return $this->ajax->resolve($html);
	}

	/**
	 * Post process after settings is saved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveSettings()
	{
		// User must be logged in.
		ES::requireLogin();

		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	/**
	 * Display confirmation that the settings is saved successfully.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveSuccess()
	{
		// User must be logged in.
		ES::requireLogin();

		// Get the themes library
		$theme = ES::themes();

		$output = $theme->output('site/apps/dialogs/saved');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the app settings
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function settings()
	{
		// User must be logged in.
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$app = ES::table('App');
		$app->load($id);

		if (!$id || !$app->id) {
			return $this->exception('COM_EASYSOCIAL_APPS_INVALID_ID_PROVIDED');
		}

		// Ensure that the user can really access this app settings.
		if (!$app->isInstalled()) {
			$this->setMessage('COM_EASYSOCIAL_APPS_SETTINGS_NOT_INSTALLED', SOCIAL_MSG_ERROR);
			return $this->ajax->reject($this->getMessage());
		}

		$theme = ES::themes();

		$params	= $app->getUserParams();
		$form = $app->renderForm('user', $params);

		$theme->set('id', $app->id);
		$theme->set('form', $form);

		$output = $theme->output('site/apps/dialogs/settings');

		return $this->ajax->resolve($output);
	}
}
