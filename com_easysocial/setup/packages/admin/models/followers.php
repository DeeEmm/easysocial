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

ES::import('admin:/includes/model');

class EasySocialModelFollowers extends EasySocialModel
{
	private $data = null;

	public function __construct()
	{
		parent::__construct('followers');
	}

	/**
	 * Retrieve a list of followers
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTotalFollowers($userId , $options = array())
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_subscriptions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.type' , SOCIAL_TYPE_USER . '.' . SOCIAL_TYPE_USER );
		$sql->where('a.uid' , $userId);

		$sql->join('#__users' , 'uu' , 'INNER');
		$sql->on('a.user_id' , 'uu.id');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('uu.id', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on('uu.id' , 'bus.user_id');
			$sql->on('bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on('uu.id' , 'bus.target_id');
			$sql->on('bus.user_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('uu.block' , '0');

		$db->setQuery($sql);

		$total 	= $db->loadResult();

		return $total;
	}

	/**
	 * Retrieve a list of following items
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTotalFollowing($userId , $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_subscriptions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.type' , SOCIAL_TYPE_USER . '.' . SOCIAL_TYPE_USER);
		$sql->where('a.user_id' , $userId);

		$sql->join('#__users' , 'uu' , 'INNER');
		$sql->on('a.uid' , 'uu.id');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('uu.id', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on('uu.id' , 'bus.user_id');
			$sql->on('bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on('uu.id' , 'bus.target_id');
			$sql->on('bus.user_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('uu.block' , '0');

		$db->setQuery($sql);

		$total 	= $db->loadResult();

		return $total;
	}


	/**
	 * Retrieve a list of followers
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFollowers($userId, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$my = ES::user();

		$sql->select('#__social_subscriptions', 'a');
		$sql->column('a.user_id', 'id');
		$sql->where('a.type', SOCIAL_TYPE_USER . '.' . SOCIAL_TYPE_USER);
		$sql->where('a.uid', $userId);

		$sql->join('#__users', 'uu', 'INNER');
		$sql->on('a.user_id', 'uu.id');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('uu.id', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		if (ES::isBlockEnabled($my)) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on('uu.id', 'bus.user_id');
			$sql->on('bus.target_id', $my->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on('uu.id', 'bus.target_id');
			$sql->on('bus.user_id', $my->id);
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('uu.block', '0');

		$limit = isset($options['limit']) ? $options['limit'] : '';

		if ($limit != 0) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart' , 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			// Get the list of users
			$rows = $this->getData($sql);

		} else {
			$db->setQuery($sql);
			$rows = $db->loadObjectList();
		}

		if (!$rows) {
			return $rows;
		}

		$followers = array();
		$ids = array();

		foreach ($rows as $row) {
			$ids[] = $row->id;
		}

		$followers = ES::user($ids);

		return $followers;
	}

	/**
	 * Retrieve a list of followers
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFollowing($userId , $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$my = ES::user();

		$sql->select('#__social_subscriptions', 'a');
		$sql->column('a.uid', 'id');
		$sql->where('a.type' , SOCIAL_TYPE_USER . '.' . SOCIAL_TYPE_USER);
		$sql->where('a.user_id' , $userId);

		$sql->join('#__users' , 'uu' , 'INNER');
		$sql->on('a.uid' , 'uu.id');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('uu.id', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		if (ES::isBlockEnabled($my)) {
			$sql->leftjoin('#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on('uu.id' , 'bus.user_id');
			$sql->on('bus.target_id', $my->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on('uu.id' , 'bus.target_id');
			$sql->on('bus.user_id', $my->id);
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('uu.block' , '0');

		$limit = isset($options['limit']) ? $options['limit'] : '';

		if ($limit != 0) {
			$this->setState('limit' , $limit);

			// Get the limitstart.
			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart' , 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart' , $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			// Get the list of users
			$rows = $this->getData($sql);
		} else {
			$db->setQuery($sql);

			$rows = $db->loadObjectList();
		}

		if (!$rows) {
			return $rows;
		}

		$followers = array();
		$ids = array();

		foreach ($rows as $row) {
			$ids[] = $row->id;
		}

		$followers = ES::user($ids);

		return $followers;
	}

	public function getTotalSuggestions($userId)
	{
		static $_count = array();

		if (!isset($_count[$userId])) {
			$options = array('countOnly' => true);
			$count = $this->getSuggestions($userId, $options);

			$_count[$userId] = $count;
		}

		return $_count[$userId];
	}


	/**
	 * Retrieve a list of suggested followers
	 *
	 * @since	1.3.26
	 * @access	public
	 */
	public function getSuggestions($userId, $options = array())
	{
		$db = ES::db();
		$rows = array();
		$query = "";

		$countOnly = isset($options['countOnly']) ? $options['countOnly'] : false;

		$query = "SELECT f.`uid`";
		$query .= " FROM `#__users` AS u";

		$query .= " INNER JOIN `#__social_profiles_maps` as upm on u.`id` = upm.`user_id`";
		$query .= " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";

		$query .= " 	INNER JOIN `#__social_subscriptions` AS following";
		$query .= " 		ON following.`user_id` = u.`id` and following.`type` = " . $db->Quote('user.user');
		$query .= " 	INNER JOIN `#__social_subscriptions` AS f";
		$query .= "			ON f.`user_id` = following.`uid` and f.`type` = " . $db->Quote('user.user');
		$query .= " 	LEFT JOIN `#__social_subscriptions` AS follower";
		$query .= " 		ON follower.`user_id` = f.`uid`";
		$query .= " 		AND follower.`uid` = u.`id`";
		$query .= "			AND follower.`type` = " . $db->Quote('user.user');

		if (ES::isBlockEnabled()) {
			// user block
			$query .= " LEFT JOIN " . $db->nameQuote('#__social_block_users') . " as bus";
			$query .= ' ON (';
			$query .= ' f.' . $db->nameQuote('uid') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ') OR (';
			$query .= ' f.' . $db->nameQuote('uid') . ' = bus.' . $db->nameQuote('target_id') ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query .= ')';
		}

		$query .= " WHERE follower.`user_id` IS NULL";
		$query .= " and u.`id` = " . $db->Quote($userId);
		$query .= " and f.`uid` != " . $db->Quote($userId);

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query .= " AND bus." . $db->nameQuote('id') . " IS NULL";
		}

		$query .= " GROUP BY u.`id`, f.`uid`";

		// at this point, no more where claus assignment!
		if ($countOnly) {

			$cntQuery = "select count(1) from (" . $query . ") as x";

			$db->setQuery($cntQuery);
			$result = $db->loadResult();

			return $result;
		}

		// normal pagination
		$max = isset($options['max']) ? $options['max'] : '';
		if ($max) {
			$query .= " limit $max";

			$db->setQuery($query);

			$rows = $db->loadObjectList();

		} else {
			// for pagination
			$limit = $this->normalize($options, 'limit', ES::getLimit('followersLimit'));

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);

			$query .= " limit $limitstart, $limit";

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// use the cache copy if available
			$total = $this->getTotalSuggestions($userId);

			// Set the total number of items.
			$this->setTotalCount($total);

		}

		if (!$rows) {
			return $rows;
		}

		$suggestions = array();
		$ids = array();

		foreach ($rows as $row) {
			$ids[] = $row->uid;
		}

		$suggestions = ES::user($ids);

		return $suggestions;
	}
}
