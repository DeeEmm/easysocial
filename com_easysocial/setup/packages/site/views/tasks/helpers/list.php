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

class EasySocialViewTasksListHelper extends EasySocial
{
	/**
	 * Return tasks data for user
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getData($userId, $hidePersonal)
	{
		static $_cache = array();

		if (!isset($_cache[$userId])) {

			$model = ES::model('Tasks');
			$result = $model->getItems($userId, $hidePersonal);
			$counters = $model->getUserTaskCounters($userId);

			// If there are tasks, we need to bind them with the table.
			$tasks = array();

			if ($result) {

				foreach ($result as $row) {
					$task = ES::table('Task');
					$task->bind($row);
					$task->cluster = '';

					if ($task->uid && $task->type) {
						$cluster = ES::cluster($task->type, $task->uid);

						// Check for cluster privacy
						if (!$cluster->canViewItem()) {

							// Re-adjust the counters
							$counters[$task->type]--;
							$counters['total']--;

							if ($task->isResolved()) {
								$counters['resolved']--;
							} else {
								$counters['unresolved']--;
							}

							continue;
						}

						$task->cluster = $cluster;
					}

					$tasks[] = $task;
				}
			}

			$data = new stdClass();
			$data->tasks = $tasks;
			$data->counters = $counters;

			$_cache[$userId] = $data;
		}

		return $_cache[$userId];
	}

	/**
	 * Return tasks data for user
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getClusterData($cluster, $options)
	{
		static $_cache = array();

		$idx = $cluster->id;

		if (!isset($_cache[$idx])) {

			$model = ES::model('Tasks');
			$milestones = $model->getMilestones($cluster->id, $cluster->getType(), $options);

			$counters = array();
			$counters['tasks'] = $model->getTotalTasksForCluster($cluster);
			$counters['milestones'] = $model->getTotalMilestones($cluster);

			$data = new stdClass();
			$data->milestones = $milestones;
			$data->counters = $counters;

			$_cache[$idx] = $data;
		}

		return $_cache[$idx];
	}
}
