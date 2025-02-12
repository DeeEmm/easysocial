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

class TasksViewGroups extends SocialAppsView
{
	public function display($uid = null, $docType = null)
	{
		$group = ES::group($uid);

		// Check if the viewer is allowed here.
		if (!$group->canViewItem() || !$group->canAccessTasks()) {
			return $this->redirect($group->getPermalink(false));
		}

		$this->setTitle('APP_TASKS_APP_TITLE');

		// Get app params
		$params = $this->app->getParams();
		$options = array();

		// Determines if we should populate completed milestones
		if ($params->get('display_completed_milestones', true)) {
			$options['completed'] = true;
		}

		$helper = ES::viewHelper('tasks', 'list');
		$data = $helper->getClusterData($group, $options);

		$milestones = $data->milestones;
		$counters = $data->counters;


		$this->set('counters', $counters);
		$this->set('milestones', $milestones);
		$this->set('params', $params);
		$this->set('cluster', $group);
		$this->set('user', false);

		echo parent::display('themes:/site/tasks/default/default');
	}

	public function sidebar($moduleLib, $cluster)
	{
		$helper = ES::viewHelper('tasks', 'list');

		// Get app params
		$params = $this->app->getParams();

		$options = array();

		// Determines if we should populate completed milestones
		if ($params->get('display_completed_milestones', true)) {
			$options['completed'] = true;
		}

		$data = $helper->getClusterData($cluster, $options);

		$milestones = $data->milestones;
		$counters = $data->counters;

		$this->set('counters', $counters);
		$this->set('app', $this->app);
		$this->set('milestones', $milestones);
		$this->set('moduleLib', $moduleLib);
		$this->set('cluster', $cluster);

		echo parent::display('themes:/site/tasks/default/sidebar.cluster');
	}
}
