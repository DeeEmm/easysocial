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

class EasySocialControllerWorkflows extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'save');
		$this->registerTask('savenew', 'save');
		$this->registerTask('apply', 'save');
		$this->registerTask('savecopy', 'save');
	}

	/**
	 * Duplicates a workflow on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function duplicate()
	{
		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			return $this->view->exception('Invalid ID provided');
		}

		foreach ($ids as $id) {
			$workflow = ES::workflows($id);

			if (!$workflow->id) {
				return $this->view->exception('Invalid Id provided');
			}

			$title = $workflow->getTitle();
			$id = $workflow->id;

			$options = array('copy' => true);

			$workflow->save(array(
				'copy' => true
			));

			$this->actionlog->log('COM_ES_ACTION_LOG_' . strtoupper($workflow->getType()) . '_FORM_DUPLICATED', 'workflows', [
				'name' => $title,
				'link' => 'index.php?option=com_easysocial&view=workflows&layout=form&id=' . $id
			]);
		}

		$this->view->setMessage('COM_ES_WORKFLOW_DUPLICATED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $workflow, $task);
	}

	/**
	 * Save the workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save()
	{
		ES::requireLogin();

		// Get the current task
		$task = $this->getTask();

		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER);

		$post = $this->input->getArray('post');

		// Allow raw input for post fields
		$fields = $this->input->post->get('fields', null, 'raw');
		$post['fields'] = $fields;

		$workflow = ES::workflows($id, $type);
		$workflow->bind($post);

		$copy = $task == 'savecopy' ? true : false;
		$options = array('copy' => $copy);

		$workflow->save($options);

		$actionString = $id ? 'COM_ES_ACTION_LOG_' . strtoupper($workflow->getType()) . '_FORM_UPDATED' : 'COM_ES_ACTION_LOG_' . strtoupper($workflow->getType()) . '_FORM_CREATED';

		$this->actionlog->log($actionString, 'workflows', [
			'name' => $workflow->getTitle(),
			'link' => 'index.php?option=com_easysocial&view=workflows&layout=form&id=' . $workflow->id
		]);

		// Set message.
		$message = 'COM_ES_WORKFLOW_CREATED_SUCCESSFULLY';

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $workflow, $task);
	}

	/**
	 * Delete the worfklow
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function remove()
	{
		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			return $this->view->exception('Invalid ID provided');
		}

		foreach ($ids as $id) {
			$workflow = ES::workflows($id);

			if (!$workflow->id) {
				return $this->view->exception('Invalid Id provided');
			}

			$name = $workflow->getTitle();
			$type = strtoupper($workflow->getType());

			$state = $workflow->delete();

			if (!$state) {
				$this->view->setMessage($workflow->getError(), ES_ERROR);

				return $this->view->call(__FUNCTION__);
			}

			$this->actionlog->log('COM_ES_ACTION_LOG_' . $type . '_FORM_DELETED', 'workflows', [
				'name' => $name
			]);
		}

		$this->view->setMessage('Workflows deleted successfully');
		return $this->view->call(__FUNCTION__);
	}
}
