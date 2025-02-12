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

class SocialEventAppDiscussions extends SocialAppItem
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
	 * Performs clean up when a event is deleted.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onBeforeDelete(&$event)
	{
		// Delete all discussions from a event
		$model = ES::model('Discussions');
		$model->delete($event->id, SOCIAL_TYPE_EVENT);
	}

	/**
	 * Processes likes notifications.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('discussions.event.create', 'discussions.event.reply');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the actor
		$actor = ES::user($likes->created_by);

		// Get the discussion object since it's tied to the stream
		$discussion = ES::table('Discussion');
		$discussion->load($likes->uid);

		list($element, $group, $verb) = explode('.', $likes->type);

		 $systemOptions  = array(
			'context_type' => $likes->type,
			'url' => FRoute::stream(array('layout' => 'item', 'id' => $likes->stream_id, 'sef' => false)),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		 // Notify the owner first
		 if ($likes->created_by != $discussion->created_by) {
			 ES::notify('likes.item', array($discussion->created_by), false, $systemOptions);
		 }

		 // Get a list of recipients to be notified for this stream item
		 // We exclude the owner of the discussion and the actor of the like here
		 $recipients = $this->getStreamNotificationTargets($likes->uid, $element, $group, $verb, array(), array($discussion->created_by, $likes->created_by));

		 ES::notify('likes.involved', $recipients, false, $systemOptions);
	}

	public function onAfterCommentSave($comment)
	{
		$allowed = array('discussions.event.create', 'discussions.event.reply');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		$stream = ES::table('Stream');
		$stream->load($comment->stream_id);
		$streamItems = $stream->getItems();

		// Since we have the stream, we can get the event id
		$event = ES::event($stream->cluster_id);

		// Get the actor
		$actor = ES::user($comment->created_by);

		// Get the discussion object since it's tied to the stream
		$discussion = ES::table('Discussion');
		$discussion->load($streamItems[0]->context_id);

		list($element, $group, $verb) = explode('.', $comment->element);

		// retrieve the first mentioned user from the comment
		$exclusion = $comment->getFirstMentionedUserFromComment();

		// Convert the mention tag to permalink only for comment.
		$commentOptions = [
			'commentId' => $comment->id,
			'hasTag' => true,
			'exclusion' => $exclusion
		];

		$parseBBCodeOptions = [
			'escape' => false,
			'links' => true,
			'code' => true
		];

		$stringLib = ES::string();
		$commentContent = $stringLib->normalizeContent($comment->comment, $parseBBCodeOptions, false, '', $commentOptions);

		$emailOptions = array(
			'title' => 'APP_EVENT_DISCUSSIONS_EMAILS_' . strtoupper($verb) . '_COMMENT_ITEM_SUBJECT',
			'template' => 'apps/event/discussions/' . $verb . '.comment.item',
			'permalink' => $stream->getPermalink(true, true),
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'event' => $event->getName(),
			'comment' => $commentContent
		);

		$systemOptions  = array(
			'context_type' => $comment->element,
			'context_ids' => $discussion->id,
			'content' => $commentContent,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		 // Notify the owner first
		if ($comment->created_by != $discussion->created_by) {
			ES::notify('comments.item', array($discussion->created_by), $emailOptions, $systemOptions);
		}

		 // Get a list of recipients to be notified for this stream item
		 // We exclude the owner of the discussion and the actor of the comment here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($discussion->created_by, $comment->created_by));

		$emailOptions['title'] = 'APP_EVENT_DISCUSSIONS_EMAILS_' . strtoupper($verb) . '_COMMENT_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/event/discussions/' . $verb . '.comment.involved';

		if (!$comment->isChild()) {
			 // Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/event/discussions/reply.comment.item';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Prepare notification items for discussions.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		// Get the event information
		$event = ES::event($item->uid);
		$actor = ES::user($item->actor_id);

		// Process comments and likes notification
		if (in_array($item->cmd, array('likes.item', 'likes.involved', 'comments.item', 'comments.involved', 'comments.replied')) && in_array($item->context_type, array('discussions.event.create', 'discussions.event.reply'))) {
			$hook = $this->getHook('notification', $item->type);
			$hook->execute($item);
			return;
		}

		if ($item->cmd == 'events.discussion.create') {

			$discussion = ES::table('Discussion');
			$discussion->load($item->context_ids);

			$item->title = JText::sprintf('APP_EVENT_DISCUSSIONS_NOTIFICATIONS_CREATED_DISCUSSION', $actor->getName(), $event->getName());
			$item->content = $discussion->title;

			return $item;
		}

		if ($item->cmd == 'events.discussion.reply') {
			$reply = ES::table('Discussion');
			$reply->load($item->context_ids);

			// Get title of discussion
			$discussion = ES::table('Discussion');
			$discussion->load(array('id' => $reply->parent_id));

			$pattern = '/\[file id="(.*?)"\](.*?)\[\/file\]/is';
			$reply->content = trim(preg_replace($pattern, '', $reply->content));

			$item->title = JText::sprintf('APP_EVENT_DISCUSSIONS_NOTIFICATIONS_REPLED_DISCUSSION', $actor->getName(), $event->getName(), $discussion->title);

			$item->content = ESJString::substr($reply->content , 0, 50) . JText::_('COM_EASYSOCIAL_ELLIPSES');

			return $item;
		}

		if ($item->cmd == 'events.discussion.answered') {
			$reply = ES::table('Discussion');
			$reply->load($item->context_ids);

			$discussion = ES::table('Discussion');
			$discussion->load($reply->parent_id);

			$item->title = JText::sprintf('APP_EVENT_DISCUSSIONS_NOTIFICATIONS_ACCEPTED_DISCUSSION', $discussion->title);

			return $item;
		}

		if ($item->cmd == 'events.discussion.locked') {

		}
	}

	/**
	 * Determine if the cmd is allowed for notifications
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array('events.discussion.create', 'events.discussion.reply', 'events.discussion.answered', 'events.discussion.locked', 'likes.item', 'likes.involved', 'comments.item', 'comments.involved', 'comments.replied');

		return in_array($cmd, $allowed);
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'discussions') {
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params = ES::registry($item->params);
		$event = ES::event($params->get('event'));

		if (!$event) {
			return;
		}

		$item->cnt = 1;

		// If event is not open and the user is not a guest
		if (!$event->isOpen() && !$event->getGuest()->isGuest()) {
			$item->cnt = 0;
		}

		return true;
	}


	/**
	 * Prepares the stream item.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'discussions') {
			return;
		}

		// Get the event object
		$event = ES::event($item->cluster_id);

		if (!$event || !$event->canViewItem()) {
			return;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$params = $this->getApp()->getParams();

		$defaultValue = $item->verb == 'reply' || $item->verb == 'lock' ? false : true;

		if ($params->get('stream_' . $item->verb, $defaultValue) == false) {
			return;
		}

		// Do not allow user to repost discussions
		$item->repost = false;

		// Process likes and comments differently.
		$item->likes = ES::likes()->get($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, $item->uid, array('clusterId' => $item->cluster_id));
		$url = ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false));

		// Apply comments on the stream
		$item->comments = false;

		if ($params->get('stream_comments', true)) {
			$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, [
									'url' => $url,
									'clusterId' => $item->cluster_id
								], $item->uid);
		}

		// Get the params of the stream item
		$streamParams = $item->getParams();

		if ($item->verb == 'create') {
			$this->prepareCreateStream($item, $streamParams);
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

		$event = ES::event($item->cluster_id);

		if ($item->verb == 'create') {
			$discussion	= ES::table('Discussion');
			$exists = $discussion->load($item->contextId);

			if (!$exists) {
				return;
			}

			$permalink = $discussion->getPermalink(true, true);
			$description = $this->formatContent($discussion);
			$actorName = $this->formatRestObjectName('user', $item->actor->id);
			$eventName = $this->formatRestObjectName('event', $event->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_CREATE_TITLE_' . $item->getPerspective(), $actorName, $eventName);
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
			$eventName = $this->formatRestObjectName('event', $event->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_REPLY_TITLE_' . $item->getPerspective(), $actorName, $eventName);
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
			$eventName = $this->formatRestObjectName('event', $event->id);

			// Get the reply author
			$replyAuthor = ES::user($reply->created_by);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_ANSWERED_TITLE_' . $item->getPerspective(),
				$actorName,
				$replyName,
				$eventName
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
			$eventName = $this->formatRestObjectName('event', $event->id);

			$title = JText::sprintf('APP_DISCUSSIONS_STREAM_REPLY_TITLE_' . $item->getPerspective(), $actorName, $discussion->title, $eventName);
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
	 * Prepares the stream item for new discussion creation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function prepareCreateStream(&$item, $params)
	{
		$event = $item->getCluster();

		$discussion = ES::table('Discussion');
		$exists = $discussion->load($item->contextId);

		if (!$exists) {
			return;
		}

		$access = $event->getAccess();
		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $discussion->getEditPermalink();;
		}

		// Determines if there are files associated with the discussion
		$files = $discussion->getFiles();
		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($discussion);

		$this->set('item', $item);
		$this->set('cluster', $event);
		$this->set('files', $files);
		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/create.title');
		$item->preview = parent::display('themes:/site/streams/discussions/create.preview');

		// Append the opengraph tags
		$item->addOgDescription();
	}

	/**
	 * Prepares the stream item for new discussion creation.
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function prepareReplyStream(&$item, $params)
	{
		$event = $item->getCluster();

		$reply = ES::table('Discussion');
		$exists = $reply->load($params->get('reply')->id);

		if (!$exists) {
			return;
		}

		$discussion = $reply->getParent();
		$permalink = $discussion->getPermalink();
		$content = $this->formatContent($reply);

		$this->set('item', $item);
		$this->set('cluster', $event);
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
	 * @since   2.0
	 * @access  public
	 */
	private function prepareAnsweredStream(&$item, $params)
	{
		$event = $item->getCluster();

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

		$this->set('item', $item);
		$this->set('cluster', $event);
		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('reply', $reply);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/answered.title');
		$item->preview = parent::display('themes:/site/streams/discussions/answered.preview');

		// We want it to be SOCIAL_STREAM_DISPLAY_MINI but we also want the accepted answer to show as well.
		// Hence we leave the display to full but we disable comments, likes, sharing and repost
		$item->comments = false;
		$item->likes = false;
		$item->sharing = false;
	}

	/**
	 * Prepares the stream item for locked discussions
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function prepareLockedStream(&$item, $params)
	{
		$event = $item->getCluster();

		// Get the discussion item
		$discussion = ES::table('Discussion');
		$exists = $discussion->load($params->get('discussion')->id);

		if (!$exists) {
			return;
		}

		$permalink = $discussion->getPermalink();

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		$this->set('item', $item);
		$this->set('cluster', $event);
		$this->set('permalink', $permalink);
		$this->set('actor', $item->actor);
		$this->set('discussion', $discussion);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/discussions/locked.title');
	}

	/**
	 * Formats a discussion content
	 *
	 * @since   4.0
	 * @access  public
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

	public function appListing($view, $eventId, $type)
	{
		$event = ES::event($eventId);
		$registry = $event->getParams();
		$access = $event->getAccess();

		if (!$access->get('discussions.enabled', true)) {
			return false;
		}

		if (!$registry->get('discussions', true)) {
			return false;
		}

		return true;
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

		// Process comments and likes notification
		if (in_array($item->cmd, array('likes.item', 'likes.involved', 'comments.item', 'comments.involved')) && !in_array($item->context_type, array('discussions.event.create', 'discussions.event.reply'))) {
			return;
		}

		$discussion = ES::table('Discussion');
		$discussion->load($item->context_ids);

		$content->type = 'discussions';
		$content->object = $discussion->toExportData($viewer);
	}
}
