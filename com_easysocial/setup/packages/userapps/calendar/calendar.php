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

ES::import('admin:/includes/apps/apps');

class SocialUserAppCalendar extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'calendar') {
			return;
		}

		$my = ES::user();
		$privacyLib = $my->getPrivacy();

		$calendar = ES::table('Calendar');
		$calendar->load($uid);

		if (!$calendar->id) {
			// calendar not found.
			return false;
		}

		// Ensure that the viewer is really allowed to view the calendar
		if (!$privacyLib->validate('apps.calendar', $calendar->id, 'calendar', $calendar->user_id)) {
			return false;
		}

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation( &$item, $includePrivacy = true )
	{
		// If this is not it's context, we don't want to do anything here.
		if( $item->context_type != 'calendar' )
		{
			return false;
		}

		$item->cnt = 1;

		if( $includePrivacy )
		{
			$uid		= $item->id;
			$my         = ES::user();
			$privacy	= ES::privacy( $my->id );

			$sModel = ES::model( 'Stream' );
			$aItem 	= $sModel->getActivityItem( $item->id, 'uid' );

			if( $aItem )
			{
				$uid 	= $aItem[0]->id;

				if( !$privacy->validate( 'core.view', $uid , SOCIAL_TYPE_ACTIVITY , $item->actor_id ) )
				{
					$item->cnt = 0;
				}
			}
		}

		return true;
	}

	/**
	 * Prepares the activity log item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'calendar') {
			return;
		}

		// Get the context id.
		$calendar = ES::table('calendar');
		$calendar->load($item->contextId);

		$permalink = $calendar->getPermalink();

		$this->set('permalink', $permalink);
		$this->set('calendar', $calendar);

		$item->title = parent::display('logs/' . $item->verb . '.title');
		$item->content = '';
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = [];

		if(! $params->get('stream_create', true)) {
			$excludeVerb[] = 'create';
		}

		if (! $params->get('stream_update', true)) {
			$excludeVerb[] = 'update';
		}

		if ($excludeVerb !== false) {
			$exclude['calendar'] = $excludeVerb;
		}
	}

	/**
	 * Triggers when an event is created
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onEventAfterSave(SocialEvent &$event, SocialUser &$author, $isNew)
	{
		$eventstart = $event->getEventStart();
		$eventend = $event->getEventEnd();

		// When a new event is created, we want to ensure that it's stored in the user's calendar
		if ($isNew) {

			// Ensure that the start and end date is set
			if (!$eventstart && !$eventend) {
				return;
			}

			$calendar = ES::table('Calendar');

		} else {

			// Update the calendar
			$calendar = ES::table('Calendar');
			$calendar->load(array('uid' => $event->id, 'type' => SOCIAL_TYPE_EVENT));

			// Delete the record if no startend
			if ($calendar->id && !$eventstart && !$eventend) {
				$calendar->delete();
				return;
			}
		}

		$calendar->title = $event->getName();
		$calendar->description = $event->description;
		$calendar->uid = $event->id;
		$calendar->type = SOCIAL_TYPE_EVENT;
		$calendar->date_start = $eventstart->toSql();
		$calendar->date_end = $eventend->toSql();
		$calendar->user_id = $author->id;
		$calendar->all_day = $event->isAllDay() ? 1 : 0;

		$calendar->store();
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context !== 'calendar') {
			return;
		}

		// Determine if we should display create stream
		$params = $this->getParams();
		$allowed = array('create', 'update');
		$verb = $item->verb;

		if (!in_array($verb, $allowed) || !$params->get('stream_' . $verb, true)) {
			return;
		}

		// Load the calendar
		$calendar = ES::table('Calendar');
		$calendar->load((int) $item->contextId);

		if (!$calendar->id) {
			return;
		}

		// Respect user's calendar privacy
		$privacy = $this->my->getPrivacy();

		// We need to check for the calendar privacy
		if ($includePrivacy && !$privacy->validate('apps.calendar', $calendar->id, 'calendar', $item->actor->id)) {
			return;
		}

		// Apply actions on the stream
		$item->likes = ES::likes($item->contextId , 'calendar', $item->verb, SOCIAL_APPS_GROUP_USER, $item->uid );
		$item->comments = ES::comments($item->contextId, 'calendar' , $item->verb, SOCIAL_APPS_GROUP_USER, array('url' => $calendar->getPermalink(false, true, false)), $item->uid);

		$app = $this->getApp();

		// Get the term to be displayed
		$genderValue = $item->actor->getFieldData('GENDER');
		$gender = 'THEIR';

		if ($genderValue == 1) {
			$gender = 'MALE';
		}

		if ($genderValue == 2) {
			$gender = 'FEMALE';
		}

		$timeformat = $params->get('agenda_timeformat', '12') == '24' ? JText::_('COM_EASYSOCIAL_DATE_DMY24H') : JText::_('COM_EASYSOCIAL_DATE_DMY12H');

		$eventPermalink = ESR::apps(array('layout' => 'canvas', 'id' => $app->getAlias(), 'uid' => $item->actor->getAlias(), 'type' => SOCIAL_TYPE_USER, 'customView' => 'item', 'schedule_id' => $calendar->id));
		$calendarPermalink = ESR::profile(array('id' => $item->actor->getAlias(), 'appId' => $app->getAlias()));

		$this->set('timeformat', $timeformat);
		$this->set('gender', $gender);
		$this->set('app', $app);
		$this->set('calendar', $calendar);
		$this->set('actor', $item->actor);
		$this->set('params', $params);
		$this->set('eventPermalink', $eventPermalink);
		$this->set('calendarPermalink', $calendarPermalink);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = parent::display('themes:/site/streams/calendar/' . $item->verb . '.title');
		$item->preview = parent::display('themes:/site/streams/calendar/preview');

		if ($includePrivacy) {
			$item->privacy 	= $privacy->form($item->contextId, 'calendar', $item->actor->id, 'apps.calendar', false, $item->uid, array(), array('iconOnly' => true));
		}
	}

	/**
	 * Sends notification on new comments
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onAfterCommentSave($comment)
	{
		$allowed = array('calendar.user.create', 'calendar.user.update');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		$segments = explode('.', $comment->element);
		$verb = $segments[2];

		// Stream is passing context id to comment, hence comment uid is the calendar item id directly.
		$calendar = ES::table('Calendar');
		$calendar->load($comment->uid);

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
			'title' => 'APP_USER_CALENDAR_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/user/calendar/comment.item',
			'comment' => $commentContent,
			'permalink' => $calendar->getPermalink(true, true)
		);

		$systemOptions = array(
			'title' => '',
			'content' => $commentContent,
			'context_type' => $comment->element,
			'url' => $calendar->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		if ($calendar->user_id != $comment->created_by && !$comment->isChild()) {
			ES::notify('comments.item', array($calendar->user_id), $emailOptions, $systemOptions);
		}

		$recipients = array();
		$users = $this->getStreamNotificationTargets($comment->uid, 'calendar', 'user', $verb, array(), array($calendar->user_id, $comment->created_by));

		if ($users) {
			// we need to make sure all the participants still has the access to view this calendar or not.

			foreach ($users as $r) {
				$rUser = $r;

				if (!$r instanceof SocialUser) {
					$rUser = ES::user($r);
				}

				$privacy = $rUser->getPrivacy();

				// Ensure that the viewer is really allowed to view the calendar
				if ($privacy->validate('apps.calendar', $calendar->id, 'calendar', $calendar->user_id)) {
					$recipients[] = $rUser;
				}
			}
		}

		if ($recipients && !$comment->isChild()) {
			$emailOptions['title'] = 'APP_USER_CALENDAR_EMAILS_COMMENT_INVOLVED_TITLE';
			$emailOptions['template'] = 'apps/user/calendar/comment.involved';

			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/calendar/comment.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Sends notification on new likes
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onAfterLikeSave($likes)
	{
		// calendar.user.create
		// calendar.user.update

		$allowed = array('calendar.user.create', 'calendar.user.update');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		$segments = explode('.', $likes->type);
		$verb = $segments[2];

		$calendar = ES::table('calendar');
		$calendar->load($likes->uid);

		$systemOptions 	= array(
			'title' => '',
			'context_type' => $likes->type,
			'url' => $calendar->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		if ($calendar->user_id != $likes->created_by) {
			ES::notify('likes.item', array($calendar->user_id), array(), $systemOptions);
		}

		$recipients = array();
		$users = $this->getStreamNotificationTargets($likes->uid, 'calendar', 'user', $verb, array(), array($calendar->user_id, $likes->created_by));

		if ($users) {
			// we need to make sure all the participants still has the access to view this calendar or not.

			foreach ($users as $r) {
				$rUser = $r;

				if (!$r instanceof SocialUser) {
					$rUser = ES::user($r);
				}

				$privacy = $rUser->getPrivacy();

				// Ensure that the viewer is really allowed to view the calendar
				if ($privacy->validate('apps.calendar', $calendar->id, 'calendar', $calendar->user_id)) {
					$recipients[] = $rUser;
				}
			}
		}

		if ($recipients) {
			ES::notify('likes.involved', $recipients, array(), $systemOptions);
		}
	}

	/**
	 * Processes notifications
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('calendar.user.create', 'calendar.user.update');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		$calendar = ES::table('calendar');
		$state = $calendar->load($item->uid);

		if (!$state) {
			return;
		}

		$hook = $this->getHook('notification', $item->type);
		$hook->execute($item, $calendar);
	}
}
