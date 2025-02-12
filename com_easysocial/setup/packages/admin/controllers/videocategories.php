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

class EasySocialControllerVideoCategories extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		// Register task aliases here.
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');

		$this->registerTask('publish', 'publish');
		$this->registerTask('unpublish', 'unpublish');
	}

	/**
	 * Sets a video category as a default video category
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function toggleDefault()
	{
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');
		$id = $ids[0];

		$table = ES::table('VideoCategory');
		$table->load($id);

		// Set the record as the default video category
		$state = $table->setDefault();

		if ($state) {
			$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_CATEGORIES_SET_DEFAULT', 'video', [
						'categoryTitle' => $table->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=videocategories&layout=form&id=' . $table->id
					]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_CATEGORIES_SET_DEFAULT_SUCCESS');
		return $this->view->call(__FUNCTION__, $table);
	}

	/**
	 * Deletes a category
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function delete()
	{
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$category = ES::table('VideoCategory');
			$category->load($id);

			if ($category->default) {
				$this->view->setMessage(JText::sprintf('COM_ES_VIDEO_CATEGORIES_DELETE_DEFAULT_ERROR', $category->title), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			$state = $category->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_CATEGORIES_DELETED', 'video', ['categoryTitle' => $category->getTitle()]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_CATEGORIES_DELETED_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Publishes a category
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function publish()
	{
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$category = ES::table('VideoCategory');
			$category->load($id);

			$state = $category->publish();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_CATEGORIES_PUBLISHED', 'video', [
						'categoryTitle' => $category->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=videocategories&layout=form&id=' . $table->id
					]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_CATEGORIES_PUBLISHED_SUCCESS');

		return $this->view->call('togglePublish');
	}

	/**
	 * Unpublishes video categories
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function unpublish()
	{
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$category = ES::table('VideoCategory');
			$category->load($id);

			$state = $category->unpublish();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_CATEGORIES_UNPUBLISHED', 'video', [
						'categoryTitle' => $category->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=videocategories&layout=form&id=' . $table->id
					]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_CATEGORIES_UNPUBLISHED_SUCCESS');

		return $this->view->call('togglePublish');
	}

	/**
	 * Saves a new video category
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function save()
	{
		// Check for request forgeries
		ES::checkToken();

		// Perhaps the user is editing a video category
		$id = $this->input->get('id', 0, 'int');

		// Get the category
		$category = ES::table('VideoCategory');
		$category->load($id);

		$category->title = $this->input->get('title', '', 'default');
		$category->alias = $this->input->get('alias', '', 'default');
		$category->description = $this->input->get('description', '', 'default');
		$category->state = $this->input->get('state', true, 'bool');
		$category->user_id = $this->my->id;

		$state = $category->store();

		// Bind video category access
		if ($state) {
			$categoryAccess = $this->input->get('create_access', '', 'default');
			$category->bindCategoryAccess('create', $categoryAccess);

			$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_CATEGORIES_SAVED', 'video', [
						'categoryTitle' => $category->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=videocategories&layout=form&id=' . $category->id
					]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_CATEGORY_SAVED_SUCCESS');

		return $this->view->call('save', $this->getTask(), $category);
	}

	/**
	 * Method to update categories ordering
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		ES::checkToken();

		$cid = $this->input->get('cid', array(), 'array');
		$ordering = $this->input->get('order', array(), 'array');

		if (!$cid) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_ORDERING_NO_ITEMS');
		}

		$model = ES::model('Videos');

		for ($i = 0; $i < count($cid); $i++) {

			$id = $cid[$i];
			$order = $ordering[$i];

			$model->updateCategoriesOrdering($id, $order);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_ORDERING_UPDATED');
		return $this->view->call(__FUNCTION__);
	}

	public function moveUp()
	{
		$direction = $this->input->get('direction', 'asc');

		if ($direction == 'desc') {
			return $this->move(1);
		}

		return $this->move(-1);
	}

	public function moveDown()
	{
		$direction = $this->input->get('direction', 'asc');

		if ($direction == 'desc') {
			return $this->move(-1);
		}

		return $this->move(1);
	}

	private function move($index)
	{
		$layout = $this->input->get('layout', '', 'cmd');

		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_VIDEOS_CATEGORIES_INVALID_IDS');
		}

		$db = ES::db();

		foreach ($ids as $id) {
			$table = ES::table('videocategory');
			$table->load($id);

			$table->move($index);
		}

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_CATEGORIES_ORDERED_SUCCESSFULLY');

		return $this->view->call('move', $layout);
	}
}
