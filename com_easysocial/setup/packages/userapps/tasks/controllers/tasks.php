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

class TasksControllerTasks extends SocialAppsController
{
	/**
	 * Unresolve a task
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function unresolve()
	{
		// Check for request forgeries.
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Load the task ORM
		$id = $this->input->get('id', 0, 'int');
		$task = ES::table('Task');
		$state = $task->load($id);

		// Title should never be empty.
		if (!$id || !$state) {
			return $this->ajax->reject( JText::_( 'APP_USER_TASKS_INVALID_ID_PROVIDED' ) );
		}

		// Title should never be empty.
		if ($task->user_id != $this->my->id) {
			return $this->ajax->reject( JText::_( 'APP_USER_TASKS_NO_ACCESS' ) );
		}

		if (!$task->unresolve()) {
			return $this->ajax->reject($task->getError());
		}

		return $this->ajax->resolve();
	}

	/**
	 * When a note is stored, this method would be invoked.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function resolve()
	{
		// Check for request forgeries.
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Load the task ORM
		$id = $this->input->get('id', 0, 'int');
		$task = ES::table('Task');
		$state = $task->load($id);

		// Title should never be empty.
		if (!$id || !$state) {
			return $this->ajax->reject( JText::_( 'APP_USER_TASKS_INVALID_ID_PROVIDED' ) );
		}

		// Title should never be empty.
		if ($task->user_id != $this->my->id) {
			return $this->ajax->reject(JText::_('APP_USER_TASKS_NO_ACCESS'));
		}

		if (!$task->resolve()) {
			return $this->ajax->reject($task->getError());
		}

		// Return the ajax response.
		return $this->ajax->resolve();
	}

	/**
	 * When a note is stored, this method would be invoked.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save()
	{
		// Check for request forgeries.
		ES::checkToken();
		ES::requireLogin();

		$title = $this->input->get('title', '', 'string');

		// Title should never be empty.
		if (!$title) {
			return $this->ajax->reject(JText::_('APP_USER_TASKS_EMPTY_TITLE'));
		}

		$task = ES::table('Task');
		$task->title = $title;
		$task->user_id = $this->my->id;
		$task->due = '0000-00-00 00:00:00';

		// By default the state is unresolved.
		$task->state = SOCIAL_TASK_UNRESOLVED;

		$task->milestone_id = 0;
		$task->uid = 0;
		$task->type = '';
		$task->description = '';

		// Store the note.
		if ($task->store()) {
			$stream	= ES::stream();

			$data = $stream->getTemplate();
			$data->setActor($this->my->id, SOCIAL_STREAM_ACTOR_TYPE_USER );
			$data->setContext( $task->id, SOCIAL_STREAM_CONTEXT_TASKS);
			$data->setVerb( 'add' );
			$data->setType( 'mini' );
			$data->setAccess('core.view');

			$stream->add($data);
		}

		// Get the theme
		$theme = ES::themes();
		$theme->set('user', $this->my);
		$theme->set('task', $task);
		$contents = $theme->output('apps/user/tasks/profile/item');

		// Return the ajax response.
		return $this->ajax->resolve($contents);
	}

	/**
	 * When a note is stored, this method would be invoked.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$task = ES::table('Task');
		$state = $task->load($id);

		// Title should never be empty.
		if (!$id || !$state) {
			return $this->ajax->reject('APP_USER_TASKS_INVALID_ID_PROVIDED');
		}

		// Title should never be empty.
		if ($task->user_id != $this->my->id) {
			return $this->ajax->reject('APP_USER_TASKS_NO_ACCESS');
		}

		if (!$task->delete()) {
			return $this->ajax->reject($task->getError());
		}

		return $this->ajax->resolve();
	}
}
