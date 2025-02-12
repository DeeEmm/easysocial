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

require_once(__DIR__ . '/template.php');

class SocialPolls extends EasySocial
{
	public $table = null;

	public static function factory()
	{
		return new self();
	}

	/**
	 * Renders the polls form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form($element, $uid = 0, $source = '', $cluster_id = '', $options = array())
	{
		$theme = ES::themes();
		$theme->set('element', $element);
		$theme->set('uid', $uid);
		$theme->set('source', $source);
		$theme->set('cluster_id', $cluster_id);
		$theme->set('privacy', ES::privacy());

		$type = $this->normalize($options, 'type', 'story');

		$namespace = 'site/polls/form/' . $type;

		$table = ES::table('Polls');
		$items = [];

		if ($element && $uid) {
			$state = $table->load(array('element' => $element, 'uid' => $uid));

			if ($state) {
				$items = $table->getItems();
			}
		}

		$theme->set('poll', $table);
		$theme->set('items', $items);

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Use @form instead
	 *
	 * @deprecated	2.0
	 */
	public function getForm($element, $uid = 0, $source = '', $cluster_id = '')
	{
		return $this->form($element, $uid, $source, $cluster_id);
	}

	/**
	 * Renders the poll item layout
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function html($id)
	{
		$table = ES::table('Polls');
		$table->load((int) $id);

		if (!$table->id) {
			return false;
		}

		// Determines if the user has voted
		$voted = $table->isVoted($this->my->id);

		$canEdit = ($this->my->id == $table->created_by || $this->my->isSiteAdmin()) ? true : false;

		// Determines if the poll is already expired
		$expired = $table->hasExpired();

		// Get the options
		$options = $table->getItems();

		$theme = ES::themes();
		$theme->set('poll', $table);
		$theme->set('options', $options);
		$theme->set('voted', $voted);
		$theme->set('expired', $expired);

		$output = $theme->output('site/polls/item/default');

		return $output;
	}

	/**
	 * Deprecated. Use @html instead.
	 *
	 * @deprecated 	2.0
	 */
	public function getDisplay($id)
	{
		return $this->html($id);
	}

	/**
	 * get html for polls voting form.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getVoters($pollId, $pollItemId = '')
	{
		$model = ES::model("Polls");

		$ids = $model->getVoterIds($pollId, $pollItemId);
		$voters = array();

		if ($ids) {

			// pre-load users
			ES::user($voters);

			foreach($ids as $id) {
				$user = ES::user($id);
				$voters[] = $user;
			}
		}

		return $voters;
	}

	/**
	 * Get cluster object from Poll if any
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getCluster($pollId)
	{
		$poll = ES::table('Polls');
		$poll->load($pollId);

		if (!$poll->cluster_id) {
			return false;
		}

		return ES::cluster($poll->cluster_id);
	}

	/**
	 * get poll template for creation.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getTemplate()
	{
		$template 	= new SocialPollsTemplate();
		return $template;
	}

	/**
	 * Create a new poll item
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function create(SocialPollsTemplate $template, $options = array())
	{
		if (!$this->my->canCreatePolls()) {
			return;
		}

		$table = ES::table('Polls');
		$table->element = $template->element;
		$table->uid = $template->uid;
		$table->title = $template->title;
		$table->multiple = $template->multiple;
		$table->locked = $template->locked;
		$table->created = $template->created;
		$table->created_by = empty($template->created_by) ? $this->my->id : $template->created_by;
		$table->expiry_date = $template->expiry_date ? $template->expiry_date : null;
		$table->cluster_id = $template->cluster_id;

		$state = $table->store();

		if (!$state) {
			return false;
		}

		$privacyData = '';
		if (isset($template->privacy['privacy'])) {

			$privacyData = new stdClass();
			$privacyData->rule = 'audios.view';
			$privacyData->value = $template->privacy['privacy'];
			$privacyData->custom = $template->privacy['customPrivacy'];

			$this->insertPrivacy($privacyData, $table->id);
		}

		// Since all polls are associated with a stream item, we need to ensure it has a stream associated
		$createStream = $this->normalize($options, 'createStream', false);
		$cluster = false;

		if ($template->cluster_id && isset($template->cluster_type)) {
			$cluster = ES::cluster($template->cluster_type, $template->cluster_id);
		}

		if ($createStream) {
			$stream = ES::stream();

			$tpl = $stream->getTemplate();
			$tpl->setActor($this->my->id, SOCIAL_TYPE_USER);
			$tpl->setContext($table->id, 'polls');
			$tpl->setVerb('create');

			// Set stream privacy
			if ($privacyData) {

				$value = $privacyData->value;
				if (is_string($value)) {
					$privacyLib = ES::privacy();
					$value = $privacyLib->toValue($value);
				}

				$tpl->setAccess('polls.view', $value, $privacyData->custom);
			} else {
				$tpl->setAccess('polls.view');
			}

			// Set cluster target
			if ($cluster && $cluster->id) {
				$tpl->setCluster($cluster->id, $cluster->getType(), $cluster->type);
			}

			$streamItem = $stream->add($tpl);

			// Once the stream item is added we need to link the stream uid with the poll
			$table->uid = $streamItem->uid;

			$table->store();
		}

		// Send notifications to cluster members
		if ($cluster && $cluster->id) {
			$options = array(
				'userId' => $this->my->id,
				'title' => $table->title,
				'permalink' => ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true),
				'id' => $table->id,
				'sid' => $streamItem->uid
				);

			$cluster->notifyMembers('polls.create', $options);
		}

		// Insert the poll options
		if ($template->items) {

			foreach ($template->items as $item) {

				$pollItem = ES::table('PollsItems');
				$pollItem->poll_id = $table->id;
				$pollItem->value = $item->text;
				$pollItem->count = 0;

				$pollItem->store();
			}
		}

		ES::points()->assign('polls.add', 'com_easysocial', $this->my->id);

		return $table;
	}

	/**
	 * Insert privacy for this polls
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function insertPrivacy($privacy, $id)
	{
		$privacyLib = ES::privacy();
		$privacyLib->add($privacy->rule, $id, SOCIAL_TYPE_POLLS, $privacy->value, null, $privacy->custom);
	}

	/**
	 * Vote or un-vote a poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function vote($pollId, $optionId, $userId)
	{
		// check if user has the access to vote on polls or not.
		$my = ES::user();
		$access = $my->getAccess();

		if (!$access->allowed('polls.vote')) {
			return false;
		}

		$pollItem = ES::table('PollsItems');
		$state = $pollItem->load($optionId);

		if ($state) {
			$addCount = false;

			// add into polls_users table if not exists
			$pollUser = ES::table('PollsUsers');
			$votedPollItem = $pollUser->load(array('poll_id' => $pollId, 'poll_itemid' => $optionId, 'user_id' => $userId));

			// check for the current voter whether got vote this poll before or not
			$pollsModel = ES::model('Polls');
			$hasVoted = $pollsModel->isVoted($pollId, $userId);

			if (!$votedPollItem) {
				$pollUser->poll_id = $pollId;
				$pollUser->poll_itemid = $optionId;
				$pollUser->user_id = $userId;

				$pollUser->store();
				$addCount = true;

			} else if ($pollUser->state != SOCIAL_STATE_PUBLISHED) {
				// Change the state to 1
				$pollUser->state = SOCIAL_STATE_PUBLISHED;
				$pollUser->store();

				$addCount = true;
			}

			// if addCount is FALSE, this mean user is trying to do the same action again and again
			// we need to prevent that.
			if (!$addCount) {
				return false;
			}

			$pollItem->count = (int) $pollItem->count + 1;
			$ok = $pollItem->store();

			// Just in case that poll allow user vote multiple option
			// So we just need to add point to the user once if the user vote multiple option from this poll.
			if ($ok && !$hasVoted) {
				ES::points()->assign('polls.vote', 'com_easysocial', $userId);

				$table = ES::table('Polls');
				$table->load($pollId);

				// Assign points to the poll owner when someone voted on his/her poll
				ES::points()->assign('polls.voted', 'com_easysocial', $table->created_by);
			}
		} else {
			return false;
		}

		return true;
	}

	public function unvote($pollId, $optionId, $userId)
	{
		// check if user has the access to vote on polls or not.
		$my = ES::user();
		$access = $my->getAccess();

		if (! $access->allowed('polls.vote')) {
			return false;
		}

		$pollItem = ES::table('PollsItems');
		$state = $pollItem->load($optionId);

		if ($state) {
			// Delete from pollsuser table
			$pollUser = ES::table('PollsUsers');
			$votedPollItem = $pollUser->load(array('poll_id' => $pollId, 'poll_itemid' => $optionId, 'user_id' => $userId));

			if (!$votedPollItem) {
				return false;
			}

			$minusCount = false;

			// check if we really need to unvote or not
			if ($votedPollItem && $pollUser->state == SOCIAL_STATE_PUBLISHED) {
				$pollUser->state = SOCIAL_STATE_UNPUBLISHED;
				$pollUser->store();

				$minusCount = true;
			}

			// check for the current voter whether got vote this poll before or not
			$pollsModel = ES::model('Polls');
			$hasVoted = $pollsModel->isVoted($pollId, $userId);

			if ($minusCount) {
				$pollItem->count = (int) $pollItem->count - 1;

				// we do not allow negative value
				if ($pollItem->count < 0) {
					$pollItem->count = 0;
				}

				$ok = $pollItem->store();

				// Just in case that poll allow user vote multiple option
				// So we just need to minus the point if the user unvote all the option from this poll.
				if (!$hasVoted) {
					ES::points()->assign('polls.unvote', 'com_easysocial', $userId);

					$table = ES::table('Polls');
					$table->load($pollId);

					// Assign points to the poll owner when someone unvoted on his/her poll
					ES::points()->assign('polls.unvoted', 'com_easysocial', $table->created_by);
				}

			} else {
				// user are not suppose to unvote. show error
				return false;
			}


		} else {
			return false;
		}

		return true;
	}

	/**
	 * Perform a revote action
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function revote($pollId, $optionId, $userId)
	{
		// Get all of the voted options from the user
		$model = ES::model('Polls');
		$votedIds = $model->getVotedByUser($pollId, $userId);

		// Unvote all of that options
		foreach ($votedIds as $votedId) {
			$this->unvote($pollId, $votedId, $userId);
		}

		// Vote with the new option
		$this->vote($pollId, $optionId, $userId);

		return true;
	}

	public function notifyVote($pollId, $itemId)
	{
		$poll = ES::table('Polls');
		$poll->load($pollId);

		// Current voter
		$voter = ES::user();

		// Do not self notify.
		if ($voter->id == $poll->created_by) {
			return;
		}

		// Default rule
		$rule = 'polls.vote.item';

		// Prepare params for email notification
		$mailParams = array();

		$mailParams['actor'] = $voter->getName();
		$mailParams['posterAvatar'] = $voter->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['posterLink'] = $voter->getPermalink(true, true);
		$mailParams['permalink'] = ESR::stream(array('id' => $poll->uid, 'layout' => 'item', 'external' => true), true);

		// Default email settings
		$emailTitle = 'COM_EASYSOCIAL_EMAILS_POLLS_VOTE_ITEM';
		$emailTemplate = 'site/polls/vote.item';

		// Prepare the system notification params
		$systemParams = array();

		$systemParams['url'] = ESR::stream(array('id' => $poll->uid, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $voter->id;
		$systemParams['uid'] = $poll->uid;
		$systemParams['title'] = JText::sprintf('COM_EASYSOCIAL_POLLS_VOTED', $voter->getName(), $poll->title);
		$systemParams['aggregate'] = true;

		// If the polls is from cluster, we need to respect it.
		if ($poll->cluster_id) {

			// how to get cluster type?
			$clusterTable = ES::table('Cluster');
			$clusterTable->load($poll->cluster_id);

			// To be use to call the cluster type library.
			$clusterType = $clusterTable->cluster_type;

			// To be use in context type and rules type since it contain 's' at the end of character.
			$clusterTypes = $clusterType . 's';

			// Load the cluster. Works for event and group
			$cluster = ES::cluster($clusterType, $clusterTable->id);

			$systemParams['context_type'] = $clusterTypes;
			$systemParams['context_ids'] = $cluster->id;
			$systemParams['title'] = JText::sprintf('COM_EASYSOCIAL_POLLS_VOTED_IN_' . strtoupper($clusterType), $voter->getName(), $cluster->getName(), $poll->title);

			$mailParams['cluster'] = $cluster->getName();
			$mailParams['clusterLink'] = $cluster->getPermalink(true, true);

			$emailTitle = 'APP_' . strtoupper($clusterType) . '_STORY_EMAILS_NEW_POLLS_VOTE_IN_' . strtoupper($clusterType);
			$emailTemplate = 'apps/' . $clusterType . '/polls/vote.item';

			$rule = $clusterTypes . '.polls.vote.item';
		}

		$mailParams['title'] = $emailTitle;
		$mailParams['template'] = $emailTemplate;

		// Try to send the notification
		$state = ES::notify($rule, array($poll->created_by), $mailParams, $systemParams);

		return $state;
	}

	public function notifyUnvote($pollId, $itemId)
	{
		$poll = ES::table('Polls');
		$poll->load($pollId);

		// Current voter
		$voter = ES::user();

		// Do not self notify.
		if ($voter->id == $poll->created_by) {
			return;
		}

		// Default rule
		$rule = 'polls.unvote.item';

		// Prepare params for email notification
		$mailParams = array();

		$mailParams['actor'] = $voter->getName();
		$mailParams['posterAvatar'] = $voter->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['posterLink'] = $voter->getPermalink(true, true);
		$mailParams['permalink'] = ESR::stream(array('id' => $poll->uid, 'layout' => 'item', 'external' => true), true);

		// Default email settings
		$emailTitle = 'COM_EASYSOCIAL_EMAILS_POLLS_UNVOTE_ITEM';
		$emailTemplate = 'site/polls/unvote.item';

		// Prepare the system notification params
		$systemParams = array();

		$systemParams['url'] = ESR::stream(array('id' => $poll->uid, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $voter->id;
		$systemParams['uid'] = $poll->uid;
		$systemParams['title'] = JText::sprintf('COM_EASYSOCIAL_POLLS_UNVOTED', $voter->getName());

		// If the polls is from cluster, we need to respect it.
		if ($poll->cluster_id) {

			// how to get cluster type?
			$clusterTable = ES::table('Cluster');
			$clusterTable->load($poll->cluster_id);

			// To be use to call the cluster type library.
			$clusterType = $clusterTable->cluster_type;

			// To be use in context type and rules type since it contain 's' at the end of character.
			$clusterTypes = $clusterType . 's';

			// Load the cluster. Works for event and group
			$cluster = ES::cluster($clusterType, $clusterTable->id);

			$systemParams['context_type'] = $clusterTypes;
			$systemParams['context_ids'] = $cluster->id;
			$systemParams['title'] = JText::sprintf('COM_EASYSOCIAL_POLLS_UNVOTED_IN_' . strtoupper($clusterType), $voter->getName(), $cluster->getName());

			$mailParams['cluster'] = $cluster->getName();
			$mailParams['clusterLink'] = $cluster->getPermalink(true, true);

			$emailTitle = 'APP_' . strtoupper($clusterType) . '_STORY_EMAILS_NEW_POLLS_UNVOTE_IN_' . strtoupper($clusterType);
			$emailTemplate = 'apps/' . $clusterType . '/polls/unvote.item';

			$rule = $clusterTypes . '.polls.unvote.item';
		}

		$mailParams['title'] = $emailTitle;
		$mailParams['template'] = $emailTemplate;

		// Try to send the notification
		$state = ES::notify($rule, array($poll->created_by), $mailParams, $systemParams);

		return $state;
	}

	public function notifyChangeVote($pollId, $itemId)
	{
		$poll = ES::table('Polls');
		$poll->load($pollId);

		// Current voter
		$voter = ES::user();

		// Do not self notify.
		if ($voter->id == $poll->created_by) {
			return;
		}

		// Default rule
		$rule = 'polls.changevote.item';

		// Prepare params for email notification
		$mailParams = array();

		$mailParams['actor'] = $voter->getName();
		$mailParams['posterAvatar'] = $voter->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['posterLink'] = $voter->getPermalink(true, true);
		$mailParams['permalink'] = ESR::stream(array('id' => $poll->uid, 'layout' => 'item', 'external' => true), true);

		// Default email settings
		$emailTitle = 'COM_EASYSOCIAL_EMAILS_POLLS_CHANGE_VOTE_ITEM';
		$emailTemplate = 'site/polls/vote.change.item';

		// Prepare the system notification params
		$systemParams = array();

		$systemParams['url'] = ESR::stream(array('id' => $poll->uid, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $voter->id;
		$systemParams['uid'] = $poll->uid;
		$systemParams['title'] = JText::sprintf('COM_EASYSOCIAL_POLLS_CHANGE_VOTE', $voter->getName());

		// If the polls is from cluster, we need to respect it.
		if ($poll->cluster_id) {

			// how to get cluster type?
			$clusterTable = ES::table('Cluster');
			$clusterTable->load($poll->cluster_id);

			// To be use to call the cluster type library.
			$clusterType = $clusterTable->cluster_type;

			// To be use in context type and rules type since it contain 's' at the end of character.
			$clusterTypes = $clusterType . 's';

			// Load the cluster. Works for event and group
			$cluster = ES::cluster($clusterType, $clusterTable->id);

			$systemParams['context_type'] = $clusterTypes;
			$systemParams['context_ids'] = $cluster->id;
			$systemParams['title'] = JText::sprintf('COM_EASYSOCIAL_POLLS_CHANGE_VOTE_IN_' . strtoupper($clusterType), $voter->getName(), $cluster->getName());

			$mailParams['cluster'] = $cluster->getName();
			$mailParams['clusterLink'] = $cluster->getPermalink(true, true);

			$emailTitle = 'APP_GROUP_STORY_EMAILS_NEW_POLLS_CHANGE_VOTE_IN_'. strtoupper($clusterType);
			$emailTemplate = 'apps/' . $clusterType . '/polls/vote.change.item';

			$rule = $clusterTypes . '.polls.changevote.item';
		}

		$mailParams['title'] = $emailTitle;
		$mailParams['template'] = $emailTemplate;

		// Try to send the notification
		$state = ES::notify($rule, array($poll->created_by), $mailParams, $systemParams);

		return $state;
	}
}
