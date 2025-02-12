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

class SocialGroupAppNews extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_NEWS) {
			return;
		}

		$table = ES::table('ClusterNews');
		$table->load($uid);

		if (!$table->id) {
			return false;
		}

		$cluster = ES::group($table->cluster_id);

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
	 * Determines if the app should appear on the sidebar
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function appListing($view, $id, $type)
	{
		if ($type != SOCIAL_TYPE_GROUP) {
			return true;
		}

		// We should not display the news app if it's disabled
		$group = ES::group($id);
		$access = $group->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return false;
		}

		$registry = $group->getParams();

		if (!$registry->get('news', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Displays notifications from the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{

		// Processes notifications when someone posts a new update in a group
		// context_type: group.news
		// type: groups
		if ($item->cmd == 'group.news') {

			$hook   = $this->getHook('notification', 'news');
			$hook->execute($item);

			return;
		}

		if ($item->type == 'likes' && $item->context_type == 'news.group.create') {

			$hook 	= $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		if ($item->type == 'comments' && $item->context_type == 'news.group.create') {

			$hook 	= $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}
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
		if ($item->context_type != SOCIAL_TYPE_NEWS) {
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params 	= ES::registry($item->params);
		$group 		= ES::group($params->get('group'));

		if(!$group)
		{
			return;
		}

		$item->cnt = 1;

		if($group->type != SOCIAL_GROUPS_PUBLIC_TYPE)
		{
			if(!$group->isMember(ES::user()->id))
			{
				$item->cnt = 0;
			}
		}

		return true;
	}

	/**
	 * Processes after someone comments on an announcement
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('news.group.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the stream object
		$news = ES::table('ClusterNews');
		$news->load($comment->uid);

		$segments = explode('.' , $comment->element);
		$element = $segments[0];
		$group = $segments[1];
		$verb = $segments[2];

		// Get the comment actor
		$actor = ES::user($comment->created_by);

		// Get the cluster
		$cluster = ES::group($news->cluster_id);

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
			'title' => 'APP_GROUP_NEWS_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/group/news/comment.news.item',
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
			ES::notify('comments.item', array($news->created_by), $emailOptions, $systemOptions, $cluster->notification);
		}

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($news->created_by, $comment->created_by));

		$emailOptions['title'] = 'APP_GROUP_NEWS_EMAILS_COMMENT_ITEM_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/group/news/comment.news.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $cluster->notification);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/group/news/comment.news.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions, $cluster->notification);
		}
	}

	/**
	 * Processes after someone likes an announcement
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('news.group.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		if ($likes->type == 'news.group.create') {

			$news = ES::table('ClusterNews');
			$news->load($likes->uid);

			$actor = ES::user($likes->created_by);
			$cluster = ES::group($news->cluster_id);

			$systemOptions  = array(
				'context_type' => $likes->type,
				'context_ids' => $news->cluster_id,
				'url' => $news->getPermalink(false, false, false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// Notify the owner first
			if ($news->created_by != $likes->created_by) {
				ES::notify('likes.item', array($news->created_by), false, $systemOptions, $cluster->notification);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'news', 'group', 'create', array(), array($news->created_by, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions, $cluster->notification);

			return;
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
		if ($item->context != SOCIAL_TYPE_NEWS) {
			return;
		}

		// Ensure that announcements are enabled for this group
		$group = ES::group($item->cluster_id);

		$registry = $group->getParams();

		if (!$registry->get('news', true)) {
			return;
		}

		$params = $item->getParams();
		$news = ES::table('ClusterNews');
		$exists = $news->load($params->get('news')->id);

		if (!$exists) {
			return;
		}

		$actor = $item->actor;

		$item->title = '';
		$item->preview = '';
		$item->link = $news->getPermalink(true, true);

		// for now we only process member join feed.
		if ($item->verb == 'create') {
			$item->title = JText::sprintf('COM_ES_APP_NEWS_DIGEST_CREATE_TITLE', $actor->getName(), $news->title);
		}
	}

	/**
	 * Prepares the stream item for groups
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_NEWS) {
			return;
		}

		// group access checking
		$group = ES::group($item->cluster_id);

		if (!$group) {
			return;
		}

		if (!$group->canViewItem()) {
			return;
		}

		// Only group members will be able to see this announcement.
		// It is not necessary to show announcement to other user regardless this group is open or private
		if (!$group->isMember() && $item->getPerspective() == 'DASHBOARD') {
			return;
		}

		// Ensure that announcements are enabled for this group
		$registry = $group->getParams();

		if (!$registry->get('news', true)) {
			return;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->repost = false;

		$params = $item->getParams();

		if ($item->verb == 'create') {
			$this->prepareCreateStream($item, $group, $params);
		}
	}

	private function prepareCreateStream(SocialStreamItem &$item, SocialGroup $group, $params)
	{
		$news = ES::table('ClusterNews');
		$news->load($params->get('news')->id);

		// Get the permalink
		$permalink = $news->getPermalink();
		$commentUrl = $news->getPermalink(true, false, false);

		// Get the app params
		$appParams 	= $this->getApp()->getParams();

		$truncateLimit = (int) $appParams->get('stream_length', 250);

		// Format the content
		$content = $this->format($news, $truncateLimit);

		// Attach actions to the stream
		$this->attachActions($item, $news, $commentUrl, $appParams);

		$access = $group->getAccess();
		if ($this->my->isSiteAdmin() || $group->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $news->getEditPermalink();;
		}

		// Append the opengraph tags
		$item->addOgDescription();

		$this->set('item', $item);
		$this->set('cluster', $group);
		$this->set('appParams', $appParams);
		$this->set('permalink', $permalink);
		$this->set('news', $news);
		$this->set('actor', $item->actor);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/news/group/create.title');
		$item->preview = parent::display('themes:/site/streams/news/preview');
	}

	private function format($news, $length = 0)
	{
		if ($length == 0) {
			return $news->getContent();
		}

		$content = $news->getContent();
		$content = ESJString::substr(strip_tags($content), 0, $length) . ' ' . JText::_('COM_EASYSOCIAL_ELLIPSES');

		return $content;
	}

	private function attachActions(&$item , &$news , $permalink , $appParams)
	{
		// We need to link the comments to the news
		$item->comments = ES::comments($news->id , 'news' , 'create', SOCIAL_APPS_GROUP_GROUP , array('url' => $permalink, 'clusterId' => $item->cluster_id));

		// The comments for the stream item should link to the news itself.
		if (!$appParams->get('allow_comments', 1) || !$news->comments) {
			$item->comments = false;
		}

		// The likes needs to be linked to the news itself
		$likes = ES::likes();
		$likes->get($news->id, 'news', 'create', SOCIAL_APPS_GROUP_GROUP, false, array('clusterId' => $item->cluster_id));

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
		if ($item->cmd == 'group.news') {
			$id = $item->context_ids;
		}

		// Comments and likes
		if ($item->context_type == 'news.group.create') {
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
