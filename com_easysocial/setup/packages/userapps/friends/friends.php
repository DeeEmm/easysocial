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

ES::import('admin:/includes/apps/apps');

class SocialUserAppFriends extends SocialAppItem
{
	/**
	 * Notification triggered when generating notification item.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->config->get('friends.enabled')) {
			return;
		}

		$allowed = array('friends.approve');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		if ($item->cmd == 'friends.approve') {
			$user = ES::user($item->actor_id);
			$item->title = JText::sprintf('APP_USER_FRIENDS_NOTIFICATIONS_USER_ACCEPTED_YOUR_FRIEND_REQUEST', $user->getName());
			$item->image = $user->getAvatar();
		}
	}

	/**
	 * Responsible to generate the activity contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if (! $this->config->get('friends.enabled')) {
			return;
		}

		if ($item->context != 'friends') {
			return;
		}

		// There should be at least 1 target
		if (!$item->targets) {
			return;
		}

		$my = ES::user();

		// Receiving actor.
		$target = $item->targets[0];

		// Set the target.
		$this->set('actor', $item->actor);
		$this->set('target', $target);

		// User A made friends with user B
		$item->title = parent::display('streams/friends.title');

		if ($includePrivacy) {
			$privacy = ES::privacy($my->id);
			$item->privacy = $privacy->form($item->contextId, 'friends', $item->actor->id, 'core.view', false, $item->aggregatedItems[0]->uid);
		}

		return true;
	}


	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		if (!$this->config->get('friends.enabled')) {
			return;
		}

		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'friends') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			$sModel = ES::model('Stream');
			$aItem = $sModel->getActivityItem($item->id, 'uid');

			$contextId = $aItem[0]->context_id;

			if (!$privacy->validate('core.view', $contextId, 'friends', $item->actor_id)) {
				$item->cnt = 0;
			}
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = [];

		if (!$params->get('stream_friends', true)) {
			$exclude['friends'] = true;
		}

		// force override
		if (! $this->config->get('friends.enabled')) {
			$exclude['friends'] = true;
		}
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if ($item->context != 'friends') {
			return;
		}

		if (!$this->config->get('friends.enabled')) {
			return;
		}

		// Determines if the stream should be generated
		$params = $this->getParams();

		if (!$params->get('stream_friends', true)) {
			return;
		}

		// Get the actor
		$actor = $item->actor;

		// check if the actor is ESAD profile or not, if yes, we skip the rendering.
		if (!$actor->hasCommunityAccess()) {
			return;
		}

		if ($includePrivacy) {
			$privacy = $this->my->getPrivacy();

			if (!$privacy->validate('core.view', $item->contextId, 'friends', $item->actor->id)) {
				return;
			}

			$item->privacy = $privacy->form($item->contextId, 'friends', $item->actor->id, 'core.view', false, $item->uid, array(), array('iconOnly' => true));
		}

		// no target. this could be data error. ignore this item.
		if(!$item->targets) {
			return;
		}

		// Receiving actor.
		$item->targets = $item->targets[0];
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->show = true;
	}

	/**
	 * Responsible to generate the stream contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'friends') {
			return;
		}

		if (! $this->config->get('friends.enabled')) {
			return;
		}

		// Determines if the stream should be generated
		$params = $this->getParams();

		if (!$params->get('stream_friends', true)) {
			return;
		}

		// Get the actor
		$actor = $item->actor;

		// check if the actor is ESAD profile or not, if yes, we skip the rendering.
		if (!$actor->hasCommunityAccess()) {
			$item->title = '';
			return;
		}

		if ($includePrivacy) {
			$privacy = $this->my->getPrivacy();

			if(!$privacy->validate('core.view', $item->contextId, 'friends', $item->actor->id)) {
				return;
			}

			$item->privacy = $privacy->form($item->contextId, 'friends', $item->actor->id, 'core.view', false, $item->uid, array(), array('iconOnly' => true));
		}

		// Get the context id.
		$id = $item->contextId;

		// no target. this could be data error. ignore this item.
		if(!$item->targets) {
			return;
		}

		// Receiving actor.
		$target = $item->targets[0];

		// Get the current id.
		$id = $this->input->get('id', 0, 'int');

		$this->set('actor', $actor);
		$this->set('target', $target);

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('streams/friends.title');

		return true;
	}

	/**
	 * Processes a saved story so that we can notify users who are tagged in the system
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterStorySave(&$stream, $streamItem, $streamTemplate)
	{
		// If there's no "with" data, skip this.
		if (!$streamTemplate->with) {
			return;
		}

		// if this is a scheduled post, do not create notification.
		if ($streamItem->isScheduled()) {
			return;
		}

		// Get list of users that are tagged in this post.
		$taggedUsers = $streamTemplate->with;

		// Get the creator of this update
		$poster = ES::user($streamTemplate->actor_id);

		// Get the content of the stream item.
		$content = $streamTemplate->content;

		if (!$taggedUsers) {
			return;
		}

		foreach ($taggedUsers as $id) {

			$taggedUser = ES::user($id);


			// Set the email options
			$emailOptions = array(
				'title' => 'APP_USER_FRIENDS_EMAILS_USER_TAGGED_YOU_IN_POST_SUBJECT',
				'template' => 'apps/user/friends/post.tagged',
				'permalink' => $streamItem->getPermalink(true, true),
				'actor' => $poster->getName(),
				'actorAvatar' => $poster->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $poster->getPermalink(true, true),
				'message' => $content,
				// sid used by scheduled post.
				'sid' => $streamItem->uid
			);

			$systemOptions = array(
				'uid' => $streamItem->id,
				// sid used by scheduled post.
				'sid' => $streamItem->uid,
				'context_type' => 'tagged',
				'type' => 'stream',
				'url' => $streamItem->getPermalink(false, false, false),
				'actor_id' => $poster->id,
				'aggregate' => false
			);

			// We'll need to pass the scheduled date for notification processing.
			if (isset($stream->scheduled) && $stream->scheduled) {
				$emailOptions['scheduled'] = $stream->scheduled;
				$systemOptions['scheduled'] = $stream->scheduled;
			}

			// Add new notification item
			ES::notify('stream.tagged',  array($taggedUser->id), $emailOptions, $systemOptions);
		}

		return true;
	}

	/**
	 * Retrieve the content of the notification object
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if (!$this->config->get('friends.enabled')) {
			return;
		}

		$allowed = array('friends.approve');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->actor_id;
		$target->type = 'user';
		$target->endpoint = 'profile';
		$target->query_string = $target->endpoint . '&targetUserId=' . $target->id;

		$item->target = $target;
	}
}
