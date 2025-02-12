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

require_once(__DIR__ . '/helper.php');

class SocialPageAppPolls extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		// Only for polls
		if ($context != SOCIAL_TYPE_POLLS) {
			return;
		}

		return false;
	}

	/**
	 * Process notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd, $item->context_type)) {
			return;
		}

		if ($item->cmd == 'pages.polls.create') {
			// Get the page item
			$page = ES::page($item->context_ids);

			// [Page] - For Page poll creator will always be the Page itself
			$item->setActorAlias($page);

			// Format the title
			if ($item->context_type == 'pages') {
				$item->title = JText::sprintf('APP_PAGE_STORY_POLLS_CREATED_IN_PAGE', $page->getName());
				$item->image = $page->getAvatar();
			}

			return;
		}

		$page = ES::page($item->uid);

		if ($item->cmd == 'likes.item') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// Someone voted on the page poll
		if ($item->cmd == 'pages.polls.vote.item') {
			$hook = $this->getHook('notification', 'vote');
			$hook->execute($item);

			return;
		}

		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.replied' || $item->cmd == 'comments.involved') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		return;
	}

	/**
	 * Determines if the element is supported in this app
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function isAllowedCmd($cmd, $type = '')
	{
		static $supported = null;

		$key = $cmd . $type;

		if (!isset($supported[$key])) {

			// Default value
			$supported[$key] = false;
			$allowed = array('pages.polls.create', 'comments.item', 'comments.replied', 'comments.involved', 'likes.item', 'pages.polls.vote.item');

			if (in_array($cmd, $allowed)) {
				$supported[$key] = true;
			}

			if ($type && $type != SOCIAL_TYPE_PAGES) {
				$supported[$key] = false;
			}
		}

		return $supported[$key];
	}

	/**
	 * Determines if polls should appear on the sidebar of the group
	 *
	 * @since   2.0.14
	 * @access  public
	 */
	public function appListing($context, $id)
	{
		$page = ES::page($id);

		if (!$page->canAccessPolls()) {
			return false;
		}

		return true;
	}

	/**
	 * Processes a before saved story.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onBeforeStorySave(&$template, &$stream, &$content)
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_polls', true)) {
			return;
		}

		$in = ES::input();

		$title = $in->getString('polls_title', '');
		$multiple = $in->getInt('polls_multiple', 0);
		$expiry = $in->getString('polls_expirydate', '');
		$items = $in->get('polls_items', array(), 'array');
		$element = $in->getString('polls_element', 'stream');
		$uid = $in->getInt('polls_uid', 0);
		$sourceid = $in->getInt('polls_sourceid', 0);

		// Ensure that the page has polls
		$page = ES::page($sourceid);

		if (!$page->canAccessPolls()) {
			return;
		}

		if (empty($title) || empty($items)) {
			// Ensure that this is actually a poll form
			$attachment = $this->input->getString('attachment', SOCIAL_TYPE_STORY);

			if ($attachment === 'polls') {
				throw ES::exception(JText::_('COM_EASYSOCIAL_POLLS_EMPTY_MESSAGE'), 500);
			}

			return;
		}

		$my = ES::user();

		$poll = ES::get('Polls');
		$polltmpl = $poll->getTemplate();

		$polltmpl->setTitle($title);
		$polltmpl->setCreator($my->id);
		$polltmpl->setContext($uid, $element);
		$polltmpl->setMultiple($multiple);
		$polltmpl->setCluster($sourceid);

		if ($expiry) {
			$polltmpl->setExpiry($expiry);
		}

		if ($items) {
			if (count($items) < 2) {
				throw ES::exception(JText::_('COM_EASYSOCIAL_POLLS_EMPTY_MESSAGE'), 500);
			}

			foreach($items as $itemOption) {
				$polltmpl->addOption($itemOption);
			}
		}

		// polls creation option
		$saveOptions = array('createStream' => false);

		$pollTbl = $poll->create($polltmpl, $saveOptions);

		$template->context_type = SOCIAL_TYPE_POLLS;
		$template->context_id = $pollTbl->id;

		$params = array(
			'poll' => $pollTbl
		);

		$template->setParams(ES::json()->encode($params));
	}

	/**
	 * Processes a saved story.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_polls', true)) {
			return;
		}

		if (($streamItem->context_type != SOCIAL_TYPE_POLLS) || (! $streamItem->context_id)) {
			return;
		}

		//load poll item and assign uid.
		$poll = ES::table('Polls');
		$state = $poll->load($streamItem->context_id);

		// Set this poll as scheduled so it would not displayed on polls listing.
		if ($streamItem->isScheduled()) {
			$poll->state = SOCIAL_STATE_SCHEDULED;

			// Lets create the scheduled item.
			$scheduled = ES::Scheduler();
			$scheduled->create($streamItem->uid, $template);
		}

		if ($state) {
			$poll->uid = $streamItem->uid;
			$poll->store();

			// We need to notify the page followers
			$this->notify($stream, $streamItem, $template);

			// reset the stream privacy to use polls.view privacy instead of story.view
			// $poll->updateStreamPrivacy($streamItem->uid);
		}

		return true;
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if ($item->context != SOCIAL_TYPE_POLLS) {
			return;
		}

		// Determines if the stream should be generated
		$params = $this->getParams();

		if (!$params->get('stream_' . $item->verb, true)) {
			return;
		}

		$privacy = $viewer->getPrivacy();

		// Get the page
		$page = ES::page($item->cluster_id);

		// privacy validation
		if ($includePrivacy && !$privacy->validate('polls.view', $item->contextId, SOCIAL_TYPE_POLLS, $item->actor->id)) {
			return;
		}

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->label = JText::_('APP_POLLS_STREAM', true);

		$pollTbl = ES::table('Polls');
		$pollTbl->load($item->contextId);

		// Apply comments on the stream
		$commentParams = array('url' => $item->getPermalink(false, false, false), 'clusterId' => $page->id);

		$item->comments = ES::comments($pollTbl->id, SOCIAL_TYPE_POLLS, $item->verb, SOCIAL_APPS_GROUP_PAGE, $commentParams, $item->uid);
		$item->likes = ES::likes($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));

		// we need to determine if current user can edit this poll or not.
		$item->editablepoll = ($viewer->id == $pollTbl->created_by || $viewer->isSiteAdmin()) ? true : false;

		if ($includePrivacy) {
			$item->privacy  = $privacy->form($item->contextId, $item->context, $item->actor->id, 'polls.view', false, $item->uid);
		}

		// Set the actor alias
		if ($item->post_as == SOCIAL_TYPE_PAGE) {
			$item->setActorAlias($page);
		}

		$item->contentObj = $pollTbl->toExportData($viewer);
		$item->targets = $page;
		$item->show = true;
	}

	/**
	 * Prepares the stream item.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_POLLS) {
			return;
		}

		// Determines if the stream should be generated
		$params = $this->getParams();

		if (!$params->get('stream_' . $item->verb, true)) {
			return;
		}

		$my = ES::user();
		$privacy = $my->getPrivacy();

		// Get the page
		$page = ES::page($item->cluster_id);

		// privacy validation
		if ($includePrivacy && !$privacy->validate('polls.view', $item->contextId, SOCIAL_TYPE_POLLS, $item->actor->id)) {
			return;
		}

		$permalink = ESR::stream(array('layout' => 'item', 'id' => $item->uid));
		$pollId = $item->contextId;

		$actor = $item->getPostActor($page);

		$poll = ES::get('Polls');
		$contents = $poll->getDisplay($pollId);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->label = JText::_('APP_POLLS_STREAM', true);

		$pollTbl = ES::table('Polls');
		$pollTbl->load($pollId);

		$this->set('poll', $pollTbl);
		$this->set('contents', $contents);
		$this->set('permalink', $permalink);
		$this->set('actor', $actor);
		$this->set('cluster', $page);

		// Apply comments on the stream
		$commentParams = array('url' => $item->getPermalink(false, false, false));

		// Set the cluster id so that we know the comment is belong to this cluster
		$commentParams['clusterId'] = $page->id;

		// Load the comments on the stream
		$item->comments = ES::comments($pollTbl->id, SOCIAL_TYPE_POLLS, $item->verb, SOCIAL_APPS_GROUP_PAGE, $commentParams, $item->uid);

		// For Page, we need to manually ceate the likes
		$item->likes = ES::likes($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));

		$item->title = parent::display('themes:/site/streams/polls/cluster/title.' . $item->verb);
		$item->preview = parent::display('themes:/site/streams/polls/preview');

		// we need to determine if current user can edit this poll or not.
		$item->editablepoll = ($my->id == $pollTbl->created_by || $my->isSiteAdmin()) ? true : false;

		if ($includePrivacy) {
			$item->privacy  = $privacy->form($item->contextId, $item->context, $item->actor->id, 'polls.view', false, $item->uid);
		}

		return true;

		// Append the opengraph tags
		$item->addOgDescription();
	}


	/**
	 * Prepares the story panel
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStoryPanel($story)
	{
		if (!$this->config->get('polls.enabled')) {
			return;
		}

		if (!$this->my->canCreatePolls()) {
			return;
		}

		$params = $this->getParams();

		// Load the page
		$page = ES::page($story->cluster);
		$access = $page->getAccess();

		// Polls has been disabled explicitly
		if (!$access->get('polls.enabled')) {
			return;
		}

		if (!$params->get('story_polls', true) || !$page->canCreatePolls() || !$this->getApp()->hasAccess($page->category_id)) {
			return;
		}

		if (!$this->getApp()->hasAccess($page->category_id)) {
			return;
		}

		// We only allow polls creation on dashboard, which means if the story target and current logged in user is different, then we don't show this
		// Empty target is also allowed because it means no target.
		if (!empty($story->target) && $story->target != $this->my->id) {
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('polls', 'panel');

		// We need to attach the button to the story panel
		$theme = ES::themes();

		// content. need to get the form from poll lib.
		$poll = ES::get('Polls');
		$form = $poll->getForm(SOCIAL_TYPE_STREAM, 0, '', $page->id);
		$theme->set('form', $form);
		$theme->set('page', $page);
		$theme->set('title', $plugin->title);

		// Attachment script
		$script = ES::script();

		$button = $theme->output('site/story/polls/button');
		$form = $theme->output('site/story/polls/form');

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/polls/plugin'));

		return $plugin;
	}

	public function notify($stream, $streamItem, $template)
	{
		$page = ES::page($template->cluster_id);

		$table = ES::table('Polls');
		$table->load($template->context_id);

		$options = array(
			'userId' => $this->my->id,
			'title' => $table->title,
			'permalink' => ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true),
			'id' => $table->id,
			'sid' => $streamItem->uid,
		);

		// Prepare scheduled notification.
		if ($stream->scheduled) {
			$options['scheduled'] = $stream->scheduled;
		}

		$page->notifyMembers('polls.create', $options);
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('polls.page.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($likes->created_by);

		// Load the stream item
		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		// We need to get the context id retrieve the correct polls id
		$streamItem = ES::table('StreamItem');
		$streamItem->load(array('uid' => $likes->stream_id));

		// Load the current page
		$polls = ES::table('Polls');
		$polls->load($streamItem->context_id);

		$page = ES::page($polls->cluster_id);

		// Only notify if the liker is not page Admin
		if ($page->isAdmin($likes->created_by)) {
			return;
		}

		$systemParams = array();
		$systemParams['context_type'] = $likes->type;
		$systemParams['title'] = JText::sprintf('APP_PAGE_STORY_SYSTEM_LIKES_POLLS_INSIDE_PAGE', $actor->getName(), $page->getName(), $polls->title);
		$systemParams['url'] = ESR::stream(array('id' => $stream->id, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $stream->id;
		$systemParams['context_ids'] = $page->id;
		$systemParams['aggregate'] = true;

		ES::notify('likes.item', $page->getAdmins(), false, $systemParams, $page->notification);

		return;
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('polls.page.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

		// Load the stream item
		$stream = ES::table('Stream');
		$stream->load($comment->stream_id);

		// Load the current page.
		$polls = ES::table('Polls');
		$polls->load($comment->uid);

		$page = ES::page($polls->cluster_id);

		// Only notify if the liker is not page Admin
		if ($page->isAdmin($comment->created_by)) {
			return;
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

		// Prepare the email params
		$mailParams = array();
		$mailParams['comment'] = $commentContent;
		$mailParams['actor'] = $actor->getName();
		$mailParams['posterAvatar'] = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['posterLink'] = $actor->getPermalink(true, true);
		$mailParams['actorAvatar'] = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['actorLink'] = $actor->getPermalink(true, true);

		$mailParams['page'] = $page->getName();
		$mailParams['pageLink'] = $page->getPermalink(true, true);
		$mailParams['permalink'] = $stream->getPermalink();
		$mailParams['title'] = 'APP_PAGE_STORY_EMAILS_COMMENT_POLLS_INSIDE_PAGE';
		$mailParams['template'] = 'apps/page/polls/comment.polls.item';

		// Prepare the system notification params
		$systemParams = array();
		$systemParams['context_type'] = $comment->element;
		$systemParams['title'] = JText::sprintf('APP_PAGE_STORY_SYSTEM_COMMENT_POLLS_INSIDE_PAGE', $actor->getName(), $page->getName(), $polls->title);
		$systemParams['url'] = ESR::stream(array('id' => $stream->id, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $comment->uid;
		$systemParams['content'] = $commentContent;
		$systemParmas['context_ids'] = $page->id;
		$systemParams['aggregate'] = true;

		// Assign points to the user when they comment on a poll
		ES::points()->assign('polls.comment.add', 'com_easysocial', $comment->created_by);

		if (!$comment->isChild()) {
			// Notify all page admins
			ES::notify('comments.item', $page->getAdmins(), $mailParams, $systemParams, $page->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'polls', 'page', 'create', array(), array($polls->created_by, $comment->created_by));

		$mailParams['title'] = 'APP_PAGE_POLLS_EMAILS_COMMENT_INVOLVED_SUBJECT';
		$mailParams['template'] = 'apps/page/polls/comment.polls.involved';

		$systemParams['title'] = JText::sprintf('APP_PAGE_STORY_SYSTEM_COMMENT_POLLS_INVOLVED_TITLE', $actor->getName(), $page->getName());

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $mailParams, $systemParams, $page->notification);
		}

		$mailParams['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$mailParams['template'] = 'apps/page/polls/comment.polls.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $mailParams, $systemParams, $page->notification);
		}

		return;
	}

	/**
	 * Triggered when a comment are deleted
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onAfterDeleteComment(&$comment)
	{
		$allowed = array('polls.page.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Assign points when a comment is deleted for polls
		ES::points()->assign('polls.comment.remove', 'com_easysocial', $comment->created_by);
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if (!$this->isAllowedCmd($item->cmd, $item->context_type)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->uid;
		$target->type = 'stream';
		$target->endpoint = 'stream.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

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
		if ($streamItem->context_type != SOCIAL_TYPE_POLLS) {
			return;
		}

		$polls = ES::table('Polls');
		$polls->load($scheduled->context_id);

		// Update video scheduled flag.
		$polls->state = SOCIAL_STATE_PUBLISHED;
		$polls->store();

		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}
}
