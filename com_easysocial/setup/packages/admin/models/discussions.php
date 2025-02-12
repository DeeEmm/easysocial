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

ES::import('admin:/includes/model');

class EasySocialModelDiscussions extends EasySocialModel
{
	public function __construct($config = [])
	{
		parent::__construct('discussions', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('state', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'ordering');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');

		$this->setState('state', $filter);

		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Retrieves a list of participants from a discussion
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getParticipants($id, $options = [])
	{
		$db = ES::db();
		$viewer = ES::user();

		$query = [];
		$query[] = 'select DISTINCT(a.`created_by`) from `#__social_discussions` as a';
		$query[] = $this->getUserBlockingJoinQuery($viewer, 'bus', 'created_by', 0, ['clusterColumnAlias' => 'uid']);

		$query[] = 'WHERE (a.`parent_id` = ' . $db->Quote($id) . ' OR a.`id` = ' . $db->Quote($id) . ')';
		$query[] = 'AND a.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$exclude = isset($options['exclude']) ? $options['exclude'] : '';
		if ($exclude) {
			$exclude = ES::makeArray($exclude);
			$query[] = 'AND a.`created_by` NOT IN (' . implode(',', $exclude) . ')';
		}

		$query[] = $this->getUserBlockingClauseQuery($viewer);

		// join the query
		$query = implode(' ', $query);
		$db->setQuery($query);

		$result = $db->loadColumn();

		if (!$result) {
			return $result;
		}

		$users = ES::user($result);

		return $users;
	}

	/**
	 * Retrieves the last reply item for a discussion
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getLastReply($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions', 'a');
		$sql->where('a.parent_id', $id);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->order('a.created', 'DESC');
		$sql->limit(1);

		$db->setQuery($sql);

		$obj = $db->loadObject();

		if (!$obj) {
			return false;
		}

		$reply = ES::table('Discussion');
		$reply->bind($obj);

		return $reply;
	}

	/**
	 * Shorthand method to get counters for a cluster
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getCounters($cluster)
	{
		$counters = [];
		$counters['resolved'] = $this->getTotalResolved($cluster->id, $cluster->getType());
		$counters['unresolved'] = $this->getTotalUnresolved($cluster->id, $cluster->getType());
		$counters['locked'] = $this->getTotalLocked($cluster->id, $cluster->getType());
		$counters['unanswered'] = $this->getTotalUnanswered($cluster->id, $cluster->getType());
		$counters['total'] = $this->getTotalDiscussions($cluster->id, $cluster->getType());

		return $counters;
	}

	/**
	 * Retrieves the total number of discussions
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTotalDiscussions($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.parent_id', '0');
		$sql->where('a.uid', $uid);
		$sql->where('a.type', $type);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of resolved discussions
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalResolved($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.parent_id', '0');
		$sql->where('a.uid', $uid);
		$sql->where('a.type', $type);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.answer_id', 0, '!=');

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of resolved discussions
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalUnanswered($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.parent_id', '0');
		$sql->where('a.uid', $uid);
		$sql->where('a.type', $type);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.last_reply_id', 0);

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of unresolved discussions
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalUnresolved($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.parent_id', '0');
		$sql->where('a.uid', $uid);
		$sql->where('a.type', $type);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.answer_id', 0);

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of unresolved discussions
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalLocked($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.parent_id', '0');
		$sql->where('a.uid', $uid);
		$sql->where('a.type', $type);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.lock', 1);

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of replies for a discussion
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTotalReplies($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.parent_id', $id);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Delete files from a discussion
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function deleteFiles($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_discussions_files');
		$sql->where('discussion_id', $id);

		$db->setQuery($sql);
		return $db->Query();
	}

	/**
	 * Deletes all replies from a discussion
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function deleteReplies($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get a list of replies so we can delete their respective stream items first
		$replies = $this->getReplies($id);

		if (!$replies) {
			return;
		}

		foreach ($replies as $reply) {
			// Delete all stream items for the replies
			ES::stream()->delete($reply->id, 'discussions');

			// Delete all files related to this reply.
			$this->deleteFiles($reply->id);
		}

		// Now we delete all the items
		$sql->delete('#__social_discussions');
		$sql->where('parent_id', $id);

		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}

	/**
	 * Retrieves a list of replies to a discussion given the unique id of the discussion
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getReplies($id, $options = [])
	{
		$viewer = ES::user();
		$db = ES::db();

		$query = [];

		$query[] = 'select a.* from `#__social_discussions` as a';
		$query[] = $this->getUserBlockingJoinQuery($viewer, 'bus', 'created_by', 0, ['clusterColumnAlias' => 'uid']);
		$query[] = 'WHERE a.`parent_id` = ' . $db->Quote($id);
		$query[] = $this->getUserBlockingClauseQuery($viewer);


		$countSQL = implode(' ', $query);

		// Determines if we should always order the items
		$ordering = isset($options['ordering']) ? $options['ordering'] : '';

		if ($ordering) {
			if ($ordering == 'created') {
				$direction = $this->normalize($options, 'direction', 'ASC');
				$query[] = 'ORDER BY a.`created` ' . $direction;
			}
		}

		// join the query
		$query = implode(' ', $query);

		$limit = $this->normalize($options, 'limit', '');

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Run pagination here.
			$this->setTotal($countSQL, true);

			$result = $this->getData($query);
		} else {
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$replies = [];

		foreach ($result as $row) {
			$reply = ES::table('Discussion');
			$reply->bind($row);
			$reply->author = $reply->getAuthor();
			$reply->likes = ES::likes($row->id, 'discussion', 'reply', $row->type);

			$replies[] = $reply;
		}

		return $replies;
	}

	/**
	 * Retrieves a list of discussions given the unique id and the unique type.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getDiscussions($uid, $type, $options = [])
	{
		$db = ES::db();
		$viewer = ES::user();

		$query = [];
		$query[] = 'select a.* from `#__social_discussions` as a';

		// When viewer is a logged in user, we need to check against the blocking features
		$query[] = $this->getUserBlockingJoinQuery($viewer, 'bus', 'created_by', 0, ['clusterColumnAlias' => 'uid']);

		$query[] = 'where a.`uid` = ' . $db->Quote($uid);
		$query[] = 'and a.`type` = ' . $db->Quote($type);
		$query[] = 'and a.`parent_id` = ' . $db->Quote('0');

		// Determines if we should fetch resolved items only
		$resolved = isset($options['resolved']) ? $options['resolved'] : '';

		if ($resolved) {
			$query[] = 'and a.`answer_id` != ' . $db->Quote('0');
		}

		// Determines if we should fetch unresolved items only
		$unresolved = isset($options['unresolved']) ? $options['unresolved'] : '';

		if ($unresolved) {
			$query[] = 'and a.`answer_id` = ' . $db->Quote('0');
		}

		// Determines if we should fetch locked items only
		$locked = isset($options['locked']) ? $options['locked'] : '';

		if ($locked) {
			$query[] = 'and a.`lock` != ' . $db->Quote('0');
		}

		// Determines if we should fetch unanswered items only
		$unanswered = isset($options['unanswered']) ? $options['unanswered'] : '';

		if ($unanswered) {
			$query[] = 'and a.`last_reply_id` = ' . $db->Quote('0');
		}

		$query[] = $this->getUserBlockingClauseQuery($viewer);

		// join the query
		$query = implode(' ', $query);
		$countSql = $query;

		$query .= ' ORDER BY a.`created` DESC';

		$limit = isset($options['limit']) ? $options['limit'] : '';

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Run pagination here.
			$this->setTotal($countSql, true);

			$result = $this->getData($query);
		} else {
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$discussions = [];

		foreach ($result as $row) {
			$discussion = ES::table('Discussion');
			$discussion->bind($row);

			// If this discussion has been replied before, we need to retrieve the item
			$discussion->lastreply = false;

			if ($discussion->last_reply_id) {
				// @TODO: Need to think of a way to optimize this
				$reply = ES::table('Discussion');
				$exists = $reply->load($discussion->last_reply_id);

				if ($exists) {
					$reply->author = $reply->getAuthor();

					$discussion->lastreply = $reply;
				}
			}

			$discussion->author = $discussion->getAuthor();
			$discussions[] = $discussion;
		}

		return $discussions;
	}

	/**
	 * Retrieves a list of discussions given the unique id and the unique type.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getDiscussionsGdpr($userId, $options = [])
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->column('a.*');
		$sql->select('#__social_discussions', 'a');
		$sql->where('a.created_by', $userId);

		$exclusion = $this->normalize($options, 'exclusion', null);

		if ($exclusion) {
			$exclusion = ES::makeArray($exclusion);
			$sql->where('a.id', $exclusion, 'NOT IN');
		}

		$limit = $this->normalize($options, 'limit', 20);

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Run pagination here.
			$this->setTotal($sql->getTotalSql());

			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$discussions = [];

		foreach ($result as $row) {
			$discussion = ES::table('Discussion');
			$discussion->bind($row);

			// If this discussion has been replied before, we need to retrieve the item
			$discussion->lastreply = false;

			if ($discussion->last_reply_id) {
				// @TODO: Need to think of a way to optimize this
				$reply = ES::table('Discussion');
				$reply->load($discussion->last_reply_id);
				$reply->author = $reply->getAuthor();

				$discussion->lastreply = $reply;
			}

			$discussion->author = $discussion->getAuthor();
			$discussions[] = $discussion;
		}

		return $discussions;
	}

	/**
	 * Deletes all discussion from a given type
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_discussions');
		$sql->where('uid', $uid);
		$sql->where('type', $type);

		$db->setQuery($sql);

		$state = $db->query();

		return $state;
	}
}
