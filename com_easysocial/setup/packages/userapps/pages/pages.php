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

ES::import('admin:/includes/page/page');

class SocialUserAppPages extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'pages') {
			return;
		}

		// user pages apps should not even reach here.
		// just return false
		return false;
	}

	/**
	 * Notification triggered when generating notification item.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('page.liked', 'page.invited', 'page.requested', 'page.approved');
		$contexts = array('pages.user.create');
		$allowed = array_merge($allowed, $contexts);

		// If the cmd not allowed, return.
		if (!in_array($item->cmd, $allowed) && !in_array($item->context_type, $allowed)) {
			return;
		}

		$user = ES::user($item->actor_id);
		$page = ES::page($item->uid);
		$item->image = $page->getAvatar();

		if ($item->cmd == 'page.invited') {
			$item->title = JText::sprintf('APP_USER_PAGES_NOTIFICATIONS_USER_INVITED_YOU_TO_LIKE_PAGE', $user->getName(), $page->getName());
		}

		if ($item->cmd == 'page.liked') {
			$item->title = JText::sprintf('APP_USER_PAGES_NOTIFICATIONS_USER_LIKED_THE_PAGE', $user->getName(), $page->getName());
		}

		if ($item->cmd == 'page.requested') {
			$item->title = JText::sprintf('APP_USER_PAGES_NOTIFICATIONS_USER_ASKED_TO_LIKE_PAGE', $user->getName(), $page->getName());
		}

		if ($item->cmd == 'page.approved') {

			// [Page] - Only page admin is allow to approve the request
			$item->setActorAlias($page);

			$item->title = JText::sprintf('APP_USER_PAGES_NOTIFICATIONS_USER_APPROVED_TO_LIKE_PAGE', $page->getName());
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
		$allowed = array('story.page.create', 'links.page.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Here we know that comments associated with story is always
		// comment.id = notification.context_id
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithUid($comment->uid, $comment->element);
	}

	/**
	 * Prepares the page activity log
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onBeforeGetStream(array &$options, $view = '')
	{
		if ($view != 'dashboard') {
			return;
		}

		// //$allowedContext = array('pages','story','photos', 'tasks', 'discussions');
		// $allowedContext = array('pages','story','photos');

		// if (is_array($options['context']) && in_array('pages', $options['context'])){
		// 	// we need to make sure the stream return only cluster stream.
		// 	$options['clusterType'] = SOCIAL_TYPE_PAGE;
		// } else if ($options['context'] === 'pages') {
		// 	$options['context'] = $allowedContext;
		// 	$options['clusterType'] = SOCIAL_TYPE_PAGE;
		// }
	}

	/**
	 * Prepares the page activity log
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'pages') {
			return;
		}

		// Load the page
		$pageId = $item->contextId;
		$page = ES::page($pageId);

		if (!$page) {
			return;
		}

		$this->set('page', $page);
		$this->set('actor', $item->actor);

		$item->title = parent::display('logs/' . $item->verb . '.title');

		return true;
	}

	/**
	 * event after the user like a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('pages.user.create', 'pages.user.like', 'pages.user.makeadmin', 'pages.user.update');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		$stream = ES::table('Stream');
		$stream->load($likes->uid);

		// Get a list of recipients from the stream
		$recipients = $this->getStreamNotificationTargets($likes->uid, 'userprofile', 'user', 'update', array($stream->actor_id), array($likes->created_by));

		// Prepare the command
		$command = 'likes.item';

		$systemOptions = array(
			'title' => '',
			'context_type' => $likes->type,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid
		);

		ES::notify($command, $recipients, false, $systemOptions);
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since	2.0
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params	= $this->getParams();

		$excludeVerb = [];

		if (!$params->get('stream_like', true)) {
			$excludeVerb[] = 'like';
		}

		if (!$params->get('stream_create', true)) {
			$excludeVerb[] = 'created';
		}

		// when view in dashboard or user profile, we always exclude below verbs as
		// these verbs should appear only in pages page.
		$excludeVerb[] = 'update';
		$excludeVerb[] = 'makeAdmin';

		// only clusters app for user will return as object!
		$obj = new stdClass();
		$obj->group = SOCIAL_TYPE_PAGE;
		$obj->excludeVerb = $excludeVerb;

		// add pages into exclude list.
		$exclude['pages'] = $obj;
	}


	/**
	 * Trigger for onPrepareStream
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		// We only want to process related items
		if ($item->cluster_type !== SOCIAL_TYPE_PAGE || empty($item->cluster_id)) {
			return;
		}

		if ($item->context !== 'pages') {
			return;
		}

		// Get the page object
		$page = ES::page($item->cluster_id);

		// If we can't find the page, skip this.
		if (!$page) {
			return;
		}

		// Determines if the user can view this page item.
		if (!$page->canViewItem()) {
			return;
		}

		// Get the app params so that we determine which stream should be appearing
		$app = $this->getApp();
		$params	= $app->getParams();

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Prepare the likes
		$likes = ES::likes($item->uid, 'pages', $item->verb, SOCIAL_TYPE_USER, $item->uid);
		$item->likes = $likes;

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		// Display stream item for new user like
		if ($item->verb == 'like' && $params->get('stream_like', true)) {
			$this->prepareLikeStream($item, $page);
		}

		// Display stream item for new page creation
		if ($item->verb == 'create' && $params->get('stream_create', true)) {
			$this->prepareCreateStream($item, $page);
		}

		// Hide these items if the user is not a member of the page.
		if (!$page->isMember()) {
			$item->commentLink = false;
			$item->repost = false;
			$item->commentForm = false;
		}

		// Only show Social sharing in public page
		if ($page->type != SOCIAL_PAGES_PUBLIC_TYPE) {
			$item->sharing = false;
		}
	}

	/**
	 * Prepare Page discussion stream
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function preparePageDiscussion(SocialStreamItem &$item, $page, $includePrivacy)
	{
		$app = ES::table('app');
		$app->loadByElement('discussions', SOCIAL_APPS_GROUP_PAGE, 'apps');

		$params = $app->getParams();

		if ($params->get('stream_' . $item->verb, true) == false) {
			return;
		}

		if ($item->verb == 'create') {
			$this->prepareCreateDiscussionStream($item, $page, $includePrivacy, $app);
		}

		if ($item->verb == 'reply') {
			$this->prepareReplyStream($item, $page, $includePrivacy, $app);
		}

		if ($item->verb == 'answered') {
			$this->prepareAnsweredStream($item, $page, $includePrivacy, $app);
		}

		if ($item->verb == 'lock') {
			$this->prepareLockedStream($item, $page, $includePrivacy, $app);
		}


	}

	/**
	 * Prepares the stream item for new discussion creation
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function prepareCreateDiscussionStream(&$item, $page, $includePrivacy, $app)
	{
		// Get the context params
		$params = ES::registry($item->params);
		$page = ES::page($params->get('page')->id);

		$discussion	= ES::table('Discussion');
		$discussion->load($item->contextId);

		// Determines if there are files associated with the discussion
		$files = $discussion->hasFiles();
		$permalink = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE, 'id' => $app->getAlias(), 'discussionId' => $discussion->id), false);

		$content = $this->formatContent($discussion);

		$this->set('files', $files);
		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('streams/discussions/create.title');
		$item->preview = parent::display('streams/discussions/create.content');
	}

	/**
	 * Prepares the stream item for new reply on discussion
	 *
	 * @since	2.0
	 * @access	public
	 * @param	SocialStreamItem	The stream item.
	 * @return
	 */
	private function prepareReplyStream(&$item, $page, $includePrivacy, $app)
	{
		// Get the context params
		$params = ES::registry($item->params);
		$data = $params->get('page');

		if (!$data) {
			return;
		}

		$page = ES::page($data->id);

		$discussion = ES::table('Discussion');
		$discussion->load($item->contextId);

		$reply = ES::table('Discussion');
		$reply->load($params->get('reply')->id);

		$permalink = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE, 'id' => $app->getAlias(), 'discussionId' => $discussion->id), false);

		$content = $this->formatContent($reply);

		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('reply', $reply);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('streams/discussions/reply.title');
		$item->preview = parent::display('streams/discussions/reply.content');
	}

	/**
	 * Prepares the stream item for answered discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function prepareAnsweredStream(&$item, $page, $includePrivacy, $app)
	{
		// Get the context params
		$params = ES::registry($item->params);
		$page = ES::page($params->get('page')->id);

		$discussion = ES::table('Discussion');
		$discussion->bind($params->get('discussion'));

		$reply = ES::table('Discussion');
		$reply->bind($params->get('reply'));

		$permalink = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE, 'id' => $app->getAlias(), 'discussionId' => $discussion->id), false);

		$content = $this->formatContent($reply);

		// Get the reply author
		$reply->author = ES::user($reply->created_by);

		$this->set('actor', $item->actor);
		$this->set('permalink', $permalink);
		$this->set('discussion', $discussion);
		$this->set('reply', $reply);
		$this->set('content', $content);

		// Load up the contents now.
		$item->title = parent::display('streams/discussions/answered.title');
		$item->preview = parent::display('streams/discussions/answered.content');
	}

	/**
	 * Prepares the stream item for locked discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function prepareLockedStream(&$item, $page, $includePrivacy, $app)
	{
		// Get the context params
		$params = ES::registry($item->params);
		$page = ES::page($params->get('page')->id);

		$discussion = ES::table('Discussion');
		$discussion->bind($params->get('discussion'));

		$permalink = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE, 'id' => $app->getAlias(), 'discussionId' => $discussion->id), false);

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		$this->set('permalink', $permalink);
		$this->set('actor', $item->actor);
		$this->set('discussion', $discussion);

		// Load up the contents now.
		$item->title = parent::display('streams/discussions/locked.title');
	}

	/**
	 * Internal method to format the discussions
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function formatContent($discussion)
	{
		// Get the app params so that we determine which stream should be appearing
		$app = $this->getApp();
		$params	= $app->getParams();

		$content = ES::string()->parseBBCode($discussion->content, array('code' => true, 'escape' => false));

		// Remove [file] from contents
		$content = $discussion->removeFiles($content);
		$maxlength = 250;

		if ($maxlength) {
			$content = strip_tags($content);
			$content = ESJString::strlen($content) > $maxlength ? ESJString::substr($content, 0, $maxlength) . JText::_('COM_EASYSOCIAL_ELLIPSES') : $content;
		}

		return $content;
	}

	private function prepareMakeAdminStream(SocialStreamItem &$item, $page)
	{
		// Get the actor
		$actor = $item->actor;

		$this->set('page', $page);
		$this->set('actor', $actor);

		$item->title = parent::display('streams/admin.title');

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_USER_PAGES_STREAM_PROMOTED_TO_BE_ADMIN', $actor->getName(), $page->getName()));
	}

	private function prepareLikeStream(SocialStreamItem &$item, SocialPage $page)
	{
		// Get the actor
		$actor = $item->actor;

		$this->set('page', $page);
		$this->set('actor', $actor);

		$item->title = parent::display('streams/like.title');

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_USER_PAGES_STREAM_HAS_LIKE_PAGE', $actor->getName(), $page->getName()));
	}

	private function prepareCreateStream(SocialStreamItem &$item, SocialPage $page)
	{
		// We want a full display for page creation.
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get the actor.
		$actor = $item->actor;

		$this->set('page', $page);
		$this->set('actor', $actor);

		$item->title = parent::display('streams/create.title');
		$item->preview = parent::display('streams/content');

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_USER_PAGES_STREAM_CREATED_PAGE', $actor->getName(), $page->getName()));
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		$allowed = array('page.liked', 'page.invited', 'page.requested', 'page.approved');
		$contexts = array('pages.user.create');
		$allowed = array_merge($allowed, $contexts);

		if (!in_array($item->cmd, $allowed) && !in_array($item->context_type, $allowed)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->uid;
		$target->type = 'page';
		$target->endpoint = 'page.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

		// Redirect to followers page
		if ($item->cmd == 'page.requested') {
			$target->nav = 'followers';
		}

		$item->target = $target;
	}
}
