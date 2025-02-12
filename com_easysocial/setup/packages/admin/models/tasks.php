<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

class EasySocialModelTasks extends EasySocialModel
{
	public function __construct( $config = array() )
	{
		parent::__construct( 'tasks' , $config );
	}

	/**
	 * Deletes all tasks from a milestone
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function deleteTasks( $milestoneId )
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->delete( '#__social_tasks' );
		$sql->where( 'milestone_id' , $milestoneId );

		$db->setQuery( $sql );

		return $db->Query();
	}

	/**
	 * Retrieves a list of tasks from a milestone
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTasks( $milestoneId  , $options = array() )
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select( '#__social_tasks' );
		$sql->where( 'milestone_id' , $milestoneId );

		// Determines if we should only fetch open tasks
		$open 	= isset($options['open']) ? $options['open'] : false;
		$closed = isset($options['closed']) ? $options['closed'] : false;
		$due = isset($options['due']) ? $options['due'] : false;
		$uid = isset($options['uid']) ? $options['uid'] : false;

		if( $open )
		{
			$sql->where( 'state' , SOCIAL_STATE_PUBLISHED );
		}

		if( $closed )
		{
			$sql->where( 'state' , 2 );
		}

		if ($due) {
			$sql->order('due', 'ASC');
		} else {
			$sql->order('created', 'DESC');
		}

		if ($uid) {
			$sql->where('uid', $uid);
		}

		$db->setQuery( $sql );

		$rows 	= $db->loadObjectList();

		if( !$rows )
		{
			return $rows;
		}

		$tasks 	= array();

		foreach( $rows as $row )
		{
			$task 	= ES::table( 'Task' );
			$task->bind( $row );

			$tasks[]	= $task;
		}

		return $tasks;
	}

	/**
	 * Retrieves a list of milestones for a node
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getMilestones( $uid , $type , $options = array() )
	{
		$db 	= ES::db();
		$query 	= $db->sql();

		$query->select( '#__social_tasks_milestones', 'a' );
		$query->column('a.*');

		if (ES::isBlockEnabled()) {
			$query->leftjoin( '#__social_block_users' , 'bus');

			$query->on('(');
			$query->on( 'a.owner_id' , 'bus.user_id' );
			$query->on( 'bus.target_id', JFactory::getUser()->id);
			$query->on(')');

			$query->on('(', '', '', 'OR');
			$query->on( 'a.owner_id' , 'bus.target_id' );
			$query->on( 'bus.user_id', JFactory::getUser()->id );
			$query->on(')');

			$query->isnull('bus.id');
		}

		$query->where( 'a.uid' , $uid );
		$query->where( 'a.type', $type );

		// Should we fetch completed milestones?
		$completed 	= isset( $options[ 'completed' ] ) ? $options[ 'completed' ] : false;

		if( !$completed )
		{
			$query->where( 'a.state' , SOCIAL_STATE_PUBLISHED );
		}

		$db->setQuery( $query );

		$rows 	= $db->loadObjectList();

		if( !$rows )
		{
			return $rows;
		}

		$milestones 	= array();

		foreach( $rows as $row )
		{
			$milestone 	= ES::table( 'Milestone' );
			$milestone->bind( $row );

			$milestones[]	= $milestone;
		}

		return $milestones;
	}

	/**
	 * Retrieves the total number of tasks a milestone has
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTotalTasks($milestoneId, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select( '#__social_tasks' );
		$sql->column( 'COUNT(1)' );
		$sql->where( 'milestone_id' , $milestoneId );

		$open 	= isset( $options[ 'open' ] ) ? $options[ 'open' ] : false;
		$closed = isset( $options[ 'closed' ] ) ? $options[ 'closed' ] : false;

		if( $open )
		{
			$sql->where( 'state' , SOCIAL_TASK_UNRESOLVED );
		}

		if( $closed )
		{
			$sql->where( 'state' , SOCIAL_TASK_RESOLVED );
		}

		$db->setQuery( $sql );
		$total 	= $db->loadResult();

		return $total;
	}

	/**
	 * Get total tasks for a cluster type
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalTasksForCluster($cluster)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->column('count(1)');
		$query->select('#__social_tasks');
		$query->where('uid' , $cluster->id);
		$query->where('type', $cluster->getType());

		$db->setQuery($query);

		// Get the result.
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Get total milestones for a cluster type
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalMilestones($cluster)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->column('count(1)');
		$query->select('#__social_tasks_milestones');
		$query->where('uid' , $cluster->id);
		$query->where('type', $cluster->getType());

		$db->setQuery($query);

		// Get the result.
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of tasks a milestone has
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getUserTaskCounters($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$counters = array();
		$counters['user'] = $this->getTotalUserTasks($userId);
		$counters['group'] = $this->getTotalClusterTasks($userId, 'group');
		$counters['event'] = $this->getTotalClusterTasks($userId, 'event');
		$counters['resolved'] = $this->getTotalResolved($userId);
		$counters['unresolved'] = $this->getTotalUnresolved($userId);
		$counters['total'] = $counters['user'] + $counters['group'] + $counters['event'];

		return $counters;
	}

	/**
	 * Get total tasks for a cluster type
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalClusterTasks($userId, $clusterType)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->column('count(1)');
		$query->select('#__social_tasks');
		$query->where('user_id' , $userId);
		$query->where('type', $clusterType);

		$db->setQuery($query);

		// Get the result.
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Get total tasks for a user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalUserTasks($userId)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->column('count(1)');
		$query->select('#__social_tasks');
		$query->where('user_id' , $userId);
		$query->where('uid', 0);

		$db->setQuery($query);

		// Get the result.
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of tasks created by a particular user.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalResolved($userId)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->column('count(1)');
		$query->select('#__social_tasks');
		$query->where('user_id' , $userId);
		$query->where('state', SOCIAL_TASK_RESOLVED);

		$db->setQuery($query);

		// Get the result.
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Get total unresolved tasks
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalUnresolved($userId)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->column('count(1)');
		$query->select('#__social_tasks');
		$query->where('user_id' , $userId);
		$query->where('(', '', '', 'AND');
		$query->where('state', SOCIAL_TASK_UNRESOLVED, '=', 'OR');
		$query->where('state', SOCIAL_TASK_UNPUBLISHED, '=', 'OR');
		$query->where(')');

		$db->setQuery($query);

		// Get the result.
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of tasks created by a particular user.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getItems($userId, $hidePersonal = false)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT * FROM ' . $db->qn('#__social_tasks');
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote($userId);

		if ($hidePersonal) {
			$query[] = 'AND ' . $db->qn('uid') . '!=' . $db->Quote(0);
			$query[] = 'AND ' . $db->qn('type') . ' IS NOT NULL';
		}

		$db->setQuery($query);
		$tasks = $db->loadObjectList();

		return $tasks;
	}

	public function getTasksGdpr($userId, $excludeIds, $limit = 20, $options = array())
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT * FROM ' . $db->qn('#__social_tasks') . ' AS a';
		$query[] = 'WHERE a.' . $db->qn('user_id') . '=' . $db->Quote($userId);

		if ($excludeIds) {
			$query[] = 'AND a.`id` NOT IN (' . implode(',', $excludeIds) . ')';
		}

		$query[] = 'LIMIT ' . ($limit + 1);

		$query = implode(' ', $query);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		return $result;
	}
}
