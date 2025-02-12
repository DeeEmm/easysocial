<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppEvents extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_EVENTS) {
			return;
		}

		return false;
	}

	/**
	 * Determines if this app should be visible in the page page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function appListing($view, $pageId, $type)
	{
		$page = ES::page($pageId);

		if (!$this->config->get('events.enabled') || !$page->canAccessEvents()) {
			return false;
		}

		return $page->getAccess()->get('events.pageevent', true);
	}

	public function onPrepareStoryPanel($story)
	{
		$params = $this->getParams();

		if (!$this->config->get('events.enabled') || $story->clusterType != SOCIAL_TYPE_PAGE || !$params->get('story_event', true)) {
			return;
		}

		 // Ensure that the page category has access to create events
		$page = ES::page($story->cluster);

		if (!$page->canCreateEvent() || !$page->canAccessEvents()) {
			return;
		}

		// Prevent creating scheduled event from story post.
		if ($story->isScheduling()) {
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

	public function onBeforeStorySave(&$template, &$stream, &$content)
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_event', true)) {
			return;
		}

		$input = ES::input();

		$title = $input->getString('event_title');
		$description = $input->getString('event_description');
		$categoryid = $input->getInt('event_category');
		$start = $input->getString('event_start');
		$end = $input->getString('event_end');
		$timezone = $input->getString('event_timezone');

		// If no category id, then we don't proceed
		if (empty($categoryid)) {
			return;
		}

		// Perhaps in the future we use ES::model('Event')->createEvent() instead.
		// For now just hardcode it here to prevent field triggering and figuring out how to punch data into the respective field data because the form is not rendered through field trigger.

		$my = ES::user();
		$event = ES::event();
		$model = ES::model('Events');
		$event->title = $title;
		$event->description = $description;

		// event type will always follow page type
		$page = ES::page($template->cluster_id);

		// Check for permission
		if (!$page->canCreateEvent()) {
			return false;
		}

		// Set a default params for this event first
		$event->params = '{"photo":{"albums":true},"news":true,"discussions":true,"allownotgoingguest":true,"allowmaybe":true,"guestlimit":0}';

		// event type will always follow page type
		$event->type = $page->type;
		$event->creator_uid = $my->id;
		$event->creator_type = SOCIAL_TYPE_USER;
		$event->category_id = $categoryid;
		$event->cluster_type = SOCIAL_TYPE_EVENT;
		$event->alias = $model->getUniqueAlias($title);
		$event->created = ES::date()->toSql();
		$event->key = md5($event->created . $my->password . uniqid());

		$event->state = SOCIAL_CLUSTER_PUBLISHED;

		// Trigger apps
		ES::apps()->load(SOCIAL_TYPE_USER);

		$dispatcher = ES::dispatcher();
		$triggerArgs = array(&$event, &$my, true);

		// @trigger: onEventBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventBeforeSave', $triggerArgs);

		$state = $event->save();

		// Notifies admin when a new event is created
		if ($event->state === SOCIAL_CLUSTER_PENDING || !$my->isSiteAdmin()) {
			$model->notifyAdmins($event);
		}

		// Process the event meta
		$this->processEventMeta($event, $start, $end, $timezone, $page->id);

		// Recreate the event object
		SocialEvent::$instances[$event->id] = null;
		$event = ES::event($event->id);

		// Create a new owner object
		$event->createOwner($my->id);

		// Transfer members from page
		$model->transferPageMembers($event, false, 'invite');

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

	public function processEventMeta($event, $startDatetime, $endDatetime, $timezone, $pageId)
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
		$eventMeta->start_gmt = $startGMT;
		$eventMeta->end_gmt = $endGMT;
		$eventMeta->timezone = !empty($timezone) ? $timezone : '';
		$eventMeta->all_day = 0;

		// Set the page id
		$eventMeta->page_id = $pageId;

		$eventMeta->store();
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

	public function onBeforeGetStream(&$options, $view = '')
	{
		if ($view != 'pages') {
			return;
		}

		$layout = $this->input->get('layout', '', 'string');

		if ($layout == 'category') {
			// if this is viewing page category page, we ignore the events stream for pages.
			return;
		}

		// Check if there are any page events
		$pageEvents = ES::model('Events')->getEvents(array(
			'page_id' => $options['clusterId'],
			'state' => SOCIAL_STATE_PUBLISHED,
			'idonly' => true
		));

		if (count($pageEvents) == 0) {
			return;
		}

		// Support in getting event stream as well
		if (!is_array($options['clusterType'])) {
			$options['clusterType'] = array($options['clusterType']);
		}

		if (!in_array(SOCIAL_TYPE_EVENT, $options['clusterType'])) {
			$options['clusterType'][] = SOCIAL_TYPE_EVENT;
		}

		if (!is_array($options['clusterId'])) {
			$options['clusterId'] = array($options['clusterId']);
		}

		$options['clusterId'] = array_merge($options['clusterId'], $pageEvents);
	}
}
