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

class SocialEventAppEvents extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		// Only for album
		if ($context != SOCIAL_TYPE_ALBUM) {
			return;
		}

		$album = ES::table('Album');
		$album->load($uid);

		$lib = ES::albums($album->uid, SOCIAL_TYPE_EVENT, $album);

		if (!$lib->viewable()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the element is suppsorted in this app
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function isSupportedElement($element)
	{
		static $supported = null;

		if (!isset($supported[$element])) {
			$supported[$element] = false;
			$allowed = array('events.event.create', 'events.event.featured', 'events.event.update');

			if (in_array($element, $allowed)) {
				$supported[$element] = true;
			}
		}

		return $supported[$element];
	}

	public function onStreamVerbExclude(&$exclude, $perspective)
	{
		$params = $this->getParams();

		$excludeVerb = array();

		if (!$params->get('stream_create', true)) {
			$excludeVerb[] = 'create';
		}
		if (!$params->get('stream_update', true)) {
			$excludeVerb[] = 'update';
		}

		// These are the verbs that we don't want to appear in dashboard stream
		// Only display them in Event perspective
		$view = $this->input->get('source', '', 'string');
		if (! $view) {
			$view = $this->input->get('view', '', 'string');
		}

		if ($view != 'events') {

			// Lastly check for perspective
			if ($perspective && strtoupper($perspective) !== 'EVENTS') {
				$excludeVerb[] = 'makeadmin';
				$excludeVerb[] = 'update';
				$excludeVerb[] = 'notgoing';
			}
		}

		if (!empty($excludeVerb)) {
			$exclude['events'] = $excludeVerb;
		}

		// since we are remmoving these stream setting from user app, for site that upgraded from 1.4, we still
		// need to exclude these items.
		$excludeVerb = array();

		if (!$params->get('stream_going', true)) {
			$excludeVerb[] = 'going';
		}

		$excludeVerb[] = 'feature';
		$excludeVerb[] = 'update';
		$excludeVerb[] = 'makeadmin';
		$excludeVerb[] = 'notgoing';

		$exclude['guests'] = $excludeVerb;
	}

	/**
	 * Trigger for onPrepareDigest
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareDigest(SocialStreamItem &$item)
	{
		if ($item->cluster_type !== SOCIAL_TYPE_EVENT || empty($item->cluster_id)) {
			return;
		}

		if ($item->context != 'events') {
			return;
		}

		$event = $item->getCluster();


		$actor = $item->actor;

		$item->title = '';
		$item->link = $event->getPermalink(true, true);

		if ($item->verb == 'create') {
			$item->title = JText::sprintf('COM_ES_APP_EVENTS_DIGEST_CREATED_TITLE', $actor->getName(), $event->getTitle());
		}

		if ($item->verb == 'featured') {
			$item->title = JText::sprintf('COM_ES_APP_EVENTS_DIGEST_FEATURED_TITLE', $actor->getName(), $event->getTitle());
		}
	}

	/**
	 * Pre-process the stream item
	 *
	 * @since   3.1.0
	 * @access  public
	 */
	public function isValid(&$item, $viewerId = null)
	{
		// We only want to process related items
		if ($item->cluster_type !== SOCIAL_TYPE_EVENT || empty($item->cluster_id)) {
			return false;
		}

		if ($item->context !== 'events') {
			return false;
		}

		$event = $item->getCluster();

		if (!$event || !$event->id) {
			return false;
		}

		// Only show Social sharing in public group
		if (!$event->isOpen()) {
			$item->sharing = false;
		}

		if (!$event->canViewItem($viewerId)) {
			return false;
		}

		// These are the verbs that we don't want to appear in dashboard stream
		// Only display them in Event perspective
		$eventVerbs = array('makeadmin', 'notgoing', 'update');

		if (in_array($item->verb, $eventVerbs) && $item->getPerspective() != 'EVENTS') {
			return false;
		}

		// If the event is pending and is a new item, this means this event is created from the story form, and we want to show a message stating that the event is in pending
		if ($event->isNew() && $event->isPending()) {

			// Newly created event from story panel
			$item->title = JText::_('APP_USER_EVENTS_STREAM_EVENT_PENDING_APPROVAL');
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
			return false;
		}

		if (!$event->isPublished()) {
			// if this event is not published, then we should not show
			return false;
		}

		if (!$this->getParams()->get('stream_' . $item->verb, true)) {
			return false;
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
		if (!$this->isValid($item, $viewer->id)) {
			return;
		}

		$event = $item->getCluster();

		$item->contentObj = $event->toExportData($viewer);

		$access = $event->getAccess();
		if ($item->verb == 'create' && ($viewer->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id))) {
			$item->edit_link = $event->getEditPermalink();;
		}

		// There are possibility that this is a cluster event
		if ($event->isGroupEvent()) {
			$group = $event->getGroup();
			$item->targets = $group;
			$this->set('group', $group);

			$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, $item->uid, array('clusterId' => $group->id));

			$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $group->id);
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, $commentParams, $item->uid);
		}

		if ($event->isPageEvent()) {
			$page = $event->getPage();
			$this->set('page', $page);

			// For Page, we need to manually ceate the likes and comments object
			$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));

			$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $page->id);
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $commentParams, $item->uid);

			// Set an alias for actor
			// This is to change the actor avatar to use Page's avatar
			$item->setActorAlias($page);
		}

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if ($item->verb == 'going' || $item->verb == 'update') {
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		}

		$item->show = true;
	}

	/**
	 * Prepares the stream item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if (!$this->isValid($item)) {
			return;
		}

		$event = $item->getCluster();

		$access = $event->getAccess();
		if ($item->verb == 'create' && ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id))) {
			$item->edit_link = $event->getEditPermalink();
		}

		// There are possibility that this is a cluster event
		if ($event->isGroupEvent()) {
			$group = $event->getGroup();
			$this->set('group', $group);

			// For Group, we need to manually ceate the likes and comments object
			$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, $item->uid, array('clusterId' => $group->id));

			$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $group->id);
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, $commentParams, $item->uid);
		}

		if ($event->isPageEvent()) {
			$page = $event->getPage();
			$this->set('page', $page);

			// For Page, we need to manually ceate the likes and comments object
			$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));

			$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $page->id);
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $commentParams, $item->uid);

			// Set an alias for actor
			// This is to change the actor avatar to use Page's avatar
			$item->setActorAlias($page);
		}

		$this->set('event', $event);
		$this->set('actor', $item->actor);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if ($item->verb == 'going' || $item->verb == 'update') {
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		}

		$item->title = parent::display('themes:/site/streams/events/' . $item->verb . '.title');
		$item->preview = parent::display('themes:/site/streams/events/preview');

		// APP_EVENT_EVENTS_STREAM_OPENGRAPH_UPDATE
		// APP_EVENT_EVENTS_STREAM_OPENGRAPH_CREATE
		// APP_EVENT_EVENTS_STREAM_OPENGRAPH_FEATURE
		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_EVENT_EVENTS_STREAM_OPENGRAPH_' . strtoupper($item->verb), $item->actor->getName(), $event->getName()));
	}

	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowedCmd = array('likes.item', 'likes.involved', 'comments.item', 'comments.involved', 'comments.replied');

		if (!in_array($item->cmd, $allowedCmd)) {
			return;
		}

		if (!$this->isSupportedElement($item->context_type)) {
			return;
		}

		$event = ES::event($item->uid);
		$actor = ES::user($item->actor_id);

		$hook = $this->getHook('notification', $item->type);
		$hook->execute($item);

		return;
	}

	public function onAfterCommentSave($comment)
	{
		$segments = explode('.', $comment->element);

		if (count($segments) !== 3 || $segments[1] !== SOCIAL_TYPE_EVENT) {
			return;
		}

		list($element, $group, $verb) = explode('.', $comment->element);

		if ($element !== 'events') {
			return;
		}

		// Get the actor
		$actor = ES::user($comment->created_by);

		$event = ES::event($comment->uid);

		$stream = ES::table('Stream');
		$stream->load($comment->stream_id);

		$owner = ES::user($stream->actor_id);

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
			'title' => 'APP_USER_EVENTS_EMAILS_' . strtoupper($verb) . '_COMMENT_ITEM_SUBJECT',
			'template' => 'apps/user/events/' . $verb . '.comment.item',
			'permalink' => $stream->getPermalink(true, true),
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'comment' => $commentContent
		);

		 $systemOptions  = array(
			'context_type' => $comment->element,
			'content' => $commentContent,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		// Notify the owner first
		if ($comment->created_by != $owner->id && !$comment->isChild()) {
			ES::notify('comments.item', array($owner->id), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the discussion and the actor of the comment here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($owner->id, $comment->created_by));

		$emailOptions['title'] = 'APP_USER_EVENTS_EMAILS_' . strtoupper($verb) . '_COMMENT_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/user/events/' . $verb . '.comment.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/events/comment.event.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	public function onAfterLikeSave($likes)
	{
		$segments = explode('.', $likes->type);

		if (count($segments) !== 3 || $segments[1] !== SOCIAL_TYPE_EVENT) {
			return;
		}

		list($element, $group, $verb) = explode('.', $likes->type);

		if ($element !== 'events') {
			return;
		}

		$actor = ES::user($likes->created_by);
		$event = ES::event($likes->uid);

		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		$owner = ES::user($stream->actor_id);

		$systemOptions = array(
			'context_type' => $likes->type,
			'url' => FRoute::stream(array('layout' => 'item', 'id' => $likes->stream_id, 'sef' => false)),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		// Notify the owner first
		if ($likes->created_by != $owner->id) {
			ES::notify('likes.item', array($owner->id), false, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the guest and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $group, $verb, array(), array($owner->id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions);
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		$allowedCmd = array('likes.item', 'likes.involved', 'comments.item', 'comments.involved');

		if (!in_array($item->cmd, $allowedCmd)) {
			return;
		}

		if (!$this->isSupportedElement($item->context_type)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$streamId = false;

		// Load the comments or likes to get the stream id
		// Since $item->uid is an event id instead of the actual comment/like id.
		if ($item->cmd == 'comments.item') {
			$comment = ES::table('Comments');
			$comment->load(array('uid' => $item->uid, 'element' => $item->context_type));

			$streamId = $comment->stream_id;
		}

		if ($item->cmd == 'likes.item') {
			$likes = ES::table('Likes');
			$likes->load(array('uid' => $item->uid, 'type' => $item->context_type));

			$streamId = $likes->stream_id;
		}

		if (!$streamId) {
			return;
		}

		$target->id = $streamId;
		$target->type = 'stream';
		$target->endpoint = 'stream.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

		$item->target = $target;
	}
}
