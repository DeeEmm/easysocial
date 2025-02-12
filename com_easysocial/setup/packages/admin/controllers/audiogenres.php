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

class EasySocialControllerAudioGenres extends EasySocialController
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
	 * Sets an audio genre as a default genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function toggleDefault()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');
		$id = $ids[0];

		$table = ES::table('AudioGenre');
		$table->load($id);

		// Set the record as the default audio genre
		$state = $table->setDefault();

		if ($state) {
			$this->actionlog->log('COM_ES_ACTION_LOG_AUDIOGENRE_SET_DEFAULT', 'audio', [
				'genreTitle' => $table->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=audiogenres&layout=form&id=' . $table->id
			]);
		}

		$this->view->setMessage('COM_ES_AUDIO_GENRES_SET_DEFAULT_SUCCESS');
		return $this->view->call(__FUNCTION__, $table);
	}

	/**
	 * Deletes a genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$genre = ES::table('AudioGenre');
			$genre->load($id);

			if ($genre->default) {
				$this->view->setMessage(JText::sprintf('COM_ES_AUDIO_GENRES_DELETE_DEFAULT_ERROR', $genre->title), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			$state = $genre->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_AUDIOGENRE_DELETED', 'audio', ['genreTitle' => $genre->getTitle()]);
			}
		}

		$this->view->setMessage('COM_ES_AUDIO_GENRES_DELETED_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Publishes a genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function publish()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$genre = ES::table('AudioGenre');
			$genre->load($id);

			$state = $genre->publish();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_AUDIOGENRE_PUBLISHED', 'audio', [
						'genreTitle' => $genre->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=audiogenres&layout=form&id=' . $genre->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_AUDIO_GENRES_PUBLISHED_SUCCESS');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Unpublishes audio genres
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function unpublish()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the list of ids here
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$genre = ES::table('AudioGenre');
			$genre->load($id);

			$state = $genre->unpublish();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_AUDIOGENRE_UNPUBLISHED', 'audio', [
						'genreTitle' => $genre->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=audiogenres&layout=form&id=' . $genre->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_AUDIO_GENRES_UNPUBLISHED_SUCCESS');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Saves a new audio genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save()
	{
		// Check for request forgeries
		ES::checkToken();

		// Perhaps the user is editing an audio genre
		$id = $this->input->get('id', 0, 'int');

		// Get the genre
		$genre = ES::table('AudioGenre');
		$genre->load($id);

		$genre->title = $this->input->get('title', '', 'default');
		$genre->alias = $this->input->get('alias', '', 'default');
		$genre->description = $this->input->get('description', '', 'default');
		$genre->state = $this->input->get('state', true, 'bool');
		$genre->user_id = $this->my->id;

		$state = $genre->store();

		// Bind audio genre access
		if ($state) {
			$genreAccess = $this->input->get('create_access', '', 'default');
			$genre->bindGenreAccess('create', $genreAccess);

			$this->actionlog->log('COM_ES_ACTION_LOG_AUDIOGENRE_SAVED', 'audio', [
						'genreTitle' => $genre->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=audiogenres&layout=form&id=' . $genre->id
					]);
		}

		$task = $this->getTask();

		$this->view->setMessage('COM_ES_AUDIO_GENRES_SAVED_SUCCESS');
		$this->view->call($task, $genre);
	}

	/**
	 * Method to update genre ordering
	 *
	 * @since   2.1
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

		$model = ES::model('Audios');

		for ($i = 0; $i < count($cid); $i++) {

			$id = $cid[$i];
			$order = $ordering[$i];

			$model->updateGenresOrdering($id, $order);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_ORDERING_UPDATED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Move up the ordering
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function moveUp()
	{
		$direction = $this->input->get('direction', 'asc');

		if ($direction == 'desc') {
			return $this->move(1);
		}

		return $this->move(-1);
	}

	/**
	 * Move down the ordering
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function moveDown()
	{
		$direction = $this->input->get('direction', 'asc');

		if ($direction == 'desc') {
			return $this->move(-1);
		}

		return $this->move(1);
	}

	/**
	 * Allow caller to move the ordering up/down
	 *
	 * @since   2.1
	 * @access  public
	 */
	private function move($index)
	{
		$layout = $this->input->get('layout', '', 'cmd');

		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			return $this->view->exception('COM_ES_AUDIO_GENRES_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$table = ES::table('audiogenre');
			$table->load($id);

			$table->move($index);
		}

		$this->view->setMessage('COM_ES_AUDIO_GENRE_ORDERED_SUCCESSFULLY');

		return $this->view->call('move', $layout);
	}

}
