<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialEventAppNews extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'news') {
			return;
		}

		// Load the news
		$table = ES::table('ClusterNews');
		$table->load($uid);

		// No news, no view
		if (!$table->id) {
			return false;
		}

		// Load the cluster
		$cluster = ES::event($table->cluster_id);

		// If it is a public cluster, it should allow this
		if ($cluster->isOpen()) {
			return true;
		}

		if ($cluster->isAdmin()) {
			return true;
		}

		// Here we assume that the cluster is not open and we need to ensure that the viewer is a member of the cluster
		if ($cluster->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the app should appear on the sidebar
	 *
	 * @since   2.0.14
	 * @access  public
	 */
	public function appListing($view, $id, $type)
	{
		if ($type != SOCIAL_TYPE_GROUP) {
			return true;
		}

		// We should not display the news app if it's disabled
		$event = ES::event($id);
		$access = $event->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return false;
		}

		$registry = $event->getParams();

		if (!$registry->get('news', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Displays notifications from the event
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{

		// Processes notifications when someone posts a new update in a event
		// context_type: event.news
		// type: events
		if ($item->cmd == 'events.news') {
			$hook = $this->getHook('notification', 'news');
			$hook->execute($item);
			return;
		}

		if ($item->type == 'likes' && $item->context_type == 'news.event.create') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		if ($item->type == 'comments' && $item->context_type == 'news.event.create') {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}
	}

	/**
	 * Processes after someone comments on an announcement
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('news.event.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		if ($comment->element == 'news.event.create') {

			// Get the stream object
			$news = ES::table('ClusterNews');
			$news->load($comment->uid);

			list($element, $group, $verb) = explode('.', $comment->element);

			// Get the comment actor
			$actor = ES::user($comment->created_by);

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
				'title' => 'APP_EVENT_NEWS_EMAILS_COMMENT_ITEM_TITLE',
				'template' => 'apps/event/news/comment.news.item',
				'comment' => $commentContent,
				'permalink' => $news->getPermalink(true, true),
				'actorName' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true)
			);

			$systemOptions = array(
				'content' => $commentContent,
				'context_type' => $comment->element,
				'context_ids' => $news->cluster_id,
				'url' => $news->getPermalink(false, false, false),
				'actor_id' => $comment->created_by,
				'uid' => $comment->id,
				'aggregate' => true
			);


			// Notify the note owner
			if ($comment->created_by != $news->created_by && !$comment->isChild()) {
				ES::notify('comments.item', array($news->created_by), $emailOptions, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item.
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($news->created_by, $comment->created_by));

			$emailOptions['title'] = 'APP_EVENT_NEWS_EMAILS_COMMENT_ITEM_INVOLVED_TITLE';
			$emailOptions['template'] = 'apps/event/news/comment.news.involved';

			if (!$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/event/news/comment.news.replied';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}

			return;
		}
	}

	/**
	 * Processes after someone likes an announcement
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('news.event.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		if ($likes->type == 'news.event.create') {

			// Get the stream object
			$news = ES::table('ClusterNews');
			$news->load($likes->uid);

			// Get the likes actor
			$actor = ES::user($likes->created_by);

			$systemOptions = array(
				'context_type' => $likes->type,
				'context_ids' => $news->cluster_id,
				'url' => $news->getPermalink(false, false, false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// Notify the owner first
			if ($news->created_by != $likes->created_by) {
				ES::notify('likes.item', array($news->created_by), false, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'news', 'event', 'create', array(), array($news->created_by, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}
	}

	/**
	 * Prepares the stream item for events
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'news') {
			return;
		}

		// event access checking
		$event = ES::event($item->cluster_id);

		if (!$event) {
			return;
		}

		if (!$event->canViewItem()) {
			return;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->repost = false;

		if ($item->verb == 'create') {
			$this->prepareCreateStream($item, $event, $item->getParams());
		}
	}

	private function prepareCreateStream(SocialStreamItem &$item, $event, $params)
	{
		// Load the news data
		$news = ES::table('ClusterNews');
		$news->load($params->get('news')->id);

		// Get the permalink
		$permalink = $news->getPermalink();

		// Get the app params
		$appParams = $this->getApp()->getParams();

		$truncateLimit = (int) $appParams->get('stream_length', 250);

		// Format the content
		$content = $this->format($news, $truncateLimit);

		// Attach actions to the stream
		$commentUrl = $news->getPermalink(true, false, false);
		$this->attachActions($item, $news, $commentUrl, $appParams);

		$access = $event->getAccess();
		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $news->getEditPermalink();;
		}

		// Append the opengraph tags
		$item->addOgDescription();

		$this->set('item', $item);
		$this->set('cluster', $event);
		$this->set('appParams', $appParams);
		$this->set('permalink', $permalink);
		$this->set('news', $news);
		$this->set('actor', $item->actor);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/news/event/create.title');
		$item->preview = parent::display('themes:/site/streams/news/preview');
	}

	private function format($news, $length = 0 )
	{
		if ($length == 0) {
			return $news->getContent();
		}

		$content = $news->getContent();
		$content = ESJString::substr(strip_tags($content), 0, $length) . ' ' . JText::_('COM_EASYSOCIAL_ELLIPSES');

		return $content;
	}

	private function attachActions(&$item, &$news, $permalink, $appParams)
	{
		// We need to link the commentsss to the news
		$item->comments = ES::comments($news->id, 'news', 'create', SOCIAL_APPS_GROUP_EVENT, array('url' => $permalink, 'clusterId' => $item->cluster_id));

		// The comments for the stream item should link to the news itself.
		if (!$appParams->get('allow_comments', 1) || !$news->comments) {
			$item->comments = false;
		}

		// The likes needs to be linked to the news itself
		$likes = ES::likes();
		$likes->get($news->id, 'news', 'create', SOCIAL_APPS_GROUP_EVENT, false, array('clusterId' => $item->cluster_id));

		$item->likes = $likes;
	}

	/**
	 * Process Notification Export
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onNotificationExport(&$item, &$content, $viewer)
	{
		$id = false;

		// Stream update
		if ($item->cmd == 'events.news') {
			$id = $item->context_ids;
		}

		// Comments and likes
		if ($item->context_type == 'news.event.create') {
			$id = $item->uid;
		}

		if (!$id) {
			return;
		}

		// Set the news item
		$news = ES::table('ClusterNews');
		$news->load($id);

		$content->type = 'news';
		$content->object = $news->toExportData($viewer);
	}
}
