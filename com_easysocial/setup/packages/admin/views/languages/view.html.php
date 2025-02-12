<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewLanguages extends EasySocialAdminView
{
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_LANGUAGES', 'COM_EASYSOCIAL_DESCRIPTION_LANGUAGES');

		// Get the server keys
		$key = $this->config->get('general.key');

		// Check if there's any data on the server
		$model = ES::model('Languages', array('initState' => true));
		$initialized = $model->initialized();

		if (!$initialized) {
			$domain = $this->getDomain();

			$this->set('domain', $domain);
			$this->set('key', $key);

			return parent::display('admin/languages/initialize/default');
		}

		// Add Joomla buttons
		JToolbarHelper::custom('discover', 'refresh', '', JText::_('COM_EASYSOCIAL_TOOLBAR_BUTTON_FIND_UPDATES'), false);
		JToolbarHelper::custom('purge', 'trash', '', JText::_('COM_EASYSOCIAL_TOOLBAR_BUTTON_PURGE_CACHE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('install', 'upload' , '' , JText::_('COM_EASYSOCIAL_TOOLBAR_BUTTON_INSTALL_OR_UPDATE'));
		JToolbarHelper::custom('uninstall', 'remove', '', JText::_('COM_EASYBLOG_TOOLBAR_BUTTON_UNINSTALL'));
		

		// Get filter states.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'cmd');
		$direction = $this->input->get('direction', $model->getState('direction'), 'cmd');
		$limit = $model->getState('limit');
		$published = $model->getState('published');
		$search = $model->getState('search');

		// Get the list of languages now
		$languages = $model->getLanguages();

		foreach ($languages as &$language) {
			$translators = json_decode($language->translator);
			$language->translator = $translators;
		}

		$pagination	= $model->getPagination();

		$this->set('published', $published);
		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('languages', $languages);
		$this->set('pagination', $pagination);

		return parent::display('admin/languages/default/default');
	}

	/**
	 * Discover languages from our server
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function discover()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_LANGUAGES');
		$this->setDescription('COM_EASYSOCIAL_DESCRIPTION_LANGUAGES');

		// Get the stored settings
		$key = $this->config->get('general.key');
		$domain = $this->getDomain();

		$this->set('domain', $domain);
		$this->set('key', $key);

		return parent::display('admin/languages/initialize/default');
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
