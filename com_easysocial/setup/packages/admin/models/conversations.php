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

jimport('joomla.application.component.model');
ES::import('admin:/includes/model');

class EasySocialModelConversations extends EasySocialModel
{
	private $data = null;
	protected $pagination = null;
	protected $limitstart = null;
	protected $limit = null;

	public function __construct()
	{
		parent::__construct('conversations');
		parent::initStates();
	}

	public function resetPagination()
	{
		$this->pagination = null;
	}

	/**
	 * Adds a list of recipients that can see a particular message
	 *
	 * @param	int $conversationId
	 * @param	int $messageId
	 * @param	Array $recipients
	 */
	public function addMessageMaps($conversationId , $messageId , $recipients , $creator)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'INSERT INTO ' . $db->nameQuote('#__social_conversations_message_maps');
		$query[] = '(' . $db->nameQuote('conversation_id') .',' . $db->nameQuote('message_id') . ',' . $db->nameQuote('user_id') . ',' . $db->nameQuote('isread') . ',' . $db->nameQuote('state') . ')';
		$query[] = 'VALUES';

		// Go through the list of participants.
		foreach ($recipients as $recipient) {
			$id = $recipient;

			if ($recipient instanceof SocialUser) {
				$id = $recipient->id;
			}

			//Since the creator is the one that created the message, it should be marked as read by default.
			$isRead = $creator == $id ? SOCIAL_CONVERSATION_READ : SOCIAL_CONVERSATION_UNREAD;

			$query[] = '(' . $db->Quote($conversationId) . ',' . $db->Quote($messageId) . ',' . $db->Quote($id) . ',' . $db->Quote($isRead) . ',' . $db->Quote(SOCIAL_STATE_PUBLISHED) . ')';

			if (next($recipients) !== false) {
				$query[] = ',';
			}
		}

		// Glue back the query.
		$query = implode(' ' , $query);

		$db->setQuery($query);
		return $db->Query();
	}

	/**
	 * Retrieves the total number of messages sent today
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTotalSentDaily($userId)
	{
		static $_cache = array();

		if (!isset($_cache[$userId])) {

			$db = ES::db();

			// Get today's date
			$start = ES::date()->format('Y-m-d 00:00:00');
			$end = ES::date()->format('Y-m-d 23:59:59');

			$query = array();
			$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQUote('#__social_conversations_message');
			$query[] = 'WHERE ' . $db->nameQuote('created_by') . '=' . $db->Quote($userId);
			$query[] = 'AND ' . $db->nameQuote('created') . ' BETWEEN ' . $db->Quote($start) . ' AND ' . $db->Quote($end);

			$query = implode(' ', $query);

			$db->setQuery($query);

			$total= $db->loadResult();

			$_cache[$userId] = $total;

		}

		return $_cache[$userId];
	}

	/**
	 * Retrieves the total number of messages sent in total
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTotalSent($userId)
	{
		static $_cache = array();

		if (!isset($_cache[$userId])) {
			$db = ES::db();

			$query = array();
			$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQUote('#__social_conversations_message');
			$query[] = 'WHERE ' . $db->nameQuote('created_by') . '=' . $db->Quote($userId);

			$query = implode(' ', $query);

			$db->setQuery($query);
			$total = $db->loadResult();

			$_cache[$userId] = $total;
		}

		return $_cache[$userId];
	}

	/**
	 * Adds a list of participants into a conversation
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique conversation id.
	 * @param	Array	An array of participants.
	 * @return	boolean	True if adding was successfull, false otherwise.
	 */
	public function addParticipants($conversationId , $participants)
	{
		$db = ES::db();
		$ids = array();

		foreach ($participants as $participant) {
			$query = array();
			$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_conversations_participants');
			$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
			$query[] = 'AND ' . $db->nameQuote('user_id') . '=' . $db->Quote($participant);

			// Glue query back.
			$query = implode(' ' , $query);

			$db->setQuery($query);
			$exists = $db->loadResult();

			if (!$exists) {
				$ids[] = $participant;
			}
		}

		// If there are already participants here, skip adding anything.
		if (!$ids) {
			return;
		}

		$query = array();
		$query[] = 'INSERT INTO ' . $db->nameQuote('#__social_conversations_participants');
		$query[] = '(' . $db->nameQuote('conversation_id') .',' . $db->nameQuote('user_id') . ',' . $db->nameQuote('state') . ')';
		$query[] = 'VALUES';

		// Add all participints
		$total = count($ids);

		foreach ($ids as $userId) {
			$query[] = '(' . $db->Quote($conversationId) . ',' . $db->Quote($userId) . ',' . $db->Quote(1) . '),';
		}

		// Glue query back.
		$query = implode(' ' , $query);

		// trim the last common to avoid sql error.
		$query = rtrim($query, ",");

		$db->setQuery($query);
		$result = $db->Query();

		return $result;
	}

	/**
	 * Determines if the conversation is archived for the particular node
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int $conversationId
	 * @param	int $userId
	 *
	 * @return	boolean				True if conversation is archived, false otherwise.
	 */
	public function isArchived($conversationId , $userId)
	{
		$db = ES::db();

		$query = "SELECT COUNT(1)";
		$query .= " FROM `#__social_conversations` AS a";
		$query .= "		INNER JOIN `#__social_conversations_message` AS b ON a.`id` = b.`conversation_id`";
		$query .= "		INNER JOIN `#__social_conversations_message_maps` AS c ON c.`message_id` = b.`id`";
		$query .= " WHERE c.`state` = " . $db->Quote(SOCIAL_CONVERSATION_STATE_ARCHIVED);
		$query .= " AND c.`conversation_id` = " . $db->Quote($conversationId);
		$query .= " AND c.`user_id` = " . $db->Quote($userId);
		$query .= " AND a.`id` = " . $db->Quote($conversationId);

		// echo $query;
		// exit;

		$db->setQuery($query);

		$archived= $db->loadResult();

		return $archived >= 1;
	}

	/**
	 * Determines if the conversation is archived for the particular node
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int			The unique conversation i.d
	 * @param	int 		The user's id.
	 * @return	boolean		True if the given user is a participant
	 */
	public function isParticipant($conversationId , $userId)
	{
		$db = ES::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_conversations_participants');
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND ' . $db->nameQuote('user_id') . '=' . $db->Quote($userId);

		// Glue query back.
		$query = implode(' ' , $query);
		$db->setQuery($query);

		$isParticipant= $db->loadResult() > 0;

		return $isParticipant;
	}

	/**
	 * Determines if the conversation is new for the particular node.
	 *
	 * @return	boolean
	 * @param	int $conversationId
	 * @param	int $nodeId
	 */
	public function isNew($conversationId , $userId)
	{
		$db = ES::db();

		$query = 'SELECT ' . $db->nameQuote('isread')
				. 'FROM ' . $db->nameQuote('#__social_conversations') . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message') . ' AS b '
				. 'ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('conversation_id') . ' '
				. 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message_maps') . ' AS c '
				. 'ON c.' . $db->nameQuote('message_id') . ' = b.' . $db->nameQuote('id') . ' '
				. 'WHERE a.' . $db->nameQuote('id') . ' = ' . $db->Quote($conversationId) . ' '
				. 'AND c.' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId) . ' '
				. 'AND c.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId) . ' '
				. 'ORDER BY c.' . $db->nameQuote('id') . ' DESC '
				. 'LIMIT 1';

		$db->setQuery($query);
		$isNew = $db->loadResult() == SOCIAL_CONVERSATION_UNREAD;

		return $isNew;
	}


	/**
	 * check for new message
	 *
	 * @return	boolean
	 * @param	int $conversationId
	 * @param	int $nodeId
	 */
	public function getNotification($conversationId , $userId)
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = 'SELECT count(' . $db->nameQuote('isread') . ') AS newMsg  '
				. 'FROM ' . $db->nameQuote('#__social_conversations_message_maps') . ' '
				. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId) . ' '
				. 'AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId) . ' '
				. 'AND ' . $db->nameQuote('isread') .'= 0';
		$db->setQuery($query);
		$isNew = $db->loadResult();
		// $sql->raw($query);
		// echo $sql->debug();exit;
		return $isNew;
	}

	/**
	 * Mark a conversation to old.
	 *
	 * @return	boolean
	 * @param	int $conversationId
	 * @param	int $userId
	 */
	public function markAsRead($conversationId , $userId)
	{
		$db = ES::db();
		$query = 'UPDATE ' . $db->nameQuote('#__social_conversations_message_maps') . ' '
					. 'SET ' . $db->nameQuote('isread') . ' = ' . $db->Quote(SOCIAL_CONVERSATION_READ) . ' '
					. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId) . ' '
					. 'AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Mark a conversation to new.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int $conversationId
	 * @param	int $userId
	 *
	 * @return	boolean
	 */
	public function markAsUnread($conversationId , $userId)
	{
		$db = ES::db();
		$query[] = 'UPDATE ' . $db->nameQuote('#__social_conversations_message_maps');
		$query[] = 'SET  ' . $db->nameQuote('isread') . '=' . $db->Quote(SOCIAL_CONVERSATION_UNREAD);
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND ' . $db->nameQuote('user_id') . '=' . $db->Quote($userId);

		// Glue back query.
		$query = implode(' ' , $query);

		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Mark a message in conversation to new.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int $conversationId
	 * @param	int $userId
	 *
	 * @return	boolean
	 */
	public function markThisAsUnread($conversationId , $userId, $messageId)
	{
		$db = ES::db();
		$query[] = 'UPDATE ' . $db->nameQuote('#__social_conversations_message_maps');
		$query[] = 'SET  ' . $db->nameQuote('isread') . '=' . $db->Quote(SOCIAL_CONVERSATION_UNREAD);
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND ' . $db->nameQuote('message_id') . '=' . $db->Quote($messageId);
		$query[] = 'AND ' . $db->nameQuote('user_id') . '=' . $db->Quote($userId);

		// Glue back query.
		$query = implode(' ' , $query);

		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Remove the child message mapping for the particular node.
	 *
	 * @return	boolean
	 * @param	int $conversationId
	 * @param	int $userId
	 */
	public function delete($conversationId, $userId)
	{
		$db = ES::db();
		$query = array();

		// We need to check if this is the last child item.
		// If it's the last item, we need to delete everything else.
		$query[] = 'SELECT COUNT(DISTINCT(c.' . $db->nameQuote('user_id') . '))';
		$query[] = 'FROM ' . $db->nameQuote('#__social_conversations') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message') . ' AS b';
		$query[] = 'ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('conversation_id');
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message_maps') . ' AS c';
		$query[] = 'ON a.' . $db->nameQuote('id') . ' = c.' . $db->nameQuote('conversation_id');
		$query[] = 'WHERE a.' . $db->nameQuote('id') . ' = ' . $db->Quote($conversationId);
		$query[] = 'AND c.' . $db->nameQuote('user_id') . ' != ' . $db->Quote($userId);
		$query[] = 'GROUP BY a.' . $db->nameQuote('id');

		// Glue query back.
		$query = implode(' ', $query);

		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total <= 0) {
			return $this->cleanup($conversationId);
		}

		// @TODO: If user is on a multiconversation, leave the conversation

		// @rule: Delete all mappings for this specific node
		$query = array();
		$query[] = 'DELETE FROM ' . $db->nameQuote('#__social_conversations_message_maps');
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

		$db->setQuery($query);

		return $db->Query();
	}

	/**
	 * Completely removes the conversation from the site.
	 *
	 * @return	boolean
	 * @param	int $conversationId
	 */
	private function cleanup($conversationId)
	{
		$db = ES::db();

		// Delete files related to this conversations
		$this->deleteFiles($conversationId);

		// @rule: Delete conversation first
		$query = 'DELETE FROM ' . $db->nameQuote('#__social_conversations') . ' '
				. 'WHERE ' . $db->nameQuote('id') . ' = ' . $db->Quote($conversationId);
		$db->setQuery($query);
		$db->Query();

		// @rule: Delete messages for the conversation.
		$query = 'DELETE FROM ' . $db->nameQuote('#__social_conversations_message') . ' '
				. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId);
		$db->setQuery($query);
		$db->Query();

		// @rule: Delete messages mapping for the conversation.
		$query = 'DELETE FROM ' . $db->nameQuote('#__social_conversations_message_maps') . ' '
				. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId);
		$db->setQuery($query);
		$db->Query();

		// @rule: Delete participants for the conversation.
		$query = 'DELETE FROM ' . $db->nameQuote('#__social_conversations_participants') . ' '
				. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId);
		$db->setQuery($query);
		$db->Query();

		// // @rule: Delete any history of actions for this conversation
		// $query = 'DELETE FROM ' . $db->nameQuote('#__social_conversations_participants_history') . ' '
		// 		. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId);
		// $db->setQuery($query);
		// $db->Query();

		return true;
	}

	/**
	 * Archiving a conversation simply means modifying the state :)
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int $conversationId
	 * @param	int $nodeId
	 * @return	boolean
	 */
	public function archive($conversationId , $userId)
	{
		$db = ES::db();
		$query = array();
		$query[] = 'UPDATE ' . $db->nameQuote('#__social_conversations_message_maps');
		$query[] = 'SET ' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_CONVERSATION_STATE_ARCHIVED);
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId);
		$query[] = 'AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

		// Glue query back.
		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Unarchiving a conversation item.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	$conversationId
	 * @param	int		$userId
	 * @return	boolean
	 */
	public function unarchive($conversationId , $userId)
	{
		$db = ES::db();

		$query = 'UPDATE ' . $db->nameQuote('#__social_conversations_message_maps') . ' '
				. 'SET ' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_CONVERSATION_STATE_PUBLISHED) . ' '
				. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId) . ' '
				. 'AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);
		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Leave a conversation and inserts into the history so that it could later form some sort of activity
	 * in the conversation thread.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int			The unique conversation id.
	 * @param	int			The unique user id.
	 * @return	boolean
	 */
	public function leave($conversationId, $userId)
	{
		// Add user history into the messages table to keep track of who left the conversation.
		$message = ES::table('ConversationMessage');

		$message->conversation_id = $conversationId;
		$message->type = SOCIAL_CONVERSATION_TYPE_LEAVE;
		$message->created_by = $userId;

		$state = $message->store();

		// Add message mapping so that participants can see the user leaving.
		$this->addMessageMaps($conversationId , $message->id , $this->getParticipants($conversationId) , $userId);

		// Update user's participant state in this conversation.
		// We will not delete the record yet unless the entire conversation is deleted by all parties.
		$participant = ES::table('ConversationParticipant');
		$participant->load(array('conversation_id' => $conversationId , 'user_id' => $userId));

		$participant->state= SOCIAL_CONVERSATION_STATE_LEFT;

		// Try to store the participant state.
		$participant->store();


		return true;
	}

	/**
	 * Checks whether or not the user has any access to the conversation.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int			The unique conversation id.
	 * @param	int			The unique user id.
	 * @return	boolean		True if the user has access.
	 */
	public function hasAccess($conversationId , $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$sql->select('#__social_conversations_participants');
		$sql->column('COUNT(1)');
		$sql->where('conversation_id' , $conversationId);
		$sql->where('user_id' , $userId);

		$db->setQuery($sql);

		$result = $db->loadResult();

		return $result > 0;
	}


	/**
	 * Retrieves a list of users who are participating in a conversation.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The conversation id.
	 * @param	Array	An array of excluded users.
	 * @return	Array	An array of @{Socialusers}
	 */
	public function getParticipants($conversationId, $excludeUsers = array(), $includeBlockedUser = false, $use4send = false)
	{
		// Ensure excluded users is an array.
		$excludeUsers = ES::makeArray($excludeUsers);

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_conversations_participants' , 'a');
		// $sql->column('DISTINCT(a.`user_id`)');
		$sql->column('a.user_id');

		if (ES::isBlockEnabled() && !$includeBlockedUser) {
			$sql->leftjoin('#__social_block_users' , 'bus');

			if ($use4send) {
				$sql->on('a.user_id' , 'bus.target_id');
				$sql->on('bus.user_id', JFactory::getUser()->id);
			} else {
				$sql->on('(');
				$sql->on( 'a.user_id' , 'bus.user_id' );
				$sql->on( 'bus.target_id', JFactory::getUser()->id);
				$sql->on(')');

				$sql->on('(', '', '', 'OR');
				$sql->on( 'a.user_id' , 'bus.target_id' );
				$sql->on( 'bus.user_id', JFactory::getUser()->id );
				$sql->on(')');
			}

			$sql->isnull('bus.id');
		}

		$sql->innerjoin('#__users', 'b');
		$sql->on('a.user_id', 'b.id');

		$sql->where('a.conversation_id' , $conversationId);
		$sql->where('a.state' , SOCIAL_STATE_PUBLISHED);
		$sql->where('b.block', SOCIAL_JOOMLA_USER_UNBLOCKED);

		if (!empty($excludeUsers)) {
			foreach($excludeUsers as $userId) {
				$sql->where('a.user_id', $userId, '!=');
			}
		}

		$db->setQuery($sql);

		// Load the data.
		$rows = $db->loadColumn();

		if (!$rows) {
			return false;
		}

		// Load the list of users.
		$users = ES::user($rows);

		return $users;
	}

	/**
	 * Retrieves a list of message id's from a conversation.
	 *
	 * @since	4.0.13
	 * @access	public
	 */
	public function getMessageIds($conversationId)
	{
		$db = ES::db();
		$query = array();
		$query[] = 'SELECT a.' . $db->nameQuote('id');
		$query[] = 'FROM ' . $db->nameQuote('#__social_conversations_message') . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);

		// Glue the query back
		$query = implode(' ' , $query);

		$db->setQuery($query);
		$ids = $db->loadColumn();

		return $ids;
	}

	/**
	 * Delete the entire attachment file for each of the conversations
	 *
	 * @since	4.0.13
	 * @access	public
	 */
	public function deleteFiles($conversationId)
	{
		// Retrieve a lists of conversation message ids to delete the attachement files
		$messageIds = $this->getMessageIds($conversationId);

		if (!$messageIds) {
			return;
		}

		$filesModel = ES::model('Files');

		// Delete files related to this conversations
		foreach ($messageIds as $messageId) {
			$filesModel->deleteFiles($messageId, 'conversations');
		}
	}

	/**
	 * Delete attachment file for a specific conversation message (reply)
	 *
	 * @since	4.0.13
	 * @access	public
	 */
	public function deleteFile($messageId)
	{
		if (!$messageId) {
			return;
		}

		$filesModel = ES::model('Files');
		$filesModel->deleteFiles($messageId, 'conversations');
	}

	/**
	 * Retrieves the last message posted in a conversation.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int			The unique conversation id.
	 * @param	int			The unique viewer's id.
	 * @return	SocialTableConversationMessage
	 */
	public function getLastMessage($conversationId , $viewerId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->column('b.*');
		$sql->select('#__social_conversations' , 'a');
		$sql->innerjoin('#__social_conversations_message' , 'b');
		$sql->on('a.id' , 'b.conversation_id');


		$sql->innerjoin('#__users' , 'uu');
		$sql->on('b.created_by' , 'uu.id');
		$sql->on('uu.block' , '0');

		$sql->innerjoin('#__social_conversations_message_maps' , 'c');
		$sql->on('c.message_id' , 'b.id');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on( 'b.created_by' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'b.created_by' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('a.id' , $conversationId);
		$sql->where('c.user_id' , $viewerId);

		$sql->order('b.created' , 'DESC');
		$sql->limit(0 , 1);

		// echo $sql;exit;

		$db->setQuery($sql);

		$data = $db->loadObject();

		if (!$data) {
			return $data;
		}

		$message = ES::table('ConversationMessage');
		$message->bind($data);

		return $message;
	}

	/**
	 * Retrieves a list of messages in a particular conversation
	 *
	 * @param	int		$conversationId		The unique id of that conversation
	 * @param	int		$userId				The current user id of the viewer
	 *
	 * @return	array	An array that contains SocialTableConversationMessage objects.
	 */
	public function getMessages($conversationId , $userId , $options = array())
	{
		$config = ES::config();
		$db = ES::db();;
		$query = array();

		// this is to use date to act as limit start to prevent duplicate messages from appearing in load more operation
		$nextLimitDate = isset($options['nextLimitDate']) && $options['nextLimitDate'] ? $options['nextLimitDate'] : '';

		$query[] = 'SELECT a.*,c.latitude,c.longitude,c.id AS location_id ,c.address,';
		$query[] = ' FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('created') . ')) / 60 / 60 / 24) AS ' . $db->nameQuote('day');


		$query[] = 'FROM ' . $db->nameQuote('#__social_conversations') . ' AS con';
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message') . ' AS a ON a.`conversation_id` = con.`id`';

		// The messages of the banned users should not be shown #3683
		$query[] = 'INNER JOIN `#__users` AS `uu` ON `a`.`created_by` = `uu`.`id` and `uu`.`block` = ' . $db->Quote(SOCIAL_JOOMLA_USER_UNBLOCKED);

		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message_maps') . ' AS b';
		$query[] = 'ON b.' . $db->nameQuote('message_id') . ' = a.' . $db->nameQuote('id');
		$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_locations') . ' as c';
		$query[] = ' ON a.' . $db->nameQuote('id') . ' = c.' . $db->nameQuote('uid') ;
		$query[] = ' 	and  c.' . $db->nameQuote('type') . ' = ' . $db->Quote('conversations') ;

		if (ES::isBlockEnabled()) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' a.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'user_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote( 'target_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id );
			$query[] = ') OR (';
			$query[] = ' a.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id ) ;
			$query[] = ')';

		}

		$query[] = 'WHERE con.' . $db->nameQuote('id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND b.' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND b.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId);

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$sort = isset($options['sort']) ? $options['sort'] : 'DESC';

		if ($nextLimitDate) {
			// currently we are showing most recent 1st thus we need to use 'less than' to
			// get the older messages.
			if ($sort != 'desc') {
				$query[] = ' AND a.' . $db->nameQuote('created') . ' > ' . $db->Quote($nextLimitDate);
			} else {
				$query[] = ' AND a.' . $db->nameQuote('created') . ' < ' . $db->Quote($nextLimitDate);
			}
		}

		// Glue back query.
		$query = implode(' ' , $query);

		// check if we need to override the limit / limitstart or not
		$limit = isset($options['limit']) ? $options['limit'] : 0;
		if ($limit) {

			$this->setState('limit' , $limit);

			// Get the limitstart.
			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart' , 0);

			// in order to support conversekit's the dynamic limitstart, we canot do a floor here.
			// $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart' , $limitstart);
		}

		// Set limit
		$this->setTotal(str_ireplace('SELECT a.*', 'SELECT COUNT(1)', $query));

		// now add the odering here
		$query .= ' ORDER BY a.' . $db->nameQuote('id') . ' ' . $sort;

		// Get the data.
		$rows = $this->getData($query);

		if (!$rows) {
			return $rows;
		}

		// Reverse the result.
		$rows = array_reverse($rows);

		$messages= array();
		foreach ($rows as $row) {
			$message = ES::table('ConversationMessage');
			$message->bind($row);

			// we need this day value to 'group' by date.
			$message->day = $row->day;
			$messages[] = $message;
		}
		return $messages;
	}

	public function getTotal()
	{
		return $this->total;
	}


	/**
	 * Get new messages for a conversation since the lastupdate
	 *
	 * @since	1.5
	 * @access	public
	 */
	public function getNewMessages($conversationId , $userId , $lastupdate, $exclusion = array())
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = array();

		$query[] = 'SELECT a.*,';
		$query[] = ' FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('created') . ')) / 60 / 60 / 24) AS ' . $db->nameQuote('day');
		$query[] = 'FROM ' . $db->nameQuote('#__social_conversations_message') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message_maps') . ' AS b';
		$query[] = 'ON b.' . $db->nameQuote('message_id') . ' = a.' . $db->nameQuote('id');

		if (ES::isBlockEnabled()) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' a.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'user_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote( 'target_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id );
			$query[] = ') OR (';
			$query[] = ' a.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id ) ;
			$query[] = ')';

		}

		$query[] = 'WHERE a.' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND a.' . $db->nameQuote('created') . ' > ' . $db->Quote($lastupdate);

		$query[] = 'AND b.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId);
		if (ES::isBlockEnabled()) {
			// user block continue here
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		// Exclusion list
		if (!empty($exclusion)) {
			$query[] = 'AND a.`id` NOT IN(' . implode(',', $exclusion) . ')';
		}

		$query[] = 'ORDER BY a.' . $db->nameQuote('created') . ' ASC';

		// Glue back query.
		$query = implode(' ' , $query);


		// echo $query;exit;


		// Get the data.
		$sql->raw($query);
		$db->setQuery($sql);
		$rows = $db->loadObjectList();

		if (!$rows) {
			return array();
		}

		$messages= array();
		foreach ($rows as $row) {
			$message = ES::table('ConversationMessage');
			$message->bind($row);

			// we need this day value to 'group' by date.
			$message->day = $row->day;
			$messages[] = $message;
		}
		return $messages;
	}


	/**
	 * Deletes all conversations involving a particular user.
	 *
	 * @since	5.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function deleteConversationsInvolvingUser($userId)
	{
		// Get a list of conversations on the site where the user is involved.
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT ' . $db->qn('conversation_id') . ' FROM ' . $db->qn('#__social_conversations_message_maps');
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote($userId);
		$query[] = ' UNION ';
		$query[] = 'SELECT ' . $db->qn('conversation_id') . ' FROM ' . $db->qn('#__social_conversations_participants');
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote($userId);
		$query[] = ' UNION ';
		$query[] = 'SELECT ' . $db->qn('conversation_id') . ' FROM ' . $db->qn('#__social_conversations_message');
		$query[] = 'WHERE ' . $db->qn('created_by') . '=' . $db->Quote($userId);


		$query = implode(' ', $query);
		$sql->raw($query);

		$db->setQuery($sql);
		$conversationIds = $db->loadColumn();

		// If the user isn't involved in any conversation at all, skip this.
		if (!$conversationIds) {
			return;
		}

		foreach ($conversationIds as $id) {
			$id = (int) $id;

			$conversation = ES::conversation($id);

			if ($conversation->isMultiple()) {

				// 1. delete user from conversations_message
				// 2. delete user from conversations_message_maps
				// 3. delete user from conversations_participants

				// 1
				$query = "delete a, b";
				$query .= " from `#__social_conversations_message` as a";
				$query .= "		inner join `#__social_conversations_message_maps` as b on a.`id` = b.`message_id`";
				$query .= " where a.`conversation_id` = " . $db->Quote($id);
				$query .= " and (a.`created_by` = " . $db->Quote($userId) . " OR a.`message` = " . $db->Quote($userId) . ")";
				$db->setQuery($query);
				$db->query();

				// 2
				$query = "delete from `#__social_conversations_message_maps`";
				$query .= " where `conversation_id` = " . $db->Quote($id);
				$query .= " and `user_id` = " . $db->Quote($userId);
				$db->setQuery($query);
				$db->query();

				// 3
				$query = "delete from `#__social_conversations_participants`";
				$query .= " where `conversation_id` = " . $db->Quote($id);
				$query .= " and `user_id` = " . $db->Quote($userId);
				$db->setQuery($query);
				$db->query();

				// need to check if there is more than 1 participants or not.
				// if no, then we need to delete this conversation completely
				$query = "select count(1) from `#__social_conversations_participants` where conversation_id = " . $db->Quote($id);
				$db->setQuery($query);
				$results = $db->loadResult();

				if ($results <= 1) {
					$query = "delete a, b, c, d";
					$query .= " from `#__social_conversations` as a";
					$query .= "  INNER JOIN `#__social_conversations_message` as b on a.`id` = b.`conversation_id`";
					$query .= "  INNER JOIN `#__social_conversations_message_maps` as c on a.`id` = c.`conversation_id`";
					$query .= "  INNER JOIN `#__social_conversations_participants` as d on a.`id` = d.`conversation_id`";
					$query .= " where a.`id` = " . $db->Quote($id);

					$db->setQuery($query);
					$db->query();

					// Delete files related to this conversations
					$this->deleteFiles($id);
				}

			} else {
				$this->cleanup($id);
			}
		}

		return true;
	}

	/**
	 * Renders the list of conversations for a user
	 *
	 * @since	5.0.0
	 * @access	public
	 */
	public function getConversations($userId, $options = array())
	{
		$config = ES::config();
		$db = ES::db();

		$searchQuery = isset($options['search']) && $options['search'] ? $options['search'] : false;
		$sorting = isset($options['sorting']) ? $options['sorting'] : false;
		$ordering = isset($options['ordering']) ? $options['ordering'] : 'DESC';
		$exclusion = isset($options['exclusion']) ? $options['exclusion'] : null;

		$debug = isset($options['debug']) ? $options['debug'] : false;

		$hasIsReadFilter = false;

		$totalQuery = '';

		// get the conversation id first.
		$subQuery = array();
		$subQuery[] = '	select distinct a1.`id`';
		$subQuery[] = '		FROM `#__social_conversations` AS `a1`';
		$subQuery[] = '			INNER JOIN `#__social_conversations_message_maps` AS `c1` ON `c1`.`conversation_id` = `a1`.`id`';

		// We should not show the banned user's conversation #3683
		$subQuery[] = '			INNER JOIN `#__users` AS `uu` ON `a1`.`created_by` = `uu`.`id` and `uu`.`block` = ' . $db->Quote(SOCIAL_JOOMLA_USER_UNBLOCKED);
		if ($config->get('users.blocking.enabled')) {
			$subQuery[] = '			LEFT JOIN `#__social_block_users` AS `bus`';
			$subQuery[] = '				ON (`a1`.`created_by` = `bus`.`user_id` AND `bus`.`target_id` = ' . $db->Quote($userId);
			$subQuery[] = '						OR `a1`.`created_by` = `bus`.`target_id` AND `bus`.`user_id` = ' . $db->Quote($userId) . ')';
		}

		if ($searchQuery) {
			$subQuery[] = 'INNER JOIN `#__social_conversations_participants` as parti on a1.id = parti.conversation_id';
			$subQuery[] = 'INNER JOIN `#__users` AS `su` ON `parti`.`user_id` = `su`.`id`';
		}


		$subQuery[] = '			where c1.user_id = ' . $db->Quote($userId);

		if (isset($options['archives']) && $options['archives']) {
			$subQuery[] = '			and c1.state = ' . $db->Quote(SOCIAL_CONVERSATION_STATE_ARCHIVED);
		} else {
			$subQuery[] = '			and c1.state = ' . $db->Quote(SOCIAL_CONVERSATION_STATE_PUBLISHED);
		}

		// @rule: Respect filter options
		if (isset($options['filter'])) {

			$hasIsReadFilter = true;

			if ($options['filter'] == 'unread') {
				$subQuery[] = 'AND `c1`.`isread` = ' . $db->Quote(SOCIAL_CONVERSATION_UNREAD);
			}

			if ($options['filter'] == 'read') {
				$subQuery[] = 'AND `c1`.`isread` = ' . $db->Quote(SOCIAL_CONVERSATION_READ);
			}
		}

		if ($config->get('users.blocking.enabled')) {
			$subQuery[] = '			and `bus`.`id` IS NULL';
		}

		if ($searchQuery) {
			$subQuery[] = 'AND (`su`.`username` LIKE ' . $db->Quote('%' . $searchQuery . '%') . ' OR `su`.`name` LIKE ' . $db->Quote('%' . $searchQuery . '%') . ' OR `a1`.`title` LIKE ' . $db->Quote('%' . $searchQuery . '%') . ' OR `a1`.`title_alias` LIKE ' . $db->Quote('%' . $searchQuery . '%') . ')';
		}

		if ($exclusion) {
			$exclusion = ES::makeArray($exclusion);
			$subQuery[] = ' AND `a1`.`id` NOT IN (' . implode(',', $exclusion) . ')';
		}

		$totalQuery = implode(' ', $subQuery);
		$totalQuery = str_replace('select distinct a1.`id`', 'select count(distinct a1.`id`)', $totalQuery);

		// always order by unread 1st
		$orderby = 'ORDER BY `c1`.isread asc';

		// // @rule: Respect sorting options
		if (isset($options['sorting']) && $options['sorting'] == true) {
			$sorting = $options['sorting'];
			$ordering = isset($options['ordering']) ? $options['ordering'] : 'DESC';
			$ordering   = ($ordering) ? $ordering : 'DESC';

			$orderby .= ', `a1`.' . $sorting . ' ' . $ordering;
		}
		$subQuery[] = $orderby;

		// set limit here
		$maxlimit = isset($options['maxlimit']) ? $options['maxlimit'] : 0;

		$rows = array();

		if ($maxlimit) {
			$subQuery[] = ' LIMIT ' . $maxlimit;
		} else {
			$limit = isset($options['limit']) ? $options['limit'] : '';

			if ($limit) {
				$limitstart = $this->getUserStateFromRequest('limitstart' , 0);
				$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
				$subQuery[] = ' LIMIT ' . $limitstart . ',' . $limit;
			}
		}

		$subQueries = implode(' ', $subQuery);
		$db->setQuery($subQueries);

		if ($debug) {
			echo $subQueries;
			echo '<br><br>';
		}

		$mainResults = $db->loadColumn();

		if (count($mainResults) == 0) {
			if (isset($options['count']) && $options['count'] == true) {
				return 0;
			}
			return array();
		}


		$header = array();

		$header[] = 'SELECT distinct IFNULL(party.`user_id`, 0) as isparticipant, a.`id`,';
		$header[] = '(SELECT count(1) FROM `#__social_conversations_message_maps` as mm WHERE mm.`conversation_id` = a.`id` AND mm.`isread` = 0 AND mm.`user_id` = '. $db->Quote($userId).') AS newMsg';

		$mainQuery = array();
		$mainQuery[] = 'FROM `#__social_conversations` AS `a`';

		$query = array();
		$queryConds = array();

		$query[] = 'LEFT JOIN `#__social_conversations_participants` as party on a.id = party.conversation_id and party.user_id = '. $db->Quote($userId);
		$query[] = 'INNER JOIN `#__social_conversations_message_maps` AS `c` ON `c`.`conversation_id` = `a`.`id`';


		$queryConds[] = 'WHERE a.`id` IN (' . implode(',', $mainResults) . ')';
		$queryConds[] = 'and c.user_id = ' . $db->Quote($userId);

		if (isset($options['archives']) && $options['archives']) {
			$queryConds[] = 'and c.state = ' . $db->Quote(SOCIAL_CONVERSATION_STATE_ARCHIVED);
		} else {
			$queryConds[] = 'and c.state = ' . $db->Quote(SOCIAL_CONVERSATION_STATE_PUBLISHED);
		}

		if (isset($options['filter'])) {

			$hasIsReadFilter = true;

			if ($options['filter'] == 'unread') {
				$queryConds[] = 'AND `c`.`isread` = ' . $db->Quote(SOCIAL_CONVERSATION_UNREAD);
			}

			if ($options['filter'] == 'read') {
				$queryConds[] = 'AND `c`.`isread` = ' . $db->Quote(SOCIAL_CONVERSATION_READ);
			}
		}


		$query[] = implode(' ', $queryConds);

		// glue the query string.
		$query = implode(' ', $query);

		// glue all the string for main query

		$header = implode(' ', $header);
		$mainQuery = implode(' ', $mainQuery);

		$query = $header . ' ' . $mainQuery . ' ' . $query;

		$orderby = ' ORDER BY `c`.isread asc';

		// // // @rule: Respect sorting options
		if (isset($options['sorting']) && $options['sorting'] == true) {
			$sorting = $options['sorting'];
			$ordering = isset($options['ordering']) ? $options['ordering'] : 'DESC';
			$ordering   = ($ordering) ? $ordering : 'DESC';

			$orderby .= ', `a`.' . $sorting . ' ' . $ordering;
		}
		$query .= $orderby;


		if ($debug) {

			echo $query;
			echo '<br><br>';

			echo $totalQuery;
			echo '<br><br>';
			// exit;
		}

		// build the query used for total.

		// @rule: Only get the count
		if (isset($options['count']) && $options['count'] == true) {

			$db->setQuery($totalQuery);
			$total = $db->loadResult();
			return $total;
		}

		// retrieve total count
		$this->setTotal($totalQuery, false);

		// now retrieve the actual data
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$conversations = array();

		if ($rows) {

			// lets do manuall grouping to remove duplicates entries
			$tmpContainer = array();

			foreach ($rows as $row) {

				if (!in_array($row->id, $tmpContainer)) {
					$conversation = ES::conversation($row->id);
					$conversation->conversation->isparticipant = $row->isparticipant;
					$conversation->conversation->notification = $row->newMsg;
					$conversations[] = $conversation;

					$tmpContainer[] = $row->id;
				}
			}
		}

		return $conversations;
	}

	/**
	save attachment reference
	*/
	// public function addTempAttachment($userId, $file){
	// 	$currentTime = new JDate('now');
	// 	$db = ES::db();
	// 	$query = array();
	// 	$sql = $db->sql();

	// 	$query[] = 'INSERT INTO ' . $db->nameQuote('#__social_uploader');
	// 	$query[] = '(' . $db->nameQuote('path') .',' . $db->nameQuote('name') . ',' . $db->nameQuote('mime');
	// 	$query[] = ','. $db->nameQuote('size') . ', ' . $db->nameQuote('created') . ', ' . $db->nameQuote('user_id').')';
	// 	$query[]    = 'VALUES ('. $db->Quote($file['tmp_name']).','. $db->Quote($file['name']).', '. $db->Quote($file['type']).',';
	// 	$query[] = $db->Quote($file['size']).', '. $db->Quote($currentTime).','. $db->Quote($userId).')';
	// 	$query = implode(' ' , $query);


	// 	$db->setQuery($query);
	// 	$db->Query();

	// 	$addressID = $db->insertid();
	// 	return $addressID;
	// }

	/**
	save attachment reference
	*/
	public function getAttachment($userId, $id){
		$currentTime = new JDate('now');
		$db = ES::db();
		$query = array();
		$sql = $db->sql();


		$db = ES::db();
		$query = 'SELECT * FROM '.$db->nameQuote('#__social_uploader').' WHERE id ='. $db->Quote($id);
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;

	}

	public function getTotalConversations($userId)
	{
		$config = ES::config();
		$db = ES::db();
		$query = 'SELECT COUNT(1) FROM '.$db->nameQuote('#__social_conversations').' AS a LEFT JOIN '.$db->nameQuote('#__social_conversations_participants').'AS b ON a.id = b.conversation_id WHERE b.user_id ='. $db->Quote($userId);
		$db->setQuery($query);
		$total = $db->loadResult($query);
		return $total;

	}


	/**
	 * Gets the total number of new conversations
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The mailbox type. E.g: inbox / archives
	 * @return	int		The total count of new conversations.
	 */
	public function getNewCount($userId, $type)
	{
		$archives = $type == 'archives' ? true : false;
		$total = $this->getConversations($userId , array('count' => true, 'filter' => 'unread', 'archives' => $archives));

		return $total;
	}

	/**
	 * Gets the total number of new conversations
	 *
	 * @since	1.5
	 * @access	public
	 * @param	string	The mailbox type. E.g: inbox / archives
	 * @return	int		The total count of new conversations.
	 */
	public function getTotalCount($userId , $type)
	{
		$archives = $type == 'archives' ? true : false;
		$total = $this->getConversations($userId , array('count' => true , 'archives' => $archives));

		return $total;
	}

	/**
	 * Inserts a new reply into an existing conversation.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The conversation id.
	 * @param	string	Content of the message
	 * @param 	int 	The user id (owner) of the message
	 *
	 * @return	SocialTableConversationMessage	The message object
	 */
	public function addReply($conversationId , $msg , $creatorId, $type = SOCIAL_CONVERSATION_TYPE_MESSAGE)
	{
		// Try to load the conversation id first.
		$conversation= ES::table('Conversation');
		$conversation->load($conversationId);

		// Now, we need to create a new record for the conversation message.
		$message = ES::table('ConversationMessage');
		$message->conversation_id = $conversation->id;
		$message->message = $msg;
		$message->created_by = $creatorId;
		$message->setType($type);
		$message->store();

		// @badge: conversation.reply
		$badge = ES::badges();
		$badge->log('com_easysocial' , 'conversation.reply' , $creatorId , JText::_('COM_EASYSOCIAL_CONVERSATIONS_BADGE_REPLIED_IN_A_CONVERSATION'));

		// @points: conversation.reply
		// Assign points when user replies in a conversation
		$points = ES::points();
		$points->assign('conversation.reply' , 'com_easysocial' , $creatorId);


		// Since a new message is added, add the visibility of this new message to the participants.
		$users= $this->getParticipants($conversation->id, null, true);

		if($users)
		{
			foreach($users as $user)
			{
				$map = ES::table('ConversationMessageMap');
				$map->user_id = $user->id;
				$map->conversation_id = $conversation->id;
				$map->state = SOCIAL_STATE_PUBLISHED;
				$map->isread = SOCIAL_CONVERSATION_UNREAD;
				$map->message_id = $message->id;
				$map->store();

				// If the same person created a reply, reset all as viewed since they are already viewing the message.
				if($user->id == $creatorId)
				{
					$this->markAsRead($conversation->id , $user->id);
				}
				else
				{
					$this->markThisAsUnread($conversation->id , $user->id , $message->id);
				}

			}
		}

		// In case a message has been archived by the creator, and the creator added a reply to this
		// conversation, automatically unarchive all messages.
		$db = ES::db();
		$query = 'UPDATE ' . $db->nameQuote('#__social_conversations_message_maps') . ' '
				. 'SET ' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_CONVERSATION_STATE_PUBLISHED) . ' '
				. 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId) . ' '
				. 'AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($creatorId);
		$db->setQuery($query);
		$db->Query();

		// Every time a reply is added, we need to ensure that the last replied is updated so that we can order them later.
		$conversation->set('lastreplied' , ES::date()->toMysQL());
		$conversation->store();

		return $message;
	}

	/**
	 * Delete a reply from the conversation
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function deleteMessageMap($messageId, $conversationId)
	{
		$db = ES::db();

		$query = array();
		$query[] = 'DELETE FROM ' . $db->nameQuote('#__social_conversations_message_maps');
		$query[] = 'WHERE ' . $db->nameQuote('message_id') . ' = ' . $db->Quote($messageId);
		$query[] = 'AND ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($conversationId);

		$query = implode(' ', $query);
		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Retrieves all attachments for a specific message
	 *
	 * @param	int $messageId		The unique message id to lookup for.
	 * @return	Array				An array of @SocialTableConversationAttachment
	 */
	public function getAttachments($messageId)
	{
		$db = ES::db();
		$query = 'SELECT * FROM ' . $db->nameQuote('#__social_conversations_attachments') . ' '
				. 'WHERE ' . $db->nameQuote('message_id') . '=' . $db->Quote($messageId) . ' '
				. 'AND ' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_CONVERSATION_ATTACHMENTS_PUBLISHED);

		$db->setQuery($query);
		$data = $db->loadObjectList();

		if(!$data)
		{
			return $data;
		}

		$attachments= array();

		foreach($data as $row)
		{
			// We do not want to load this all the time as this loads uneccessary queries.
			$attachment= ES::table('ConversationAttachment');
			$attachment->bind($row);

			$attachments[] = $attachment;
		}

		return $attachments;
	}

	/**
	 * Detects if a conversation contains any attachments.
	 *
	 * @param	int	$conversationId		The conversation id to lookup for.
	 * @return	boolean					True on success, false otherwise.
	 **/
	 public function hasAttachments($conversationId)
	 {
		$db = ES::db();

		$query = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_conversations') . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote('#__social_conversations_message') . ' AS b '
				. 'ON b.' . $db->nameQuote('conversation_id') . ' = a.' . $db->nameQuote('id') . ' '
				. 'INNER JOIN ' . $db->nameQuote('#__social_files') . ' AS c '
				. 'ON c.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('id') . ' '
				. 'AND c.' . $db->nameQuote('type') . ' = ' . $db->Quote('conversations') . ' '
				. 'WHERE a.' . $db->nameQuote('id') . ' = ' . $db->Quote($conversationId);
		$db->setQuery($query);

		$exists= $db->loadResult() > 0;

		return $exists;
	 }

	/**
	 * Retrieves the last message user id
	 * used by a custom request. #2391
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function getLastMessageUserId($conversationId , $viewerId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->column('b.created_by');
		$sql->select('#__social_conversations_message' , 'b');
		$sql->join('#__social_conversations_message_maps' , 'c');
		$sql->on('c.message_id' , 'b.id');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on( 'b.created_by' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'b.created_by' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('c.conversation_id', $conversationId);
		$sql->where('c.user_id', $viewerId);

		// Skip the viewer last message
		$sql->where('b.created_by', $viewerId, '!=');

		$sql->order('b.created' , 'DESC');
		$sql->limit(0 , 1);

		$db->setQuery($sql);

		$result = $db->loadResult();

		// if there do not have any last message creator (not included the viewer)
		if (!$result) {

			$participants = $this->getParticipants($conversationId, array($viewerId));

			if (count($participants) <= 0 || count($participants) >= 2) {
				return false;
			}

			// Only return the first participant
			return $participants[0]->id;
		}

		return $result;
	}

	/**
	 * Remove participant
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function deleteParticipant($conversationId, $userId)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'DELETE FROM ' . $db->nameQuote('#__social_conversations_participants');
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

		$db->setQuery($query);

		return $db->Query();
	}

	/**
	 * Retrieves the last second message data
	 * And compare with the new message creation datetime and see whether need to send notification or not
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function sendNotification($message)
	{
		$db = ES::db();
		$query = array();

		// Retrieve the last second message created datetime
		$query[] = 'SELECT `created` FROM ' . $db->nameQuote('#__social_conversations_message');
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . ' = ' . $db->Quote($message->conversation_id);
		$query[] = 'AND ' . $db->nameQuote('created_by') . ' = ' . $db->Quote($message->created_by);
		$query[] = 'ORDER BY ' . $db->nameQuote('created') . 'DESC LIMIT 1,1';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$lastSecondMessageCreatedDate = $db->loadResult();

		if (!$lastSecondMessageCreatedDate) {
			return false;
		}

		// second conversation message created datetime timestamp
		$lastSecondMessageCreatedDateTimestamp = strtotime($lastSecondMessageCreatedDate);

		// new message created datetime timestamp
		$currentMessageCreatedDateTimestamp = strtotime($message->created);

		// calculate between message created datetime
		$timediff = $currentMessageCreatedDateTimestamp - $lastSecondMessageCreatedDateTimestamp;

		// Don't send email notification if the time different within 1 minute
		if ($timediff < 60) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user has participated in a conversation before or not.
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function hasParticipateBefore($conversationId , $userId)
	{
		$db = ES::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_conversations_message_maps');
		$query[] = 'WHERE ' . $db->nameQuote('conversation_id') . '=' . $db->Quote($conversationId);
		$query[] = 'AND ' . $db->nameQuote('user_id') . '=' . $db->Quote($userId);

		// Glue query back.
		$query = implode(' ' , $query);
		$db->setQuery($query);

		$hasParticipanted = $db->loadResult() > 0;
		return $hasParticipanted;
	}

	/**
	 * Method to cleanup older deleted messages
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cleanupDeletedMessages($days = 15)
	{
		$db = $this->db;

		$sql = $db->sql();
		$date = ES::date();

		$query = 'DELETE FROM `#__social_conversations_message_deleted` WHERE DATE_ADD(`created`, INTERVAL ' . $days . ' DAY) <= ' . $db->Quote($date->toMySQL());

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}
}
