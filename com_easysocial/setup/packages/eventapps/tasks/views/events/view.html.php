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

class TasksViewEvents extends SocialAppsView
{
	public function display($eventId = null, $docType = null)
	{
		$event = ES::event($eventId);

		// Check if the viewer is allowed here.
		if (!$event->canViewItem() || !$event->canAccessTasks()) {
			return $this->redirect($event->getPermalink(false));
		}

		$access = $event->getAccess();

		if (!$access->get('tasks.enabled', true)) {
			return false;
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
		$data = $helper->getClusterData($event, $options);

		$milestones = $data->milestones;
		$counters = $data->counters;

		$this->set('counters', $counters);
		$this->set('app', $this->app);
		$this->set('milestones', $milestones);
		$this->set('params', $params);
		$this->set('cluster', $event);
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
