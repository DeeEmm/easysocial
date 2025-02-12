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

class EasySocialModelPageMembers extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('pagemembers', $config);
	}

	public function initStates()
	{
		$ordering = $this->getUserStateFromRequest('ordering', 'a.id');
		$direction = $this->getUserStateFromRequest('direction', 'asc');

		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);

		parent::initStates();
	}

	public function getItems($options = array())
	{
		$db = ES::db();
		$my = ES::user();

		$includeBlockedUser = ES::normalize($options, 'includeBlockUser', false);

		$q = [];
		$q[] = 'select a.* from `#__social_clusters_nodes` as a';
		$q[] = ' INNER JOIN `#__users` as b on a.`uid` = b.`id`';

		if (ES::isBlockEnabled()) {
			$q[] = $this->getUserBlockingJoinQuery($my, 'bus', 'uid', 0, array('clusterColumnAlias' => 'cluster_id'));
		}

		$cond = [];

		if (!$includeBlockedUser) {
			$cond[] = 'b.`block` = 0';
		}

		if (!empty($options['pageid'])) {
			$cond[] = 'a.`cluster_id` = ' . $db->Quote($options['pageid']);
		}

		if (isset($options['state'])) {
			$cond[] = 'a.`state` = ' . $db->Quote($options['state']);
		}

		if (isset($options['admin'])) {
			$cond[] = 'a.`admin` = ' . $db->Quote($options['admin']);
		}

		if (ES::isBlockEnabled()) {
			$cond[] = $this->getUserBlockingClauseQuery($my, 'bus', false);
		}

		// glue the condition
		if ($cond) {
			$q[] = ' WHERE ' . implode(' AND ', $cond);
		}

		// prepare for count sql.
		$countSql = implode(' ', $q);

		// echo $countSql;

		if (!empty($ordering)) {
			$direction = $this->getState('direction');

			if ($ordering == 'username') {
				$ordering = 'b.username';
			} 

			if ($ordering == 'name') {
				$ordering = 'b.name';
			}

			if ($ordering == 'id') {
				$ordering = 'b.id';
			}

			if ($ordering == 'state') {
				$ordering = 'a.state';
			} 

			$q[] = ' ORDER BY ' . $ordering . ' ' . $direction;
		}

		// join query
		$query = implode(' ', $q);

		// echo $query;
		// exit;

		$limit = $this->getState('limit');

		$result = array();

		if ($limit > 0) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($countSql, true);

			// Get the list of users
			$result = parent::getData($query);
		} else {
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}

		$followers = array();

		if ($result) {
			foreach ($result as $row) {
				$follower = ES::table('PageMember');
				$follower->bind($row);

				$followers[] = $follower;
			}
		}

		return $followers;
	}
}
