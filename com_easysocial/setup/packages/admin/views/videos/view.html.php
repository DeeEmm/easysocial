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

class EasySocialViewVideos extends EasySocialAdminView
{
	/**
	 * Renders the videos from the back end
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_VIDEOS', 'COM_EASYSOCIAL_DESCRIPTION_VIDEOS');

		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::deleteList();
		JToolbarHelper::custom('setFeatured', 'featured', '', JText::_('COM_ES_FEATURE'));
		JToolbarHelper::custom('removeFeatured', '', '', JText::_('COM_ES_UNFEATURE'));
		JToolbarHelper::custom('switchOwner', 'switchowner', '', JText::_('COM_EASYSOCIAL_CHANGE_OWNER'));

		$model = ES::model('Videos', array('initState' => true, 'namespace' => 'videos.listing'));

		$filter = $model->getState('filter');
		$state = $model->getState('published');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$search = $model->getState('search');

		if ($filter != 'all') {
			$filter = (int) $filter;
		}

		// Load a list of extensions so that users can filter them.
		$videos = $model->getItems();

		// Get pagination
		$pagination = $model->getPagination();

		if ($this->input->getString('tmpl') == 'component') {
			$pagination->setVar('tmpl', 'component');
		}

		$this->set('filter', $filter);
		$this->set('direction', $direction);
		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('videos', $videos);
		$this->set('pagination', $pagination);
		$this->set('simple', $this->input->getString('tmpl') == 'component');

		parent::display('admin/videos/default/default');
	}

	/**
	 * Renders the video form
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function form()
	{
		// Try to load the video that needs to be edited
		$id = $this->input->get('id', 0, 'int');

		$this->setHeading('COM_EASYSOCIAL_HEADING_VIDEOS_EDIT_VIDEO', 'COM_EASYSOCIAL_DESCRIPTION_EDIT_VIDEO');

		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		// Load front end's language file
		ES::language()->loadSite();

		$model = ES::model('Videos');
		$categories = $model->getCategories();
		$categoryOptions = ES::convertToDropdownOptions($categories, 'id', 'title');

		$privacy = ES::privacy();
		$userTags = $video->getEntityTags();
		$userTagItemList = array();

		if ($userTags) {
			foreach($userTags as $userTag) {
				$userTagItemList[] = $userTag->item_id;
			}
		}


		$hashtags = $video->getTags(true);

		$this->set('categoryOptions', $categoryOptions);
		$this->set('privacy', $privacy);
		$this->set('hashtags', $hashtags);
		$this->set('userTags', $userTags);
		$this->set('categories', $categories);
		$this->set('table', $table);
		$this->set('video', $video);

		// Add Joomla buttons here
		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		parent::display('admin/videos/form');
	}

	/**
	 * Displays the process to transcode the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function process()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		// Ensure that the current user really owns this video
		if (!$video->canProcess()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_PROCESS'), 500);
		}

		$this->setHeading('COM_EASYSOCIAL_HEADING_VIDEOS_EDIT_VIDEO', 'COM_EASYSOCIAL_DESCRIPTION_EDIT_VIDEO');

		$this->set('video', $video);

		parent::display('admin/videos/process/default');
	}

	/**
	 * Post process after a video is saved
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function save(SocialVideo $video, $task)
	{

		$redirect = 'index.php?option=com_easysocial&view=videos';

		if ($video->isPendingProcess()) {

			if (!$this->config->get('video.autoencode')) {
				$message = JText::_('COM_EASYSOCIAL_VIDEOS_UPLOAD_SUCCESS_AWAIT_PROCESSING');

				if ($task == 'apply') {
					$redirect .= '&layout=form&id=' . $video->id;
				}
			} else {
				$message = JText::_('COM_EASYSOCIAL_VIDEOS_UPLOAD_SUCCESS_PROCESSING_VIDEO_NOW');
				$redirect .= '&layout=process&id=' . $video->id;
			}

			$this->info->set($message);
		} else {
			$this->info->set($this->getMessage());

			if ($task == 'apply') {
				$redirect .= '&layout=form&id=' . $video->id;
			}
		}


		return $this->redirect($redirect);
	}

	/**
	 * Redirection to video listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function redirectToVideos()
	{
		return $this->redirect('index.php?option=com_easysocial&view=videos');
	}
}
