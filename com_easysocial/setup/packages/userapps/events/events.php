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

class SocialUserAppEvents extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'events') {
			return;
		}

		// user events apps should not even reach here.
		// just return false
		return false;
	}

	/**
	 * Determines if the element is supported in this app
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function isSupportedElement($element)
	{
		static $supported = null;

		if (is_null($supported)) {
			$supported = false;
			$allowed = array('events.event.create', 'events.event.featured', 'events.event.update', 'story.event.create', 'links.event.create', 'polls.event.create', 'files.event.uploaded');

			if (in_array($element, $allowed)) {
				$supported = true;
			}
		}

		return $supported;
	}

	/**
	 * Handles notifications for events
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('events.reminder');

		if (!in_array($item->cmd, $allowed) && !in_array($item->context_type, $allowed)) {
			return;
		}

		// Event reminder
		if ($item->cmd == 'events.reminder') {
			$params = $item->getParams();
			$total = (int) $params->get('total');

			$item->title = JText::sprintf('APP_USER_EVENTS_NEW_UPCOMING_MULTIPLE_EVENTS', $params->get('total'));

			if ($total == 1) {
				$events = $params->get('events');
				$events = json_decode($events);
				$event = ES::event($events[0]);

				$item->title = JText::sprintf('APP_USER_EVENTS_NEW_UPCOMING_SINGLE_EVENT', $event->getEventStart()->format(JText::_('DATE_FORMAT_LC3')));

				$item->content = $event->getTitle();
				$item->image = $event->getAvatar();
			}

		}

		// there is nothing to process.
		return false;
	}

	/**
	 * Redirects the user to the appropriate page
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function onBeforeNotificationRedirect(&$item)
	{
		$allowed = array('events.reminder');

		if (!in_array($item->cmd, $allowed) && !in_array($item->context_type, $allowed)) {
			return;
		}

		$params = $item->getParams();
		$total = (int) $params->get('total');

		// Update the redirection link
		if ($total > 1) {
			$item->url = ESR::events(array('filter' => 'upcoming'));
		} else {
			$events = $params->get('events');
			$events = json_decode($events);
			$event = ES::event($events[0]);

			$item->url = $event->getPermalink();
		}
	}

	/**
	 * Renders the events story form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStoryPanel($story)
	{
		// If the anywhereId exists, means this came from Anywhere module
		// We need to exclude event form from it.
		if (!is_null($story->anywhereId)) {
			return;
		}

		// Prevent creating scheduled event from story post.
		if ($story->isScheduling()) {
			return;
		}

		// Check if user are allowed to create event.
		if (!$this->my->canCreateEvents()) {
			return;
		}

		// We only allow event creation on dashboard, which means if the story target and current logged in user is different, then we don't show this
		// Empty target is also allowed because it means no target.
		if (!empty($story->target) && $story->target != $this->my->id) {
			return;
		}

		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_event', true)) {
			return;
		}

		// Ensure that the user has access to create events
		if (!$this->my->getAccess()->get('events.create') || !$this->getApp()->hasAccess($this->my->profile_id)) {
			return;
		}

		// Ensure that events is enabled
		if (!$this->config->get('events.enabled')) {
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('event', 'panel');

		// Get the theme class
		$theme = ES::themes();
		$theme->set('title', $plugin->title);

		// Get the available event category
		$categories = ES::model('EventCategories')->getCreatableCategories(ES::user()->getProfile()->id);

		$theme->set('categories', $categories);

		$plugin->button->html = $theme->output('site/story/events/button');
		$plugin->content->html = $theme->output('site/story/events/form');

		$script = ES::get('Script');
		$plugin->script = $script->output('site/story/events/plugin');

		return $plugin;
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
		$uid = $comment->uid;
		$element = $comment->element;

		if ($element == 'polls.event.create') {
			$uid = $comment->stream_id;
			$element = 'events';
		}

		$model = ES::model('Notifications');
		$model->deleteNotificationsWithUid($uid, $element);
	}

	public function onBeforeStorySave(&$template, &$stream, &$content)
	{
		// Check if user are allowed to create event.
		if (!$this->my->canCreateEvents()) {
			return;
		}

		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_event', true)) {
			return;
		}

		$in = ES::input();

		$title = $in->getString('event_title');
		$description = $in->getString('event_description');
		$categoryid = $in->getInt('event_category');
		$startDatetime = $in->getString('event_start');
		$endDatetime = $in->getString('event_end');
		$timezone = $in->getString('event_timezone');

		// If no category id, then we don't proceed
		if (empty($categoryid)) {
			return;
		}

		// Perhaps in the future we use ES::model('Event')->createEvent() instead.
		// For now just hardcode it here to prevent field triggering and figuring out how to punch data into the respective field data because the form is not rendered through field trigger.

		$my = ES::user();

		$event = ES::event();

		$event->title = $title;

		$event->description = $description;

		// Set a default params for this event first
		$event->params = '{"photo":{"albums":true},"news":true,"discussions":true,"allownotgoingguest":false,"allowmaybe":true,"guestlimit":0}';

		$event->type = SOCIAL_EVENT_TYPE_PUBLIC;
		$event->creator_uid = $my->id;
		$event->creator_type = SOCIAL_TYPE_USER;
		$event->category_id = $categoryid;
		$event->cluster_type = SOCIAL_TYPE_EVENT;
		$event->alias = ES::model('Events')->getUniqueAlias($title);
		$event->created = ES::date()->toSql();
		$event->key = md5($event->created . $my->password . uniqid());

		$event->state = SOCIAL_CLUSTER_PENDING;

		if ($my->isSiteAdmin() || !$my->getAccess()->get('events.moderate')) {
			$event->state = SOCIAL_CLUSTER_PUBLISHED;
		}

		// Trigger apps
		ES::apps()->load(SOCIAL_TYPE_USER);

		$dispatcher  = ES::dispatcher();
		$triggerArgs = array(&$event, &$my, true);

		// @trigger: onEventBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventBeforeSave', $triggerArgs);

		$state = $event->save();

		// Process the event meta
		$this->processEventMeta($event, $startDatetime, $endDatetime, $timezone);

		// Notifies admin when a new event is created
		if ($event->state === SOCIAL_CLUSTER_PENDING || !$my->isSiteAdmin()) {
			ES::model('Events')->notifyAdmins($event);
		}

		// Recreate the event object
		SocialEvent::$instances[$event->id] = null;
		$event = ES::event($event->id);

		// Create a new owner object
		$event->createOwner($my->id);

		// @trigger: onEventAfterSave
		$triggerArgs = array(&$event, &$my, true);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventAfterSave' , $triggerArgs);

		// Due to inconsistency, we don't use SOCIAL_TYPE_EVENT.
		// Instead we use "events" because app elements are named with 's', namely users, groups, events.
		$template->context_type = 'events';

		$template->context_id = $event->id;
		$template->cluster_access = $event->type;
		$template->cluster_type = $event->cluster_type;
		$template->cluster_id = $event->id;

		$params = array(
			'event' => $event
		);

		$template->setParams(ES::json()->encode($params));
	}

	public function getDatetimeObject($data = null)
	{
		$dateObj = new SocialEventStartendObject;

		if (empty($data)) {
			return $dateObj;
		}

		$dateObj->load($data);

		return $dateObj;
	}

	public function processEventMeta($event, $startDatetime, $endDatetime, $timezone)
	{
		// We get the joomla timezone
		$original_TZ = new DateTimeZone(JFactory::getConfig()->get('offset'));

		// Get the date with timezone
		$tempStartDate = JFactory::getDate($startDatetime, $original_TZ);
		$tempEndDate = JFactory::getDate($endDatetime, $original_TZ);

		// Check for timezone. If the timezone has been changed, get the new startend date
		if ((!empty($timezone) && $timezone !== 'UTC')) {
			$dtz = new DateTimeZone($timezone);

			// Creates a new datetime string with user input timezone as predefined timezone
			$newStartDatetime = JFactory::getDate($startDatetime, $dtz);
			$newEndDatetime = JFactory::getDate($endDatetime, $dtz);

			// Reverse the timezone back to UTC
			$startDatetime = $newStartDatetime->toSql();
			$endDatetime = $newEndDatetime->toSql();
		}

		$startDatetimeObj = $this->getDatetimeObject($startDatetime);
		$endDatetimeObj = $this->getDatetimeObject($endDatetime);

		// Convert the date to non-offset date
		$nonOffsetStartDate = $tempStartDate->toSql();
		$nonOffsetEndDate = $tempEndDate->toSql();

		$tempStartDatetimeObj = $this->getDatetimeObject($nonOffsetStartDate);
		$tempEndDatetimeObj = $this->getDatetimeObject($nonOffsetEndDate);

		$startString = $startDatetimeObj->toSql();
		$endString = $endDatetime ? $endDatetimeObj->toSql() : '0000-00-00 00:00:00';

		$tempStartString = $tempStartDatetimeObj->toSql();
		$tempEndString = $endDatetime ? $tempEndDatetimeObj->toSql() : '0000-00-00 00:00:00';

		$startGMT = $startString;
		$endGMT = $endString;

		// if no timezone, we need to use the non-offset for the start_gmt column
		// This column used when checking for upcoming event reminder
		if (empty($timezone)) {
			$startGMT = $tempStartString;
			$endGMT = $tempEndString;
		}

		$eventMeta = $event->meta;
		// Set the meta for start end timezone
		$eventMeta->cluster_id = $event->id;
		$eventMeta->start = $startString;
		$eventMeta->end = $endString;
		$eventMeta->timezone = $timezone;
		$eventMeta->start_gmt = $startGMT;
		$eventMeta->end_gmt = $endGMT;

		$eventMeta->store();
	}

	public function onAfterCommentSave($comment)
	{
		$segments = explode('.', $comment->element);

		if (count($segments) !== 3 || $segments[1] !== SOCIAL_TYPE_EVENT) {
			return;
		}

		list($element, $group, $verb) = explode('.', $comment->element);

		// Get the actor
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

		if ($element === 'events') {
			$event = ES::event($comment->uid);

			$stream = ES::table('Stream');
			$stream->load($comment->stream_id);

			$owner = ES::user($stream->actor_id);

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

		if ($element === 'guests') {
			$guest = ES::table('EventGuest');
			$guest->load($comment->uid);

			$event = ES::event($guest->cluster_id);

			$stream = ES::table('Stream');
			$stream->load($comment->stream_id);

			$owner = ES::user($stream->actor_id);

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
				'content' => $comment->comment,
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

		if ($element === 'discussions') {

			// Uses app/event/discussions onAfterCommentSave logic and language strings since it is the same

			$stream = ES::table('Stream');
			$stream->load($comment->stream_id);

			// Get the discussion object since it's tied to the stream
			$discussion = ES::table('Discussion');
			$discussion->load($comment->uid);

			$emailOptions = array(
				'title' => 'APP_EVENT_DISCUSSIONS_EMAILS_' . strtoupper($verb) . '_COMMENT_ITEM_SUBJECT',
				'template' => 'apps/event/discussions/' . $verb . '.comment.item',
				'permalink' => $stream->getPermalink(true, true),
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true),
				'comment' => $commentContent
			);

			 $systemOptions  = array(
				'context_type' => $comment->element,
				'content' => $comment->comment,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $comment->created_by,
				'uid' => $comment->uid,
				'aggregate' => true
			);

			 // Notify the owner first
			 if ($comment->created_by != $discussion->created_by && !$comment->isChild()) {
				ES::notify('comments.item', array($discussion->created_by), $emailOptions, $systemOptions);
			 }

			 // Get a list of recipients to be notified for this stream item
			 // We exclude the owner of the discussion and the actor of the comment here
			 $recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($discussion->created_by, $comment->created_by));

			 $emailOptions['title'] = 'APP_EVENT_DISCUSSIONS_EMAILS_' . strtoupper($verb) . '_COMMENT_INVOLVED_SUBJECT';
			$emailOptions['template'] = 'apps/event/discussions/' . $verb . '.comment.involved';

			if (!$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/event/discussions/reply.comment.item';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}
		}

		if ($element === 'tasks') {
			// Uses app/event/tasks onAfterCommentSave logic and language strings since it is the same

			$identifier = $verb == 'createMilestone' ? 'milestone' : 'task';

			// Get the milestone/task table
			$table = ES::table($identifier);
			$table->load($comment->uid);

			// Get the owner
			$owner = ES::user($table->owner_id);

			// Get the event
			$event = ES::event($table->uid);

			$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($owner->id, $comment->created_by));

			$emailOptions = array(
				'title' => 'APP_EVENT_TASKS_EMAILS_COMMENTED_ON_YOUR_' . strtoupper($identifier) . '_SUBJECT',
				'template' => 'apps/event/tasks/comment.' . $identifier,
				'permalink' => FRoute::stream(array('layout' => 'item', 'id' => $comment->stream_id, 'external' => true)),
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true),
				'comment' => $commentContent
			);

			$systemOptions = array(
				'context_type' => $comment->element,
				'content' => $comment->element,
				'url' => FRoute::stream(array('layout' => 'item', 'id' => $comment->stream_id, 'sef' => false)),
				'actor_id' => $comment->created_by,
				'uid' => $comment->uid,
				'aggregate' => true
			);

			// Notify the owner first
			if ($comment->created_by != $owner->id && !$comment->isChild()) {
				ES::notify('comments.item', array($owner->id), $emailOptions, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($owner->id, $comment->created_by));

			$emailOptions['title'] = 'APP_EVENT_TASKS_EMAILS_COMMENTED_ON_A_' . strtoupper($identifier) . '_SUBJECT';
			$emailOptions['template'] = 'apps/event/tasks/comment.' . $identifier . '.involved';

			if (!$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/event/tasks/comment.task.replied';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}
		}
	}

	public function onBeforeGetStream(array &$options, $view = '')
	{
		if ($view != 'dashboard') {
			return;
		}

		// $allowedContext = array('events','story','photos', 'tasks', 'discussions', 'guests');

		// if (is_array($options['context']) && in_array('events', $options['context'])){
		// 	// we need to make sure the stream return only cluster stream.
		// 	$options['clusterType'] = SOCIAL_TYPE_EVENT;
		// } else if ($options['context'] === 'events') {
		// 	$options['context']     = $allowedContext;
		// 	$options['clusterType'] = SOCIAL_TYPE_EVENT;
		// }
	}

	public function onStreamVerbExclude(&$exclude)
	{
		$params = $this->getParams();

		$excludeVerb = array();

		if (!$params->get('stream_create', true)) {
			$excludeVerb[] = 'create';
		}

		$excludeVerb[] = 'update';


		// only clusters app for user will return as object!
		$obj = new stdClass();
		$obj->group = SOCIAL_TYPE_EVENT;
		$obj->excludeVerb = $excludeVerb;

		$exclude['events'] = $obj;

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

		// only clusters app for user will return as object!
		$obj = new stdClass();
		$obj->group = SOCIAL_TYPE_EVENT;
		$obj->excludeVerb = $excludeVerb;

		$exclude['guests'] = $obj;
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		$allowed = array('events.reminder');

		if (!in_array($item->cmd, $allowed) && !in_array($item->context_type, $allowed)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		// Event reminder
		if ($item->cmd == 'events.reminder') {
			$params = $item->getParams();
			$total = (int) $params->get('total');

			if ($total == 1) {
				$events = $params->get('events');
				$events = json_decode($events);

				$target->id = $events[0];
				$target->type = 'event';
				$target->endpoint = 'event.item';
				$target->query_string = $target->endpoint . '&id=' . $target->id;
			} else {

				// Redirect to all event that related to the user
				$target->id = $viewer->id;
				$target->type = 'profile';
				$target->endpoint = 'profile';
				$target->query_string = $target->endpoint . '&targetUserId=' . $target->id;
				$target->nav = 'events';
			}

			$item->target = $target;
		}
	}
}

class SocialEventStartendObject
{
	public $year;
	public $month;
	public $day;
	public $hour = '00';
	public $minute = '00';
	public $second = '00';

	private $date;

	public function __construct()
	{
		$args = func_get_args();

		if (empty($args)) {
			return true;
		}

		return call_user_func_array(array($this, 'load'), $args);
	}

	public function load()
	{
		$args = func_get_args();

		$count = func_num_args();

		if ($count === 1 && is_string($args[0]) && !empty($args[0])) {
			$json = ES::json();

			if ($json->isJsonString($args[0])) {
				$args[0] = $json->decode($args[0]);
			} else {
				if (strtotime($args[0])) {
					$args[0] = ES::date($args[0], false);
				}
			}
		}

		$keys = array('year', 'month', 'day', 'hour', 'minute', 'second');

		if ($count === 1 && (is_object($args[0]) || is_array($args[0]))) {
			$date = (object) $args[0];

			foreach ($keys as $key) {
				if (isset($date->$key)) {
					$this->$key = $date->$key;
				}
			}
		}

		if ($count === 1 && $args[0] instanceof SocialDate) {
			$date = $args[0];

			$this->year = $date->toFormat('Y');
			$this->month = $date->toFormat('m');
			$this->day = $date->toFormat('d');
			$this->hour = $date->toFormat('H');
			$this->minute = $date->toFormat('i');
			$this->second = $date->toFormat('s');
		}

		if ($count > 1) {
			foreach ($args as $i => $arg) {
				$this->{$keys[$i]} = $arg;
			}
		}

		$this->date = $this->toDate();

		return true;
	}

	public function isEmpty()
	{
		foreach ($this->toArray() as $k => $v) {
			// we do not want to test against the private 'date'
			if ($k == 'date') {
				continue;
			}
			if (empty($v)) {
				return true;
			}
		}

		return false;
	}

	public function isValid()
	{
		return !$this->isEmpty() && strtotime($this->day . '-' . $this->month . '-' . $this->year . ' ' . $this->hour . ':' . $this->minute . ':' . $this->second);
	}

	public function toJSON()
	{
		return ES::json()->encode($this->toArray());
	}

	public function toDate()
	{
		if (empty($this->date)) {
			if ($this->isEmpty()) {
				$this->date = ES::date();
			} else {
				$this->date = ES::date($this->year . '-' . $this->month . '-' . $this->day . ' ' . $this->hour . ':' . $this->minute . ':' . $this->second, false);
			}
		}

		return $this->date;
	}

	public function toArray($publicOnly = true)
	{
		if ($publicOnly) {
			return call_user_func('get_object_vars', $this);
		}

		return get_object_vars($this);
	}

	public function toFormat($format, $local = true)
	{
		return $this->toDate()->toFormat($format);
	}

	public function toSql()
	{
		return $this->toDate()->toSql();
	}

	public function toString()
	{
		return $this->day . ' ' . JText::_($this->toFormat('F')) . ' ' . $this->year;
	}

	public function toUnix()
	{
		return $this->toDate()->toUnix();
	}

	public function toAge()
	{
		$now = ES::date();

		$years = floor(($now->toFormat('U') - $this->toFormat('U')) / (60*60*24*365));

		return $years;
	}

	public function setTimezone($dtz)
	{
		if (empty($dtz)) {
			return $this;
		}

		if (is_string($dtz)) {
			$dtz = new DateTimeZone($dtz);
		}

		$this->date->setTimezone($dtz);
	}

	public function __toString()
	{
		return $this->isValid() ? $this->toString() : '';
	}

	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->toDate(), $method), $arguments);
	}
}

