<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppNews extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'news') {
			return;
		}

		// Get the stream object
		$news = ES::table('ClusterNews');
		$news->load($uid);

		if (!$news->id) {
			// item not found.
			return false;
		}

		if (!$news->isViewable()) {
			return false;
		}


		return true;
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

		// We should not display the news on the app if it's disabled
		$page = ES::page($id);
		$access = $page->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return false;
		}

		$registry = $page->getParams();

		if (!$registry->get('news', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Displays notifications from the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{

		// Processes notifications when someone posts a new update in a page
		// context_type: page.news
		// type: pages
		if ($item->cmd == 'page.news') {

			$hook = $this->getHook('notification', 'news');
			$hook->execute($item);

			return;
		}

		if ($item->type == 'likes' && $item->context_type == 'news.page.create') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		if ($item->type == 'comments' && $item->context_type == 'news.page.create') {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}
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
		if ($item->context_type != 'news') {
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

		if ($perspective == 'dashboard' || $perspective == 'profile') {
			$excludeVerb[] = 'create';
		}

		// add pages into exclude list
		if ($excludeVerb !== false) {
			$exclude['news'] = $excludeVerb;
		}
	}

	/**
	 * Processes after someone comments on an announcement
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('news.page.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the stream object
		$news = ES::table('ClusterNews');
		$news->load($comment->uid);

		$segments = explode('.', $comment->element);
		$element = $segments[0];
		$verb = $segments[2];

		// Get the comment actor
		$actor = ES::user($comment->created_by);

		// Load the page
		$page = ES::page($news->cluster_id);

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
			'title' => 'APP_PAGE_NEWS_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/page/news/comment.news.item',
			'comment' => $commentContent,
			'permalink' => $news->getPermalink(true, true),
			'page' => $page->getName(),
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

		// [Page Compatibility] Only notify if the commentator is not a page admin
		if (!$page->isAdmin($comment->created_by) && !$comment->isChild()) {
			 ES::notify('comments.item', $page->getAdmins(), $emailOptions, $systemOptions, $page->notification);
		}

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, SOCIAL_TYPE_PAGE, $verb, array(), array($news->created_by, $comment->created_by));

		$emailOptions['title'] = 'APP_PAGE_NEWS_EMAILS_COMMENT_ITEM_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/page/news/comment.news.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $page->notification);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/page/news/comment.news.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions, $page->notification);
		}
	}

	/**
	 * Processes after someone likes an announcement
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('news.page.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		if ($likes->type == 'news.page.create') {

			// Get the stream object
			$news = ES::table('ClusterNews');
			$news->load($likes->uid);

			// Get the likes actor
			$actor = ES::user($likes->created_by);

			// Load the Page
			$page = ES::page($news->cluster_id);

			$systemOptions = array(
				'context_type' => $likes->type,
				'context_ids' => $news->cluster_id,
				'url' => $news->getPermalink(false, false, false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// [Page Compatibility] Only notify if the liker is not a page admin
			if (!$page->isAdmin($likes->created_by)) {
				ES::notify('likes.item', $page->getAdmins(), false, $systemOptions, $page->notification);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'news', 'page', 'create', array(), array($news->created_by, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions, $page->notification);

			return;
		}
	}

	/**
	 * Prepares the stream item for pages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'news') {
			return;
		}

		// page access checking
		$page = $item->getCluster();

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		// Only page members will be able to see this announcement.
		// It is not necessary to show announcement to other user regardless this page is open or private
		// if (!$page->isMember() && $item->getPerspective() == 'DASHBOARD') {
		if (!$page->isMember() && $item->getPerspective() == 'DASHBOARD') {
			return;
		}

		// Ensure that announcements are enabled for this page
		$registry = $page->getParams();

		if (!$registry->get('news', true)) {
			return;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->repost = false;

		$params = $item->getParams();

		if ($item->verb == 'create') {
			$this->prepareCreateStream($item, $page, $params);
		}
	}

	private function prepareCreateStream(SocialStreamItem &$item, SocialPage $page, $params)
	{
		$news = ES::table('ClusterNews');
		$news->load($params->get('news')->id);

		// Get the permalink
		$permalink = $news->getPermalink();

		// Get the app params
		$appParams 	= $this->getApp()->getParams();

		$truncateLimit = (int) $appParams->get('stream_length', 250);

		// Format the content
		$content = $this->format($news, $truncateLimit);

		// Set an alias for actor
		$item->setActorAlias($page);

		// Attach actions to the stream
		$commentUrl = $news->getPermalink(true, false, false);
		$this->attachActions($item, $news, $commentUrl, $appParams, $page);

		$access = $page->getAccess();
		if ($this->my->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $news->getEditPermalink();;
		}

		// Append the opengraph tags
		$item->addOgDescription();

		$this->set('item', $item);
		$this->set('cluster', $page);
		$this->set('appParams', $appParams);
		$this->set('permalink', $permalink);
		$this->set('news', $news);
		$this->set('actor', $item->actor);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/news/page/create.title');
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

	private function attachActions(&$item, &$news, $permalink, $appParams, $page)
	{
		$commentParams = array('url' => $permalink, 'clusterId' => $page->id);

		// We need to link the comments to the news
		$item->comments = ES::comments($news->id, 'news', 'create', SOCIAL_APPS_GROUP_PAGE, $commentParams);

		// The comments for the stream item should link to the news itself.
		if (!$appParams->get('allow_comments', 1) || !$news->comments) {
			$item->comments = false;
		}

		// The likes needs to be linked to the news itself
		$likes = ES::likes();
		$likes->get($news->id, 'news', 'create', SOCIAL_APPS_GROUP_PAGE, false, array('clusterId' => $page->id));

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
		if ($item->cmd == 'page.news') {
			$id = $item->context_ids;
		}

		// Comments and likes
		if ($item->context_type == 'news.page.create') {
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
