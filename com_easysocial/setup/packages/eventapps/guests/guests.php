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

class SocialEventAppGuests extends SocialAppItem
{
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array(
			'likes.item',
			'likes.involved',
			'comments.item',
			'comments.involved',
			'comments.replied'
		);

		// Only guests.event.going, guests.event.notgoing, guests.event.makeadmin has stream item
		if (in_array($item->cmd, $allowed) && in_array($item->context_type, array('guests.event.going', 'guests.event.notgoing', 'guests.event.makeadmin'))) {

			$hook = $this->getHook('notification', $item->type);
			return $hook->execute($item);
		}

		$allowed = array(
			'events.guest.makeadmin',
			'events.guest.revokeadmin',
			'events.guest.reject',
			'events.guest.approve',
			'events.guest.remove',
			'events.guest.going',
			'events.guest.maybe',
			'events.guest.notgoing',
			'events.guest.request',
			'events.guest.withdraw',
			'events.guest.invited'
		);

		if (in_array($item->cmd, $allowed)) {
			$hook = $this->getHook('notification', 'guest');

			return $hook->execute($item);
		}

		if ($item->cmd == 'events.tagged' && $item->context_type == 'tagged') {

			// Get the actor
			$actor = ES::user($item->actor_id);

			// We need to reload the content to ensure that we get the raw data
			$table = ES::table('StreamItem');
			$table->load($item->uid);

			$stream = ES::table('Stream');
			$stream->load($table->uid);

			// Get the content from the stream table.
			$item->content = $stream->content;

			// Determine if the actor is a male or female or unknown (shemale?)
			$genderValue = $actor->getFieldData('GENDER');

			// By default we use male.
			$gender = 'MALE';

			if ($genderValue == 2) {
				$gender = 'FEMALE';
			}

			// If the item has a location, we need to display the title a little different.
			// User said he was with you at xxx
			if ($stream->location_id) {

				$location = ES::table('Location');
				$location->load($stream->location_id);

				// We need to format the address
				$address = ESJString::substr($location->address, 0, 15);

				// Determine if the location has any params
				if (!empty($location->params)) {

					$city = $location->getCity();

					if ($city) {
						$address = $city;
					}
				}

				$item->title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_USER_TAGGED_' . $gender . '_WITH_YOU_AT_LOCATION', $actor->getName(), $address);

				return;
			}

			$item->title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_USER_' . $gender . '_TAGGED_WITH_YOU', $actor->getName());
		}
	}

	public function onStreamVerbExclude(&$exclude)
	{
		$params = $this->getParams();

		$excludeVerb = array();

		$view = $this->input->get('view', '', 'cmd');

		if ((!$params->get('stream_makeadmin', true)) || ($view == 'dashboard' || $view == 'profile')) {
			$excludeVerb[] = 'makeadmin';
		}

		if (!$params->get('stream_going', true)) {
			$excludeVerb[] = 'going';
		}


		// since we are remmoving these stream setting from user app, for site that upgraded from 1.4, we still
		// need to exclude these items.
		$excludeVerb[] = 'notgoing';

		$exclude['guests'] = $excludeVerb;
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		// We only want to process related items
		if ($item->cluster_type !== SOCIAL_TYPE_EVENT || empty($item->cluster_id)) {
			return;
		}

		// Context are split into events and nodes
		// "events" context are stream items that are related to event item
		// "guests" context are stream items that are related to guests of the event
		// Only process "guests" context here
		// "events" context are processed in the app/event/events app
		if ($item->context !== 'guests') {
			return;
		}

		// Get the cluster
		$event = $item->getCluster();

		if (!$event->canViewItem()) {
			return;
		}

		if (!$this->getParams()->get('stream_' . $item->verb, true)) {
			return;
		}

		$item->targets = $event;
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->show = true;
	}

	/**
	 * Prepares the stream for event attendees
	 *
	 * @since   2.0.14
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		// We only want to process related items
		if ($item->cluster_type !== SOCIAL_TYPE_EVENT || empty($item->cluster_id)) {
			return;
		}

		// Context are split into events and nodes
		// "events" context are stream items that are related to event item
		// "guests" context are stream items that are related to guests of the event
		// Only process "guests" context here
		// "events" context are processed in the app/event/events app
		if ($item->context !== 'guests') {
			return;
		}

		// Get the cluster
		$event = $item->getCluster();

		if (!$event->canViewItem()) {
			return;
		}

		if (!$this->getParams()->get('stream_' . $item->verb, true)) {
			return;
		}

		$actor = $item->actor;
		$this->set('item', $item);
		$this->set('event', $event);
		$this->set('actor', $actor);

		// streams/going.title
		// streams/makeadmin.title
		// streams/notgoing.title
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('themes:/site/streams/events/' . $item->verb . '.title');
		$item->content = '';

		// APP_EVENT_GUESTS_STREAM_OPENGRAPH_GOING
		// APP_EVENT_GUESTS_STREAM_OPENGRAPH_NOTGOING
		// APP_EVENT_GUESTS_STREAM_OPENGRAPH_MAKEADMIN
		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_EVENT_GUESTS_STREAM_OPENGRAPH_' . strtoupper($item->verb), $actor->getName(), $event->getName()));
	}

	public function onAfterLikeSave($likes)
	{
		$segments = explode('.', $likes->type);

		if (count($segments) !== 3 || $segments[1] !== SOCIAL_TYPE_EVENT) {
			return;
		}

		list($element, $group, $verb) = explode('.', $likes->type);

		if ($element !== 'guests') {
			return;
		}

		$actor = ES::user($likes->created_by);

		$guest = ES::table('EventGuest');
		$guest->load($likes->uid);

		$event = ES::event($guest->cluster_id);

		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		$owner = ES::user($stream->actor_id);

		$systemOptions = array(
			'context_type' => $likes->type,
			'url' => ESR::stream(array('layout' => 'item', 'id' => $likes->stream_id, 'sef' => false)),
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

	public function onAfterCommentSave($comment)
	{
		$segments = explode('.', $comment->element);

		if (count($segments) !== 3 || $segments[1] !== SOCIAL_TYPE_EVENT) {
			return;
		}

		list($element, $group, $verb) = explode('.', $comment->element);

		if ($element !== 'guests') {
			return;
		}

		// Get the actor
		$actor = ES::user($comment->created_by);

		$guest = ES::table('EventGuest');
		$guest->load($comment->uid);

		$event = ES::event($guest->cluster_id);

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
			'title' => 'APP_USER_EVENTS_GUESTS_EMAILS_' . strtoupper($verb) . '_COMMENT_ITEM_SUBJECT',
			'template' => 'apps/user/events/guest.' . $verb . '.comment.item',
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

		$emailOptions['title'] = 'APP_USER_EVENTS_GUESTS_EMAILS_' . strtoupper($verb) . '_COMMENT_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/user/events/guest.' . $verb . '.comment.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/events/guest.event.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	public function appListing($view, $eventId, $type)
	{
		return false;
	}

	/**
	 * Processes a saved story so that we can notify users who are tagged in the system
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function onAfterStorySave(&$stream, $streamItem, $streamTemplate)
	{
		// If there's no "with" data, skip this.
		if (!$streamTemplate->with) {
			return;
		}

		// Determine if this is for a event
		if (!$streamTemplate->cluster_id) {
			return;
		}

		// Get list of users that are tagged in this post.
		$taggedUsers = $streamTemplate->with;

		// Get the creator of this update
		$poster = ES::user($streamTemplate->actor_id);

		// Get the content of the stream item.
		$content = $streamTemplate->content;

		// Get the event object
		$event = ES::event($streamTemplate->cluster_id);

		if (!$taggedUsers) {
			return;
		}

		foreach ($taggedUsers as $id) {

			$taggedUser = ES::user($id);

			// Set the email options
			$emailOptions = array(
				'title' => 'APP_USER_FRIENDS_EMAILS_USER_TAGGED_YOU_IN_POST_SUBJECT',
				'template' => 'apps/user/friends/post.tagged',
				'permalink' => $streamItem->getPermalink(true, true),
				'actor' => $poster->getName(),
				'actorAvatar' => $poster->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $poster->getPermalink(true, true),
				'message' => $content,
				// sid used by scheduled post.
				'sid' => $streamItem->uid
			);

			$systemOptions = array(
				'uid' => $streamItem->id,
				// sid used by scheduled post.
				'sid' => $streamItem->uid,
				'context_type' => 'tagged',
				'type' => 'events',
				'url' => $streamItem->getPermalink(false, false, false),
				'actor_id' => $poster->id,
				'aggregate' => false,
				'context_ids' => $event->id
			);

			// We'll need to pass the scheduled date for notification processing.
			if (isset($stream->scheduled) && $stream->scheduled) {
				$emailOptions['scheduled'] = $stream->scheduled;
				$systemOptions['scheduled'] = $stream->scheduled;
			}

			// Add new notification item
			ES::notify('events.tagged',  array($taggedUser->id), $emailOptions, $systemOptions);
		}

		return true;
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		$allowedCmd = array(
			'events.guest.makeadmin',
			'events.guest.revokeadmin',
			'events.guest.reject',
			'events.guest.approve',
			'events.guest.remove',
			'events.guest.going',
			'events.guest.maybe',
			'events.guest.notgoing',
			'events.guest.request',
			'events.guest.withdraw',
			'events.guest.invited'
		);

		// Run standard notification processings
		$this->onNotificationLoad($item);
		$target = $item->target;

		if (in_array($item->cmd, $allowedCmd)) {
			$guest = ES::table('EventGuest');
			$guest->load($item->uid);

			$eventId = $item->getParams()->get('eventId');

			if (!$eventId) {
				return;
			}

			$event = ES::event($eventId);

			$target->id = $event->id;
			$target->type = 'event';
			$target->endpoint = 'event.item';
			$target->query_string = $target->endpoint . '&id=' . $target->id;

			$item->target = $target;

			return;
		}

		$allowedCmd = array(
			'likes.item',
			'likes.involved',
			'comments.item',
			'comments.involved'
		);

		$allowedContext = array(
			'guests.event.going',
			'guests.event.notgoing',
			'guests.event.makeadmin'
		);

		// Only guests.event.going, guests.event.notgoing, guests.event.makeadmin has stream item
		if (in_array($item->cmd, $allowedCmd) && in_array($item->context_type, $allowedContext) || ($item->cmd == 'groups.tagged' && $item->context_type == 'tagged')) {
			$target->id = $item->uid;
			$target->type = 'stream';
			$target->endpoint = 'stream.item';
			$target->query_string = $target->endpoint . '&id=' . $target->id;

			$item->target = $target;
		}

		return;
	}
}
