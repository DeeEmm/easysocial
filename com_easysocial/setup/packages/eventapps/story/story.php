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

class SocialEventAppStory extends SocialAppItem
{
	/**
	 * Handles onAfterLikeSave event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		if (!$likes->type) {
			return;
		}

		$data = explode('.', $likes->type);

		$element = array_shift($data);

		if ($element != 'story') {
			return;
		}

		$event = array_shift($data);
		$verb = array_shift($data);

		$uid = $likes->uid;

		$stream = ES::table('Stream');
		$stream->load($uid);

		$actor = ES::user($likes->created_by);

		// If the liker is the stream actor, skip this
		if ($actor->id == $stream->actor_id) {
			return;
		}

		// Assign points to the post owner
		if ($isNew && $actor->id != $stream->actor_id) {
			ES::points()->assign('story.reaction.add.owner', 'com_easysocial', $stream->actor_id);
		}

		$systemOptions = array(
			'context_type' => $likes->type,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		// Notify the owner first
		ES::notify('likes.item', array($stream->actor_id), false, $systemOptions);

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the item and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $event, $verb, array(), array($stream->actor_id, $likes->created_by));

		// Notify other participating users
		ES::notify('likes.involved', $recipients, false, $systemOptions);
	}

	/**
	 * Triggered when reactions are withdrawn
	 *
	 * @since	4.0.8
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
	 * Triggered after a comment is deleted
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function onAfterDeleteComment(&$comment)
	{
		$allowed = array('story.event.create', 'links.event.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

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
	}


	/**
	 * Handles onAfterCommentSave event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('story.event.create', 'links.event.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		list($element, $event, $verb) = explode('.', $comment->element);

		$stream = ES::table('Stream');
		$stream->load($comment->uid);

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

		$emailOptions = array(
			'title' => 'APP_EVENT_STORY_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/event/story/comment.item',
			'comment' => $commentContent,
			'permalink' => $stream->getPermalink(true, true),
			'posterName' => $actor->getName(),
			'posterAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'posterLink' => $actor->getPermalink(true, true)
		);

		$systemOptions  = array(
			'content' => $commentContent,
			'context_type' => $comment->element,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		if ($actor->id != $stream->actor_id && !$comment->isChild()) {
			// Notify the note owner
			ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the item and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $event, $verb, array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title'] = 'APP_EVENT_STORY_EMAILS_COMMENT_ITEM_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/event/story/comment.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/event/story/comment.item.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Processes notifications.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('story.event.create', 'links.create', 'photos.event.share');
		if ($item->type == 'likes' && in_array($item->context_type, $allowed)) {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		$allowed = array('story.event.create', 'links.event.create', 'photos.event.share');
		if ($item->type == 'comments' && in_array($item->context_type, $allowed)) {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		$allowed = array('story.event.create', 'links.event.create', 'photos.event.share', 'file.event.uploaded');

		if ($item->cmd == 'events.updates' && (in_array($item->context_type, $allowed))) {
			$hook = $this->getHook('notification', 'updates');
			$hook->execute($item);

			return;
		}
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
	 * Handles onAfterStorySave trigger.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onAfterStorySave(SocialStream &$stream, SocialTableStreamItem &$streamItem, SocialStreamTemplate &$template)
	{
		// Determine if this is for a event
		if (!$template->cluster_id) {
			return;
		}

		// Now we only want to allow specific context
		$context = $template->context_type . '.' . $template->verb;
		$allowed = array('story.create', 'links.create', 'photos.share');

		if (!in_array($context, $allowed)) {
			return;
		}

		$event = ES::event($template->cluster_id);

		$actor = ES::user($streamItem->actor_id);

		// Get number of event members
		$targets = $event->getTotalMembers();

		// If there's nothing to send skip this altogether.
		if (!$targets) {
			return;
		}

		// Creating scheduled stream.
		if ($stream->scheduled && $streamItem->context_type == 'story') {
			$scheduled = ES::Scheduler();
			$scheduled->create($streamItem->uid, $template);
		}

		// Get the item's permalink
		$permalink = ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true);

		$contents = $template->content;

		// break the text and images
		if (strpos($template->content, '<img') !== false) {
			preg_match('#(<img.*?>)#', $template->content, $results);

			$img = "";
			if ($results) {
				$img = $results[0];
			}

			$segments = explode('<img', $template->content);
			$contents = $segments[0];

			if ($img) {
				$contents = $contents . '<br /><div style="text-align:center;">' . $img . "</div>";
			}
		}

		// Get the links data if the stream type is link
		if ($context === 'links.create') {

			$model = ES::model('Stream');
			$links = $model->getAssets($streamItem->uid, SOCIAL_TYPE_LINKS);

			if (isset($links[0])) {

				// Initialize default values
				$link = $links[0];

				$meta = ES::registry($link->data);
				$metaLink = $meta->get('link');

				$contents = $contents . '<br />' . $metaLink;
			}
		}

		$parseBBCodeOptions = array('escape' => false, 'links' => true, 'code' => true);
		$contents = ES::string()->normalizeContent($contents, $parseBBCodeOptions);

		$data = array(
				'userId' => $actor->id,
				'content' => $contents,
				'permalink' => ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true),
				'title' => 'APP_EVENT_STORY_EMAILS_NEW_POST_IN_EVENT',
				'template' => 'apps/event/story/new.post',
				'uid' => $streamItem->uid,
				// sid used by scheduled post.
				'sid' => $streamItem->uid,
				'context_type' => $template->context_type . '.event.' . $template->verb,
				'system_content' => $template->content
			);

		// We'll need to pass the scheduled date for notification processing.
		if (isset($stream->scheduled) && $stream->scheduled) {
			$data['scheduled'] = $stream->scheduled;
		}

		$event->notifyMembers('story.updates', $data);
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context != 'story') {
			return;
		}

		// Get the event object
		$event = $item->getCluster();

		if (!$event) {
			return;
		}

		if (!$event->canViewItem($viewer->id)) {
			return;
		}

		$access = $event->getAccess();

		// Allow editing of the stream item
		$item->editable = $viewer->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id);

		$item->show = true;
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->targets = $event;
		$item->likes = ES::likes()->get($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, $item->uid, array('clusterId' => $item->cluster_id));

		// If this update is posted in a event, the comments should be linked to the event item
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $item->cluster_id), $item->uid);

		return true;
	}

	/**
	 * Trigger to prepare a stream object.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onPrepareStream(SocialStreamItem &$item)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context != 'story') {
			return;
		}

		// Get the event object
		$event = $item->getCluster();

		if (!$event) {
			return;
		}

		if (!$event->canViewItem()) {
			return;
		}

		$access = $event->getAccess();

		// Allow editing of the stream item
		$item->editable = $this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id);

		// Get the actor
		$actor = $item->getActor();

		// Decorate the stream
		$item->fonticon = 'fa fa-pencil-alt';
		$item->color = '#6E9545';
		$item->label = ES::_('APP_EVENT_STORY_STREAM_TOOLTIP', true);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$this->set('event', $event);
		$this->set('actor', $actor);
		$this->set('stream', $item);

		$item->title = parent::display('streams/title.' . $item->verb);

		// Apply likes on the stream
		$likes = ES::likes();
		$likes->get($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, $item->uid, array('clusterId' => $item->cluster_id));

		$item->likes = $likes;

		// If this update is posted in a event, the comments should be linked to the event item
		$comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $item->cluster_id), $item->uid);
		$item->comments = $comments;

		// Append the opengraph tags
		$item->addOgDescription($item->content);

		return true;
	}

	/**
	 * Export the notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		$allowedContext = array(
			'story.event.create',
			'photos.event.share',
			'links.create',
			'links.event.create',
			'file.event.uploaded'
		);

		if (!in_array($item->context_type, $allowedContext)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->uid;
		$target->type = 'stream';
		$target->endpoint = 'stream.item';
		$target->query_string = 'stream.item&id=' . $target->id;

		$item->target = $target;

		return;
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
