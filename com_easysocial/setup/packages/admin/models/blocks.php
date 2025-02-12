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

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

class EasySocialModelBlocks extends EasySocialModel
{
	private $data = null;

	public function __construct( $config = array() )
	{
		parent::__construct('blocks', $config);
	}

	/**
	 * Determines if the user has been blocked by another user
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isBlocked($userId, $targetId, $twoWay = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_block_users');
		$sql->column('COUNT(1)');

		if ($twoWay === true) {
			$sql->where('(');
			$sql->where('user_id', $userId);
			$sql->where('target_id', $targetId);

			$sql->where('target_id', $userId, '=', 'OR');
			$sql->where('user_id', $targetId);
			$sql->where(')');
		} else {
			$sql->where('user_id', $userId);
			$sql->where('target_id', $targetId);
		}

		$db->setQuery($sql);

		return $db->loadResult() > 0;
	}

	/**
	 * Retrieves a list of blocked users
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getBlockedUsers($userId, $idOnly = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_block_users');
		$sql->where('user_id', $userId);

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		if ($idOnly) {
			$ids = [];

			foreach ($result as $row) {
				$ids[] = $row->target_id;
			}

			return $ids;
		}


		$users = array();

		foreach ($result as $row) {

			$obj = new stdClass();

			$obj->user = ES::user($row->target_id);
			$obj->reason = $row->reason;

			if (!$obj->user->id) {
				continue;
			}

			$users[] = $obj;
		}

		return $users;
	}

	/**
	 * Retrieves a list of users who blocked you
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getUsersBlocked($userId, $idOnly = false, $twoWay = false)
	{
		$db = ES::db();
		// $sql = $db->sql();


		// $sql->column('a.user_id');
		// $sql->select('#__social_block_users', 'a');
		// $sql->where('a.target_id', $userId);
		// $db->setQuery($sql);


		$query = 'select IF(`a`.`user_id` = ' . $db->Quote($userId) . ', a.target_id, a.user_id) as uid';
		if (!$idOnly) {
			$query .= ' a.reason';
		}
		$query .= ' from `#__social_block_users` as a';
		$query .= ' where (';
		$query .= ' a.target_id = ' . $db->Quote($userId);
		$query .= ' OR ';
		$query .= ' a.user_id = ' . $db->Quote($userId);
		$query .= ')';

		$db->setQuery($query);

		if ($idOnly) {
			$result = $db->loadColumn();
		} else {
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		if ($idOnly) {
			return $result;
		}

		$users = array();

		foreach ($result as $row) {

			$obj = new stdClass();

			$obj->user = ES::user($row->uid);
			$obj->reason = $row->reason;

			$users[] = $obj;
		}

		return $users;
	}

}
