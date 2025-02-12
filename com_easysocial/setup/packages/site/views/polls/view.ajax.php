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

class EasySocialViewPolls extends EasySocialSiteView
{
	/**
	 * Filters polls on polls view
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function filter()
	{
		$type = $this->input->get('type', 'all', 'string');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'default');
		$snackbar = JText::_('COM_EASYSOCIAL_POLLS');
		$userId = $this->input->get('userid', 0, 'int');

		$model = ES::model('Polls');
		$options = array();

		if ($type == 'mine') {
			$options['user_id'] = $this->my->id;
		}

		if ($userId && $type == 'mine') {
			$options['user_id'] = $userId;
		}

		if ($clusterId) {
			$options['cluster_id'] = $clusterId;
			$options['cluster_type'] = $clusterType;
			$snackbar = false;
		}

		$result = $model->getPolls($options);
		$pagination = $model->getPagination();

		if ($pagination) {

			if ($clusterId) {
				$pagination->setVar('filter' , $type);
				$cluster = ES::cluster($clusterType, $clusterId);
				$pollApp = $cluster->getApp('polls');

				$pagination->setVar('view', $cluster->getTypePlural());
				$pagination->setVar('id', $cluster->getAlias());
				$pagination->setVar('layout', 'item');
				$pagination->setVar('appId', $pollApp->getAlias());
			} elseif ($userId) {
				$userLib = ES::user($userId);
				$userAlias = $userLib->getAlias();
				$pagination->setVar('Itemid', ESR::getItemId('polls'));
				$pagination->setVar('view', 'polls');
				$pagination->setVar('userid', $userAlias);
			} else {
				$pagination->setVar('filter' , $type);
				$pagination->setVar('Itemid', ESR::getItemId('polls'));
				$pagination->setVar('view', 'polls');
			}
		}

		$polls = array();

		if ($result) {
			foreach ($result as $row) {
				$poll = ES::table('Polls');
				$poll->bind($row);

				$polls[] = $poll;
			}
		}

		$theme = ES::themes();
		$theme->set('snackbar', $snackbar);
		$theme->set('filter', $type);
		$theme->set('polls', $polls);
		$theme->set('pagination', $pagination);

		$output = $theme->output('site/polls/default/wrapper');

		return $this->ajax->resolve($output);
	}

	/**
	 * Returns an ajax chain.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function vote($isVoted = false)
	{
		$pollId = $this->input->get('id', 0, 'int');
		$itemId = $this->input->get('itemId', 0, 'int');
		$action = $this->input->get('act', '', 'default');

		$items = array();
		$msg = JText::_('COM_EASYSOCIAL_POLLS_VOTED_SUCESSFUL');

		if ($action == 'unvote') {
			$msg = JText::_('COM_EASYSOCIAL_POLLS_VOTE_REMOVED_SUCESSFUL');
		}

		return $this->ajax->resolve($msg, $items, $isVoted, $this->my->id);
	}

	/**
	 * Post process after a poll has been updated
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function update()
	{
		$pollId = $this->input->get('id', 0, 'int');
		$pollLib = ES::get('Polls');
		$content = $pollLib->getDisplay($pollId);

		return $this->ajax->resolve($content);
	}

	/**
	 * Retrieves a list of voters for a poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function voters()
	{
		$id = $this->input->get('id', 0, 'int');
		$optionId = $this->input->get('optionId', 0, 'int');

		$poll = ES::polls();
		$users = $poll->getVoters($id, $optionId);

		$hasAdminVoter = false;

		$cluster = $poll->getCluster($id);

		if ($cluster && $cluster->getType() == SOCIAL_TYPE_PAGE) {
			$hasAdminVoter = $cluster->hasPageAdmin($users, true);
		}

		$theme = ES::themes();
		$theme->set('users', $users);
		$theme->set('hasAdminVoter', $hasAdminVoter);
		$theme->set('cluster', $cluster);

		$contents = $theme->output('site/polls/item/voters');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the editing form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function edit()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		$uid = $this->input->get('uid', 0, 'int');
		$element = $this->input->get('element', '', 'default');
		$source = $this->input->get('source', '', 'default');

		$polls = ES::polls();
		$output = $polls->form($element, $uid, $source);

		return $this->ajax->resolve($output);
	}
}
