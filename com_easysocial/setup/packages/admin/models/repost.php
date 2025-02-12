<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelRepost extends EasySocialModel
{
	static $_reposts = array();

	function __construct()
	{
		parent::__construct('repost');
	}

	public function getSharesFromArray($keys , $group = SOCIAL_APPS_GROUP_USER)
	{
		$db = ES::db();

		$query = array();

		$query[] = 'SELECT * FROM ' . $db->nameQuote('#__social_shares');
		$query[] = 'WHERE';

		for ($i = 0; $i < count($keys); $i++) {
			$key = $keys[$i];

			$query[] = '(';
			$query[] = $db->nameQuote('uid') . '=' . $db->Quote($key->uid);
			$query[] = 'AND';
			$query[] = $db->nameQuote('element') . '=' . $db->Quote($key->type . '.' . $group);
			$query[] = ')';

			if (next($keys) !== false) {
				$query[] = 'OR';
			}
		}

		$query = implode(' ', $query);

		$db->setQuery($query);

		$likes = $db->loadObjectList();

		return $likes;
	}


	public function setStreamRepostBatch($data)
	{
		$db = ES::db();
		$sql = $db->sql();

		$dataset = array();

		foreach ($data as $item) {
			$element = $item->context_type;

			if ($element != 'shares') {
				$element = 'stream';
				$uid = $item->id;

				$group = ($item->cluster_id) ? $item->cluster_type : SOCIAL_APPS_GROUP_USER;

				$key = $uid . '.' . $element . '.' . $group;

				if (!isset(self::$_reposts[$key])) {
					$dataset[$key][] = $uid;
				}
			}
		}

		// lets build the sql now.
		if ($dataset) {
			$mainSQL = '';
			foreach ($dataset as $keys => $uids) {
				$keys = explode('.',$keys);
				$element = $keys[1];
				$group = $keys[2];

				$ids = implode(',', $uids);
				$element = $element . '.' . $group;

				foreach ($uids as $uid) {
					$key = $uid . '.' . $element;
					self::$_reposts[$key] = array();
				}

				$query = 'select `uid`, `element`, `user_id` from `#__social_shares` where `uid` IN (' . $ids . ')';
				$query .= ' and `element` = ' . $db->Quote($element);

				$mainSQL .= (empty($mainSQL)) ? $query : ' UNION ' . $query;
			}

			// echo $mainSQL;
			// exit;

			$sql->raw($mainSQL);
			$db->setQuery($sql);

			$result = $db->loadObjectList();

			if ($result) {
				foreach ($result as $rItem) {
					$key = $rItem->uid . '.' . $rItem->element;
					self::$_reposts[$key][] = $rItem;
				}
			}
		}
	}

	private function getRepostData($uid, $element)
	{
		$key = $uid . '.' . $element;

		if (!isset(self::$_reposts[$key])) {
			$db = ES::db();
			$sql = $db->sql();

			$query = 'select `uid`, `element`, `user_id` from `#__social_shares` where `uid` = ' . $db->Quote($uid);
			$query .= ' and `element` = ' . $db->Quote($element);

			$sql->raw($query);
			$db->setQuery($query);

			$result = $db->loadObjectList();

			self::$_reposts[$key] = $result ? $result : array();
		}

		return self::$_reposts[$key];
	}

	private function _getRepostCount($uid, $element)
	{
		$key = $uid . '.' . $element;
		$data = $this->getRepostData($uid, $element);

		return count($data);
	}

	/**
	 * Reshare post
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function add($uid, $element, $userId, $content = null, $shareAs = 'user', $clusterId = null, $streamId = null)
	{
		$table = ES::table('Share');

		$table->load(array('uid' => $uid, 'element' => $element, 'user_id' => $userId, 'share_as' => $shareAs));

		if ($table->id) {
			return true;
		}

		$table->uid = $uid;
		$table->element = $element;
		$table->user_id = $userId;
		$table->content = $content;
		$table->share_as = $shareAs;
		$table->params = '';

		$options = [];

		// if share_as == page, we store the cluster id in params
		if ($shareAs == SOCIAL_TYPE_PAGE) {
			$options['clusterId'] = $clusterId;
		}

		if ($streamId) {
			$options['streamId'] = $streamId;
		}

		// lets store the options as params
		if ($options) {
			$params = ES::registry($options);
			$table->params = $params->toString();
		}

		$state = $table->store();

		if ($state) {
			// update repost static variable
			$key = $uid . '.' . $element . '.' . $shareAs;
			$array = isset(self::$_reposts[$key]) ? self::$_reposts[$key] : array();
			$array[] = $table;
			self::$_reposts[$key] = $array;

			return $table;
		}

		return false;
	}

	/**
	 * Delete share object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($uid , $element, $userId)
	{
		$table = ES::table('Share');

		$table->load(array('uid' => $uid, 'element' => $element, 'user_id' => $userId));

		if (empty($table->id)) {
			return false;
		}

		$state =  $table->delete();

		if ($state) {
			// update repost static variable
			$key = $uid . '.' . $element;
			$array = self::$_reposts[$key];

			$new = array();

			foreach ($array as $arr) {
				if ($arr->user_id != $userId) {
					$new = $arr;
				}
			}

			self::$_reposts[$key] = $new;
		}

		return $state;
	}

	public function getCount($uid, $element)
	{
		$count = $this->_getRepostCount($uid, $element);
		return (empty($count)) ? 0 : $count;
	}

	public function getRepostUsers($uid, $element, $idOnly = true)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_shares')
			->column('user_id', '')
			->column('share_as')
			->column('params')
			->where('uid', $uid)
			->where('element', $element);
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if ($idOnly) {
			return $result;
		}

		$users = array();
		$clusters = array();

		if (count($result) > 0) {
			foreach ($result as $row) {
				if ($row->share_as != SOCIAL_TYPE_USER) {
					$params = ES::registry($row->params);
					$clusters[] = ES::cluster($row->share_as, $params->get('clusterId'));
				} else {
					$users[] = ES::user($row->user_id);
				}
			}
		}

		return array_merge($clusters, $users);
	}
}
