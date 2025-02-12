<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelEventGuests extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('eventmembers', $config);
	}

	public function initStates()
	{
		parent::initStates();
	}

	public function getItems($options = array())
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

		$eventid = isset($options['eventid']) ? $options['eventid'] : 0;

		$sql->where('cluster_id', $eventid);

		if (isset($options['state'])) {
			$sql->where('state', $state);
		}

		if (isset($options['admin'])) {
			$sql->where('admin', $options['admin']);
		}

		$ordering = $this->getState('ordering');

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
				
			$sql->order($ordering, $direction); 
		}

		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		$guests = $this->bindTable('EventGuest', $result);

		return $guests;
	}
}
