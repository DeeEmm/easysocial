<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerEmoticons extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');
	}

	/**
	 * Removes emoticons from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_EMOTICONS_INVALID_EMOTICON_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$emoticon = ES::table('Emoticon');
			$emoticon->load((int) $id);

			$title = $emoticon->title;

			$emoticon->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_EMOTICON_DELETED', 'emoticons', [
					'name' => $title
				]);
		}
		
		$this->view->setMessage('COM_ES_EMOTICONS_DELETED');
		return $this->view->call(__FUNCTION__, $task);
	}

	/**
	 * Toggles the publish state for the emoticons
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_EMOTICONS_INVALID_EMOTICON_ID_PROVIDED');
		}

		$task = $this->getTask();
		$actionString = $task == 'unpublish' ? 'COM_ES_ACTION_LOG_EMOTICON_UNPUBLISHED' : 'COM_ES_ACTION_LOG_EMOTICON_PUBLISHED';

		foreach ($ids as $id) {
			$emoticon = ES::table('Emoticon');
			$emoticon->load((int) $id);

			$emoticon->$task();

			$this->actionlog->log($actionString, 'emoticons', [
					'name' => $emoticon->title,
					'link' => 'index.php?option=com_easysocial&view=emoticons&layout=form&id=' . $emoticon->id
				]);
		}

		$message = 'COM_ES_EMOTICONS_PUBLISHED';

		if ($task == 'unpublish') {
			$message = 'COM_ES_EMOTICONS_UNPUBLISHED';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task);
	}

	/**
	 * Saves a emoticon from the back end
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		// Get the emoticon id from the request
		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', 'image', 'string');

		$emoticon = ES::table('Emoticon');

		if ($id) {
			$emoticon->load($id);
		}

		$post = $this->input->post->getArray();

		if (empty($post['title'])) {
			$this->view->setMessage('COM_ES_EMOTICONS_EMPTY_TITLE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $emoticon);
		}

		// Validate for duplicate title 
		$model = ES::model('emoticons');
		$exists = $model->validateTitle($post['title'], $emoticon->id);

		if ($exists) {
			$this->view->setMessage('COM_ES_EMOTICONS_DUPLICATE_TITLE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $emoticon);
		}

		unset($post['type']);

		$emoticon->bind($post);
		$emoticon->created = ES::date()->toSql();

		$image = true;

		if ($type == 'unicode') {
			$icon = $this->input->get('emoji', '', 'string');

			if (empty($icon) && !$emoticon->id) {
				$image = false;
			}
		} else {
			$icon = $this->input->files->get('image');
			if (empty($icon['tmp_name']) && !$emoticon->id) {
				$image = false;
			}
		}

		if (!$image) {
			$this->view->setMessage('COM_ES_EMOTICONS_NO_IMAGE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $emoticon);
		}

		$emoticon->store();
		$emoticon->uploadIcon($icon, $type);

		$actionString = $id ? 'COM_ES_ACTION_LOG_EMOTICON_UPDATED' : 'COM_ES_ACTION_LOG_EMOTICON_CREATED';

		$this->actionlog->log($actionString, 'emoticons', [
				'name' => $emoticon->title,
				'link' => 'index.php?option=com_easysocial&view=emoticons&layout=form&id=' . $emoticon->id
			]);

		$this->view->setMessage('COM_ES_EMOTICONS_UPDATED_SUCCESS');
		return $this->view->call(__FUNCTION__, $this->getTask(), $emoticon);
	}
}
