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

class EasySocialControllerPolls extends EasySocialController
{
	/**
	 * Creates a new poll
	 *
	 * @since	3.2.16
	 * @access	public
	 */
	public function create()
	{
		ES::requireLogin();
		ES::checkToken();

		// Prevent users from trying to bypass the system
		if (!$this->my->canCreatePolls()) {
			return $this->view->exception('COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION');
		}

		$title = $this->input->get('title', '', 'string');
		$items = $this->input->get('pollItems', array(), 'array');
		$privacy = $this->input->get('privacy', '', 'word');
		$customPrivacy = $this->input->get('privacyCustom', '', 'string');

		if (!$title || !$items || count($items) <= 1) {
			$this->view->setMessage('COM_EASYSOCIAL_POLLS_FORM_IS_EMPTY', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// check if all the options item has value.
		foreach ($items as $item) {
			$item = ESJString::trim($item);
			if (!$item) {
				$this->view->setMessage('COM_EASYSOCIAL_POLLS_FORM_IS_EMPTY', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}


		$poll = ES::polls();
		$template = $poll->getTemplate();
		$template->setTitle($title);
		$template->setCreator($this->my->id);
		$template->setContext(0, 'stream');
		$template->setPrivacy($privacy, $customPrivacy);

		$multiple = $this->input->get('multiple', false, 'boolean');
		$template->setMultiple($multiple);

		$expiry = $this->input->get('expiry', '', 'default');

		if ($expiry) {
			$template->setExpiry($expiry);
		}

		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'default');

		if ($clusterId) {
			$template->setCluster($clusterId, $clusterType);
		}

		if ($items) {
			foreach ($items as $item) {

				$item = ESJString::trim($item);

				// Prevent users creating blank option
				if (!$item) {
					continue;
				}

				// Since the addOption requires an array, we need to satisfy it
				$option = array('id' => 0, 'text' => $item);

				$template->addOption($option);
			}
		}

		$table = $poll->create($template, array('createStream' => true));

		$this->view->setMessage('COM_EASYSOCIAL_POLLS_CREATED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call('postCreate', $table);
	}

	/**
	 * Updates an existing poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		$pollId = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$element = $this->input->get('element', '', 'default');
		$title = $this->input->get('title', '', 'default');
		$multiple = $this->input->get('multiple', '0', 'default');
		$toberemove = $this->input->get('toberemove', '', 'default');
		$expirydate = $this->input->get('expirydate', '', 'default');
		$items  = $this->input->get('items', '', 'array');

		$poll = ES::table('Polls');
		$state = $poll->load($pollId);

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_POLLS_ERROR_INVALID_POLL_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$poll->title = $title;
		$poll->multiple = $multiple;

		// since we know the expirey date that pass in already has the timezone. We need to reverse it.
		if ($expirydate) {
			$offset = ES::date()->getOffSetObject();
			$newDate = new JDate($expirydate, $offset);
			$expirydate = $newDate->toSql();
		}

		$poll->expiry_date = $expirydate ? $expirydate : null;
		$poll->store();

		// if there are items to delete, lets do it here.
		if ($toberemove) {
			$tobeRemoved = explode(',', $toberemove);

			if ($tobeRemoved) {
				foreach($tobeRemoved as $id) {
					$pollItem = ES::table('PollsItems');
					$pollItem->delete($id);
				}
			}
		}

		// now we need to update / add new items
		if ($items) {
			foreach($items as $item) {

				$item = (object) $item;

				$pollItem = ES::table('PollsItems');
				$pollItem->load($item->id);

				$pollItem->poll_id = $pollId;
				$pollItem->value = $item->text;

				$pollItem->store();
			}
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows remote caller to vote on a poll's response
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function vote()
	{
		ES::requireLogin();
		ES::checkToken();

		$pollId = $this->input->get('id', 0, 'int');
		$itemId  = $this->input->get('itemId', 0, 'int');
		$action  = $this->input->get('act', '', 'default');

		$poll = ES::table('Polls');
		$state = $poll->load($pollId);


		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_POLLS_ERROR_INVALID_POLL_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if ($poll->hasExpired()) {
			$this->view->setMessage('COM_EASYSOCIAL_POLLS_ERROR', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if (!$poll->canVote()) {
			$this->view->setMessage('COM_ES_POLL_VOTE_NOT_ALLOWED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if ($poll->cluster_id && !$poll->canVoteInCluster()) {
			$tableCluster = ES::table('cluster');
			$tableCluster->load($poll->cluster_id);

			$clusterType = $tableCluster->cluster_type;
			$message = JText::_('COM_ES_POLL_NON_MEMBER_VOTE_ERROR_MESSAGE_' . strtoupper($clusterType));

			$this->view->setMessage($message, ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Check whether user already vote on the polls previously.
		$isVoted = $poll->isVoted($this->my->id);

		if ($action == 'vote' && !$poll->isMultiple() && $isVoted) {
			$this->view->setMessage('COM_ES_POLLS_VOTE_MULTIPLE_OPTIONS_RESTRICTED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$pollLib = ES::get('Polls');
		$allowed = array('vote', 'unvote');

		if (!in_array($action, $allowed)) {
			$this->view->setMessage('COM_EASYSOCIAL_POLLS_ERROR', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Let's vote on the poll now.
		$result = $pollLib->$action($pollId, $itemId, $this->my->id);

		if (!$result) {
			$this->view->setMessage('COM_EASYSOCIAL_POLLS_ERROR', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__, $isVoted);
	}

	public function notify()
	{
		ES::requireLogin();
		ES::checkToken();

		$pollId = $this->input->get('id', 0, 'int');
		$itemId = $this->input->get('itemId', 0, 'int');
		$action = $this->input->get('action', '', 'default');

		$poll = ES::polls();
		$method = 'notify' . ucfirst($action);

		// Let's notify the users.
		if (!empty($action)) {
			$poll->$method($pollId, $itemId);
		}

		return $this->view->call(__FUNCTION__);
	}
}
