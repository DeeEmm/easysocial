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

class EasySocialControllerVideos extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('apply', 'save');

		$this->registerTask('setFeatured', 'toggleDefault');
		$this->registerTask('removeFeatured', 'toggleDefault');
		$this->registerTask('unpublish', 'unpublish');
	}

	/**
	 * Toggles a video state
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function toggleDefault()
	{
		ES::checkToken();

		// Get the list of videos
		$cid = $this->input->get('cid', array(), 'array');

		// Get the task
		$task = $this->getTask();

		foreach ($cid as $id) {
			$id = (int) $id;

			$table = ES::table('Video');
			$table->load($id);

			$video = ES::video($table);

			// If it's set to toggle default, we need to know the video's featured state
			if ($task == 'toggleDefault') {
				$task = $video->isFeatured() ? 'removeFeatured' : 'setFeatured';
			}

			$state = $video->$task();
			$message = 'COM_EASYSOCIAL_VIDEOS_SELECTED_VIDEOS_' . strtoupper($task);

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_' . strtoupper($task), 'video', [
						'videoTitle' => $video->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=videos&layout=form&id=' . $video->id
					]);
			}
		}

		$this->view->setMessage($message);

		return $this->view->call('redirectToVideos');
	}

	/**
	 * Publishes a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function publish()
	{
		// Check for request forgeries
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Video');
			$table->load($id);

			$video = ES::video($table);
			$state = $video->publish(array('createStream' => false));

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_PUBLISHED', 'video', [
						'videoTitle' => $video->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=videos&layout=form&id=' . $video->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_VIDEO_SELECTED_VIDEO_PUBLISHED');
		return $this->view->call('redirectToVideos');
	}

	/**
	 * Unpublishes a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function unpublish()
	{
		// Check for request forgeries
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Video');
			$table->load($id);

			$video = ES::video($table);
			$state = $video->unpublish();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_UNPUBLISHED', 'video', [
						'videoTitle' => $video->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=videos&layout=form&id=' . $video->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_VIDEO_SELECTED_VIDEO_UNPUBLISHED');
		return $this->view->call('redirectToVideos');
	}

	/**
	 * Saves a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function save()
	{
		// Check for request forgeries
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		// Get the file data
		$file = $this->input->files->get('video');

		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			$file = null;
		}

		// Get the posted data
		$post = $this->input->post->getArray();

		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		$options = array();

		// Video upload will create stream once it is published.
		// We will only create a stream here when it is an external link.
		if ($post['source'] != SOCIAL_VIDEO_UPLOAD) {
			$options = array('createStream' => true);
		}

		// Save the video
		$state = $video->save($post, $file, $options);

		// Load up the session
		$session = JFactory::getSession();

		if (!$state) {

			// Store the data in the session so that we can repopulate the values again
			$data = json_encode($video->export());

			$session->set('videos.form', $data, SOCIAL_SESSION_NAMESPACE);

			$this->view->setMessage($video->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $video, $this->getTask());
		}

		// Once a video is created successfully, remove any data associated from the session
		$session->set('videos.form', null, SOCIAL_SESSION_NAMESPACE);
		$action = $id ? 'UPDATED' : 'CREATED';

		$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_' . $action, 'video', [
				'videoTitle' => $video->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=videos&layout=form&id=' . $video->id
			]);

		if (!$video->isPendingProcess()) {
			$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_' . $action . '_SUCCESS');
		}

		return $this->view->call(__FUNCTION__, $video, $this->getTask());
	}

	/**
	 * Deletes a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function delete()
	{
		ES::checkToken();

		// Get the list of ids
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Video');
			$table->load($id);

			$video = ES::video($table);
			$state = $video->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_DELETED', 'video', ['videoTitle' => $video->getTitle()]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_SELECTED_VIDEOS_DELETED');
		return $this->view->call('redirectToVideos');
	}

	/**
	 * Allows caller to change audio owner
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function switchOwner()
	{
		ES::checkToken();

		$ids = $this->input->get('ids', array(), 'int');

		$userId = $this->input->get('userId', 0, 'int');

		if (!$ids || !$userId) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_IDS');
		}

		foreach ($ids as $id) {

			$video = ES::table('Video');
			$video->load($id);

			$video->user_id = $userId;

			if ($video->type == SOCIAL_TYPE_USER) {
				$video->uid = $userId;
			}

			$video->store();

			$user = ES::user($userId);

			$this->actionlog->log('COM_ES_ACTION_LOG_VIDEO_OWNER_SWITCHED', 'video', [
					'name' => $video->title,
					'link' => 'index.php?option=com_easysocial&view=videos&layout=form&id=' . $video->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id,
				]);
		}

		$this->view->setMessage('COM_ES_VIDEO_OWNER_UPDATED_SUCCESS');
		return $this->view->call('redirectToVideos');
	}
}
