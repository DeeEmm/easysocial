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

ES::import('site:/views/views');

class EasySocialViewConversations extends EasySocialSiteView
{
	/**
	 * Display dialog to confirm deleting of attachment
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmDeleteAttachment()
	{
		// Users must be logged in
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);

		$contents = $theme->output('site/conversations/dialog/delete.attachment');
		return $this->ajax->resolve($contents);
	}

	/**
	 * Display post message after an attachment is deleted
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function attachmentDeleted()
	{
		// Users must be logged in
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$theme = ES::themes();

		$theme->set('id', $id);
		$contents = $theme->output('site/conversations/dialog/attachment.deleted');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after deleting an attachment
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteAttachment()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$message = JText::_('COM_EASYSOCIAL_CONVERSATIONS_DELETE_ATTACHMENT_DIALOG_DELETED');
		return $this->ajax->resolve($message);
	}

	/**
	 * Displays the composer form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function composer()
	{
		// User needs to be logged in.
		ES::requireLogin();

		// We need to know if the user wants to send to a list or a user id
		$id = $this->input->get('id', 0, 'int');
		$listId = $this->input->get('listId', 0, 'int');
		$message = $this->input->get('message', '', 'default');
		$ctype = $this->input->get('ctype', '', 'default');
		$title = $this->input->get('title', '', 'default');
		$type = 'user';

		$theme = ES::themes();

		if ($id) {

			$recipient = ES::user($id);

			// Check if the recipient allows the sender to send message
			$privacy = $this->my->getPrivacy();

			if (!$privacy->validate('profiles.post.message', $id, SOCIAL_TYPE_USER)) {
				$contents = $theme->output('site/conversations/dialog/disallowed');

				return $this->ajax->resolve($contents);
			}

			if (!$this->my->canStartConversation($id) && $ctype != SOCIAL_CONVERSATION_MARKETPLACE) {
				return $this->ajax->reject(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS'));
			}

			$recipients = array($recipient);
			$theme->set('recipient', $recipient);
		} else {

			$type = 'list';
			$list = ES::table('List');
			$list->load($listId);

			if (!$list->id || !$listId) {
				return $this->ajax->reject(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_INVALID_LIST_ID'));
			}

			// Check if the user really has access to send to this list.
			if ($list->user_id != $this->my->id) {
				return $this->ajax->reject(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS'));
			}

			$users = $list->getMembers();
			$recipients = ES::user($users);

			$theme->set('list', $list);
		}

		$theme->set('message', $message);
		$theme->set('type', $type);
		$theme->set('recipients', $recipients);
		$theme->set('ctype', $ctype);
		$theme->set('title', $title);

		$contents = $theme->output('site/conversations/dialog/compose');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the sent confirmation dialog to a list
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function sentList()
	{
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$list = ES::table('List');
		$list->load($id);

		$theme = ES::themes();
		$theme->set('list', $list);

		$contents = $theme->output('site/conversations/dialog/sentlist');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the sent confirmation dialog
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function sent()
	{
		// User needs to be logged in.
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$recipient = ES::user($id);

		$theme = ES::themes();
		$theme->set('recipient', $recipient);

		$contents = $theme->output('site/conversations/dialog/sent');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allow caller to view participants
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function viewParticipants()
	{
		ES::requireLogin();

		$id = $this->input->get('id');
		$showDeleteParticipantButton = false;

		// Load the conversation
		$conversation = ES::conversation($id);

		if (!$conversation->isParticipant()) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS'), SOCIAL_MSG_ERROR);

			return $this->ajax->reject($this->getMessage());
		}

		$model = ES::model('Conversations');
		$participants = $model->getParticipants($conversation->id);

		// Count total of participant for this conversation
		$totalParticipants = count($participants);

		// Determine if the current logged in user is this conversation owner
		// And this conversation must have more than 2 participant then only show this delete participant button
		if (($conversation->created_by == $this->my->id || $this->my->isSiteAdmin()) && $totalParticipants > 2) {
			$showDeleteParticipantButton = true;
		}

		$theme = ES::themes();
		$theme->set('participants', $participants);
		$theme->set('conversation', $conversation);
		$theme->set('showDeleteParticipantButton', $showDeleteParticipantButton);

		$contents = $theme->output('site/conversations/dialog/participants');

		return $this->ajax->resolve($contents);

	}

	/**
	 * Allows user to add participant to an existing conversation
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function addParticipantsForm()
	{
		// User must be logged in
		ES::requireLogin();

		$id = $this->input->get('id');

		// Load up the conversation
		$conversation = ES::conversation($id);

		// Check if the current user is a participant
		if (!$conversation->isParticipant()) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS'), SOCIAL_MSG_ERROR);

			return $this->ajax->reject($this->getMessage());
		}

		// Get a list of participants
		$participants = $conversation->getParticipants();
		$ids = array();

		foreach ($participants as $user) {
			$ids[]  = $user->id;
		}

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$theme->set('id', $id);

		$contents = $theme->output('site/conversations/dialog/add.participant');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the dialog to confirm delete participant
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function confirmDeleteParticipant()
	{
		// Require user to be logged in
		ES::requireLogin();

		$conversationId = $this->input->get('conversationId', 0, 'int');
		$participantId = $this->input->get('participantId', 0, 'int');

		$theme = ES::themes();
		$theme->set('conversationId', $conversationId);
		$theme->set('participantId', $participantId);

		$contents = $theme->output('site/conversations/dialog/delete.participant');

		$this->ajax->resolve($contents);
	}

	/**
	 * Leave conversation confirmation form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmLeave()
	{
		// User must be logged in
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id' , $id);
		$contents = $theme->output('site/conversations/dialog/leave');

		return $this->ajax->resolve($contents);
	}


	/**
	 * Displays the dialog to confirm unarchive
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmUnArchive()
	{
		// Require user to be logged in
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);
		$contents = $theme->output('site/conversations/dialog/unarchive');

		$this->ajax->resolve($contents);

	}

	/**
	 * Displays the dialog to confirm archive
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmArchive()
	{
		// Require user to be logged in
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);
		$contents = $theme->output('site/conversations/dialog/archive');

		$this->ajax->resolve($contents);
	}

	/**
	 * Displays the dialog to confirm deletion
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmDelete()
	{
		// Require user to be logged in
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);
		$contents = $theme->output('site/conversations/dialog/delete');

		$this->ajax->resolve($contents);
	}

	/**
	 * Responsible to process an ajax call that tries to store a conversation.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function store($conversation = null)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$link = FRoute::conversations(array('id' => $conversation->id) , false);
		return $this->ajax->resolve($link);
	}

	/**
	 * Handle output after conversation has been marked as unread
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function markUnread()
	{
		// Check if there's an error in this request
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	/**
	 * Handle output after conversation has been marked as unread
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function markRead()
	{
		// Check if there's an error in this request
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	/**
	 * Responsible to output a JSON encoded data.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function reply($conversation, $message = '')
	{
		ES::requireLogin();

		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		// Assign missing properties
		$conversation->message = $message->message;

		// @trigger: onPrepareConversations
		$dispatcher = ES::dispatcher();
		$conversations = array(&$conversation);
		$args = array(&$conversations);

		// Trigger event
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onPrepareConversations' , $args);

		$theme = ES::themes();
		$theme->set('conversation', $conversation);
		$theme->set('message', $message);
		$content = $theme->output('site/conversations/message/default');

		$message = JText::_('COM_EASYSOCIAL_CONVERSATION_REPLY_POSTED_SUCCESSFULLY');

		return $this->ajax->resolve($message, $content, ES::date()->toSql());
	}

	/**
	 * Post processing for unarchive conversations
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function unarchive()
	{
		$errors = $this->getErrors();

		if ($errors) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	/**
	 * Post processing after the conversation is archived
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function archive()
	{
		$errors = $this->getErrors();

		if ($errors) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	/**
	 * Post process after retrieving conversations via notifications
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getNotificationItems($conversations)
	{
		$view = $this->input->get('view', '', 'string');

		$theme = ES::themes();
		$theme->set('conversations', $conversations);
		$theme->set('view', $view);

		$output = $theme->output('site/conversations/popbox/notifications');

		return $this->ajax->resolve($output);
	}

	/**
	 * Method to return the JSON response back to the caller to update the counter.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getCount($total)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve($total);
	}

	/**
	 * Method to return the JSON response back to the caller.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getItems()
	{
		exit;
		ES::requireLogin();

		// If there's any errors, throw them
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		// Get the mail box from the request.
		$mailbox = $this->input->get('mailbox', '', 'word');
		$filter = $this->input->get('filter', '', 'word');

		if ($filter == 'all') {
			$filter = '';
		}

		$options = array(
			'sorting' => $this->config->get('conversations.sorting'),
			'ordering' => $this->config->get('conversations.ordering'),
			'limit' => ES::getLimit('conversation_limit')
			);

		// @TODO: In the future, we might want to separate mails in mailboxes.
		if ($mailbox == 'archives') {
			$options['archives'] = true;
		}

		if ($filter) {
			$options['filter'] = $filter;
		}

		// Load the conversation model.
		$model = ES::model('Conversations');
		$conversations = $model->getConversations($this->my->id, $options);
		$pagination = $model->getPagination();

		$pagination->setVar('view', 'conversations');

		if ($mailbox == 'archives') {
			$pagination->setVar('layout', 'archives');
		}

		if ($filter) {
			$pagination->setVar('filter', $filter);
		}

		$theme = ES::themes();
		$theme->set('pagination', $pagination);
		$theme->set('conversations', $conversations);

		$contents = $theme->output('site/conversations/default/item');
		return $this->ajax->resolve($contents , empty($conversations));

	}

	/**
	 * get conversations belong to user
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getConversations()
	{
		ES::requireLogin();

		$options = [
			'sorting' => $this->config->get('conversations.sorting'),
			'ordering' => $this->config->get('conversations.ordering'),
			'limit' => ES::getLimit('conversation_limit')
		];

		$filter = '';

		$active = $this->input->get('type', 'inbox', 'string');
		$keyword = $this->input->get('keyword', '', 'default');

		if (!empty($keyword)) {
			// We limit to max 50 match.
			$options['maxlimit'] = '50';
			$options['search'] = $keyword;
		}

		if ($active === 'archives') {
			$options['archives'] = true;
		}

		if ($active !== 'archives') {
			$filter = $this->input->get('filter', '', 'string');

			if ($filter === 'all') {
				$filter = '';
			}

			if ($filter) {
				$options['filter'] = $filter;
			}
		}

		$start = $this->input->get('limitstart', 0, 'int');

		// Set here so that model can get the limitstart
		$this->input->set('limitstart', $start);

		$isLoadmore = $this->input->get('loadmore', 0, 'int');

		// Load the conversation model.
		$model = ES::model('Conversations');

		$conversations = $model->getConversations($this->my->id, $options);
		$pagination = $model->getPagination();

		$activeConversation = null;

		// If there is no id provided, we load up the first item
		if (!$activeConversation && $conversations && count($conversations) > 0 && !$isLoadmore) {
			$activeConversation = $conversations[0];
		}

		$theme = ES::themes();

		$theme->set('lists', $conversations);
		$theme->set('activeConversation', $activeConversation);

		$contents = $theme->output('site/conversations/default/lists');

		$emptyListText = JText::_('COM_EASYSOCIAL_CONVERSATION_EMPTY_LIST');
		$emptyContentText = JText::_('COM_EASYSOCIAL_CONVERSATION_EMPTY');

		if ($active === 'archives') {
			$emptyListText = JText::_('COM_EASYSOCIAL_CONVERSATION_ARCHIVE_EMPTY_LIST');
			$emptyContentText = JText::_('COM_EASYSOCIAL_CONVERSATION_ARCHIVE_EMPTY');
		}

		// Let's calculate the next limist start for pagination
		$nextlimit = -1;

		if ($pagination) {
			if ($pagination->pagesTotal > $pagination->pagesCurrent) {
				$nextlimit = $pagination->pagesCurrent * $pagination->limit;
			}
		}

		return $this->ajax->resolve($contents, $emptyListText, $emptyContentText, $nextlimit);
	}

	/**
	 * Get messages from a single conversation
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getConversation()
	{
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$isLoadmore = $this->input->get('isloadmore', 0, 'int');

		$conversation = ES::conversation($id);

		// TODO: check if this user can view this conversation or not.
		if (!$conversation->isParticipant()) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_ACCESS_TO_CONVERSATION'));
		}

		$start = $this->input->get('limitstart', 0, 'int');
		$this->input->set('limitstart', $start); // set here so that model can get the limitstart

		$options = array('limit' => ES::getLimit('messages_limit'));

		// mark this conversation as read.
		$conversation->markAsRead($this->my->id);

		$contents = '';
		$title = $conversation->getTitle();


		$model = ES::model("Conversations");
		$messages = $model->getMessages($conversation->id, $this->my->id, $options);
		$pagination = $model->getPagination();

		// Retrieve the last message creator id from this conversation
		$lastCreatorUserId = null;
		$lastCreatorUserEmail = null;
		$lastCreatorExist = false;

		if (defined('ES_CONVERSATION_MIME')) {
			$lastCreatorUserEmail = 'javascript:void(0)';
			$lastCreatorUserId = $model->getLastMessageUserId($conversation->id, $this->my->id);

			if ($lastCreatorUserId) {
				$lastCreatorUserObj = ES::user($lastCreatorUserId);

				// Retrieve the user email
				$lastCreatorUserEmail = ES_CONVERSATION_MIME . $lastCreatorUserObj->email;
				$lastCreatorExist = true;
			}
		}


		if ($messages) {
			foreach($messages as $message) {
				$theme = ES::themes();
				$theme->set('conversation', $conversation);
				$theme->set('message', $message);
				$contents .= $theme->output('site/conversations/message/default');
			}
		}

		$lastupdate = ES::Date()->toSql();

		// lets calculate the next limist start for pagination
		$nextstart = -1;

		if ($pagination) {
			if ($pagination->pagesTotal > $pagination->pagesCurrent) {
				$nextlimit = $pagination->pagesCurrent * $pagination->limit;

				$theme = ES::themes();
				$theme->set('nextlimit', $nextlimit);
				$theme->set('id', $conversation->id);
				$paginationHtml = $theme->output('site/conversations/message/pagination');

				// prepand to contents.
				$contents = $paginationHtml . $contents;
			}
		}

		// get actions item for this conversation.
		$actions = '';

		if (! $isLoadmore) {
			$theme = ES::themes();
			$theme->set('conversation', $conversation);
			$actions = $theme->output('site/conversations/default/actions');
		}

		// make sure the user have permission to edit conversation title
		$canEditTitle = false;

		if ($conversation->canEditTitle()) {
			$canEditTitle = true;
		}

		return $this->ajax->resolve($title, $contents, $lastupdate, $actions, $lastCreatorUserEmail, $lastCreatorExist, $canEditTitle);
	}

	/**
	 * Retrieves a list of message items
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getNewMessages()
	{
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$lastupdate = $this->input->get('lastUpdate', ES::date()->toSql(), 'default');

		// Check if the conversation item is active or not
		$isActive = $this->input->get('isActive', false, 'bool');

		$conversation = ES::conversation($id);

		$model = ES::model('Conversations');
		$messages = $model->getNewMessages($id, $this->my->id, $lastupdate);

		$contents = '';
		$title = $conversation->getTitle();

		// Retrieve the last message creator id from this conversation
		$lastCreatorUserId = null;
		$lastCreatorUserEmail = null;
		$lastCreatorExist = false;

		if (defined('ES_CONVERSATION_MIME')) {
			$lastCreatorUserId = $model->getLastMessageUserId($conversation->id, $this->my->id);
			$lastCreatorUserEmail = 'javascript:void(0)';

			if ($lastCreatorUserId) {
				$lastCreatorUserObj = ES::user($lastCreatorUserId);

				// Retrieve the user email
				$lastCreatorUserEmail = ES_CONVERSATION_MIME . $lastCreatorUserObj->email;
				$lastCreatorExist = true;
			}
		}

		if ($messages) {
			foreach($messages as $message) {
				$theme = ES::themes();
				$theme->set('conversation', $conversation);
				$theme->set('message', $message);
				$contents .= $theme->output('site/conversations/message/default');
			}
		}

		if ($isActive) {
			// Mark the conversation as read since the user receive and read it on the spot #3702
			$conversation->markAsRead($this->my->id);
		}

		return $this->ajax->resolve($contents, $lastCreatorUserEmail, $lastCreatorExist);
	}

	/**
	 * Method to return the JSON response back to the caller to update the counter.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateTitle($conversation)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$newTitle = $conversation->getTitle();

		return $this->ajax->resolve($newTitle);
	}

	/**
	 * Return delete participant message
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteParticipant($message)
	{
		return $this->ajax->resolve($message);
	}

	/**
	 * Display confirmation dialog to delete a message
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function confirmDeleteMessage()
	{
		ES::requireLogin();

		$messageId = $this->input->get('messageId', 0, 'int');
		$conversationId = $this->input->get('conversationId', 0, 'int');

		$conversation = ES::conversation($conversationId);

		if (!$conversation->canDeleteMessage($this->my->id, $messageId)) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS'));
		}

		$theme = ES::themes();
		$theme->set('messageId', $messageId);

		$contents = $theme->output('site/conversations/dialog/delete.message');
		return $this->ajax->resolve($contents);
	}
}
