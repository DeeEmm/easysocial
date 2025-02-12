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

class SocialPageAppDiscussions extends SocialAppItem
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
	 * Performs clean up when a page is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onBeforeDelete(&$page)
	{
		// Delete all discussions from a page
		$model = ES::model('Discussions');
		$model->delete($page->id, SOCIAL_TYPE_PAGE);
	}

	/**
	 * Determines if the app should appear on the sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function appListing($view, $id, $type)
	{
		if ($type != SOCIAL_TYPE_PAGE) {
			return true;
		}

		// We should not display the discussions on the app if it's disabled
		$page = ES::page($id);
		$registry = $page->getParams();
		$access = $page->getAccess();

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
		// Only for create discussion stream.
		$allowed = array('discussions.page.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Load the stream table
		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		// Get the stream item
		$streamItems = $stream->getItems();
		$streamItem = $streamItems[0];

		// Get the liker
		$liker = ES::user($likes->created_by);

		// Get the discussion object since it's tied to the stream
		$discussion = ES::table('Discussion');
		$discussion->load($streamItem->context_id);

		// Load the Page based in discussion table
		$page = ES::page($discussion->uid);

		$systemOptions  = array(
			'context_type' => $likes->type,
			'url' => $discussion->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		// [Page Compatibility] Only notify if the liker is not a page admin
		// We should notify all the admins instead of the discussion author.
		if (!$page->isAdmin($likes->created_by)) {
			ES::notify('likes.item', $page->getAdmins(), false, $systemOptions, $page->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, 'discussions', 'page', 'create', array(), array($discussion->created_by, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions, $page->notification);
	}

	/**
	 * Prepare notification items for discussions
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		// Get the page information
		$page = ES::page($item->uid);
		$actor = ES::user($item->actor_id);

		// Notification for the page admins
		if ($item->cmd == 'likes.item' && $item->context_type == 'discussions.page.create') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// This notification is for the page follower
		if ($item->cmd == 'page.discussion.create') {

			$discussion = ES::table('Discussion');
			$discussion->load($item->context_ids);

			// Load the actor
			$actor = ES::user($discussion->created_by);

			$item->title = JText::sprintf('APP_PAGE_DISCUSSIONS_NOTIFICATIONS_CREATED_DISCUSSION', $actor->getName(), $page->getName());

			if ($page->isAdmin($actor->id)) {
				$item->title = JText::sprintf('APP_PAGE_DISCUSSIONS_NOTIFICATIONS_CREATED_DISCUSSION_ADMIN', $page->getName());
				$item->setActorAlias($page);
			}

			$item->content = $discussion->title;

			return $item;
		}

		if ($item->cmd == 'page.discussion.reply') {

			$reply = ES::table('Discussion');
			$reply->load($item->context_ids);

			// Load the replier
			$replier = ES::user($reply->created_by);

			// Get the discussion for this reply
			$discussion = ES::table('Discussion');
			$discussion->load($reply->parent_id);

			$pattern = '/\[file id="(.*?)"\](.*?)\[\/file\]/is';
			$reply->content = trim(preg_replace($pattern, '', $reply->content));

			$item->title = JText::sprintf('APP_PAGE_DISCUSSIONS_NOTIFICATIONS_REPLIED_DISCUSSION', $replier->getName(), $page->getName(), $discussion->title);

			if ($page->isAdmin($replier->id)) {
				$item->title = JText::sprintf('APP_PAGE_DISCUSSIONS_NOTIFICATIONS_REPLIED_DISCUSSION_ADMIN', $page->getName(), $discussion->title, $page->getName());
				$item->setActorAlias($page);
			}

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
		$allowed = array('page.discussion.create', 'page.discussion.reply', 'likes.item');

		return in_array($cmd, $allowed);
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != SOCIAL_TYPE_DISCUSSIONS) {
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params = ES::registry($item->params);
		$page = ES::page($params->get('page'));

		if (!$page) {
			return;
		}

		$item->cnt = 1;

		if (!$page->isOpen() && !$page->isMember()) {
			$item->cnt = 0;
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude, $perspective = null)
	{
		// Get app params
		$params	= $this->getParams();

		$excludeVerb = [];

		if ($perspective == 'profile') {
			$excludeVerb[] = 'create';
			$excludeVerb[] = 'reply';
			$excludeVerb[] = 'answered';
			$excludeVerb[] = 'lock';
		}

		// add pages into exclude list
		if ($excludeVerb !== false) {
			$exclude['discussions'] = $excludeVerb;
		}
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'discussions') {
			return;
		}

		// Get the cluster
		$page = $item->getCluster();

		if (!$page || !$page->canViewItem() || !$this->getApp()->hasAccess($page->category_id)) {
			return;
		}

		// Ensure that announcements are enabled for this page
		$registry = $page->getParams();

		if (!$registry->get('discussions', true)) {
			return;
		}

		// For profile pages, it doesn't make sense to display them here
		if ($item->getPerspective() == 'PROFILE') {
			return;
		}

		// [Page Compatibility] Manually set the perspective only for page admin
		if ($item->post_as == SOCIAL_TYPE_PAGE) {
			$item->setPerspective(SOCIAL_TYPE_PAGES);
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

		$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $page->id);
		$likeParams = array('clusterId' => $page->id);

		$streamParams = $item->getParams();

		// Process likes and comments differently.
		$item->likes = ES::likes($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, $likeParams);

		// Apply comments on the stream
		$item->comments = false;

		if ($params->get('stream_comments', true)) {
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $commentParams, $item->uid);
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

	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
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

		$page = ES::page($item->cluster_id);

		if ($item->verb == 'create') {
			$discussion	= ES::table('Discussion');
			$exists = $discussion->load($item->contextId);

			if (!$exists) {
				return;
			}

			$permalink = $discussion->getPermalink(true, true);
			$description = $this->formatContent($discussion);
			$actorName = $this->formatRestObjectName('user', $item->actor->id);
			$pageName = $this->formatRestObjectName('page', $page->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_CREATE_TITLE_' . $item->getPerspective(), $actorName, $pageName);
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
			$actorName = $this->formatRestObjectName('user', $item->actor->id);
			$pageName = $this->formatRestObjectName('page', $page->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_REPLY_TITLE_' . $item->getPerspective(), $actorName, $pageName);
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
			$actorName = $this->formatRestObjectName('user', $item->actor->id);
			$replyName = $this->formatRestObjectName('user', $reply->created_by);
			$pageName = $this->formatRestObjectName('page', $page->id);

			// Get the reply author
			$replyAuthor = ES::user($reply->created_by);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_ANSWERED_TITLE_' . $item->getPerspective(),
				$actorName,
				$replyName,
				$pageName
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
			$actorName = $this->formatRestObjectName('user', $item->actor->id);
			$pageName = $this->formatRestObjectName('page', $page->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_REPLY_TITLE_' . $item->getPerspective(), $actorName, $discussion->title, $pageName);
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
	 * @since	2.0
	 * @access	public
	 * @param	SocialStreamItem	The stream item.
	 * @return
	 */
	private function prepareCreateDiscussionStream(&$item, $params)
	{
		// Get the page library
		$page = $item->getCluster();

		$discussion	= ES::table('Discussion');
		$discussion->load($item->contextId);

		// Determines if there are files associated with the discussion
		$files = $discussion->getFiles();
		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($discussion);

		$access = $page->getAccess();
		if ($this->my->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $discussion->getEditPermalink();;
		}

		// Set the actor alias. admin will answer/create discussion as the Page alias
		// As we know only admin can create a discussion in Page
		$actor = $item->getPostActor($page);

		$this->set('item', $item);
		$this->set('files', $files);
		$this->set('actor', $actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('content', $content);
		$this->set('cluster', $page);

		$item->title = parent::display('themes:/site/streams/discussions/create.title');
		$item->preview = parent::display('themes:/site/streams/discussions/create.preview');
	}

	/**
	 * Prepares the stream item for new discussion reply
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function prepareReplyStream(&$item, $params)
	{
		$page = $item->getCluster();

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

		// Set the actor alias.
		// If the replier is the page admin, we need to change the actor to be the Page itself
		$actor = $item->getPostActor($page);

		$this->set('cluster', $page);
		$this->set('item', $item);
		$this->set('actor', $actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('reply', $reply);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/reply.title');
		$item->preview = parent::display('themes:/site/streams/discussions/reply.preview');
	}

	/**
	 * Prepares the stream item for answered discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function prepareAnsweredStream(&$item, $params)
	{
		$page = $item->getCluster();

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
		$reply->author = ES::user($reply->created_by);

		// Set the actor alias. admin will answer/create discussion as the Page alias
		$actor = $item->getPostActor($page);

		$this->set('item', $item);
		$this->set('actor', $actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('reply', $reply);
		$this->set('content', $content);
		$this->set('cluster', $page);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/answered.title');
		$item->preview = parent::display('themes:/site/streams/discussions/answered.preview');
	}

	/**
	 * Prepares the stream item for lock discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function prepareLockedStream(SocialStreamItem &$item, $params)
	{
		$page = $item->getCluster();

		// Get the discussion item
		$discussion = ES::table('Discussion');
		$exists = $discussion->load($params->get('discussion')->id);

		if (!$exists) {
			return;
		}

		// Get the permalink
		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($discussion);

		// Set the actor alias. admin will answer/create discussion as the Page alias
		$actor = $item->getPostActor($page);

		$this->set('content', $content);
		$this->set('item', $item);
		$this->set('permalink', $permalink);
		$this->set('actor', $actor);
		$this->set('discussion', $discussion);
		$this->set('cluster', $page);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/locked.title');
		$item->preview = parent::display('themes:/site/streams/discussions/locked.preview');
	}

	/**
	 * Formats a discussion content
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
		if ($item->cmd == 'likes.item' && $item->context_type != 'discussions.page.create') {
			return;
		}

		$discussion = ES::table('Discussion');
		$discussion->load($item->context_ids);

		$content->type = 'discussions';
		$content->object = $discussion->toExportData($viewer);
	}
}
