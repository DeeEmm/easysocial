<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialConversations extends EasySocial
{
	private $themeConfig = null;

	public function __construct()
	{
		parent::__construct();

		$this->themeConfig = ES::themes()->getConfig();
	}

	/**
	 * create new conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function create($userId, $post)
	{
		$this->validateUser($userId);

		$conversation = ES::conversation();
		$allowed = $conversation->canCreate();

		if (!$allowed) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_DURING_SENDING_MESSAGE'));
		}

		// Bind the conversation
		$conversation->bind($post);

		// Let's save the conversation now
		$state = $conversation->save();

		if (!$state) {
			$err = $conversation->getError();
			if (! $err) {
				$err = JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_DURING_SENDING_MESSAGE');
			}

			return $this->error($err);
		}

	   $data = $this->buildConversation($conversation);
	   return $data;
	}

	/**
	 * reply to a conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function reply($userId, $conversationId, $post)
	{
		$this->validateUser($userId);
		$this->validateConversation($conversationId);

		$conversation = ES::conversation($conversationId);
		$allowed = $conversation->canReply();

		if (!$allowed) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_DURING_SENDING_MESSAGE'));
		}

		$messge = isset($post['message']) ? $post['message'] : '';
		$attachments = isset($post['upload-id']) ? $post['upload-id'] : null;

		if (!$messge && !$attachments) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_EMPTY_MESSAGE'));
		}

		// bind the data for later processing
		$conversation->bind($post);

		// Let's save the conversation now
		$state = $conversation->save();

		if (!$state) {
			$err = $conversation->getError();
			return $this->error($err);
		}

		//set the message->day to 0 so that it will appear as 'now...'
		$conversation->message->day = 0;
		$data = $this->buildMessages($conversation->id, 1, array($conversation->message), $conversation->lastreplied);

		return $data;
	}


	/**
	 * delete a conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete($userId, $conversationIds)
	{
		$this->validateUser($userId);


		if (! is_array($conversationIds)) {
			$conversationIds = array($conversationIds);
		}

		foreach ($conversationIds as $cid) {

			$this->validateConversation($cid);

			$conversation = ES::conversation($cid);
			$hasAccess = $conversation->hasAccess();

			if (!$hasAccess) {
				return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS'));
			}

			// Let's try to delete the conversation now
			$state = $conversation->delete();

			if (!$state) {
				return $this->error($conversation->getError());
			}
		}

		return true;
	}


	/**
	 * archive conversations
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function archive($userId, $conversationIds)
	{
		$this->validateUser($userId);


		if (! is_array($conversationIds)) {
			$conversationIds = array($conversationIds);
		}

		foreach ($conversationIds as $cid) {

			$this->validateConversation($cid);

			$conversation = ES::conversation($cid);
			$hasAccess = $conversation->hasAccess();

			if (!$hasAccess) {
				return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_NO_ACCESS'));
			}

			// Let's try to delete the conversation now
			$state = $conversation->archive($userId);

			if (!$state) {
				return $this->error($conversation->getError());
			}
		}

		return true;
	}

	/**
	 * get conversations belong to user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getConversations($userId, $options = array())
	{
		$this->validateUser($userId);

		$model = ES::model('conversations');

		$sorting = isset($options['sorting']) ? $options['sorting'] : $this->themeConfig->get('conversation_sorting');
		$ordering = isset($options['ordering']) ? $options['ordering'] : $this->themeConfig->get('conversation_ordering');
		$limit = isset($options['limit']) ? $options['limit'] : $this->themeConfig->get('conversation_limit');
		$start = isset($options['start']) ? $options['start'] : 0;

		$searchOptions = array(
			'sorting' => $sorting,
			'ordering' => $ordering,
			'limit' => $limit
		);

		$lists = $model->getConversations($userId, $searchOptions);
		// get total counts
		$total = $model->getConversations($userId, array('count' => true));

		$data = new stdClass();
		$data->conversations = array();

		if ($lists) {
			foreach($lists as $item) {
				$obj = $this->buildConversation($item, $total);
				$data->conversations[] = $obj;
			}
		}

		return $data;
	}

	/**
	 * get messages for a particular conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getMessages($userId, $conversationId, $options = array())
	{
		$this->validateUser($userId);
		$this->validateConversation($conversationId);

		if (!$conversationId) {
			return $this->error(JText::_('Invalid conversation id provided.'));
		}

		$limit = isset($options['limit']) ? $options['limit'] : $this->themeConfig->get('conversation_limit');
		$start = isset($options['start']) ? $options['start'] : 0;

		$searchOptions = array(
			'limit' => $limit,
			'start' => $start
		);

		$model = ES::model("Conversations");
		$results = $model->getMessages($conversationId, $userId, $searchOptions);
		$total = $model->getTotal();

		$counter = sizeof($results);
		if ($counter > 0) {
			$data = $this->buildMessages($conversationId, $total, $results, $results[$counter-1]->created);
		}
		return $data;
	}

	/**
	 * get messages for a particular conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getNewMessages($userId, $conversationId, $lastupdate)
	{
		$this->validateUser($userId);
		$this->validateConversation($conversationId);

		if (!$userId) {
			return $this->error(JText::_('Invalid user id.'));
		}

		if (!$conversationId) {
			return $this->error(JText::_('Invalid conversation id provided.'));
		}

		$model = ES::model("Conversations");
		$results = $model->getNewMessages($conversationId, $userId, $lastupdate);
		$total = count($results);
		$data = null;
		if ($total > 0 ) {
			$data = $this->buildMessages($conversationId, $total, $results, $results[$total-1]->created);
		}
		return $data;
	}

	/**
	 * Mark a conversation to old.
	 *
	 * @return	booleanr
	 * @param	mix $conversationId - int or array of int
	 * @param	int $userId
	 */
	public function markAsRead($conversationIds, $userId)
	{
		$this->validateUser($userId);

		$model = ES::model('Conversations');

		if (! is_array($conversationIds)) {
			$conversationIds = array($conversationIds);
		}

		foreach($conversationIds as $cid) {
			$this->validateConversation($cid);
			$model->markAsRead($cid , $userId);
		}

		return true;
	}

	/**
	 * retrieve person who participants on a conversation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getParticipants($userId, $conversationId, $excludeUsers = array())
	{
		$this->validateUser($userId);
		$this->validateConversation($conversationId);

		$model = ES::model('Conversations');
		$users = $model->getParticipants($conversationId, $excludeUsers);

		$total = count($users);

		$data = $this->buildUsers($total, $users);
		return $data;
	}


	/**
	 * adding person to a conversation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function addParticipant($userId, $conversationId, $targetIds)
	{
		$this->validateUser($userId);
		$this->validateConversation($conversationId);

		if (!$targetIds) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_ADDING_PARTICIPANT'));
		}

		$conversation = ES::conversation($conversationId);

		// Check if the user is allowed to add people to the conversation
		if (!$conversation->isParticipant($userId)) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_ACCESS_TO_CONVERSATION'));
		}

		if (! is_array($targetIds)) {
			$targetIds = array($targetIds);
		}

		$ids = array();

		foreach ($targetIds as $targetId) {
			$id = (int) $targetId;
			$state = $conversation->addParticipant($userId, $id);

			if ($state === false) {
				return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_ADDING_PARTICIPANT'));
			}

			$ids[] = $id;
		}

		// now we need to make sure the conversation is now turn to 'group' chat.
		$conversationTable = ES::table('Conversation');
		$conversationTable->load($conversation->id);

		// We need to update the conversation type to multiple
		$conversationTable->type = SOCIAL_CONVERSATION_MULTIPLE;
		$conversationTable->store();

		// preload users
		ES::user($ids);

		$users = array();
		foreach ($ids as $id) {
			$users[] = ES::user($id);
		}

		$data = $this->buildUsers(count($users), $users);

		return $data;
	}

	 /**
	 * deleting person to a conversation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteParticipant($conversationId, $userId)
	{
		$this->validateUser($userId);
		$this->validateConversation($conversationId);

		$model = ES::model('conversations');
		$state = $model->leave($conversationId, $userId);

		if ($state === false) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_DELETING_PARTICIPANT'));
		}

		return true;
	}

	/**
	 * retrieve friends from user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getContacts($userId, $options = array())
	{
		$this->validateUser($userId);

		$limit = isset($options['limit']) ? $options['limit'] : $this->themeConfig->get('friendslimit');
		$start = isset($options['start']) ? $options['start'] : 0;

		$model = ES::model('Friends');

		$searchOptions = array(
			'state' => SOCIAL_FRIENDS_STATE_FRIENDS,
			'limit' => $limit,
			'limitstart' => $start,
			'idonly' => true,
			'sort' => 'lastseen'
		);

		if ($this->config->get('conversations.nonfriend')) {
			$searchOptions['everyone'] = true;
		}

		if (isset($options['exclude']) && $options['exclude']) {
			$searchOptions['exclude'] = $options['exclude'];
		}

		$ids = $model->getFriends($userId, $searchOptions);

		$results = array();
		$total = 0;

		if ($ids) {
			$total = $model->getTotalFriends($userId, $searchOptions);

			// preload users
			ES::user($ids);

			foreach ($ids as $uid) {
				$results[] = ES::user($uid);
			}
		}

		$data = $this->buildUsers($total, $results);
		return $data;
	}

	/**
	 * search friends from user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function searchContacts($userId, $searchphrase, $options = array())
	{
		$this->validateUser($userId);

		$limit = isset($options['limit']) ? $options['limit'] : $this->themeConfig->get('friendslimit');
		$start = isset($options['start']) ? $options['start'] : 0;

		$searchOptions = array(
			'limit' => $limit,
			'limitstart' => $start
		);

		if ($this->config->get('conversations.nonfriend')) {
			$searchOptions['everyone'] = true;
		}

		// Determine what type of string we should search for.
		$type = $this->config->get('users.displayName');

		$model = ES::model('Friends');
		$results = $model->search($userId, $searchphrase, $type, $searchOptions);
		$total = $model->getTotal();

		$data = $this->buildUsers($total, $results);

		return $data;
	}

	/**
	 * This method is use to add attachment into temporary location for later processing.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function addAttachment($userId, $file = null)
	{
		$this->validateUser($userId);

		// Get the limit
		$limit = $this->config->get('conversations.attachments.maxsize');

		// Set uploader options
		$options = array('name' => 'file', 'maxsize' => $limit . 'M');

		$uploader = ES::uploader($options, 'conversations');

		$data = '';
		if ($file) {
			$data = $file;
		} else {
			$data = $uploader->getFile('', $options);

			// If there was an error getting uploaded file, stop.
			if ($data instanceof SocialResponse) {
				return $this->error($data->message);
			}
		}

		if (!$data) {
			return $this->error(JText::_('COM_EASYSOCIAL_UPLOADER_FILE_DID_NOT_GET_UPLOADED'));
		}

		// Let's get the temporary uploader table.
		$uploader = ES::table('Uploader');
		$uploader->user_id = $userId;

		// Bind the data on the uploader
		$uploader->bindFile($data);

		// Try to save the uploader
		$state = $uploader->store();

		if (!$state) {
			return $this->error($uploader->getError());
		}

		$data = new stdClass();
		$data->id = $uploader->id;
		$data->preview = $uploader->getPermalink();
		$data->name = $uploader->getName();

		return $data;
	}

	/**
	 * This method is use to delete attachment from a conversation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteAttachment($userId, $fileId)
	{
		$this->validateUser($userId);

		$file = ES::table('File');
		$file->load($fileId);

		// Check if the file is owned by the user.
		$allowed = $file->deleteable($userId);

		if (!$allowed) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_TO_DELETE_ATTACHMENT'));
		}

		$state = $file->delete();

		if (!$state) {
			return $this->error($file->getError());
		}

		return true;
	}

	/**
	 * This method is use to update conversation's title
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function updateTitle($conversationId, $userId, $title)
	{

		$this->validateUser($userId);
		$this->validateConversation($conversationId);

		$conversation = ES::conversation($conversationId);

		// Check if the user is allowed to add people to the conversation
		if (!$conversation->isWritable($userId)) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_ACCESS_TO_CONVERSATION'));
		}

		if (!$title) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_NOT_ALLOWED_ACCESS_TO_CONVERSATION'));
		}

		$state = $conversation->saveTitle($title);

		if ($state === false) {
			return $this->error(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_DELETING_PARTICIPANT'));
		}

		return true;
	}

	/**
	 * ************************************
	 * internal helper functions goes here.
	 * ************************************
	 * /

	/**
	 * helper method to construct error object
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function error($message, $code = '403')
	{
		$obj = new stdClass();

		$obj->code = $code;
		$obj->message = JText::_($message);

		return $obj;
	}


	/**
	 * helper method to construct error object
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function validateUser($userId)
	{
		if (!$userId) {
			return $this->error(JText::_('Invalid user id provided.'));
		}
	}

	/**
	 * helper method to construct error object
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function validateConversation($conversationId)
	{
		if (!$conversationId) {
			return $this->error(JText::_('Invalid conversation id provided.'));
		}
	}


	/**
	 * helper method to construct json data for messages.
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function buildUsers($total = 0, $users = array(), $prefix = 'contacts')
	{
		// heading object
		$data = new stdClass();
		$data->totalcontacts = $total;
		$data->{$prefix} = array();

		if (! $users) {
			return array();
		}

		if ($users) {
			foreach ($users as $user) {

				$obj = new stdClass();

				$obj->id = $user->id;
				$obj->name = $user->getName();
				$obj->avatar = $user->getAvatar();
				$obj->url = $user->getPermalink();
				$obj->isOnline = $user->isOnline();
				$obj->lastseen = $user->getLastVisitDate('lapsed');

				$data->{$prefix}[] = $obj;
			}
		}

		return $data;
	}


	/**
	 * helper method to construct object data for conversation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function buildConversation(SocialConversation $item, $total = 0)
	{
		$obj = new stdClass();
		$obj->id = $item->id;
		$obj->isParticipant = $item->isparticipant;
		$obj->notification = $item->notification;
		$obj->type = $item->type;
		$obj->name = $item->getTitle();
		$obj->url = $item->getPermalink();

		$lastMessage = $item->getLastMessage();
		$obj->preview = $lastMessage->getIntro();
		$obj->dateLapse = $lastMessage->getRepliedDate();

		$obj->avatar = $item->getAvatar(false);
		$obj->totalConversations = $total;

		// most recent message
		$obj->message = $this->genMessage($lastMessage);

		return $obj;
	}


	/**
	 * helper method to construct json data for messages.
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function buildMessages($conversationId, $total = 0, $messages = array(), $created  = false)
	{
		// heading object
		$data = new stdClass();
		$data->conversationId = $conversationId;
		$data->totalmessages = $total;
		$data->lastupdate = ES::date()->toSql();
		$data->timestamp = $created;
		$data->messages = array();

		if ($messages) {
			foreach ($messages as $item) {

				$obj = $this->genMessage($item);
				$data->messages[] = $obj;
			}
		}

		return $data;
	}

	/**
	 * helper method to construct json data for message.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function genMessage(SocialTableConversationMessage $item)
	{

		$obj = new stdClass();

		$obj->id = $item->id;
		$obj->type = $item->getType();
		$obj->dateLapse = $item->getRepliedDate();
		$obj->timestamp = $item->created;
		$obj->text = $item->getContents();

		// creator of the message
		$creator = $item->getCreator();
		$obj->creator = new stdClass();

		$obj->creator->id = $creator->id;
		$obj->creator->name = $creator->getName();
		$obj->creator->avatar = $creator->getAvatar();
		$obj->creator->url = $creator->getPermalink();
		$obj->creator->isOnline = $creator->isOnline();

		//attachments
		$obj->attachments = array();
		$attachments = $item->getAttachments();

		if ($attachments) {
			foreach ($attachments as $atm) {
				$file = new stdClass();

				$file->id = $atm->id;
				$file->name = $atm->name;
				$file->size = $atm->getSize();
				$file->mimeType = $atm->mime;
				$file->hits = $atm->hits;
				$file->url = $atm->getPermalink();
				$file->previewUri = '';
				if ($atm->hasPreview()) {
					$file->previewUri = $atm->getPreviewURI();
				}

				$obj->attachments[] = $file;
			}
		}

		//locations
		 $obj->locations = array();
		 $loc = $item->getLocation();

		 if ($loc) {

			$location = new stdClass();
			$location->id = $loc->uid;
			$location->lat = $loc->latitude;
			$location->lon = $loc->longitude;
			$location->add = $loc->address;

			$obj->locations[] = $location;
		 }

		return $obj;
	}


}
