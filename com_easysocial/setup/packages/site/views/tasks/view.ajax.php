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

class EasySocialViewTasks extends EasySocialSiteView
{
	/**
	 * Confirmation to delete a milestone
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDeleteMilestone()
	{
		$id = $this->input->get('id', 0, 'int');
		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('return', $return);

		$output = $theme->output('site/tasks/dialogs/delete.milestone');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post process after milestone is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete($milestone, $cluster)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after task is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteTask($milestone, $cluster, $task)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after milestone is resolved
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function resolve($milestone, $cluster)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after milestone is unresolved
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unresolve($milestone, $cluster)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after task is resolved
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function resolveTask($milestone, $cluster, $task)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after task is unresolved
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unresolveTask($milestone, $cluster, $task)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after saving a task
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveTask($milestone, $task, $cluster)
	{
		// Get the assignee
		$assignee = ES::user($task->user_id);

		// Get the contents
		$theme = ES::themes();
		$theme->set('milestone', $milestone);
		$theme->set('cluster', $cluster);
		$theme->set('task', $task);
		$theme->set('assignee', $assignee);

		$output = $theme->output('site/tasks/item/task');

		return $this->ajax->resolve($output);
	}

	/**
	 * Get the tasks of the milestone
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function viewOpenTasks()
	{

		$milestoneId = $this->input->get('milestone_id', '', 'int');
		$cluster_id = $this->input->get('cluster_id', '', 'int');
		$type = $this->input->get('cluster_type', '', 'string');

		$cluster = ES::cluster($type, $cluster_id);

		$options = array('open' => true, 'due' => true, 'uid' => $cluster_id);

		$model = ES::model('Tasks');

		$tasks = $model->getTasks($milestoneId, $options);

		$theme = ES::themes();
		$output = array();

		foreach ($tasks as $task) {
			$theme->set('task', $task);
			$theme->set('assignee', ES::user($task->user_id));
			$theme->set('cluster', $cluster);

			$output[] = $theme->output('site/tasks/item/task');
		}

		return $this->ajax->resolve($output);
	}

	/**
	 * Render the edit task form
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function editTask()
	{
		// Get the task id
		$taskId = $this->input->get('task_id', 0, 'int');

		$task = ES::table('Task');
		$task->load($taskId);

		$cluster = ES::cluster($task->type, $task->uid);

		$assignee = '';
		if ($task->user_id) {
			$assignee = ES::user($task->user_id);
		}

		$theme = ES::themes();
		$theme->set('task', $task);
		$theme->set('assignee', $assignee);
		$theme->set('due', ES::date($task->due)->toFormat('d-m-Y'));
		$theme->set('cluster', $cluster);

		$output = $theme->output('site/tasks/item/edittask');

		return $this->ajax->resolve($output, $taskId);
	}

	/**
	 * Post processing after saving a edited task
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function saveEditedTask($task, $cluster)
	{
		// Get the updated assignee
		$assignee = ES::user($task->user_id);

		$theme = ES::themes();
		$theme->set('task', $task);
		$theme->set('assignee', $assignee);
		$theme->set('cluster', $cluster);

		$output = $theme->output('site/tasks/item/task');

		return $this->ajax->resolve($output);
	}
}
