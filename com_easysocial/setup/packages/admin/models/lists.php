<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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

class EasySocialModelLists extends EasySocialModel
{
	private $data = null;

	public function __construct()
	{
		parent::__construct('lists');
	}

	/**
	 * Sets a list as the default list
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setDefault($id, $userId)
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		// Update all existing lists to not default.
		$sql->update('#__social_lists');
		$sql->set('default', 0);
		$sql->where('user_id', $userId);

		$db->setQuery($sql);
		$db->Query();

		// Reset the sql pointer
		$sql->clear();

		$sql->update('#__social_lists');
		$sql->set('default', 1);
		$sql->where('id', $id);
		$sql->where('user_id', $userId);
		$db->setQuery($sql);
		$db->Query();

		return true;
	}

	/**
	 * Searches for a friend list.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function search($userId, $search)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_lists');
		$sql->where('user_id', $userId);
		$sql->where('title', '%' . $search . '%', 'LIKE');
		$sql->where('type', SOCIAL_TYPE_FRIEND);

		$db->setQuery($sql);

		$rows = $db->loadObjectList();

		return $rows;
	}

	/**
	 * Retrieves a list of friend list for a specific node id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLists($options = array())
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();
		$query = array();

		$query[] = 'SELECT DISTINCT a.* FROM ' . $db->nameQuote('#__social_lists') . ' AS a';

		// Determine if we should show empty lists
		$showEmpty = isset($options['showEmpty']) ? $options['showEmpty'] : null;
		$type = isset($options['type']) ? $options['type'] : SOCIAL_TYPE_FRIEND;

		if ($showEmpty == '0' && $type == SOCIAL_TYPE_FRIEND) {
			$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_lists_maps') . ' AS b';
			$query[] = 'ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('list_id');

			$query[] = 'INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu';
			$query[] = 'ON uu.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('target_id');

			if (ES::isBlockEnabled()) {
				// user block
				$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

				$query[] = ' ON (';
				$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
				$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
				$query[] = ') OR (';
				$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
				$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
				$query[] = ')';

			}
		}

		$query[] = 'WHERE 1';

		if ($showEmpty == '0' && $type == SOCIAL_TYPE_FRIEND) {
			$query[] = 'AND uu.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

			if (ES::isBlockEnabled()) {
				// user block continue here
				$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
			}
		}

		// Check if caller wants to filter by state.
		$state = isset($options['state']) ? $options['state'] : null;

		if (!is_null($state)) {
			// Ensure that it's an array
			$state = ES::makeArray($state);

			$query[] = ' AND (';

			for($i = 0; $i < count($state); $i++) {
				$query[] = 'a.' . $db->nameQuote('state') .'=' . $db->Quote($state[$i]);

				if (next($state) !== false) {
					$query[] = ' OR ';
				}
			}

			$query[] = ')';
		}

		// Check if the caller wants to filter by user id.
		$user = isset($options['user_id']) ? $options['user_id'] : null;

		if (!is_null($user)) {
			$query[] = 'AND a.' . $db->nameQuote('user_id') . '=' . $db->Quote($user);
		}

		$query[] = 'AND a.' . $db->nameQuote('type') . '=' . $db->Quote($type);

		// Glue query back.
		$query = implode(' ', $query);

		// Set limit
		$countQuery	= str_ireplace('SELECT DISTINCT a.*', 'SELECT COUNT(DISTINCT(a.id))', $query);

		$this->setTotal($countQuery);

		// Get the data.
		$result = $this->getData($query);

		if (!$result) {
			return false;
		}

		$lists = array();

		foreach($result as $row) {
			$list = ES::table('List');
			$list->bind($row);

			// For each list, we get the items that belong to that list
			$items = $list->getItems();
			$list->items = $items;

			$lists[] = $list;
		}

		return $lists;
	}

	/**
	 * Retrieves the list of members from this friend list.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	The unique list id.
	 * @return
	 */
	public function getMembers($id, $idOnly = false, $use4Send = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_lists_maps', 'a');

		if ($idOnly) {
			$sql->column('a.target_id');
		}

		$sql->join('#__users', 'uu', 'INNER');
		$sql->on('a.target_id', 'uu.id');

		// Check if the user is really still their friends or not.
		$sql->join('#__social_friends', 'b', 'INNER');
		$sql->on('a.target_id', 'b.target_id', '=', 'OR');
		$sql->on('a.target_id', 'b.actor_id', '=', 'OR');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users', 'bus');
			if ($use4Send) {
				$sql->on('uu.id', 'bus.target_id');
				$sql->on('bus.user_id', JFactory::getUser()->id);
			} else {
				$sql->on('(');
				$sql->on( 'uu.id' , 'bus.user_id' );
				$sql->on( 'bus.target_id', JFactory::getUser()->id);
				$sql->on(')');

				$sql->on('(', '', '', 'OR');
				$sql->on( 'uu.id' , 'bus.target_id' );
				$sql->on( 'bus.user_id', JFactory::getUser()->id );
				$sql->on(')');
			}

			$sql->isnull('bus.id');
		}

		$sql->where('a.list_id', $id);

		$sql->where('b.state', SOCIAL_FRIENDS_STATE_FRIENDS);

		$sql->where('uu.block', '0');

		$db->setQuery($sql);

		if($idOnly)
		{
			$items 	= $db->loadColumn();
		}
		else
		{
			$items 	= $db->loadObjectList();
		}

		if(!$items)
		{
			return $items;
		}

		return $items;
	}

		/**
		 * Retrieves the list of items from this list.
		 *
		 * @since	2.1
		 * @access	public
		 */
		public function getItems($id, $idOnly = false, $use4Send = false, $type = SOCIAL_TYPE_FRIEND)
		{
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__social_lists_maps', 'a');

			if ($idOnly) {
				$sql->column('a.target_id');
			}

			if ($type == SOCIAL_TYPE_AUDIOS) {
				$sql->column('a.*');
				$sql->join('#__social_audios', 'au');
				$sql->on('a.target_id', 'au.id');

				$sql->where('au.state', SOCIAL_STATE_PUBLISHED);
			}

			if ($type == SOCIAL_TYPE_FRIEND) {
				$sql->join('#__users', 'uu', 'INNER');
				$sql->on('a.target_id', 'uu.id');

				// Check if the user is really still their friends or not.
				$sql->join('#__social_friends', 'b', 'INNER');
				$sql->on('a.target_id', 'b.target_id', '=', 'OR');
				$sql->on('a.target_id', 'b.actor_id', '=', 'OR');

				if (ES::isBlockEnabled()) {
					$sql->leftjoin('#__social_block_users', 'bus');
					if ($use4Send) {
						$sql->on('uu.id', 'bus.target_id');
						$sql->on('bus.user_id', JFactory::getUser()->id);
					} else {

						$sql->on('(');
						$sql->on( 'uu.id' , 'bus.user_id' );
						$sql->on( 'bus.target_id', JFactory::getUser()->id);
						$sql->on(')');

						$sql->on('(', '', '', 'OR');
						$sql->on( 'uu.id' , 'bus.target_id' );
						$sql->on( 'bus.user_id', JFactory::getUser()->id );
						$sql->on(')');
					}

					$sql->isnull('bus.id');
				}

				$sql->where('b.state', SOCIAL_FRIENDS_STATE_FRIENDS);
				$sql->where('uu.block', '0');
			}

			$sql->where('a.list_id', $id);

			if ($type == SOCIAL_TYPE_AUDIOS) {
				$sql->order('a.created', 'ASC');
			}

			$db->setQuery($sql);

			if ($idOnly) {
				$items = $db->loadColumn();
			} else {
				$items = $db->loadObjectList();
			}

			return $items;
		}

	/**
	 * Retrieves the total number of friends list a user has
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalLists($userId, $type = SOCIAL_TYPE_FRIEND)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_lists');
		$sql->column('COUNT(1)', 'total');
		$sql->where('user_id', $userId);

		$sql->where('type', $type);

		$db->setQuery($sql);

		$total 	= $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of members in a specific list.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCount($id, $type = SOCIAL_TYPE_FRIEND)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_lists_maps', 'a');
		$sql->column('COUNT(1)');

		// Should only fetch from specific list
		$sql->where('list_id', $id);

		$sql->join('#__social_lists', 'b', 'INNER');
		$sql->on('a.list_id', 'b.id');

		if ($type == SOCIAL_TYPE_AUDIOS) {
			$sql->join('#__social_audios', 'au', 'INNER');
			$sql->on('a.target_id', 'au.id');

			$sql->where('au.state', SOCIAL_STATE_PUBLISHED);
		}

		if ($type == SOCIAL_TYPE_FRIEND) {
			$sql->join('#__users', 'uu', 'INNER');
			$sql->on('a.target_id', 'uu.id');

			// exclude esad users
			$sql->innerjoin('#__social_profiles_maps', 'upm', 'INNER');
			$sql->on('uu.id', 'upm.user_id');

			$sql->innerjoin('#__social_profiles', 'up', 'INNER');
			$sql->on('upm.profile_id', 'up.id');
			$sql->on('up.community_access', '1');

			// Ensure that the user is really their friend
			$sql->join('#__social_friends', 'c', 'INNER');
			$sql->on('(');
			$sql->on('c.target_id', 'a.target_id');
			$sql->on('c.actor_id', 'b.user_id');
			$sql->on(')');
			$sql->on('(', '', '', 'OR');
			$sql->on('c.actor_id', 'a.target_id');
			$sql->on('c.target_id', 'b.user_id');
			$sql->on(')');

			if (ES::isBlockEnabled()) {
				$sql->leftjoin('#__social_block_users', 'bus');
				$sql->on('uu.id', 'bus.user_id');
				$sql->on('bus.target_id', JFactory::getUser()->id);
				$sql->isnull('bus.id');
			}

			$sql->where('c.state', SOCIAL_FRIENDS_STATE_FRIENDS);
			$sql->where('uu.block', '0');
		}

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Deletes the list mapping between the list and the users.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteMapping($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_lists_maps');
		$sql->where('list_id', $id);

		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}

	/**
	 * Delete item from the list
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteItem($id, $type = SOCIAL_TYPE_FRIEND)
	{
		$db = ES::db();
		$sql = $db->sql();
		$sql->delete('#__social_lists_maps');
		$sql->where('target_id', $id);
		$sql->where('target_type', $type);

		$db->setQuery($sql);
		$db->Query();
	}
}
