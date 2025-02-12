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

class SocialGroupAppDiscussions extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_DISCUSSIONS && $context != SOCIAL_TYPE_DISCUSSION) {
			return;
		}

		$discussion = ES::table('Discussion');
		$discussion->load($uid);

		if (!$discussion->id) {
			return false;
		}

		$cluster = ES::cluster($discussion->type, $discussion->uid);

		// If it is a public cluster, it should allow this
		if ($cluster->isOpen()) {
			return true;
		}

		if ($cluster->isAdmin()) {
			return true;
		}

		if ($cluster->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Performs clean up when a group is deleted
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onBeforeDelete(&$group)
	{
		// Delete all discussions from a group
		$model = ES::model('Discussions');
		$model->delete($group->id , SOCIAL_TYPE_GROUP);
	}

	/**
	 * Determines if the app should appear on the sidebar
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function appListing($view, $id, $type)
	{
		if ($type != SOCIAL_TYPE_GROUP) {
			return true;
		}

		// We should not display the discussions on the app if it's disabled
		$group = ES::group($id);
		$registry = $group->getParams();
		$access = $group->getAccess();

		if (!$access->get('discussions.enabled', true)) {
			return false;
		}

		if (!$registry->get('discussions', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Processes likes notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('discussions.group.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the actor
		$actor = ES::user($likes->created_by);

		$discussion = ES::table('Discussion');
		$discussion->load($likes->uid);

		$group = ES::group($discussion->uid);

		$systemOptions  = array(
			'context_type' => $likes->type,
			'url' => $discussion->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		if ($likes->created_by != $discussion->created_by) {
			ES::notify('likes.item', array($discussion->created_by), false, $systemOptions, $group->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the item and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, 'discussions', 'group', 'create', array(), array($discussion->created_by, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions, $group->notification);
	}

	/**
	 * Prepare notification items for discussions
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		$group = ES::group($item->uid);
		$actor = $item->getActor();

		// When a user likes a discussion item created by a user
		if ($item->cmd == 'likes.item' && $item->context_type == 'discussions.group.create') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		if ($item->cmd == 'group.discussion.create') {
			$discussion = ES::table('Discussion');
			$discussion->load($item->context_ids);

			$item->title = JText::sprintf('APP_GROUP_DISCUSSIONS_NOTIFICATIONS_CREATED_DISCUSSION', $actor->getName(), $group->getName());
			$item->content = $discussion->title;

			return $item;
		}

		if ($item->cmd == 'group.discussion.reply') {
			$reply = ES::table('Discussion');
			$reply->load($item->context_ids);

			$pattern = '/\[file id="(.*?)"\](.*?)\[\/file\]/is';
			$reply->content = trim(preg_replace($pattern, '', $reply->content));

			// Get title of discussion
			$discussion = ES::table('Discussion');
			$discussion->load(array('id' => $reply->parent_id));

			$item->title = JText::sprintf('APP_GROUP_DISCUSSIONS_NOTIFICATIONS_REPLIED_DISCUSSION', $actor->getName(), $group->getName(), $discussion->title);

			$item->content = ESJString::substr($reply->content , 0, 50) . JText::_('COM_EASYSOCIAL_ELLIPSES');

			return $item;
		}
	}

	/**
	 * Determine if the cmd is allowed for the notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array('group.discussion.create', 'group.discussion.reply', 'likes.item');

		return in_array($cmd, $allowed);
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if($item->context_type != SOCIAL_TYPE_DISCUSSIONS)
		{
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params 	= ES::registry($item->params);
		$group 		= ES::group($params->get('group'));

		if (!$group) {
			return;
		}

		$item->cnt = 1;

		if (!$group->isOpen() && !$group->isMember()) {
			$item->cnt = 0;
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();
		$excludeVerb = [];

		if(! $params->get('stream_create', true)) {
			$excludeVerb[] = 'create';
		}

		if (! $params->get('stream_reply', true)) {
			$excludeVerb[] = 'reply';
		}

		if (! $params->get('stream_answered', true)) {
			$excludeVerb[] = 'answered';
		}

		if (! $params->get('stream_lock', true)) {
			$excludeVerb[] = 'lock';
		}

		if ($excludeVerb !== false) {
			$exclude['discussions'] = $excludeVerb;
		}
	}

	/**
	 * Trigger for onPrepareDigest
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareDigest(SocialStreamItem &$item)
	{
		if ($item->context != SOCIAL_TYPE_DISCUSSIONS) {
			return;
		}

		// Ensure that discussions are enabled for this group
		$group = ES::group($item->cluster_id);
		$registry = $group->getParams();
		if (!$registry->get('discussions', true)) {
			return;
		}

		$discussion	= ES::table('Discussion');
		$exists = $discussion->load($item->contextId);

		if (!$exists) {
			return;
		}

		$actor = $item->actor;

		$item->title = '';
		$item->preview = '';
		$item->link = $discussion->getPermalink(true, true);

		if ($item->verb == 'create') {
			$item->title = JText::sprintf('COM_ES_APP_DISCUSSIONS_DIGEST_CREATE_TITLE', $actor->getName(), $discussion->title);
		}

		if ($item->verb == 'reply') {
			$item->title = JText::sprintf('COM_ES_APP_DISCUSSIONS_DIGEST_REPLY_TITLE', $actor->getName(), $discussion->title);

		}

		if ($item->verb == 'answered') {
			$item->title = JText::sprintf('COM_ES_APP_DISCUSSIONS_DIGEST_ANSWERED_TITLE', $actor->getName(), $discussion->title);

		}

		if ($item->verb == 'lock') {
			$item->title = JText::sprintf('COM_ES_APP_DISCUSSIONS_DIGEST_LOCKED_TITLE', $actor->getName(), $discussion->title);

		}
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_DISCUSSIONS) {
			return;
		}

		// Get the cluster
		$group = ES::group($item->cluster_id);

		if (!$group || !$group->canViewItem() || !$this->getApp()->hasAccess($group->category_id)) {
			return;
		}

		// Ensure that announcements are enabled for this group
		$registry = $group->getParams();

		if (!$registry->get('discussions', true)) {
			return;
		}

		// For profile pages, it doesn't make sense to display them here
		if ($item->getPerspective() == 'PROFILE') {
			return;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get the app params
		$params = $this->getApp()->getParams();

		$defaultValue = $item->verb == 'reply' || $item->verb == 'lock' ? false : true;

		if ($params->get('stream_' . $item->verb, $defaultValue) == false) {
			return;
		}

		// Do not allow user to repost discussions
		$item->repost = false;

		$item->likes = ES::likes($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, $item->uid, array('clusterId' => $item->cluster_id));

		// Apply comments on the stream
		$item->comments = false;

		if ($params->get('stream_comments', true)) {
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP , array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)),'clusterId' => $item->cluster_id), $item->uid);
		}

		// Get the params of the stream item
		$streamParams = $item->getParams();

		// Do not proceed this if the discussion id is null
		if ($streamParams && !isset($streamParams->get('discussion')->id)) {
			return;
		}

		if ($item->verb == 'create') {
			$this->prepareCreateDiscussionStream($item, $streamParams);
		}

		if ($item->verb == 'reply') {
			$this->prepareReplyStream($item, $streamParams);
		}

		if ($item->verb == 'answered') {
			$this->prepareAnsweredStream($item, $streamParams);
		}

		if ($item->verb == 'lock') {
			$this->prepareLockedStream($item, $streamParams);
		}

		// Append the opengraph tags
		$item->addOgDescription();
	}

	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_DISCUSSIONS) {
			return;
		}

		$this->onPrepareStream($item, $includePrivacy);

		if (!$item->title) {
			return;
		}

		$streamParams = $item->getParams();

		$title = '';
		$description = '';

		$group = ES::group($item->cluster_id);

		if ($item->verb == 'create') {
			$discussion	= ES::table('Discussion');
			$exists = $discussion->load($item->contextId);

			if (!$exists) {
				return;
			}

			$permalink = $discussion->getPermalink(true, true);
			$description = $this->formatContent($discussion);

			$actorUser = $this->formatRestObjectName('user', $item->actor->id);
			$group = $this->formatRestObjectName('group', $group->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_CREATE_TITLE_' . $item->getPerspective(), $actorUser, $group);
		}

		if ($item->verb == 'reply') {
			// Get the reply item
			$reply = ES::table('Discussion');
			$exists = $reply->load($streamParams->get('reply')->id);

			if (!$exists) {
				return;
			}

			// Get the main permalink
			$discussion = $reply->getParent();
			$permalink = $discussion->getPermalink(true, true);
			$description = $this->formatContent($reply);

			$actorUser = $this->formatRestObjectName('user', $item->actor->id);
			$group = $this->formatRestObjectName('group', $group->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_REPLY_TITLE_' . $item->getPerspective(), $actorUser, $discussion->title, $group);
		}

		if ($item->verb == 'answered') {
			// Get the discussion object
			$reply = ES::table('Discussion');
			$exists = $reply->load($streamParams->get('reply')->id);

			if (!$exists) {
				return;
			}

			$discussion = $reply->getParent();
			$permalink = $discussion->getPermalink(true, true);
			$description = $this->formatContent($reply);

			// Get the reply author
			$replyAuthor = ES::user($reply->created_by);

			$actorUser = $this->formatRestObjectName('user', $item->actor->id);
			$replyAuthor = $this->formatRestObjectName('user', $replyAuthor->id);
			$group = $this->formatRestObjectName('group', $group->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_ANSWERED_TITLE_' . $item->getPerspective(),
				$actorUser,
				$replyAuthor,
				$group
			);
		}

		if ($item->verb == 'lock') {
			// Get the discussion item
			$discussion = ES::table('Discussion');
			$exists = $discussion->load($streamParams->get('discussion')->id);

			if (!$exists) {
				return;
			}

			// Get the permalink
			$permalink = $discussion->getPermalink(true, true);
			$description = $this->formatContent($discussion);

			$actorUser = $this->formatRestObjectName('user', $item->actor->id);
			$group = $this->formatRestObjectName('group', $group->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_LOCKED_TITLE_' . $item->getPerspective(), $actorUser, $discussion->title, $group);
		}

		$webview = [
			'title' => strip_tags($discussion->title),
			'permalink' => $permalink,
			'description' => $description,
			'cover' => false,
			'autologin' => true,
			'stream' => [
				'title' => strip_tags($title),
				'showUserName' => false,
				'showUserAvatar' => true
			]
		];

		$item->webview = $webview;
	}

	/**
	 * Prepares the stream item for new discussion creation
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function prepareCreateDiscussionStream(&$item, $params)
	{
		$group = ES::group($item->cluster_id);

		$discussion	= ES::table('Discussion');
		$exists = $discussion->load($item->contextId);

		if (!$exists) {
			return;
		}

		// Determines if there are files associated with the discussion
		$files = $discussion->getFiles();
		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($discussion);

		$access = $group->getAccess();
		if ($this->my->isSiteAdmin() || $group->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $discussion->getEditPermalink();;
		}

		$this->set('item', $item);
		$this->set('files', $files);
		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('content', $content);
		$this->set('cluster', $group);

		$item->title = parent::display('themes:/site/streams/discussions/create.title');
		$item->preview = parent::display('themes:/site/streams/discussions/create.preview');
	}

	/**
	 * Prepares the stream item for new discussion creation
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function prepareReplyStream(&$item, $params)
	{
		$group = $item->getCluster();

		// Get the reply item
		$reply = ES::table('Discussion');
		$exists = $reply->load($params->get('reply')->id);

		if (!$exists) {
			return;
		}

		// Get the main permalink
		$discussion = $reply->getParent();
		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($reply);

		$this->set('cluster', $group);
		$this->set('item', $item);
		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('reply', $reply);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/reply.title');
		$item->preview = parent::display('themes:/site/streams/discussions/reply.preview');
	}

	/**
	 * Prepares the stream item for new discussion creation
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function prepareAnsweredStream(&$item, $params)
	{
		$group = $item->getCluster();

		// Get the discussion object
		$reply = ES::table('Discussion');
		$exists = $reply->load($params->get('reply')->id);

		if (!$exists) {
			return;
		}

		$discussion = $reply->getParent();

		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($reply);

		// Get the reply author
		$reply->author	= ES::user($reply->created_by);

		$this->set('item', $item);
		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('reply', $reply);
		$this->set('content', $content);
		$this->set('cluster', $group);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/answered.title');
		$item->preview = parent::display('themes:/site/streams/discussions/answered.preview');
	}

	/**
	 * Prepares the stream item for new discussion creation
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function prepareLockedStream(SocialStreamItem &$item, $params)
	{
		$group = $item->getCluster();

		// Get the discussion item
		$discussion = ES::table('Discussion');
		$exists = $discussion->load($params->get('discussion')->id);

		if (!$exists) {
			return;
		}

		// Get the permalink
		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($discussion);

		$this->set('content', $content);
		$this->set('item', $item);
		$this->set('permalink', $permalink);
		$this->set('actor', $item->actor);
		$this->set('discussion', $discussion);
		$this->set('cluster', $group);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/locked.title');
		$item->preview = parent::display('themes:/site/streams/discussions/locked.preview');
	}

	/**
	 * Export notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onNotificationExport(&$item, &$content, $viewer)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		// Likes is associated with discussion creation.
		if ($item->cmd == 'likes.item' && $item->context_type != 'discussions.group.create') {
			return;
		}

		$discussion = ES::table('Discussion');
		$discussion->load($item->context_ids);

		$content->type = 'discussions';
		$content->object = $discussion->toExportData($viewer);
	}

	/**
	 * Format the stream content
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function formatContent($discussion)
	{
		// Reduce length based on the settings
		$params = $this->getParams();
		$length = $params->get('stream_length', 250);

		if ($length == 0) {
			return $discussion->getContent(true);
		}

		$content = $discussion->getContent(true);
		$content = strip_tags($content);
		$content = ESJString::substr($content, 0, $length) . ' ' . JText::_('COM_EASYSOCIAL_ELLIPSES');

		return $content;
	}
}
