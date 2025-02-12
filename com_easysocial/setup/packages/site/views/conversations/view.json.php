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

ES::import('site:/views/views');

class EasySocialViewConversations extends EasySocialSiteView
{
	/**
	 * create new conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function create()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		// post data
		$data = $this->input->getArray('post');

		$lib = ES::conversations();
		$obj = $lib->create($userId, $data);

		$this->set('data', $obj);
		parent::display();
	}

	/**
	 * get conversations belong to user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getConversations()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		$sorting = $this->input->get('sorting', $this->themeConfig->get('conversation_sorting'), 'default');
		$ordering = $this->input->get('ordering', $this->themeConfig->get('conversation_ordering'), 'default');

		$limit = $this->input->get('limit', $this->themeConfig->get('conversation_limit'), 'default');
		$start = $this->input->get('limitstart', 0, 'int');

		$options = array(
						'sorting' => $sorting,
						'ordering' => $ordering,
						'limit' => $limit,
						'start' => $start
						);

		$lib = ES::conversations();
		$data = $lib->getConversations($userId, $options);

		$this->set('data', $data);
		parent::display();
	}


	/**
	 * get messages for a particular conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getMessages()
	{
		ES::checkToken();
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		if (!$id) {
			$this->showError('Invalid conversation id provided.');
		}

		$config = ES::config();

		$limit = $this->input->get('limit', $this->themeConfig->get('conversation_limit'), 'default');
		$start = $this->input->get('limitstart', 0, 'int');

		$options = array(
			'limit' => $limit,
			'start' => $start
		);


		$lib = ES::conversations();
		$data = $lib->getMessages($userId, $id, $options);

		$this->set('data', $data);

		parent::display();
	}

	/**
	 * get new messages for a particular conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getNewMessages()
	{
		ES::checkToken();
		ES::requireLogin();
		$session = JFactory::getSession();
		$id = $this->input->get('id', 0, 'int');
		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		if (!$id) {
			$this->showError('Invalid conversation id provided.');
		}

		$lastupdate = $this->input->get('lastupdate', ES::date()->toSql(), 'default');

		$lib = ES::conversations();
		$data = $lib->getNewMessages($userId, $id, $lastupdate);

		$this->set('data', $data);

		parent::display();
	}


	/**
	 * reply to a conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function reply()
	{
		ES::checkToken();
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		if (!$id) {
			$this->showError('Invalid conversation id provided.');
		}


		$post = $this->input->getArray('post');

		$lib = ES::conversations();
		$data = $lib->reply($userId, $id, $post);


		$this->set('data', $data);
		parent::display();
	}


	/**
	 * delete a conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		ES::checkToken();
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		if (!$id) {
			$this->showError('Invalid conversation id provided.');
		}

		$lib = ES::conversations();
		$data = $lib->delete($userId, $id);

		$this->set('data', $data);
		parent::display();
	}


	/**
	 * retrieve friends from user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getContacts()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		$config = ES::config();

		$limit = $this->input->get('limit', $this->themeConfig->get('friendslimit'), 'default');
		$start = $this->input->get('limitstart', 0, 'int');

		$model = ES::model('Friends');

		$options = array(
			'state' => SOCIAL_FRIENDS_STATE_FRIENDS,
			'limit' => $limit,
			'idonly' => true,
			'start' => $start
		);

		$lib = ES::conversations();
		$data = $lib->getContacts($userId, $options);

		$this->set('data', $data);
		parent::display();
	}

	/**
	 * retrieve friends from user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function searchContacts()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		$config = ES::config();

		$limit = $this->input->get('limit', $this->themeConfig->get('friendslimit'), 'default');
		$start = $this->input->get('limitstart', 0, 'int');
		$searchphrase = $this->input->get('searchphrase', '', 'default');

		$options = array(
			'limit' => $limit,
			'start' => $start
		);

		$lib = ES::conversations();
		$data = $lib->searchContacts($userId, $searchphrase, $options);

		$this->set('data', $data);
		parent::display();
	}

	/**
	 * upload attachment to temporary folder in server
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function addAttachment()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		$file = $this->input->files->get('file');

		// do a basic check to see if there is something to process or not.
		if (!isset($file['name']) || !$file['name']) {
			$this->showError('Upload file failed.');
		}

		$lib = ES::conversations();
		$data = $lib->addAttachment($userId);

		$this->set('data', $data);
		parent::display();
	}


	/**
	 * delete attachment for a conversation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteAttachment()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$id = $this->input->get('id', 0, 'int');

		$this->validateUser($userId);

		$lib = ES::conversations();
		$data = $lib->deleteAttachment($userId, $id);

		$this->set('data', $data);
		parent::display();
	}



	/**
	 * retrieve conversation's participants
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getParticipants()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		$conversationId = $this->input->get('id', 0, 'int');

		$lib = ES::conversations();
		$data = $lib->getParticipants($userId, $conversationId);

		$this->set('data', $data);
		parent::display();
	}

	 /**
	 * add person into conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function addParticipant()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');

		$lib = ES::conversations();
		$data = $lib->addParticipant($userId, $id, $uid);

		$this->set('data', $data);

		parent::display();
	}

	/**
	 * delete participant from conversation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteParticipant()
	{
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$this->validateUser($userId);

		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');

		$lib = ES::conversations();
		$data = $lib->deleteParticipant($userId, $id, $uid);

		$this->set('data', $data);
		parent::display();
	}



	// ==================================================================================
	// WIP ==============================================================================
	// ==================================================================================



	 /**
	 * mark a conversation as read
	 *
	 * @since   2.0
	 * @access  public
	 */
	 public function markAsRead()
	 {
		ES::checkToken();
		ES::requireLogin();

		$userId = $this->input->get('userId', 0, 'int');
		$id = $this->input->get('id', 0, 'int');
		$this->validateUser($userId);

		$model = ES::model('conversations');
		$data = $model->markAsRead($id, $userId);
		$this->set('data', $data);

		parent::display();
	 }



	/**
	 * ***********************************************************
	 *  HELPER FUNCTIONS
	 * ***********************************************************
	 */
	/**
	 * helper method to validate the user id.
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function validateUser($userId)
	{
		if (! $userId) {
			$this->showError('Invalid user id provided.');
		}

		$my = ES::user();
		if ($my->id != $userId) {
			$this->showError('Invalid user id provided.');
		}
	}


	/**
	 * helper method to construct json data for participants
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function buildParticipants($users = array())
	{
		// heading object
		$data = new stdClass();
		$data->Allparticipants = array();

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

				$data->participants[] = $obj;
			}
		}

		return $data;
	}


	private function showError($message, $code = '403')
	{
		$this->set('code', $code);
		$this->set('message', JText::_($message));
		return parent::display();
	}
}
