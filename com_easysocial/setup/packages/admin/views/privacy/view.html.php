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

class EasySocialViewPrivacy extends EasySocialAdminView
{
	/**
	 * Main method to display the privacy view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display( $tpl = null )
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_PRIVACY', 'COM_EASYSOCIAL_DESCRIPTION_PRIVACY');

		$state = $this->input->get('state', 1, 'int');

		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::deleteList('', 'delete' , JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		$model = ES::model('Privacy', array('initState' => true, 'namespace' => 'privacy.listing'));
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$search = $model->getState('search');
		$filter = $model->getUserStateFromRequest('filter');
		$privacy = $model->getList();
		$pagination = $model->getPagination();

		$lib = ES::privacy();

		$this->set('lib', $lib);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('pagination', $pagination);
		$this->set('privacy', $privacy);
		$this->set('state', $state);
		$this->set('filter', $filter);

		ES::language()->loadSite();
		ES::apps()->loadAllLanguages();

		parent::display('admin/privacy/default/default');
	}

	/**
	 * Post process privacy after publishing
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function togglePublish()
	{
		return $this->redirect('index.php?option=com_easysocial&view=privacy');
	}

	/**
	 * Post process privacy after deleted
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete()
	{
		return $this->redirect('index.php?option=com_easysocial&view=privacy');
	}

	/**
	 * Post process points saving
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save( $task , $privacy )
	{
		$url = 'index.php?option=com_easysocial&view=privacy';

		if ($this->hasErrors() || $task == 'apply') {
			return $this->redirect($url . '&layout=form&id=' . $privacy->id);
		}

		return $this->redirect($url);
	}

	/**
	 * Main method to display the form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form()
	{
		// Load front end language file since we need the translations for privacy
		ES::language()->loadSite();

		// Get the id from the request.
		$id = $this->input->get('id', 0, 'int');

		// Get the table object
		$privacy = ES::table('Privacy');
		$privacy->load($id);

		// Add heading here.
		$this->setHeading($privacy->description, 'COM_EASYSOCIAL_DESCRIPTION_EDIT_PRIVACY');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel();

		$options = $privacy->getOptions();

		$this->set('options', $options);
		$this->set('privacy', $privacy);

		parent::display('admin/privacy/form/default');
	}

	/**
	 * Displays the installation layout for points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install($tpl = null)
	{
		// Add heading here.
		$this->setHeading('COM_EASYSOCIAL_HEADING_INSTALL_PRIVACY', 'COM_EASYSOCIAL_DESCRIPTION_INSTALL_PRIVACY');

		JToolbarHelper::custom('upload', 'upload', '', JText::_('COM_EASYSOCIAL_UPLOAD_AND_INSTALL'), false);

		parent::display('admin/privacy/install/default');
	}

	/**
	 * Displays the discover layout for points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_DISCOVER_PRIVACY', 'COM_EASYSOCIAL_DESCRIPTION_INSTALL_PRIVACY');

		JToolbarHelper::custom('discover', 'download', '', JText::_('COM_EASYSOCIAL_DISCOVER_BUTTON'), false);

		parent::display('admin/privacy/discover');
	}

	/**
	 * Redirects user back to the points listing once it's installed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		return $this->redirect('index.php?option=com_easysocial&view=privacy&layout=install');
	}

}
