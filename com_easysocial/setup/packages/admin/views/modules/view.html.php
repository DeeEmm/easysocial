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

class EasySocialViewModules extends EasySocialAdminView
{
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_HEADING_MODULES_PACKAGE_MANAGER');

		// Get the server keys
		$key = $this->config->get('general.key');

		// Check if there's any data on the server
		$model= ES::model('Modules', ['initState' => true]);
		$initialized = $model->initialized();

		if (!$initialized) {
			return parent::display('admin/modules/initialize/default');
		}

		JToolbarHelper::custom('install', 'upload' , '' , JText::_('Install / Update'));
		JToolbarHelper::custom('uninstall', 'remove', '', JText::_('COM_EASYBLOG_TOOLBAR_BUTTON_UNINSTALL'));
		JToolbarHelper::custom('discover', 'refresh', '', JText::_('COM_EASYSOCIAL_TOOLBAR_BUTTON_FIND_UPDATES'), false);

		// Get filter states.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'cmd');
		$direction = $this->input->get('direction', $model->getState('direction'), 'cmd');
		$limit = $model->getState('limit');
		$published = $model->getState('published');
		$search = $model->getState('search');

		// Get the list of languages now
		$modules = $model->getModules();
		$pagination	= $model->getPagination();

		$this->set('published', $published);
		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('modules', $modules);
		$this->set('pagination', $pagination);

		return parent::display('admin/modules/default/default');
	}

	/**
	 * Discover modules from stackideas repository
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function discover()
	{
		$this->setHeading('COM_ES_HEADING_MODULES_PACKAGE_MANAGER');

		return parent::display('admin/modules/initialize/default');
	}

	/**
	 * Retrieves the current domain
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function getDomain()
	{
		$domain = rtrim(JURI::root(), '/');
		$domain = str_ireplace(array('http://', 'https://'), '', $domain);

		return $domain;
	}

	/**
	 * Post processing after uninstall happens
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function uninstall()
	{
		return $this->redirect('index.php?option=com_easysocial&view=languages');
	}

	/**
	 * Post processing after purge happens
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purge()
	{
		return $this->redirect('index.php?option=com_easysocial&view=languages');
	}

	/**
	 * Post processing after language has been installed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install()
	{
		return $this->redirect('index.php?option=com_easysocial&view=languages');
	}
}
