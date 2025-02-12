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

class EasySocialControllerAudios extends EasySocialController
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
	 * Toggles an audio state
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function toggleDefault()
	{
		// Check for token
		ES::checkToken();

		// Get the list of audios
		$cid = $this->input->get('cid', array(), 'array');

		// Get the task
		$task = $this->getTask();

		foreach ($cid as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);
			$audio = ES::audio($table);

			// If it's set to toggle default, we need to know the audio's featured state
			if ($task == 'toggleDefault') {
				$task = $audio->isFeatured() ? 'removeFeatured' : 'setFeatured';
			}

			$state = $audio->$task();
			$message = 'COM_ES_AUDIO_SELECTED_AUDIO_' . strtoupper($task);

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_AUDIO_' . strtoupper($task), 'audio', [
						'audioTitle' => $audio->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=audios&layout=form&id=' . $audio->id
					]);
			}
		}

		$this->view->setMessage($message);
		return $this->view->call('redirectToAudios');
	}

	/**
	 * Publishes an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function publish()
	{
		// Check for request forgeries
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);

			$audio = ES::audio($table);
			$state = $audio->publish(array('createStream' => false));

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_AUDIO_PUBLISHED', 'audio', [
						'audioTitle' => $audio->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=audios&layout=form&id=' . $audio->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_AUDIO_SELECTED_AUDIO_PUBLISHED');
		return $this->view->call('redirectToAudios');
	}

	/**
	 * Unpublishes an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function unpublish()
	{
		// Check for request forgeries
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);

			$audio = ES::audio($table);
			$state = $audio->unpublish();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_AUDIO_UNPUBLISHED', 'audio', [
						'audioTitle' => $audio->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=audios&layout=form&id=' . $audio->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_AUDIO_SELECTED_AUDIO_UNPUBLISHED');
		return $this->view->call('redirectToAudios');
	}

	/**
	 * Saves an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save()
	{
		// Check for request forgeries
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		// Get the file data
		$file = $this->input->files->get('audio');

		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			$file = null;
		}

		// Get the posted data
		$post = $this->input->post->getArray();

		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table);

		$options = array();

		// Audio upload will create stream once it is published.
		// We will only create a stream here when it is an external link.
		if ($post['source'] != SOCIAL_AUDIO_UPLOAD) {
			$options = array('createStream' => true);
		}

		// Save the audio
		$state = $audio->save($post, $file, $options);

		// Load up the session
		$session = JFactory::getSession();

		if (!$state) {

			// Store the data in the session so that we can repopulate the values again
			$data = json_encode($audio->export());

			$session->set('audios.form', $data, SOCIAL_SESSION_NAMESPACE);

			$this->view->setMessage($audio->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $audio, $this->getTask());
		}

		$action = $id ? 'UPDATED' : 'CREATED';

		$this->actionlog->log('COM_ES_ACTION_LOG_AUDIO_' . $action, 'audio', [
				'audioTitle' => $audio->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=audios&layout=form&id=' . $audio->id
			]);

		// Once an audio is created successfully, remove any data associated from the session
		$session->set('audios.form', null, SOCIAL_SESSION_NAMESPACE);

		$this->view->setMessage('COM_ES_AUDIO_' . $action . '_SUCCESS');

		return $this->view->call(__FUNCTION__, $audio, $this->getTask());
	}

	/**
	 * Deletes an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the list of ids
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Audio');
			$table->load($id);

			$audio = ES::audio($table);
			$state = $audio->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_AUDIO_DELETED', 'audio', ['audioTitle' => $audio->getTitle()]);
			}
		}

		$this->view->setMessage('COM_ES_SELECTED_AUDIO_DELETED');
		return $this->view->call('redirectToAudios');
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

			$audio = ES::table('Audio');
			$audio->load($id);

			$audio->user_id = $userId;

			if ($audio->type == SOCIAL_TYPE_USER) {
				$audio->uid = $userId;
			}

			$audio->store();

			$user = ES::user($userId);

			$this->actionlog->log('COM_ES_ACTION_LOG_AUDIO_OWNER_SWITCHED', 'audio', [
					'name' => $audio->title,
					'link' => 'index.php?option=com_easysocial&view=audios&layout=form&id=' . $audio->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id,
				]);
		}

		$this->view->setMessage('COM_ES_AUDIO_OWNER_UPDATED_SUCCESS');
		return $this->view->call('redirectToAudios');
	}
}

