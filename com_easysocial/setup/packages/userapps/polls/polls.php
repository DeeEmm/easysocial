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

ES::import('admin:/includes/apps/apps');

class SocialUserAppPolls extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_POLLS) {
			return;
		}

		// get polls onwer
		$table = ES::table('Polls');
		$table->load($uid);

		if (!$table->id) {
			return false;
		}

		// Privacy
		$privacy = $this->my->getPrivacy();
		if (!$privacy->validate('polls.view', $table->id, SOCIAL_TYPE_POLLS, $table->created_by)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the element is supported in this app
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function isSupportedElement($element, $type = '')
	{
		static $supported = null;

		$key = $element . $type;

		if (!isset($supported[$key])) {

			// Default value
			$supported[$key] = false;
			$allowed = array('polls.user.create', 'polls.vote.item');

			if (in_array($element, $allowed)) {
				$supported[$key] = true;
			}

			if ($supported[$key] && $type) {
				$allowedType = array('likes', 'comments', 'polls');

				if (!in_array($type, $allowedType)) {
					$supported[$key] = false;
				}
			}
		}

		return $supported[$key];
	}

	/**
	 * Prepares the notifications for poll items
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		// Someone voted on the user poll
		// Special case. Because there is no value stored in $item->context_type for polls.vote.item
		if ($item->cmd == 'polls.vote.item') {
			$hook = $this->getHook('notification', 'vote');
			$hook->execute($item);

			return;
		}

		if (!$this->isSupportedElement($item->context_type, $item->type)) {
			return;
		}

		$hook = $this->getHook('notification', $item->type);
		$hook->execute($item);
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

		// Get the app params
		$params = $this->getParams();

		if (!$params->get('stream_' . $item->verb, true)) {
			return;
		}

		// Privacy
		$privacy = $viewer->getPrivacy();
		if ($includePrivacy && !$privacy->validate('polls.view', $item->contextId, SOCIAL_TYPE_POLLS, $item->actor->id)) {
			return;
		}

		$table = ES::table('Polls');
		$table->load((int) $item->contextId);

		$item->contentObj = $table->toExportData($viewer);
		$item->targets = $item->getCluster();
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$item->comments = ES::comments($item->contextId, SOCIAL_TYPE_POLLS, $item->verb, SOCIAL_APPS_GROUP_USER, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false))), $item->uid);
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_TYPE_USER, $item->uid);

		// Determines if current user can edit this poll or not.
		$item->editablepoll = ($viewer->id == $table->created_by || $viewer->isSiteAdmin()) ? true : false;

		if ($includePrivacy) {
			$item->privacy = $privacy->form($item->contextId, $item->context, $item->actor->id, 'polls.view', false, $item->uid, array(), array('iconOnly' => true));
		}

		$item->show = true;
	}

	/**
	 * Prepares the stream item for polls
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_POLLS) {
			return;
		}

		// Get the app params
		$params = $this->getParams();

		if (!$params->get('stream_' . $item->verb, true)) {
			return;
		}

		// Privacy
		$privacy = $this->my->getPrivacy();
		if ($includePrivacy && !$privacy->validate('polls.view', $item->contextId, SOCIAL_TYPE_POLLS, $item->actor->id)) {
			return;
		}

		// Get the permalink for the stream
		$permalink = $item->getPermalink();

		$polls = ES::polls();
		$contents = $polls->getDisplay((int) $item->contextId);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$table = ES::table('Polls');
		$table->load((int) $item->contextId);

		$this->set('actor', $item->actor);
		$this->set('poll', $table);
		$this->set('contents', $contents);
		$this->set('permalink', $permalink);

		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_TYPE_USER, $item->uid);

		$item->title = parent::display('themes:/site/streams/polls/user/' . $item->verb . '.title');
		$item->preview = parent::display('themes:/site/streams/polls/preview');

		// Determines if current user can edit this poll or not.
		$item->editablepoll = ($this->my->id == $table->created_by || $this->my->isSiteAdmin()) ? true : false;

		if ($includePrivacy) {
			$item->privacy = $privacy->form($item->contextId, $item->context, $item->actor->id, 'polls.view', false, $item->uid, array(), array('iconOnly' => true));
		}

		return true;
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		// Here we know that comments associated with article is always
		// comment.uid = notification.uid
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithContextId($comment->id, $comment->element);
	}

	/**
	 * Processes a before saved story.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onBeforeStorySave(&$template, &$stream, &$content)
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_polls', true)) {
			return;
		}

		$title = $this->input->getString('polls_title', '');
		$multiple = $this->input->getInt('polls_multiple', 0);
		$expiry = $this->input->getString('polls_expirydate', '');
		$items = $this->input->get('polls_items', array(), 'array');
		$element = $this->input->getString('polls_element', 'stream');
		$uid = $this->input->getInt('polls_uid', 0);

		if (empty($title) || empty($items)) {

			// Ensure that this is actually a poll form
			$attachment = $this->input->getString('attachment', SOCIAL_TYPE_STORY);

			if ($attachment === 'polls') {
				throw ES::exception(JText::_('COM_EASYSOCIAL_POLLS_EMPTY_MESSAGE'), 500);
			}

			return;
		}

		// Poll posts should never contain any status updates
		$template->content = '';

		$my = ES::user();

		$poll = ES::get('Polls');
		$polltmpl = $poll->getTemplate();

		$polltmpl->setTitle($title);
		$polltmpl->setCreator($my->id);
		$polltmpl->setContext($uid, $element);
		$polltmpl->setMultiple($multiple);
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
	 * Processes the poll is stored
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_polls', true)) {
			return;
		}

		if ( ($streamItem->context_type != SOCIAL_TYPE_POLLS) || (! $streamItem->context_id)) {
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

			// reset the stream privacy to use polls.view privacy instead of story.view
			// $poll->updateStreamPrivacy($streamItem->uid);
		}

		return true;
	}

	/**
	 * Prepares what should appear in the story form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStoryPanel($story)
	{
		// If the anywhereId exists, means this came from Anywhere module
		// We need to exclude polls form from it.
		if (!is_null($story->anywhereId)) {
			return;
		}

		if (!$this->config->get('polls.enabled')) {
			return;
		}

		$params = $this->getParams();

		if (!$this->my->canCreatePolls()) {
			return;
		}

		// We only allow polls creation on dashboard, which means if the story target and current logged in user is different, then we don't show this
		// Empty target is also allowed because it means no target.
		if (!empty($story->target) && $story->target != ES::user()->id) {
			return;
		}

		// Determine if we should attach ourselves here.
		if (!$params->get('story_polls', true)) {
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('polls', 'panel');

		// We need to attach the button to the story panel
		$theme = ES::themes();

		// content. need to get the form from poll lib.
		$poll = ES::get('Polls');
		$form = $poll->getForm(SOCIAL_TYPE_STREAM, 0);
		$theme->set('form', $form);

		$theme->set('title', $plugin->title);

		// Attachment script
		$script = ES::script();

		$button = $theme->output('site/story/polls/button');
		$form = $theme->output('site/story/polls/form');

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/polls/plugin'));

		return $plugin;
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		if (!$this->isSupportedElement($likes->type)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($likes->created_by);

		// Load the stream item
		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		// Load the current group.
		$polls = ES::table('Polls');
		$polls->load($likes->uid);

		$systemParams = array();
		$systemParams['title'] = JText::sprintf('APP_USER_STORY_SYSTEM_LIKES_YOUR_POLLS', $actor->getName());
		$systemParams['url'] = ESR::stream(array('id' => $stream->id, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $stream->id;
		$systemParams['context_type'] = $likes->type;
		$systemParams['aggregate'] = true;

		// Only notify if the actor is not the poll's owner
		if ($likes->created_by != $stream->actor_id) {
			ES::notify('likes.item', array($stream->actor_id), false, $systemParams);
		}

		return;
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

		// Load the stream item
		$stream = ES::table('Stream');
		$stream->load($comment->stream_id);

		// Load the current group.
		$polls = ES::table('Polls');
		$polls->load($comment->uid);

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

		$mailParams['permalink'] = $stream->getPermalink();
		$mailParams['title'] = 'APP_USER_STORY_EMAILS_COMMENT_YOUR_POLLS';
		$mailParams['template'] = 'apps/user/polls/comment.polls.item';

		// Prepare the system notification params
		$systemParams = array();
		$systemParams['title'] = JText::sprintf('APP_USER_STORY_SYSTEM_COMMENT_YOUR_POLLS', $actor->getName());
		$systemParams['url'] = ESR::stream(array('id' => $stream->id, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $comment->uid;
		$systemParams['content'] = $commentContent;
		$systemParams['context_ids'] = $comment->id;
		$systemParams['context_type'] = $comment->element;
		$systemParams['aggregate'] = true;

		// Notify the owner of the photo first
		if ($comment->created_by != $polls->created_by && !$comment->isChild()) {
			ES::notify('comments.item', array($polls->created_by), $mailParams, $systemParams);
		}

		// Assign points to the user when they comment on a poll
		ES::points()->assign('polls.comment.add', 'com_easysocial', $comment->created_by);

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'polls', 'user', 'create', array(), array($polls->created_by, $comment->created_by));

		$mailParams['title'] = 'APP_USER_POLLS_EMAILS_COMMENT_INVOLVED_SUBJECT';
		$mailParams['template'] = 'apps/user/polls/comment.polls.involved';

		$systemParams['title'] = JText::sprintf('APP_USER_STORY_SYSTEM_COMMENT_POLLS_INVOLVED_TITLE', $actor->getName());

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $mailParams, $systemParams);
		}

		$mailParams['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$mailParams['template'] = 'apps/user/polls/comment.polls.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $mailParams, $systemParams);
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
		$allowed = array('polls.user.create');

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
		$element = $item->context_type;

		if (!$element) {
			$element = $item->cmd;
		}

		if (!$this->isSupportedElement($element, $item->type)) {
			return;
		}

		$streamId = $item->uid;

		if ($item->type == 'comments') {
			$pollId = $item->uid;

			$model = ES::model('Polls');
			$streamId = $model->getStreamId($pollId, 'create');
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $streamId;
		$target->type = 'stream';
		$target->endpoint = 'stream.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

		$item->target = $target;
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
