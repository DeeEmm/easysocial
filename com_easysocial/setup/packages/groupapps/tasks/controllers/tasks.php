<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class TasksControllerTasks extends SocialAppsController
{
	/**
	 * Displays delete confirmation dialog
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete()
	{
		// Check for request forgeries.
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		$ajax 		= ES::ajax();

		// Get the group
		$groupId 	= $this->input->get('groupId' , 0, 'int');
		$group		= ES::group( $groupId );

		$my = ES::user();

		// Check if the user is allowed to create a discussion
		if( !$group->isMember() && !$my->isSiteAdmin() )
		{
			return $ajax->reject();
		}

		$id 	= $this->input->get('id' , 0, 'int');
		$task 	= ES::table( 'Task' );
		$task->load( $id );

		if( !$id || !$task->id || $task->uid != $group->id )
		{
			return $ajax->reject();
		}

		$task->delete();

		// @points: groups.task.delete
		$points = ES::points();
		$points->assign( 'groups.task.delete' , 'com_easysocial' , $my->id );

		return $ajax->resolve();
	}

	/**
	 * Displays delete confirmation dialog
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Check for request forgeries.
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		$ajax 		= ES::ajax();

		// Get the group
		$groupId 	= $this->input->get('groupId' , 0, 'int');
		$group		= ES::group( $groupId );

		$my = ES::user();

		// Check if the user is allowed to create a discussion
		if( !$group->isMember() && !$my->isSiteAdmin())
		{
			return $ajax->reject();
		}

		$theme 	= ES::themes();
		$contents 	= $theme->output( 'apps/group/tasks/views/dialog.delete.task' );

		return $ajax->resolve( $contents );
	}

	/**
	 * Allows caller to resolve a task
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function resolve()
	{
		// Check for request forgeries
		ES::checkToken();

		// Only allow logged in users
		ES::requireLogin();

		// Load up ajax library
		$ajax 	= ES::ajax();

		// Get the current group and logged in user
		$groupId 	= $this->input->get('groupId' , 0, 'int');
		$group 		= ES::group( $groupId );
		$my 		= ES::user();

		if( !$group || !$groupId )
		{
			return $ajax->reject( 'failed' );
		}

		// Test if the current user is a member of this group.
		if( !$group->isMember() && !$my->isSiteAdmin())
		{
			return $ajax->reject();
		}

		// Determines if this is a new record
		$id 		= $this->input->get('id' , 0, 'int');
		$task 		= ES::table( 'Task' );
		$task->load( $id );

		$task->resolve();

		// @points: groups.task.resolve
		$points = ES::points();
		$points->assign( 'groups.task.resolve' , 'com_easysocial' , $my->id );

		// Get the app
		$app 	= $this->getApp();

		// Get the application params
		$params = $app->getParams();

		if( $params->get( 'notify_complete_task' , true ) )
		{
			$milestone 	= ES::table( 'Milestone' );
			$milestone->load( $task->milestone_id );

			// Get the redirection url
			$url 	= FRoute::apps( array( 'layout' => 'canvas' , 'customView' => 'item' , 'uid' => $group->getAlias() , 'type' => SOCIAL_TYPE_GROUP , 'id' => $app->getAlias() , 'milestoneId' => $milestone->id ) , false );

			$group->notifyMembers( 'task.completed' , array( 'userId' => $my->id , 'id' => $task->id , 'title' => $task->title , 'content' => $milestone->description , 'permalink' => $url , 'milestone' => $milestone->title ) );
		}
		return $ajax->resolve();
	}

	/**
	 * Allows caller to unresolve a task
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function unresolve()
	{
		// Check for request forgeries
		ES::checkToken();

		// Only allow logged in users
		ES::requireLogin();

		// Load up ajax library
		$ajax = ES::ajax();

		// Get the current group and logged in user
		$groupId = $this->input->get('groupId' , 0, 'int');
		$group = ES::group($groupId);
		$my = ES::user();

		if (!$group || !$groupId) {
			return $ajax->reject();
		}

		// Test if the current user is a member of this group.
		if (!$group->isMember() && !$my->isSiteAdmin()) {
			return $ajax->reject();
		}

		// Determines if this is a new record
		$id = $this->input->get('id' , 0, 'int');
		$task = ES::table('Task');
		$task->load($id);

		$task->state = 1;
		$task->store();

		// @points: groups.task.unresolve
		$points = ES::points();
		$points->assign('groups.task.unresolve', 'com_easysocial', $my->id);

		// Get the app
		$app 	= $this->getApp();

		// Get the application params
		$params = $app->getParams();

		if ($params->get('notify_uncomplete_task', true)) {
			$milestone = ES::table('Milestone');
			$milestone->load($task->milestone_id);

			// Get the redirection url
			$url = FRoute::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $group->getAlias(), 'type' => SOCIAL_TYPE_GROUP, 'id' => $app->getAlias(), 'milestoneId' => $milestone->id), false);

			// Only send to the task's owner
			$targets = array($task->user_id);
			
			$group->notifyMembers('task.uncompleted', array( 'targets' => $targets, 'userId' => $my->id, 'id' => $task->id, 'title' => $task->title, 'content' => $milestone->description, 'permalink' => $url, 'milestone' => $milestone->title));
		}

		return $ajax->resolve();
	}


	/**
	 * Allows caller to create a new task given the milestone id and the group id.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function save()
	{
		// Check for request forgeries
		ES::checkToken();

		// Only allow logged in users
		ES::requireLogin();

		// Load up ajax library
		$ajax = ES::ajax();

		// Get the current group and logged in user
		$groupId = $this->input->get('groupId' , 0, 'int');
		$group = ES::group( $groupId );
		$my = ES::user();

		if (!$group || !$groupId) {
			return $ajax->reject();
		}

		// Test if the current user is a member of this group.
		if (!$group->isMember()) {
			return $ajax->reject();
		}

		// Determines if this is a new record
		$id = $this->input->get('id' , 0, 'int');
		$task = ES::table( 'Task' );
		$task->load($id);

		$milestoneI = $this->input->get('milestoneId', 0, 'int');
		$title 	= $this->input->get('title', '', 'string');
		$due = $this->input->get('due', '', 'default');
		$assignee = $this->input->get('assignee', 0, 'int');

		// Save task
		$task->milestone_id = $milestoneId;
		$task->uid = $group->id;
		$task->type = SOCIAL_TYPE_GROUP;
		$task->title = $title;
		$task->due = $due;
		$task->user_id = !$assignee ? $my->id : $assignee;
		$task->state = SOCIAL_STATE_PUBLISHED;

		if (!$task->title) {
			return $ajax->reject();
		}

		$task->store();

		if (!$id) {
			// @points: groups.task.create
			$points = ES::points();
			$points->assign( 'groups.task.create' , 'com_easysocial' , $my->id );

			// Get the app
			$app 	= $this->getApp();

			// Get the application params
			$params = $app->getParams();

			if( $params->get( 'notify_new_task' , true ) )
			{
				$milestone 	= ES::table( 'Milestone' );
				$milestone->load( $milestoneId );

				// Get the redirection url
				$url 	= FRoute::apps( array( 'layout' => 'canvas' , 'customView' => 'item' , 'uid' => $group->getAlias() , 'type' => SOCIAL_TYPE_GROUP , 'id' => $app->getAlias() , 'milestoneId' => $milestoneId ) , false );

				$group->notifyMembers( 'task.create' , array( 'userId' => $my->id , 'id' => $task->id , 'title' => $task->title , 'content' => $milestone->description , 'permalink' => $url , 'milestone' => $milestone->title ) );
			}
		}

		// Get the contents
		$theme 	= ES::themes();
		$theme->set( 'group', $group);
		$theme->set( 'task' , $task );
		$theme->set( 'user'	, ES::user($task->user_id) );

		$output = $theme->output( 'apps/group/tasks/views/item.task' );
		return $ajax->resolve( $output );
	}
}
