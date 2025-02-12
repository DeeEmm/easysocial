<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppStory extends SocialAppItem
{
	/**
	 * event onLiked on story
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		if (!$likes->type) {
			return;
		}

		// Set the default element.
		$uid = $likes->uid;
		$data = explode('.', $likes->type);
		$element = $data[0];
		$page = $data[1];
		$verb = $data[2];

		if ($element != 'story') {
			return;
		}

		// Get the owner of the post.
		$stream = ES::table('Stream');
		$stream->load($uid);

		// Get the actor
		$actor = ES::user($likes->created_by);

		// If the liker is the stream actor, skip this
		if ($actor->id == $stream->actor_id) {
			return;
		}

		// Assign points to the post owner
		if ($isNew && $actor->id != $stream->actor_id) {
			ES::points()->assign('story.reaction.add.owner', 'com_easysocial', $stream->actor_id);
		}

		// Load the page
		$cluster = $stream->getCluster();

		// [Page Compatibility] - If the liker is the page admin, change the author
		if ($cluster->isAdmin($actor->id)) {
			$actor = $cluster;
		}

		$systemOptions = array(
			'context_type' => $likes->type,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		// Notify the owner first
		if ($actor->id != $stream->actor_id && $stream->post_as != SOCIAL_TYPE_PAGE) {
			ES::notify('likes.item', array($stream->actor_id), false, $systemOptions, $cluster->notification);
		}

		// If this is post as Page, notify all the page admin
		if ($stream->post_as == SOCIAL_TYPE_PAGE) {
			ES::notify('likes.item', $cluster->getAdmins(), false, $systemOptions, $cluster->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $page, $verb, array(), array($stream->actor_id, $likes->created_by));

		if (!$recipients) {
			return;
		}

		ES::notify('likes.involved', $recipients, false, $systemOptions, $cluster->notification);
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
		$allowed = array('story.page.create', 'links.page.create');

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
	 * Triggered before comments notify subscribers
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('story.page.create', 'links.page.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		$segments = explode('.' , $comment->element);
		$element = $segments[0];
		$page = $segments[1];
		$verb = $segments[2];

		// Load up the stream object
		$stream = ES::table('Stream');
		$stream->load($comment->uid);

		// Get the comment actor
		$actor = ES::user($comment->created_by);

		$owner = ES::user($stream->actor_id);

		// Assign points to the post owner
		if ($actor->id != $owner->id) {
			ES::points()->assign('story.comment.add.owner', 'com_easysocial', $owner->id);
		}

		// Get the cluster
		$cluster = $stream->getCluster();

		// [Page Compatibility] - If the liker is the page admin, change the author
		if ($cluster->isAdmin($actor->id)) {
			$actor = $cluster;
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
			'title' => 'APP_PAGE_STORY_EMAILS_COMMENT_ITEM_TITLE_' . strtoupper($stream->post_as),
			'template' => 'apps/page/story/comment.item',
			'comment' => $commentContent,
			'permalink' => $stream->getPermalink(true, true),
			'page' => $cluster->getName(),
			'posterName' => $actor->getName(),
			'posterAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'posterLink' => $actor->getPermalink(true, true)
		);

		$systemOptions = array(
			'content' => $commentContent,
			'context_type' => $comment->element,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		if ($comment->created_by != $stream->actor_id && $stream->post_as != SOCIAL_TYPE_PAGE && !$comment->isChild()) {
			ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions, $cluster->notification);
		}

		if ($stream->post_as == SOCIAL_TYPE_PAGE && !$comment->isChild()) {
			ES::notify('comments.item', $cluster->getAdmins(), $emailOptions, $systemOptions, $cluster->notification);
		}

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $page, $verb, array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title'] = 'APP_PAGE_STORY_EMAILS_COMMENT_ITEM_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/page/story/comment.involved';

		if ($recipients && !$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $cluster->notification);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/page/story/comment.item.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions, $cluster->notification);
		}
	}

	/**
	 * Processes notifications
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// Process notifications when someone likes your post
		// context_type: stream.page.create, links.create
		// type: likes
		if ($item->type == 'likes') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// Process notifications when someone posts a comment on your status update
		// context_type: stream.page.create
		// type: comments
		if ($item->type == 'comments') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// Processes notifications when someone posts a new update in a page
		// context_type: story.page.create, links.page.create
		// type: groups
		if ($item->cmd == 'pages.updates') {
			$hook = $this->getHook('notification', 'updates');
			$hook->execute($item);

			return;
		}
	}

	/**
	 * Determine if the context is allowed for the notifications
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedContext($context)
	{
		$allowed = array(
			'story.page.create',
			'links.create',
			'photos.page.share',
			'links.page.create',
			'file.page.uploaded'
		);

		return in_array($context, $allowed);
	}

	/**
	 * Process notifications for urls
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function processLinksNotifications(&$item)
	{
		// Get the stream id.
		$streamId = $item->uid;

		// We don't want to process notification for likes here.
		if ($item->type == 'likes') {
			return;
		}

		// Get the links that are posted for this stream
		$model = ES::model('Stream');
		$links = $model->getAssets($streamId , SOCIAL_TYPE_LINKS);

		if (!isset($links[0])) {
			return;
		}

		// Initialize default values
		$link = $links[0];
		$actor = ES::user($item->actor_id);
		$meta = ES::registry($link->data);

		if ($item->cmd == 'story.tagged') {
			$item->title = JText::_('APP_PAGE_STORY_POSTED_LINK_TAGGED');
		} else {
			$item->title = JText::sprintf('APP_PAGE_STORY_POSTED_LINK_ON_YOUR_TIMELINE' , $meta->get('link'));
		}
	}

	public function processPhotosNotifications(&$item)
	{
		if ($item->context_ids) {
			// If this is multiple photos, we just show the last one.
			$ids = ES::json()->decode($item->context_ids);
			$id = $ids[ count($ids) - 1 ];

			$photo = ES::table('Photo');
			$photo->load($id);

			$item->image = $photo->getSource();

			$actor = ES::user($item->actor_id);

			$title = JText::sprintf('APP_PAGE_STORY_POSTED_PHOTO_ON_YOUR_TIMELINE' , $actor->getName());

			if (count($ids) > 1) {
				$title = JText::sprintf('APP_PAGE_STORY_POSTED_PHOTO_ON_YOUR_TIMELINE_PLURAL' , $actor->getName(), count($ids));
			}

			$item->title = $title;
		}
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'story') {
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params = ES::registry($item->params);
		$page = ES::page($params->get('page'));

		if (!$page) {
			return;
		}

		$item->cnt = 1;

		if ($page->type != SOCIAL_PAGES_PUBLIC_TYPE) {
			if (!$page->isMember(ES::user()->id)) {
				$item->cnt = 0;
			}
		}

		return true;
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
	 * We need to notify page followers when someone posts a new story in the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterStorySave(SocialStream &$stream , SocialTableStreamItem &$streamItem, SocialStreamTemplate &$template)
	{
		// Determine if this is for a page
		if (!$template->cluster_id) {
			return;
		}

		// Now we only want to allow specific context
		$context = $template->context_type . '.' . $template->verb;
		$allowed = array('story.create', 'links.create', 'photos.share');

		if (!in_array($context, $allowed)) {
			return;
		}

		// Creating scheduled stream.
		if ($stream->scheduled && $streamItem->context_type == 'story') {
			$scheduled = ES::Scheduler();
			$scheduled->create($streamItem->uid, $template);
		}

		// When a user posts a new story in a page, we need to notify the page followers
		$page = ES::page($template->cluster_id);

		// Get the actor
		$actor = ES::user($streamItem->actor_id);

		// Get number of members
		$targets = $page->getTotalMembers();

		// If there's nothing to send skip this altogether.
		if (!$targets) {
			return;
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
				'title' => 'APP_PAGE_STORY_EMAILS_NEW_POST_IN_PAGE',
				'template' => 'apps/page/story/new.post',
				'uid' => $streamItem->uid,
				// sid used by scheduled post.
				'sid' => $streamItem->uid,
				'context_type' => $template->context_type . '.page.' . $template->verb,
				'system_content' => $template->content
				);

		// We'll need to pass the scheduled date for notification processing.
		if (isset($stream->scheduled) && $stream->scheduled) {
			$data['scheduled'] = $stream->scheduled;
		}

		// Only notify followers if the updates is came from Page
		// Notify page admin if the updates is came from follower
		if ($template->post_as == SOCIAL_TYPE_PAGE) {
			$page->notifyMembers('story.updates', $data);
		} else {
			$data['title'] = 'APP_PAGE_STORY_EMAILS_NEW_POST_FOLLOWER_IN_PAGE';
			$data['template'] = 'apps/page/story/new.post.follower';

			$page->notifyPageAdmins('story.updates', $data);
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
		// If this is not it's context, we don't want to do anything here.
		if ($item->context != 'story') {
			return;
		}

		// Get the event object
		$page = $item->getCluster();

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		$access = $page->getAccess();

		// Allow editing of the stream item
		$item->editable = $viewer->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$options = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $page->id);
		$item->likes = ES::likes($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));
		$item->comments = ES::comments($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $options, $item->uid);

		$actor = $item->getPostActor($page);

		if ($actor->getType() == SOCIAL_TYPE_USER) {
			$item->targets = $page;
		}

		$item->show = true;
	}

	/**
	 * Triggered to prepare the stream item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context != 'story') {
			return;
		}

		// Get the event object
		$page = $item->getCluster();

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		$access = $page->getAccess();

		// Allow editing of the stream item
		$item->editable = $this->my->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$actor = $item->getPostActor($page);

		$this->set('page', $page);
		$this->set('actor' , $actor);
		$this->set('stream', $item);

		$item->title = parent::display('themes:/site/streams/story/page/title');

		// Generates options for comment
		$options = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $page->id);

		$item->likes = ES::likes($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));

		$item->comments = ES::comments($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $options, $item->uid);

		// Append the opengraph tags
		$item->addOgDescription($item->content);
	}

	/**
	 * Export Notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$stream = ES::stream();
		$streamItem = $stream->getItem($item->uid);

		if (!$streamItem) {
			$item->exclude = true;
			return;
		}

		$streamItem = $streamItem[0];

		if (!$streamItem) {
			return;
		}

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
