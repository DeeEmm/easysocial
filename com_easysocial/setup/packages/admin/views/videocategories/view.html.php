<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewVideoCategories extends EasySocialAdminView
{
	/**
	 * Main method to display the video categories
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_VIDEOS_CATEGORIES', 'COM_EASYSOCIAL_DESCRIPTION_VIDEOS_CATEGORIES');

		// Insert Joomla buttons
		JToolbarHelper::addNew();
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::deleteList();

		// Get the model
		$model = ES::model('Videos', array('initState' => true, 'namespace' => 'videocategories.listing'));

		// Remember the states
		$search = $model->getState('search');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');

		// Get the categories
		$categories = $model->getCategories(array('search' => $search, 'administrator' => true, 'ordering' => $ordering, 'direction' => $direction));

		// Get the pagination 
		$pagination = $model->getPagination();

		$this->set('simple', $this->input->getString('tmpl') == 'component');
		$this->set('categories', $categories);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('pagination', $pagination);
		$this->set('search', $search);

		parent::display('admin/videocategories/default');
	}

	/**
	 * Displays the category form
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');

		$this->setHeading('COM_EASYSOCIAL_HEADING_VIDEOS_CATEGORIES_CREATE', 'COM_EASYSOCIAL_DESCRIPTION_VIDEOS_CATEGORIES_CREATE');

		$category = ES::table('VideoCategory');
		$category->load($id);

		if ($id) {
			$this->setHeading('COM_EASYSOCIAL_HEADING_VIDEOS_CATEGORIES', 'COM_EASYSOCIAL_DESCRIPTION_VIDEOS_CATEGORIES');
		} else {
			// If new record, it should be published by default.
			$category->state = SOCIAL_STATE_PUBLISHED;
		}

		// Get the active category
		$activeTab = $this->input->get('active', 'settings', 'cmd');

		// Get the acl for creation access
		$createAccess = $category->getProfileAccess();

		// Insert Joomla buttons
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::save2new();
		JToolbarHelper::cancel();

		$this->set('createAccess', $createAccess);
		$this->set('activeTab', $activeTab);
		$this->set('category', $category);

		parent::display('admin/videocategories/forms/default');
	}

	/**
	 * Post process after publishing / unpublishing a category
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function togglePublish()
	{
		return $this->redirect('index.php?option=com_easysocial&view=videocategories');
	}

	/**
	 * Post process after deleting category
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function delete()
	{
		return $this->redirect('index.php?option=com_easysocial&view=videocategories');
	}

	/**
	 * Post process after saving
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function save($task, $category)
	{
		$url = 'index.php?option=com_easysocial&view=videocategories';

		if ($task == 'save2new') {
			$url .= '&layout=form';
		}

		if ($task == 'apply') {
			$url .= '&layout=form&id=' . $category->id;
		}

		return $this->redirect($url);
	}

	/**
	 * Post process after a category is set as default
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function toggleDefault()
	{
		return $this->redirect('index.php?option=com_easysocial&view=videocategories');
	}

	/**
	 * Post processing for updating ordering.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function saveorder()
	{
		return $this->redirect('index.php?option=com_easysocial&view=videocategories');
	}

	/**
	 * Post process after moving video categories order
	 *
	 * @since  2.0
	 * @access public
	 */
	public function move($layout = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=videocategories');
	}
}
