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

class TasksViewForm extends SocialAppsView
{
	/**
	 * Displays the application output in the canvas.
	 *
	 * @access	public
	 * @param	int		The user id that is currently being viewed.
	 */
	public function display($uid = null, $docType = null)
	{
		$group = ES::group($uid);

		// Check if the viewer is allowed here.
		if (!$group->canViewItem() || !$group->canAccessTasks()) {
			return $this->redirect($group->getPermalink(false));
		}

		if (!$group->canCreateMilestones()) {
			return $this->redirect($group->getAppPermalink('tasks'));
		}		

		// Get app params
		$params = $this->app->getParams();

		// Load the milestone
		$id = $this->input->get('milestoneId', 0, 'int');
		$milestone = ES::table( 'Milestone' );
		$milestone->load($id);

		$title = 'APP_GROUP_TASKS_TITLE_CREATE_MILESTONE';

		if ($id && $milestone->id) {
			$title = 'APP_GROUP_TASKS_TITLE_EDITING_MILESTONE';
		}

		$title = JText::_($title);

		$this->page->title($title);
		$this->title = $title;

		// get the assignee
		$assignee = null;
		if ($milestone->user_id) {
			$assignee = ES::user($milestone->user_id);
		}

		$this->set('appId', $this->app->id);
		$this->set('milestone', $milestone);
		$this->set('params', $params);
		$this->set('cluster', $group);
		$this->set('assignee', $assignee);

		echo parent::display('themes:/site/tasks/form/default');
	}

}
