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

class EasySocialViewConversations extends EasySocialSiteView
{
	/**
	 * Checks if the conversation system is enabled.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isFeatureEnabled()
	{
		$state = $this->config->get('conversations.enabled');

		if (!$state) {
			return $this->exception('COM_EASYSOCIAL_CONVERSATIONS_NOT_ENABLED');
		}
	}

	/**
	 * Displays a list of conversations for a particular user. This is the default view of the conversations.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::requireLogin();
		ES::checkCompleteProfile();
		ES::setMeta();

		$isArchive = false;
		$options = array(
					'sorting' => $this->config->get('conversations.sorting'),
					'ordering' => $this->config->get('conversations.ordering'),
					'limit' => ES::getLimit('conversation_limit')
				);

		$filter = '';

		$active = $this->input->get('type', 'inbox');
		if ($active == 'archives') {
			$options['archives'] = true;
		} else {
			$filter = $this->input->get('filter');

			if ($filter == 'all') {
				$filter = '';
			}

			if ($filter) {
				$options['filter'] = $filter;
			}
		}

		$start = $this->input->get('limitstart', 0, 'int');
		$this->input->set('limitstart', $start); // set here so that model can get the limitstart

		// Load the conversation model.
		$model = ES::model('Conversations');

		$conversations = $model->getConversations($this->my->id, $options);


		// Get the active conversation
		$id = $this->input->get('id', 0, 'int');
		$activeConversation = null;

		// Retrieve the last message creator id from this conversation
		$lastCreatorUserId = null;
		$lastCreatorUserEmail = null;
		$lastCreatorExist = false;

		$isListing = !$id ? true : false;

		// If there was an id, we know the user wants to view an active conversation
		if ($id) {
			$conversation = ES::conversation($id);

			// Check if user can read this or not.
			if (!$conversation->isReadable($this->my->id) && !$conversation->hasParticipateBefore($this->my->id)) {

				// redirect user back to conversations page.
				ES::info()->set(JText::_('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_TO_READ'), SOCIAL_MSG_ERROR);
				return $this->redirect(ESR::conversations(array(), false));
			}


			if ($conversation->id) {
				$activeConversation = $conversation;
			}

			if (defined('ES_CONVERSATION_MIME')) {

				// default it to javascript void.
				$lastCreatorUserEmail = 'javascript:void(0)';
				$lastCreatorUserId = $model->getLastMessageUserId($conversation->id, $this->my->id);

				if ($lastCreatorUserId) {
					$lastCreatorUserObj = ES::user($lastCreatorUserId);

					// Retrieve the user email
					$lastCreatorUserEmail = ES_CONVERSATION_MIME . $lastCreatorUserObj->email;
					$lastCreatorExist = true;
				}
			}
		}

		// If there is no id provided, we load up the first item
		if (!$activeConversation && $conversations && count($conversations) > 0) {
			$activeConversation = $conversations[0];
		}

		// Mark the discussion as read since it is already opened
		if ($activeConversation) {
			$activeConversation->markAsRead($this->my->id);

			// @trigger: onPrepareConversations
			$dispatcher = ES::dispatcher();
			$args = array(&$messages);

			$dispatcher->trigger(SOCIAL_TYPE_USER, 'onPrepareConversations', $args);
		}

		$pagination = $model->getPagination();

		// Set the page title
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_CONVERSATIONS_INBOX');
		if ($active == 'archives') {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_CONVERSATIONS_ARCHIVES');
			$isArchive = true;
		}

		ES::document()->title($title);

		// Set breadcrumbs
		ES::document()->breadcrumb($title);

		// Check if user has access to create new conversations
		$access = ES::access();

		$totalInbox = $model->getTotalCount($this->my->id , 'inbox');
		$totalArchive = $model->getTotalCount($this->my->id, 'archives');

		$this->set('totalInbox', $totalInbox);
		$this->set('totalArchive', $totalArchive);
		$this->set('active', $active);
		$this->set('conversations', $conversations);
		$this->set('activeConversation', $activeConversation);
		$this->set('pagination', $pagination);
		$this->set('filter', $filter);
		$this->set('isArchive', $isArchive);
		$this->set('lastCreatorUserEmail', $lastCreatorUserEmail);
		$this->set('lastCreatorExist', $lastCreatorExist);
		$this->set('access', $access);
		$this->set('isListing', $isListing);

		// calcuate the nextlimit for conversations
		$nextlimit = -1;
		if ($pagination) {
			if ($pagination->pagesTotal > $pagination->pagesCurrent) {
				$nextlimit = $pagination->pagesCurrent * $pagination->limit;
			}
		}

		$this->set('nextlimit', $nextlimit);

		$model = ES::model('Emoticons');
		$this->set('emoticons', $model->getJsonEmoticons());

		echo parent::display('site/conversations/default/default');
	}


	/**
	 * Archives layout displays all conversations that are archived.
	 *
	 * @param	null
	 */
	public function archives()
	{
		// Deprecated since 4.0.7. Use normal display instead with type=archives
		$this->input->set('type', 'archives');

		return $this->display();
	}

	/**
	 * Displays a list of unread conversations for a particular user.
	 *
	 * @param	null
	 * @return	null
	 */
	public function unread()
	{
		// We know for user that the guest cannot access conversations.
		ES::requireLogin();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		$my = ES::user();
		$config = ES::config();
		$options = array(
					'sorting' => $config->get('conversations.list.sorting'),
					'ordering' => $config->get('conversations.list.ordering'),
					'filter' => 'unread'
				);

		$model = ES::model('Conversations');
		$conversations = $model->getConversations($my->get('node_id'), $options);
		$pagination = $model->getPagination();

		$this->set('conversations', $conversations);
		$this->set('pagination', $pagination);

		echo parent::display('site/conversations/conversations');
	}

	/**
	 * Processes after the unread task is called.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function markUnread()
	{
		$info = ES::info();
		$info->set($this->getMessage());

		$url = ESR::conversations(array(), false);

		return $this->redirect($url);
	}

	/**
	 * This method is invoked when a conversation is created.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($conversation = null)
	{
		$info = ES::info();

		// var_dump( $this->getMessage() );exit;
		$info->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->compose();
		}

		$this->redirect(ESR::conversations(array('id' => $conversation->id), false));
	}

	/**
	 * Renders the conversation composer on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compose($conversation = null)
	{
		ES::requireLogin();
		ES::checkCompleteProfile();
		ES::setMeta();

		$conversation = ES::conversation();
		$allowed = $conversation->canCreate();

		// This should render an exception because the user shouldn't be allowed to access this page at all
		if (!$allowed) {
			// redirect user back to conversations page.
			ES::info()->set(false, $conversation->getError(), SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::conversations(array(), false));
		}

		// Set the page title
		$title = 'COM_EASYSOCIAL_PAGE_TITLE_CONVERSATIONS_COMPOSE';

		$this->page->title($title);
		$this->page->breadcrumb($title);

		// There could be errors on the form, we need to reset the message
		$message = $this->input->getVar('message', '');

		// Get a list of friend list from the current user.
		$listModel = ES::model('Lists');
		$lists = $listModel->getLists(array('user_id' => $this->my->id));

		$model = ES::model('Emoticons');

		$this->set('lists', $lists);
		$this->set('message', $message);
		$this->set('emoticons', $model->getJsonEmoticons());

		parent::display('site/conversations/compose/default');
	}


	/**
	 * Responsible to make the appropriate redirect calls after a conversation is unarchived.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function unarchive()
	{
		$info = ES::info();
		$info->set($this->getMessage());

		$url = ESR::conversations(array(), false);
		return $this->redirect($url);
	}

	/**
	 * Determines what should be done after the conversation is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function archive()
	{
		$info = ES::info();
		$info->set($this->getMessage());

		$url = ESR::conversations(array(), false);
		return $this->redirect($url);
	}

	/**
	 * Determines what should be done after the conversation is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		$info = ES::info();
		$info->set($this->getMessage());

		$url = ESR::conversations(array(), false);
		return $this->redirect($url);
	}

	/**
	 * Determins what should be done after a participant is added into the conversation.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function addParticipant(SocialConversation $conversation)
	{
		$info = ES::info();
		$info->set($this->getMessage());

		$url = ESR::conversations(array('id' => $conversation->id), false);
		return $this->redirect($url);
	}

	/**
	 * Allows viewer to download a conversation file
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function download()
	{
		// Currently only registered users are allowed to view a file.
		ES::requireLogin();

		// Get the file id from the request
		$fileId = $this->input->get('fileid', null, 'int');

		$file = ES::table('File');
		$file->load($fileId);

		if (!$file->id || !$fileId) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Load up the conversation message
		$message = ES::table('ConversationMessage');
		$message->load($file->uid);

		// Something went wrong with this discussion as it doesn't have participants
		if (!$message->id) {
			// Throw error message here.
			$this->redirect( ESR::dashboard( array() , false ) );
			$this->close();
		}

		$conversation = ES::conversation($message->conversation_id);

		// Something went wrong with this discussion as it doesn't have participants
		if (!$conversation->id) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Check if viewer is a participant
		if (!$conversation->isParticipant($this->my->id)) {
			// Throw error message here.
			$this->redirect( ESR::dashboard(array(), false));
			$this->close();
		}


		$file->download();
		exit;
	}

	/**
	 * Allows viewer to download a conversation file
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preview()
	{
		// Currently only registered users are allowed to view a file.
		ES::requireLogin();

		// Get the file id from the request
		$fileId = $this->input->get('fileid', null, 'int');

		$file = ES::table('File');
		$file->load($fileId);

		if (!$file->id || !$fileId) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Load up the conversation message
		$message = ES::table('ConversationMessage');
		$message->load($file->uid);

		// Something went wrong with this discussion as it doesn't have participants
		if (!$message->id) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		$conversation = ES::conversation($message->conversation_id);

		// Something went wrong with this discussion as it doesn't have participants
		if (!$conversation->id) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Get the current viewer
		$my = ES::user();

		// Check if viewer is a participant
		if (!$conversation->isParticipant($my->id)) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		$file->preview();
		exit;
	}

	/**
	 * Post processing after leaving a conversation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function leave()
	{
		$info = ES::info();
		$info->set($this->getMessage());

		$url = ESR::conversations(array(), false);
		return $this->redirect($url);
	}

	/**
	 * Process mass conversation store
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function massConversation()
	{
		ES::checkToken();
		ES::requireLogin();

		if (!$this->my->isSiteAdmin()) {
			return $this->exception('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NOT_ALLOWED');
		}

		$message = $this->input->get('message', '', 'string');
		$uploadId = $this->input->get('upload-id', '', 'default');

		if (!$message) {
			$this->info->set(false, 'COM_EASYSOCIAL_CONVERSATIONS_ERROR_EMPTY_MESSAGE', ES_ERROR);
			return $this->redirect(ESR::conversations(array('layout' => 'compose')));
		}

		// Get total users on the site (exclude self).
		$model = ES::model('Users');
		$totalUsers = $model->getTotalUsers() - 1;

		$this->set('message', $message);
		$this->set('uploadId', $uploadId);
		$this->set('totalUsers', $totalUsers);

		parent::display('site/conversations/compose/massconversation');
	}

	/**
	 * Post processing after processing mass conversations
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function createMassSuccess()
	{
		$this->info->set(false, 'COM_EASYSOCIAL_CONVERSATIONS_MESSAGE_SENT', SOCIAL_MSG_SUCCESS);

		$this->redirect(ESR::conversations());
	}
}
