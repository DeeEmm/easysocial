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

class EasySocialViewAlerts extends EasySocialAdminView
{
	public function display($tpl = null)
	{
		// Add heading here.
		$this->setHeading('COM_EASYSOCIAL_HEADING_ALERTS', 'COM_EASYSOCIAL_DESCRIPTION_ALERTS');

		// Default filters
		$options = array();

		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();

		JToolbarHelper::custom('toggleEmailPublish', 'loop', '', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_TOGGLE_EMAIL'));
		JToolbarHelper::custom('toggleSystemPublish', 'loop', '', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_TOGGLE_SYSTEM'));
		JToolbarHelper::custom('toggleAllowModifyEmail', 'loop', '', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_TOGGLE_EMAIL_STATE'));
		JToolbarHelper::custom('toggleAllowModifySystem', 'loop', '', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_TOGGLE_SYSTEM_STATE'));

		// Load badges model.
		$model = ES::model('Alert', array('initState' => true, 'namespace' => 'alerts.listing'));

		// Get the current ordering.
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$published = $model->getState('published');
		$limit = $model->getState('limit');
		$search = $model->getState('search');

		$alerts = $model->getItems();

		// Get pagination
		$pagination = $model->getPagination();

		$this->set('search', $search);
		$this->set('limit', $limit);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('published', $published);
		$this->set('alerts', $alerts);
		$this->set('pagination', $pagination);

		echo parent::display('admin/alerts/default/default');
	}

	/**
	 * Renders the discover layout for alert rules
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_DISCOVER_ALERTS', 'COM_EASYSOCIAL_DESCRIPTION_DISCOVER_ALERTS');

		JToolbarHelper::custom('discover', 'download', '', JText::_('COM_EASYSOCIAL_DISCOVER_BUTTON'), false);

		return parent::display('admin/alerts/discover/default');
	}

	/**
	 * Post process after alerts has been published / unpublished
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The current task
	 */
	public function togglePublish($task = null)
	{
		ES::info()->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=alerts');
	}

	/**
	 * Renders the upload rule form
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function install($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_INSTALL_ALERTS', 'COM_EASYSOCIAL_DESCRIPTION_INSTALL_ALERTS');

		JToolbarHelper::custom('upload', 'upload', '', JText::_('COM_EASYSOCIAL_UPLOAD_AND_INSTALL'), false);
		
		echo parent::display('admin/alerts/install/default');
	}

	/**
	 * Post process after uploading
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function upload()
	{
		return $this->redirect('index.php?option=com_easysocial&view=alerts&layout=install');
	}

	/**
	 * Post process after publishing or unpublishing an alert
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function togglePublishState()
	{
		return $this->redirect('index.php?option=com_easysocial&view=alerts');
	}
}