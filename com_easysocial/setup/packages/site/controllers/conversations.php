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

ES::import('site:/controllers/controller');

class EasySocialControllerConversations extends EasySocialController
{
	/**
	 * Creates a new conversation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function store()
	{
		ES::requireLogin();
		ES::checkToken();

		$data = $this->input->getArray('post');

		// use the raw content for the messege input #5042
		$data['message'] = $this->input->get('message', '', 'raw');

		$isMarketplace = false;

		if (isset($data['ctype']) && $data['ctype'] == SOCIAL_CONVERSATION_MARKETPLACE) {
			$isMarketplace = true;
		}

		// Here we need to determine whether user are trying to send message to all users in the site.
		// Only site admin are able to use this feature
		if ($this->my->isSiteAdmin() && isset($data['sendToAll']) && $data['sendToAll']) {
			return $this->view->call('massConversation');
		}

		// Check for honeypot traps
		if ($this->config->get('honeypot.conversations')) {
			$honeypot = ES::honeypot();
			$trapped = $honeypot->isTrapped('conversations');

			if ($trapped) {
				$this->view->setMessage('Message Sent');
				return $this->view->call(__FUNCTION__);
			}
		}

		$conversation = ES::conversation();

		$allowed = $conversation->canCreate();

		if (!$allowed) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_DURING_SENDING_MESSAGE'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Bind the conversation
		$conversation->bind($data);

		// No recipient
		if (!$conversation->recipients) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_CONVERSATIONS_INVALID_RECIPIENTS_PROVIDED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$isFriends = $conversation->isFriends();

		if (!$isFriends && !$isMarketplace) {
			$this->view->setMessage(JText::_('COM_ES_CONVERSATIONS_ERROR_CANNOT_SEND_TO_NONFRIEND_USER'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Let's save the conversation now
		$state = $conversation->save();

		if (!$state) {
			$errMsg = $conversation->getError();

			if ($errMsg === false) {
				$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_DURING_SENDING_MESSAGE', ES_ERROR);
			} else {
				$this->view->setMessage($errMsg, ES_ERROR);
			}

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_MESSAGE_SENT', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $conversation);
	}

	/**
	 * Process mass conversation
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function massStore()
	{
		ES::checkToken();
		ES::requireLogin();

		if (!$this->my->isSiteAdmin()) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NOT_ALLOWED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$limitstart = $this->input->get('nextstart', 0, 'int');
		$limit = $this->input->get('limit', 50, 'int');

		$ids = ES::model('Users')->getAllUserIds(true, $limit, $limitstart);

		$message = $this->input->get('message', '', 'string');
		$uploadId = $this->input->get('uploadId', '', 'default');

		if (!$ids && $uploadId) {
			foreach ($uploadId as $attachment) {
				if ($attachment) {
					$uploader = ES::table('Uploader');
					$uploader->load($attachment);
					$uploader->delete();
				}
			}

			return $this->ajax->resolve();
		}

		$data = array();
		$data['message'] = $message;
		$data['upload-id'] = $uploadId;

		$sendOptions = array('validateData' => false);

		foreach ($ids as $id) {

			$conversation = ES::conversation();

			// Manually assign the user id
			$data['uid'] = $id;

			// We need to retain the attachment temporary files from being delete during saving process.
			$data['deleteTmpAttachments'] = false;

			// Bind the data.
			$conversation->bind($data);

			// Let's save the conversation.
			$conversation->save($sendOptions);

			// After save, reset back user id.
			$data['uid'] = '';
			$conversation = null;
		}

		return $this->ajax->resolve();
	}

	/**
	 * Load previous message
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadPrevious()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$limitstart = $this->input->get('limitstart', 0, 'int');

		$model = ES::model('Conversations');
		$model->setState('limitstart', $limitstart);

		$limit = ES::getLimit('messages_limit');

		$messages = $model->setLimit($limit)->getMessages($id, $this->my->id);
		$pagination = $model->getPagination();

		$nextlimit = ($limitstart + $pagination->limit >= $pagination->total) ? 0 : $limitstart + $pagination->limit;

		return $this->view->call(__FUNCTION__, $messages, $nextlimit);
	}

	/**
	 * Processes a new reply for an existing conversation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reply()
	{
		ES::requireLogin();
		ES::checkToken();

		$data = $this->input->getArray('post');

		// need to get the raw content for message input as the input->getArray() will filter out any html tags. #5042
		$data['message'] = $this->input->get('message', '', 'raw');

		$id = (int) $data['id'];
		$conversation = ES::conversation($id);

		// If conversation id is invalid or not supplied, we need to throw some errors.
		if (!$id || !$conversation->id || !$conversation->isReadable($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Determines if the user has access to this conversation
		$hasAccess = $conversation->hasAccess();

		if (!$hasAccess) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Check for honeypot traps
		if ($this->config->get('honeypot.conversations')) {
			$honeypot = ES::honeypot();
			$trapped = $honeypot->isTrapped('conversations');

			if ($trapped) {
				$this->view->setMessage('Message Sent', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		// Bind the post data
		$conversation->bind($data);

		$state = $conversation->save();

		if ($state === false) {
			$this->view->setMessage($conversation->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		//set the message->day to 0. This 'day' variable is need in themes file.
		$conversation->message->day = 0;

		// Return message back to the view.
		return $this->view->call(__FUNCTION__, $conversation, $conversation->message);
	}

	/**
	 * Deletes an attachment
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteAttachment()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', '', 'int');

		$file = ES::table('File');
		$file->load($id);

		// Check if the file is owned by the user.
		$allowed = $file->deleteable();

		if (!$allowed) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_TO_DELETE_ATTACHMENT', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$state = $file->delete();

		if (!$state) {
			$this->view->setMessage($file->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes a conversation from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$conversation = ES::conversation($id);

		if (!$id || !$conversation->id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Determines if the user has access to this conversation
		$hasAccess = $conversation->hasAccess();

		// we need to further check if this user has previously participate in this conversation before or not.
		// this could due to user being 'removed' from the group conversation. #3816
		if (!$hasAccess) {
			$hasAccess = $conversation->hasParticipateBefore();
		}

		if (!$hasAccess) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Let's try to delete the conversation now
		$state = $conversation->delete();

		// If there's an error deleting, spit it out.
		if (!$state) {
			$this->view->setMessage($conversation->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_DELETED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Unarchives a conversation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function unarchive()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$conversation = ES::conversation($id);

		if (!$id || !$conversation->id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Check if the user has access to this conversation.
		if (!$conversation->hasAccess($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try to unarchive the conversation.
		if (!$conversation->unarchive($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_UNARCHIVING_CONVERSATION', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_CONVERSATION_UNARCHIVED', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Archives a conversation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function archive()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$conversation = ES::conversation($id);

		// Test if the conversation exist in the system.
		if (!$id || !$conversation->id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Test if user has access to the conversation.
		if (!$conversation->hasAccess($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Test if there's any problem archiving the conversation.
		if (!$conversation->archive($this->my->id)) {
			$this->view->setMessage($conversation->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_CONVERSATION_ARCHIVED', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Mark a conversation as unread for a specific node.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function markUnread()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		// If there's no id's passed, we should just ignore this and throw some errors.
		if (!$id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the conversation table.
		$conversation = ES::conversation($id);

		if (!$id || !$conversation->id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Check if the user has access to mark this as unread.
		if (!$conversation->hasAccess($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Mark this item as unread for the current user.
		$conversation->markAsUnread($this->my->id);

		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_MARKED_AS_UNREAD', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allow a user to leave a conversation.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function leave()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the conversation id.
		$id = $this->input->get('id');
		$conversation = ES::conversation($id);

		if (!$id || !$conversation->id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		// Check if the user has access to this conversation
		if (!$conversation->hasAccess($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		// Let's try to leave the conversation.
		$state = $conversation->leave($this->my->id);

		if (!$state) {
			$this->view->setMessage($conversation->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_LEFT_CONVERSATION_SUCCESS', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Adds a user into an existing conversation.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function addParticipant()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$conversation = ES::conversation($id);

		// Check that there are recipients.
		if (!$id || !$conversation->id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_INVALID_CONVERSATION_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		// Check if the user is allowed to add people to the conversation
		if (!$conversation->isParticipant()) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_ACCESS_TO_CONVERSATION', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		// Get the new recipients.
		$recipients = $this->input->get('uid', array(), 'array');

		// Ensure that the recipients is in an array form.
		$recipients = ES::makeArray($recipients);

		// Check that there are recipients.
		if (!$recipients || empty($recipients)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_INVALID_RECIPIENTS_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		// Let's go through the list of recipients and add them to the conversation.
		foreach ($recipients as &$id) {

			// Run cleanup on the node id to make sure that they are all typecasted to integer.
			$id = (int) $id;
			$state = $conversation->addParticipant($this->my->id, $id);

			if (!$state) {
				$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_ADDING_PARTICIPANT', ES_ERROR);
				return $this->view->call(__FUNCTION__, $conversation);
			}
		}

		$conversationTable = ES::table('Conversation');
		$conversationTable->load($conversation->id);

		// We need to update the conversation type to multiple
		$conversationTable->type = SOCIAL_CONVERSATION_MULTIPLE;
		$conversationTable->store();

		// Send notification email to recipients that got invited to the conversation
		foreach ($recipients as $recipientId) {
			$recipient = ES::user($recipientId);

			// Add new notification item
			$mailParams = ES::registry();
			$mailParams->set('actor', $this->my->getName());
			$mailParams->set('name', $recipient->getName());
			$mailParams->set('authorName', $this->my->getName());
			$mailParams->set('authorAvatar', $this->my->getAvatar());
			$mailParams->set('authorLink', $this->my->getPermalink(true, true));
			$mailParams->set('conversationLink', $conversation->getPermalink(true, true));

			$title = 'COM_EASYSOCIAL_EMAILS_YOU_ARE_INVITED_TO_A_CONVERSATION_SUBJECT';

			// Send a notification for all participants in this thread.
			$state = ES::notify('conversations.invite', array($recipientId), array('title' => $title, 'params' => $mailParams), false);
		}

		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_MESSAGE_SENT', SOCIAL_MSG_SUCCESS);
		$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ADDED_RECIPIENTS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $conversation);
	}

	/**
	 * Retrieves a list of conversation notifications for the user.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getNotificationItems()
	{
		ES::requireLogin();
		ES::checkToken();

		$paginate = $this->input->get('paginate', false, 'bool');
		$limit = 0;

		if ($paginate) {
			$limit = 8;
		}

		// Get the conversations model
		$model = ES::model('Conversations');
		$options = array('sorting' => 'lastreplied', 'ordering' => 'desc', 'maxlimit' => $limit);

		$filter = $this->input->get('filter', '', 'word');

		if ($filter) {
			$options['filter'] = $filter;
		}

		$conversations = $model->getConversations($this->my->id, $options);

		// Mark all items as read if auto read is enabled.
		if ($this->config->get('notifications.conversation.autoread')) {
			foreach ($conversations as $item) {
				$model->markAsRead($item->id, $this->my->id);
			}
		}

		return $this->view->call(__FUNCTION__, $conversations);
	}


	/**
	 * Returns a list of conversations.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCount()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Conversations');
		$mailbox = $this->input->get('mailbox', '', 'word');

		$total = $model->getNewCount($this->my->id, $mailbox);

		return $this->view->call(__FUNCTION__, $total);
	}

	/**
	 * Updates the title of the conversation
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function updateTitle()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$title = $this->input->get('title', '', 'default');

		$conversation = ES::conversation($id);

		if (!$title) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_EMPTY_TITLE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		if (!$id || !$conversation->id) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		// Check if the user has access to this conversation
		if (!$conversation->hasAccess($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $conversation);
		}

		$conversation->saveTitle($title);

		return $this->view->call(__FUNCTION__, $conversation);
	}

	/**
	 * Deletes participant from the group conversation.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function deleteParticipant()
	{
		ES::requireLogin();
		ES::checkToken();

		$conversationId = $this->input->get('conversationId', 0, 'int');
		$participantId = $this->input->get('participantId', 0, 'int');

		$conversation = ES::conversation($conversationId);

		if (!$conversationId || !$participantId || !$conversation->id) {
			$message = JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_ID');
			return $this->view->call(__FUNCTION__, $message);
		}

		// delete participant
		$state = $conversation->deleteParticipant($participantId, $this->my->id);

		// If there's an error deleting, spit it out.
		if (!$state) {
			$message = $conversation->getError();
			return $this->view->call(__FUNCTION__, $message);
		}

		$user = ES::user($participantId);
		$deletedParticipantName = $user->getName();

		$message = JText::sprintf('COM_ES_CONVERSATIONS_DELETED_PARTICIPANT_SUCCESSFULLY', $deletedParticipantName);
		return $this->view->call(__FUNCTION__, $message);
	}

	/**
	 * Deletes a message
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function deleteMessage()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', '', 'int');
		$conversationId = $this->input->get('conversationId', 0, 'int');

		// Check if user is able to access this conversation
		$conversation = ES::conversation($conversationId);

		if (!$conversation->canDeleteMessage($this->my->id, $id)) {
			return $this->ajax->reject(JText::_('COM_ES_CONVERSATIONS_NOT_ALLOWED_TO_DELETE_MESSAGE'));
		}

		$conversation->deleteMessage($id);

		$message = JText::_('COM_ES_CONVERSATIONS_DELETE_MESSAGE_SUCCESS');
		return $this->ajax->resolve($message);
	}
}
