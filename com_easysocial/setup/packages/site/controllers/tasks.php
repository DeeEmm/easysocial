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

class EasySocialControllerTasks extends EasySocialController
{
	/**
	 * Allows caller to delete a task
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteTask()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the task
		$id = $this->input->get('id', 0, 'int');
		$task = ES::table('Task');
		$task->load($id);

		$milestone = ES::table('Milestone');
		$milestone->load($task->milestone_id);

		// Get the cluster
		$cluster = ES::cluster($task->type, $task->uid);

		if (!$id || !$task->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		if (!$cluster->canDeleteTasks()) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ACCESS');
		}

		$task->delete();

		ES::points()->assign('events.task.delete', 'com_easysocial', $this->my->id);

		return $this->view->call(__FUNCTION__, $milestone, $cluster, $task);
	}

	/**
	 * Allows caller to resolve a task
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function resolveTask()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the milestone
		$id = $this->input->get('id', 0, 'int');
		$task = ES::table('Task');
		$task->load($id);

		$milestone = ES::table('Milestone');
		$milestone->load($task->milestone_id);

		// Get the cluster
		$cluster = ES::cluster($task->type, $task->uid);

		if (!$id || !$task->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		// Mark it as resolve
		$task->resolve();

		// Assign points
		ES::points()->assign($cluster->getType() . 's.task.resolve', 'com_easysocial', $this->my->id);

		// Get the app params
		$app = $cluster->getApp('tasks');
		$params = $app->getParams();

		if ($params->get('notify_complete_task', true)) {
			// Get the redirection url
			$url = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'milestoneId' => $milestone->id), false);

			$cluster->notifyMembers('task.completed', array('userId' => $this->my->id, 'id' => $task->id, 'title' => $task->title, 'content' => $milestone->description, 'permalink' => $url, 'milestone' => $milestone->title));
		}

		return $this->view->call(__FUNCTION__, $milestone, $cluster, $task);
	}

	/**
	 * Allows caller to unresolve a task
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function unresolveTask()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the milestone
		$id = $this->input->get('id', 0, 'int');
		$task = ES::table('Task');
		$task->load($id);

		$milestone = ES::table('Milestone');
		$milestone->load($task->milestone_id);

		// Get the cluster
		$cluster = ES::cluster($task->type, $task->uid);

		if (!$id || !$task->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		// Mark it as resolve
		$task->unresolve();

		// Assign points
		ES::points()->assign($cluster->getType() . 's.task.unresolve', 'com_easysocial', $this->my->id);

		// Get the app params
		$app = $cluster->getApp('tasks');
		$params = $app->getParams();

		if ($params->get('notify_uncomplete_task', true)) {
			$url = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'milestoneId' => $milestone->id), false);
			$cluster->notifyMembers('task.completed', array('userId' => $this->my->id, 'id' => $task->id, 'title' => $task->title, 'content' => $milestone->description, 'permalink' => $url, 'milestone' => $milestone->title));
		}

		return $this->view->call(__FUNCTION__, $milestone, $cluster, $task);
	}

	/**
	 * Allows caller to create a new task
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function saveTask()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the milestone
		$milestoneId = $this->input->get('milestoneId', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($milestoneId);

		$cluster = ES::cluster($milestone->type, $milestone->uid);

		if (!$milestoneId || !$milestone->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		if (!$cluster->canCreateTasks()) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ACCESS');
		}

		// Determines if this is a new record
		$id = $this->input->get('id', 0, 'int');
		$task = ES::table('Task');
		$task->load($id);

		// Get the task properties
		$task->uid = $milestone->uid;
		$task->type = $milestone->type;
		$task->milestone_id = $milestone->id;
		$task->title = $this->input->get('title', '', 'string');

		$dueDate = ES::date($this->input->get('due', '', 'default'))->toSql();
		$task->due = $dueDate;
		$task->user_id = $this->input->get('assignee', 0, 'int');
		$task->state = SOCIAL_TASK_UNRESOLVED;

		if (!$task->title) {
			return $this->view->setMessage('', ES_ERROR);
		}

		if (!$task->description) {
			$task->description = '';
		}

		// Save the task
		$state = $task->store();

		if ($state && !$id) {
			ES::points()->assign($cluster->getType() . 's.task.create', 'com_easysocial', $this->my->id);

			// Get the app params
			$app = $cluster->getApp('tasks');
			$params = $app->getParams();

			// Send notification
			if ($params->get('notify_new_task', true)) {
				$url = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'milestoneId' => $milestone->id), false);
				$cluster->notifyMembers('task.create', array('userId' => $this->my->id, 'id' => $task->id, 'title' => $task->title, 'content' => $milestone->description, 'permalink' => $url, 'milestone' => $milestone->title));
			}

			// Create stream
			$task->createStream('createTask');
		}

		return $this->view->call(__FUNCTION__, $milestone, $task, $cluster);
	}

	/**
	 * Allows caller to save an edited task
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function saveEditedTask()
	{
		ES::requireLogin();
		ES::checkToken();

		$taskId = $this->input->get('taskId', 0, 'int');
		$assignee = $this->input->get('assignee', 0, 'int');
		$due = $this->input->get('due', '', 'default');
		$title = $this->input->get('title', '', 'string');

		$task = ES::table('Task');
		$task->load($taskId);

		$cluster = ES::cluster($task->type, $task->uid);

		if (!$taskId || !$task->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		if (!$cluster->canEditTasks()) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ACCESS');
		}

		$task->title = $title;
		$task->user_id = $assignee;
		$task->due = ES::date($due)->toSql();

		if (!$task->title) {
			return $this->view->setMessage('', ES_ERROR);
		}

		// Save the edited data
		$task->store();

		return $this->view->call(__FUNCTION__, $task, $cluster);
	}


	/**
	 * Allows caller to delete a milestone
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteMilestone()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the milestone
		$id = $this->input->get('id', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($id);

		// Get the cluster
		$cluster = ES::cluster($milestone->type, $milestone->uid);

		if (!$id || !$milestone->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		if (!$cluster->canDeleteMilestones()) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ACCESS');
		}

		// Try to delete the milestone now
		$milestone->delete();

		// @points: events.milestone.delete
		ES::points()->assign($cluster->getType() . 's.milestone.delete', 'com_easysocial', $milestone->user_id);

		return $this->view->call(__FUNCTION__, $milestone, $cluster);
	}

	/**
	 * Allows caller to unresolve a milestone
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function unresolve()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the milestone
		$id = $this->input->get('id', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($id);

		// Get the cluster
		$cluster = ES::cluster($milestone->type, $milestone->uid);

		if (!$id || !$milestone->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		if (!$cluster->canResolveMilestones()) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ACCESS');
		}

		// Load up the data
		$id = $this->input->get('id', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($id);

		if (!$id || !$milestone->id) {
			return $ajax->reject();
		}

		$milestone->state = SOCIAL_TASK_UNRESOLVED;
		$milestone->store();

		return $this->view->call(__FUNCTION__, $milestone, $cluster);
	}

	/**
	 * Allows caller to resolve a milestone
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function resolve()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the milestone
		$id = $this->input->get('id', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($id);

		// Get the cluster
		$cluster = ES::cluster($milestone->type, $milestone->uid);

		if (!$id || !$milestone->id || !$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ID');
		}

		if (!$cluster->canResolveMilestones()) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_ACCESS');
		}

		// Load up the data
		$id = $this->input->get('id', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($id);

		if (!$id || !$milestone->id) {
			return $this->ajax->reject();
		}

		$milestone->state = SOCIAL_TASK_RESOLVED;
		$milestone->store();

		return $this->view->call(__FUNCTION__, $milestone, $cluster);
	}

	/**
	 * Allows caller to save a milestone
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function saveMilestone()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the object id
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

	   // Get the cluster
		$cluster = ES::cluster($type, $uid);

		// Ensure that it's a valid cluster
		if (!$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_CLUSTER');
		}

		// Test the cluster to see if they are allowed to create a milestone
		if (!$cluster->canCreateMilestones()) {
			return $this->view->exception('APP_EVENT_TASKS_NOT_ALLOWED_HERE');
		}

		// Get the app related to this cluster
		$app = $cluster->getApp('tasks');

		if (!$app || !$app->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_APP');
		}

		// Get the milestone data
		$id = $this->input->get('id', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($id);
		$milestone->title = $this->input->get('title', '', 'string');
		$milestone->uid = (int) $cluster->id;
		$milestone->type = $type;
		$milestone->state = SOCIAL_TASK_UNRESOLVED;

		// @TODO: Check if the assignee is really a node of the cluster
		$milestone->user_id = $this->input->get('user_id', 0, 'int');

		$milestone->description = $this->input->get('description', '', 'default');

		$dueDate = ES::date($this->input->get('due', '', 'default'))->toSql();
		$milestone->due = $dueDate;
		$milestone->owner_id = (int) $this->my->id;

		// Get the redirection url
		$options = array();
		$options['layout'] = 'canvas';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $type;
		$options['id'] = $app->getAlias();

		// Validate the milestone
		if (!$milestone->validate()) {
			$options['customView'] = 'form';
			$redirect = ESR::apps($options, false);

			$this->info->set(false, $milestone->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $cluster, $milestone, $redirect);
		}

		$milestone->store();

		$options['milestoneId'] = $milestone->id;
		$options['customView'] = 'item';

		// Get the redirection url
		$redirect = ESR::apps($options, false);

		// If this is new milestone, perform some tasks
		if (!$id) {

			// Get the application params
			$params = $app->getParams();

			if ($params->get('stream_milestone', true)) {
				$milestone->createStream('createMilestone');
			}

			if ($params->get('notify_milestone', true)) {
				$cluster->notifyMembers('milestone.create', array('userId' => $this->my->id, 'id' => $milestone->id, 'title' => $milestone->title, 'content' => $milestone->getContent(), 'permalink' => $redirect));
			}

			// Add points to the user that updated the event
			ES::points()->assign($type . 's.milestone.create', 'com_easysocial', $this->my->id);
		}

		$this->view->setMessage('APP_EVENT_TASKS_MILESTONE_CREATED');

		return $this->view->call(__FUNCTION__, $cluster, $milestone, $redirect);
	}
}
