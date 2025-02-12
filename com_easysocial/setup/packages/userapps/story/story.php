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

class SocialUserAppStory extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'story') {
			return;
		}

		// the only place that user can submit coments / react on this app is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}


	/**
	 * When someone likes the status update
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		if (!$likes->type) {
			return;
		}

		// Set the default element.
		$element = $likes->type;
		$verb = '';
		$uid = $likes->uid;

		if (strpos($element, '.') !== false) {
			$data = explode('.', $element);
			$group = $data[1];
			$element = $data[0];
			$verb = isset($data[2]) ? $data[2] : '';
		}

		// When a user likes a comment
		if ($element == 'comments') {

			// For this, we only have 1 recipient since the comment itself was posted by a user
			$comment = ES::table('Comments');
			$comment->load($uid);

			// Get the actor that likes the comment
			$actor = ES::user($likes->created_by);

			// Get the comment url
			$permalink = $comment->getPermalink();

			$systemOptions  = array(
									'context_type' => $likes->type,
									'url' => $permalink,
									'actor_id' => $likes->created_by,
									'uid' => $likes->uid,
									'aggregate' => true
								);


			// Notify the owner of the comment first
			if ($comment->created_by != $likes->created_by) {
				ES::notify('comments.like', array($comment->created_by), false, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'comments', 'user', 'like', array(), array($comment->created_by, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

		// When a user likes a story
		if ($element == 'story') {

			// Since the uid is tied to the album we can get the album object
			$stream = ES::table('Stream');
			$stream->load($likes->uid);

			// Get the actor of the likes
			$actor = ES::user($likes->created_by);

			// Assign points to the post owner
			if ($isNew && $actor->id != $stream->actor_id) {
				ES::points()->assign('story.reaction.add.owner', 'com_easysocial', $stream->actor_id);
			}


			$systemOptions  = array(
				'context_type' => $likes->type,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// Notify the owner of the photo first
			if ($likes->created_by != $stream->actor_id) {
				ES::notify('likes.item', array($stream->actor_id), false, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'user', $verb, array(), array($stream->actor_id, $likes->created_by));

			// Notify other participating users
			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}
	}

	/**
	 * Triggered when reactions are withdrawn
	 *
	 * @since	4.0.2
	 * @access	public
	 */
	public function onAfterLikeDelete(SocialTableLikes &$likes)
	{
		if (!$likes->type) {
			return;
		}

		// Set the default element.
		$element = $likes->type;
		$verb = '';
		$uid = $likes->uid;

		if (strpos($element, '.') !== false) {
			$data = explode('.', $element);
			$group = $data[1];
			$element = $data[0];
			$verb = isset($data[2]) ? $data[2] : '';
		}

		// When user withdraws a reaction from the story
		if ($element == 'story') {

			// Since the uid is tied to the album we can get the album object
			$stream = ES::table('Stream');
			$stream->load($likes->uid);

			// Get the actor of the likes
			$actor = ES::user($likes->created_by);

			// Assign points to the post owner
			if ($actor->id != $stream->actor_id) {
				ES::points()->assign('story.reaction.remove.owner', 'com_easysocial', $stream->actor_id);
			}
		}
	}

	/**
	 * Triggered before comments notify subscribers
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = ['story.user.create'];

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// For likes on albums when user uploads multiple photos within an album
		if ($comment->element == 'story.user.create') {

			// Since the uid is tied to the album we can get the album object
			$stream = ES::table('Stream');
			$stream->load($comment->uid);

			// Get the actor of the likes
			$actor = ES::user($comment->created_by);

			$owner = ES::user($stream->actor_id);

			// Assign points to the post owner
			if ($actor->id != $owner->id) {
				ES::points()->assign('story.comment.add.owner', 'com_easysocial', $owner->id);
			}

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

			// Set the email options
			$emailOptions = array(
				'title' => 'APP_USER_STORY_EMAILS_COMMENT_STATUS_ITEM_SUBJECT',
				'template' => 'apps/user/story/comment.status.item',
				'permalink' => $stream->getPermalink(true, true),
				'comment' => $commentContent,
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true),
				'target' => $owner->getName(),
				'targetLink' => $owner->getPermalink(true, true)
			);

			$systemOptions  = array(
				'context_type' => $comment->element,
				'context_ids' => $comment->id,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $comment->created_by,
				'uid' => $comment->uid,
				'aggregate' => true,
				'content' => $commentContent
			);

			// Notify the owner of the photo first
			if ($stream->actor_id != $comment->created_by && !$comment->isChild()) {
				ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($comment->uid, 'story', 'user', 'create', array(), array($stream->actor_id, $comment->created_by));

			$emailOptions['title'] = 'APP_USER_STORY_EMAILS_COMMENT_STATUS_INVOLVED_SUBJECT';
			$emailOptions['template'] = 'apps/user/story/comment.status.involved';

			if (!$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/user/story/comment.status.replied';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}

			return;
		}
	}

	/**
	 * Triggered after a comment is deleted
	 *
	 * @since	4.0.2
	 * @access	public
	 */
	public function onAfterDeleteComment(&$comment)
	{
		$allowed = ['story.user.create'];

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// For likes on albums when user uploads multiple photos within an album
		if ($comment->element == 'story.user.create') {
			// Since the uid is tied to the album we can get the album object
			$stream = ES::table('Stream');
			$stream->load($comment->uid);

			// Get the actor of the likes
			$actor = ES::user($comment->created_by);

			$owner = ES::user($stream->actor_id);

			// Assign points to the post owner
			if ($actor->id != $owner->id) {
				ES::points()->assign('story.comment.remove.owner', 'com_easysocial', $owner->id);
			}
			return;
		}
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		if ($comment->element != 'story.user.create') {
			return;
		}

		// Here we know that comments associated with story is always
		// comment.id = notification.context_id
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithContextId($comment->id, $comment->element);
	}

	/**
	 * Renders the notification item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		// Action without context type
		if ($item->type == 'comments' && $item->cmd == 'comments.like') {
			$item->context_type = 'comments.user.like';

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When a user is mentioned using @
		if ($item->cmd == 'story.tagged') {

			$hook = $this->getHook('notification', 'mentions');
			$hook->execute($item);

			return;
		}



		// When a user is tagged using the "with" method
		if ($item->cmd == 'stream.tagged') {

			$hook = $this->getHook('notification', 'tagged');
			$hook->execute($item);

			return;
		}

		// From here we will proceed with the context type checking
		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// When someone likes on a status update
		if ($item->type == 'likes' && ($item->cmd == 'likes.item' || $item->cmd == 'likes.involved')) {

			// @legacy
			$item->context_type = 'story.user.create';

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When someone comments on a status update
		if ($item->type == 'comments' && ($item->cmd == 'comments.item' || $item->cmd == 'comments.replied' || $item->cmd == 'comments.involved')) {

			// @legacy
			$item->context_type = 'story.user.create';

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// When someone likes a comment
		if ($item->type == 'likes' && ($item->cmd == 'likes.item' || $item->cmd == 'likes.involved')) {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When user posts on another person's timeline
		if ($item->cmd == 'story.story') {

			$hook = $this->getHook('notification', 'story');
			$hook->execute($item);

			return;
		}

		if ($item->cmd == 'comments.tagged') {

			$hook = $this->getHook('notification', 'tagged');
			$hook->execute($item);

			return;
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
		$allowed = array(
			'likes.item',
			'likes.involved',
			'comments.item',
			'comments.replied',
			'comments.involved',
			'comments.like',
			'story.tagged',
			'stream.tagged',
			'story.story',
			'comments.tagged'
		);

		return in_array($cmd, $allowed);
	}

	/**
	 * Determine if the context is allowed for the notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedContext($context)
	{
		$allowed = array(
			'story.user.create',
			'story',
			'comments.user.like',
			'post.user.timeline',
			'comments.user.tagged',
			'tagged'
		);

		return in_array($context, $allowed);
	}

	/**
	 * Prepares the activity log for user's actions
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != 'story') {
			return;
		}

		// Stories wouldn't be aggregated
		$actor = $stream->actor;
		$target = count($stream->targets) > 0 ? $stream->targets[0] : '';

		$stream->display = SOCIAL_STREAM_DISPLAY_MINI;

		// @triggers: onPrepareStoryContent
		// Processes any apps to process the content.
		ES::apps()->load(SOCIAL_TYPE_USER);

		$args = array(&$story , &$stream);
		$dispatcher = ES::dispatcher();

		$result = $dispatcher->trigger(SOCIAL_TYPE_USER , 'onPrepareStoryContent' , $args);

		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('stream', $stream);
		$this->set('result', $result);

		$stream->title = parent::display('logs/title.' . $stream->verb);
		$stream->content = parent::display('logs/content.' . $stream->verb);

		if ($includePrivacy) {
			$privacy = $this->my->getPrivacy();
			$stream->privacy = $privacy->form($stream->uid, SOCIAL_TYPE_STORY, $stream->actor->id, 'story.view', false, $stream->aggregatedItems[0]->uid, array(), array('iconOnly' => true));
		}

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	jos_social_stream, boolean
	 * @return  0 or 1
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'story') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			if (!$privacy->validate('story.view', $item->id , SOCIAL_TYPE_STORY , $item->actor_id)) {
				$item->cnt = 0;
			}
		}

		return true;
	}


	/**
	 * Triggered to prepare the stream item
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($stream->context != 'story') {
			return;
		}

		// Get the privacy object
		$privacy = $this->my->getPrivacy();

		// Allow editing of the stream item
		$stream->editable = $this->my->isSiteAdmin() || $stream->actor->id == $this->my->id;

		// we stil need to check for the privacy because the context might come from the 'repost'
		if ($includePrivacy && !$privacy->validate('story.view', $stream->uid, SOCIAL_TYPE_STORY, $stream->actor->id)) {
			return;
		}

		// Actor of this stream
		$actor = $stream->actor;
		$target = count($stream->targets) > 0 ? $stream->targets[0] : '';

		// Get stream params
		$streamParams = $stream->getParams();
		$articleStream = false;
		$articleStreamLink = '';
		$articleStreamTitle = '';

		if ($streamParams->get('articlestream', false)) {
			$articleStream = true;
			$articleStreamLink = $streamParams->get('permalink', '');
			$articleStreamTitle = $streamParams->get('title', '');
		}

		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Apply actions on the stream
		$stream->likes = ES::likes($stream->uid, $stream->context, $stream->verb, SOCIAL_APPS_GROUP_USER, $stream->uid);
		$stream->comments = ES::comments($stream->uid, $stream->context, $stream->verb, SOCIAL_APPS_GROUP_USER, array('url' => $stream->getPermalink(false, false, false)), $stream->uid);
		$stream->repost = ES::repost($stream->uid , SOCIAL_TYPE_STREAM, SOCIAL_APPS_GROUP_USER);

		// Get application params
		$params = $this->getParams();

		$this->set('articleStreamTitle', $articleStreamTitle);
		$this->set('articleStreamLink', $articleStreamLink);
		$this->set('articleStream', $articleStream);
		$this->set('params', $params);
		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('stream', $stream);

		$stream->title = parent::display('themes:/site/streams/story/user/title');
		$stream->addOgDescription($stream->content);

		if ($includePrivacy) {
			$stream->privacy = $privacy->form($stream->uid, SOCIAL_TYPE_STORY, $stream->actor->id, 'story.view', false, $stream->uid, array(), array('iconOnly' => true));
		}

		return true;
	}

	/**
	 * Specialize method to retrieve the stream for REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context != 'story') {
			return;
		}

		// Get the privacy object
		$privacy = $viewer->getPrivacy();

		// Allow editing of the stream item
		$item->editable = $viewer->isSiteAdmin() || $item->actor->id == $viewer->id;

		// we stil need to check for the privacy because the context might come from the 'repost'
		if ($includePrivacy && !$privacy->validate('story.view', $item->uid, SOCIAL_TYPE_STORY, $item->actor->id)) {
			return;
		}

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, array('url' => $item->getPermalink(false, false, false)), $item->uid);
		$item->repost = ES::repost($item->uid , SOCIAL_TYPE_STREAM, SOCIAL_APPS_GROUP_USER);

		// Get application params
		$params = $this->getParams();

		if ($includePrivacy) {
			$item->privacy = $privacy->form($item->uid, SOCIAL_TYPE_STORY, $item->actor->id, 'story.view', false, $item->uid, array(), array('iconOnly' => true));
		}

		$item->show = true;
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		// Further processing start here
		if ($item->cmd == 'likes.item' || $item->cmd == 'likes.involved' || $item->cmd == 'comments.item' || $item->cmd == 'comments.involved') {
			$target->id = $item->uid;
			$target->type = 'stream';
		}

		if ($item->cmd == 'story.tagged' || $item->cmd == 'stream.tagged') {
			$target->id = $item->uid;
			$target->type = 'stream';
		}

		if ($item->cmd == 'comments.like' || $item->cmd == 'comments.tagged') {
			$commentId = $item->cmd == 'comments.like' ? $item->uid : $item->context_ids;

			// Get the stream id for this comment
			$comment = ES::table('Comments');
			$comment->load($commentId);

			$target->id = $comment->stream_id;
			$target->type = 'stream';

			// Target the redirection to the stream
			// and then navigate to the comment
			$target->nav = 'comments.item';
			$target->nav_id = $commentId;
		}

		// When user posts on another person's timeline
		if ($item->cmd == 'story.story') {
			$table = ES::table('StreamItem');
			$table->load($item->uid);

			$stream = ES::table('Stream');
			$stream->load($table->uid);

			$target->id = $stream->id;
			$target->type = 'stream';
		}

		$target->endpoint = $target->type . '.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

		$item->target = $target;
	}

	/**
	 * Modifying the story template to change the state.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onBeforeStorySave(SocialStreamTemplate &$template, SocialStream &$stream, $content)
	{
		// Update the stream template to scheduled.
		// This will cater for all apps. So no need to add for each apps.
		if ($stream->scheduled) {
			$template->setState(SOCIAL_STREAM_STATE_SCHEDULED);
			$template->setScheduled($stream->scheduled);
		}
	}

	/**
	 * Creating scheduled stream after story saved.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		// Creating scheduled stream.
		if ($stream->scheduled && $streamItem->context_type == 'story') {
			$scheduled = ES::Scheduler();
			$scheduled->create($streamItem->uid, $template);
		}
	}

	/**
	 * Publishing the scheduled stream.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onPublishScheduledAppStory(SocialTableStream &$stream, SocialTableStreamItem &$streamItem, SocialTableStreamScheduled &$scheduled)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($streamItem->context_type != 'story') {
			return;
		}

		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}
}
