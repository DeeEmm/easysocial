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

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

class EasySocialModelSubscriptions extends EasySocialModel
{
	private $data = null;
	protected $pagination = null;

	protected $limitstart = null;
	protected $limit = null;

	public function __construct()
	{
		parent::__construct('subscriptions');
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 *
	 */
	private function _getSubcribers( $uuid, $uType, $userId = null )
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select( '#__social_subscriptions' );
		$sql->column( 'created_by' );
		$sql->where( 'type', $uType );
		$sql->where( 'uid', $uuid );

		if( ! is_null( $userId ) )
		{
			$sql->where( 'user_id', $userId );
		}

		$sql->order( 'id', 'desc' );

		$db->setQuery( $sql );
		$list   = $db->loadColumn();

		return $list;
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 *
	 */
	private function _getSubscribersCount( $uuid, $uType )
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select( '#__social_subscriptions' );
		$sql->where( 'type', $uType );
		$sql->where( 'uid', $uuid );

		$db->setQuery( $sql->getTotalSql() );
		$cnt   = $db->loadResult();
		return $cnt;
	}


	/**
	 * check if user has already subscribed to an item.
	 *
	 * @since	2.0
	 * @access	public
	 *
	 */
	public function isFollowing( $uid , $type , $userId )
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select( '#__social_subscriptions' );
		$sql->column( 'id' );
		$sql->where( 'uid' , $uid );
		$sql->where( 'type' , $type );
		$sql->where( 'user_id' , $userId );

		$db->setQuery( $sql );

		$isFollower = (bool) $db->loadResult();

		return $isFollower;
	}


	/**
	 * Retrieves all follower posted by specific user, in conjuction with GDPR compliance.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getFollowerGDPR($userId, $options = array())
	{
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT b.* FROM ' . $db->quoteName('#__social_subscriptions') .' AS b ';
		$query[] = 'LEFT JOIN' . $db->quoteName('#__users') . ' AS a ';
		$query[] = 'ON b.' .  $db->quoteName('uid') . '=' . 'a.' . $db->quoteName('id');
		$query[] = 'WHERE b.' . $db->quoteName('user_id') . '=' . $db->q($userId);

		$exclusion = $this->normalize($options, 'exclusion', null);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND b.' . $db->qn('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		$limit = $this->normalize($options, 'limit', 20);

		if ($limit) {
			$query[] = 'LIMIT ' . $limit;
		}

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Get digest subscribers.
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function getDigestSubscribers($now, $limit = 20)
	{
		$db = ES::db();

		$intervals = array('daily' => 1,
							'weekly' => 7,
							'monthly' => 30);

		$unions = array();

		$query = "select * from (";
		foreach($intervals as $key => $days) {
			$uQuery = " (";
			$uQuery .= " select `user_id` from `#__social_clusters_subscriptions` as `cs`";
			$uQuery .= " inner join `#__users` as `uu` on cs.`user_id` = uu.`id` and uu.`block` = 0";
			$uQuery .= " where cs.`interval` = '$key' and cs.`sent` <= date_sub('$now', INTERVAL $days DAY)";
			$uQuery .= " )";

			$unions[] = $uQuery;
		}
		$query .= implode(" union ", $unions);
		$query .= ") as x limit $limit";

		$db->setQuery($query);
		$results = $db->loadColumn();

		return $results;
	}

	/**
	 * Get user's digest subscriptions.
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function getDigestEmailSubscriptions($now, $userId)
	{
		$db = ES::db();

		$intervals = array('daily' => 1,
							'weekly' => 7,
							'monthly' => 30);

		$unions = array();

		$query = "";
		foreach($intervals as $key => $days) {

			$uQuery = "select a.*, c.`title`, c.`alias`, c.`cluster_type` from `#__social_clusters_subscriptions` as a";
			$uQuery .= "    inner join `#__social_clusters` as c on a.`cluster_id` = c.`id` and c.`state` = '" . SOCIAL_CLUSTER_PUBLISHED . "'";
			$uQuery .= " where a.`interval` = '$key' and a.`user_id` = " . $db->Quote($userId) . " and a.`sent` <= date_sub('$now', INTERVAL $days DAY)";

			$unions[] = $uQuery;
		}

		$query .= implode(" union ", $unions);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}


	/**
	 * Method to get user's subscriptions posts
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getDigestPosts($subscriptions, $now, $type)
	{
		$db = ES::db();

		if (! $subscriptions) {
			return array();
		}

		$unions = array();

		foreach ($subscriptions as $sub) {

			$days = 1;
			if ($sub->interval == 'weekly') {
				$days = 7;
			} else if ($sub->interval == 'monthly') {
				$days = 30;
			}


			$uQuery = "(select a.*, " . $db->Quote('others') . " as `digest_type`";
			$uQuery .= " FROM `#__social_stream` AS a INNER JOIN `#__social_clusters` AS sc ON a.`cluster_id` = sc.`id` and sc.`state` = " . $db->Quote(SOCIAL_CLUSTER_PUBLISHED);

			if ($type == 'events') {
				$uQuery .= " INNER JOIN `#__social_events_meta` as em on sc.`id` = em.`cluster_id` and em.`group_id` = " . $db->Quote($sub->cluster_id);
			}

			$uQuery .= " WHERE a.`state` IN(" . $db->Quote(SOCIAL_STREAM_STATE_PUBLISHED) . "," . $db->Quote(SOCIAL_STREAM_STATE_RESTORED) . ")";

			if ($type != 'events') {
				$uQuery .= " AND a.`cluster_id` = " . $db->Quote($sub->cluster_id);
			}

			$uQuery .= " and a.`created` >= " . $db->Quote($sub->sent) . " and a.`created` <= " . $db->Quote($now);
			if ($type == 'others') {
				$uQuery .= " and a.`context_type` NOT IN (" . $db->Quote('discussions') . "," . $db->Quote('news') . "," . $db->Quote('events') . "," . $db->Quote('tasks') . ")";
			} else {
				$uQuery .= " and a.`context_type` = " . $db->Quote($type);
			}

			$uQuery .= " ORDER BY a.`id` desc";

			// the limit and ordering should respect from subscription.
			if ($sub->count) {
				$uQuery .= " LIMIT " . $sub->count . ")";
			} else {
				$uQuery .= " LIMIT 10)";
			}

			$unions[] = $uQuery;
		}

		$query = implode(" UNION ", $unions);

		$db->setQuery($query);

		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Method to udpate email digeset subscriptions the sent date for next cycle
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateDigestSentOut($subs)
	{
		if (! $subs) {
			// do nothing
			return true;
		}

		$db = ES::db();

		$now = ES::date()->toSql();

		$ids = array();
		foreach($subs as $sub) {
			$ids[] = $sub->id;
		}

		$query = "update `#__social_clusters_subscriptions` set `sent` = " . $db->Quote($now);
		$query .= " where `id` IN (" . implode(',', $ids) . ")";

		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Method to delete email digeset subscriptions
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function deleteClustersSubscriptions($userId)
	{
		$db = ES::db();

		$query = "delete from `#__social_clusters_subscriptions`";
		$query .= " where `user_id` = " . $db->Quote($userId);

		$db->setQuery($query);
		$db->query();
	}

}
