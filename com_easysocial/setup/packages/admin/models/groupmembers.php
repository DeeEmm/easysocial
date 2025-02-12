<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelGroupMembers extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('groupmembers', $config);
	}

	public function initStates()
	{
		$ordering = $this->getUserStateFromRequest('ordering', 'a.id');
		$direction = $this->getUserStateFromRequest('direction', 'asc');

		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);

		parent::initStates();
	}

	public function getItems($options = [])
	{
		$db = ES::db();
		$sql = $db->sql();

		$includeBlockedUser = ES::normalize($options, 'includeBlockUser', false);

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('a.*');

		$sql->innerjoin('#__users', 'b');
		$sql->on('a.uid', 'b.id');

		if (!$includeBlockedUser) {
			$sql->where('b.block', 0);
		}

		if (!empty($options['groupid'])) {
			$sql->where('cluster_id', $options['groupid']);
		}

		if (isset($options['state'])) {
			$sql->where('state', $state);
		} else {
			$sql->where('state', SOCIAL_GROUPS_MEMBER_INVITED, '!=');
		}

		if (isset($options['admin'])) {
			$sql->where('admin', $options['admin']);
		}

		$search = $this->getState('search');

		if (!empty($search)) {
			$sql->where('(');
			$sql->where('b.name' , '%' . $search . '%' , 'LIKE' , 'OR');
			$sql->where('b.username' , '%' . $search . '%' , 'LIKE' , 'OR');
			$sql->where('b.email' , '%' . $search . '%' , 'LIKE' , 'OR');
			$sql->where(')');
		}

		$ordering = $this->getState('ordering');

		if (!empty($ordering)) {
			$direction = $this->getState('direction');

			if ($ordering === 'username') {
				$ordering = 'b.username';
			}

			if ($ordering === 'name') {
				$ordering = 'b.name';
			}

			if ($ordering === 'id') {
				$ordering = 'b.id';
			}

			if ($ordering === 'state') {
				$ordering = 'a.state';
			}

			$sql->order($ordering, $direction);	
		}

		$limit = $this->getState('limit');

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			// Get the list of users
			$result = parent::getData($sql->getSql());
		}

		if (!$limit) {
			$db->setQuery($sql);

			$result = $db->loadObjectList();
		}

		$members = [];

		foreach ($result as $row) {
			$member = ES::table('GroupMember');
			$member->bind($row);

			$members[] = $member;
		}

		return $members;
	}
}
