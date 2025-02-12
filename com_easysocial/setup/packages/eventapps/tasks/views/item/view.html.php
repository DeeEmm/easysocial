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

class TasksViewItem extends SocialAppsView
{
	public function display($eventId = null, $docType = null)
	{
		$event = ES::event($eventId);

		// Check if the viewer is allowed here.
		if (!$event->canViewItem() || !$event->canAccessTasks()) {
			return $this->redirect($event->getPermalink(false));
		}

		// Get app params
		$params = $this->app->getParams();

		$id = $this->input->get('milestoneId', 0, 'int');
		$milestone = ES::table('Milestone');
		$milestone->load($id);

		$this->page->title($milestone->title);

		// Get a list of tasks for this milestone
		$model = ES::model('Tasks');
		$openTasks = $model->getTasks($milestone->id, array('open' => true, 'due' => true));
		$closedTasks = $model->getTasks($milestone->id, array('closed' => true));

		$totalOpen = count($openTasks);
		$totalClosed = count($closedTasks);
		$total = $totalOpen + $totalClosed;
		$percentage = 100;

		if ($total != 0) {
			$percentage = round($totalClosed / $total * 100);
		}

		$this->set('percentage', $percentage);
		$this->set('total', $total);
		$this->set('totalOpen', $totalOpen);
		$this->set('totalClosed', $totalClosed);
		$this->set('openTasks', $openTasks);
		$this->set('closedTasks', $closedTasks);
		$this->set('milestone', $milestone);
		$this->set('params', $params);
		$this->set('cluster', $event);

		echo parent::display('themes:/site/tasks/item/default');
	}
}
