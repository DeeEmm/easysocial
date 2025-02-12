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

ES::import('admin:/tables/clusternode');

class SocialTableEventGuest extends SocialTableClusterNode
{
	/**
	 * Alias method to route to ES::user()->getName();
	 * @return  string   The guest's name.
	 */
	public function getName()
	{
		if (empty($this->type) || empty($this->uid)) {
			return '';
		}

		return call_user_func(array('ES', $this->type), $this->uid)->getName();
	}

	/**
	 * Retrieves the text of the current guest status
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getCurrentStateTitle()
	{
		if ($this->isGoing()) {
			return JText::_('COM_EASYSOCIAL_EVENTS_GUEST_GOING');
		}

		if ($this->isMaybe()) {
			return JText::_('COM_EASYSOCIAL_EVENTS_GUEST_MAYBE');
		}

		if ($this->isNotGoing()) {
			return JText::_('COM_EASYSOCIAL_EVENTS_GUEST_NOTGOING');
		}
	}

	public function isParticipant()
	{
		// This is to check if the current user is part of the event regardless of the state of the user
		return !empty($this->cluster_id);
	}

	public function isGuest()
	{
		// Guest with pending state or invited state is NOT consider a guest
		return !is_null($this->cluster_id) && !$this->isPending() && !$this->isUndecided();
	}

	/**
	 * Alias method for isGuest to ensure compatibility of events with groups.
	 * @return boolean True if user is a guest of the event.
	 */
	public function isMember()
	{
		return $this->isGuest();
	}

	public function isInvited()
	{
		return !is_null($this->cluster_id) && $this->state == SOCIAL_EVENT_GUEST_INVITED;
	}

	public function isUndecided()
	{
		return $this->state == SOCIAL_EVENT_GUEST_INVITED;
	}

	public function isGoing()
	{
		return $this->state == SOCIAL_EVENT_GUEST_GOING;
	}

	public function isMaybe()
	{
		return $this->state == SOCIAL_EVENT_GUEST_MAYBE;
	}

	public function isNotGoing()
	{
		return $this->state == SOCIAL_EVENT_GUEST_NOT_GOING;
	}

	public function isPending()
	{
		return $this->state == SOCIAL_EVENT_GUEST_PENDING;
	}

	/**
	 * Determine that the current viewer is invited by someone
	 *
	 * @since   4.0.5
	 * @access  public
	 */
	public function isInvitedByAttendee()
	{
		$isInvited = $this->invited_by != 0 ? true : false;
		return $isInvited;
	}

	/**
	 * Performs trigger when user responds to an event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function triggerResponse($method)
	{
		// Perform event trigger for this action
		// @onEventGuestResponse
		ES::apps()->load('user');

		$user = ES::user($this->uid);
		$args = array($method, &$user, $this->cluster_id);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger('user', 'onEventGuestResponse', $args);
	}

	/**
	 * Creates a new notification item
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function createNotification($action)
	{
		// To prevent unexpected callees in creating notification
		$allowed = array('going', 'maybe', 'notgoing', 'makeadmin', 'revokeadmin', 'approve', 'reject', 'request', 'withdraw', 'remove');

		if (!in_array($action, $allowed)) {
			return;
		}

		// Actor is the guest for:
		// going
		// maybe
		// notgoing
		// request
		// withdraw

		$actor = ES::user($this->uid);

		// Actor is the current logged in user for:
		// makeadmin
		// revokeadmin
		// reject
		// approve
		// remove

		if (in_array($action, array('makeadmin', 'revokeadmin', 'reject', 'approve', 'remove'))) {
			$actor = ES::user();
		}

		$event = ES::event($this->cluster_id);

		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_MAKEADMIN_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_REVOKEADMIN_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_REJECT_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_APPROVE_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_REMOVE_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_GOING_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_MAYBE_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_NOTGOING_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_REQUEST_SUBJECT
		// COM_EASYSOCIAL_EMAILS_EVENT_GUEST_WITHDRAW_SUBJECT

		// site/event/guest.makeadmin
		// site/event/guest.revokeadmin
		// site/event/guest.reject
		// site/event/guest.approve
		// site/event/guest.remove
		// site/event/guest.going
		// site/event/guest.maybe
		// site/event/guest.notgoing
		// site/event/guest.request
		// site/event/guest.withdraw

		$emailOptions = (object) array(
			'title' => 'COM_EASYSOCIAL_EMAILS_EVENT_GUEST_' . strtoupper($action) . '_SUBJECT',
			'template' => 'site/event/guest.' . strtolower($action),
			'event' => $event->getName(),
			'eventAvatar' => $event->getAvatar(),
			'eventLink' => $event->getPermalink(false, true),
			'response' => $action,
			'actor' => $actor->getName(),
			'actorLink' => $actor->getPermalink(false, true),
			'actorAvatar' => $actor->getAvatar()
		);

		$systemOptions = (object) array(
			'uid' => $this->id,
			'title' => '',
			'actor_id' => $actor->id,
			'context_ids' => $this->cluster_id,
			'context_type' => SOCIAL_TYPE_EVENT,
			'type' => 'events',
			'url' => $event->getPermalink(false, false, 'item', false),
			'image' => $event->getAvatar(),
			'response' => $action,
			'eventId' => $event->id
		);

		// Special case for request
		// If action is request, we use a different permalink instead so that the generated system notification is pointing to the guest app
		// Request email also needs to point to the guest app
		if ($action === 'request') {
			// Get the guest app first
			$guestApp = ES::table('App');
			$guestApp->load(array(
				'type' => SOCIAL_TYPE_APPS,
				'group' => SOCIAL_TYPE_EVENT,
				'element' => 'guests'
			));

			// $emailOptions->link = FRoute::events(array(
			// 	'layout' => 'item',
			// 	'id' => $event->getAlias(),
			// 	'appId' => $guestApp->getAlias(),
			// 	'type' => 'pending',
			// 	'external' => true
			// ));

			// $systemOptions->url = FRoute::events(array(
			// 	'layout' => 'item',
			// 	'id' => $event->getAlias(),
			// 	'appId' => $guestApp->getAlias(),
			// 	'type' => 'pending',
			// 	'sef' => false
			// ));

			$emailOptions->link = $event->getPermalink(true, true, 'item', true, array('appId' => $guestApp->getAlias(), 'type' => 'pending'));
			$systemOptions->url = $event->getPermalink(true, true, 'item', false, array('appId' => $guestApp->getAlias(), 'type' => 'pending'));
		}

		$targets = array();

		// If action is makeadmin, revokeadmin, reject, approve, remove, then supposed to notify the guest instead
		if (in_array($action, array('makeadmin', 'revokeadmin', 'reject', 'approve', 'remove'))) {
			$targets[] = $this->uid;
		} else {
			// Notify the event owner and admins
			$targets[] = $event->getCreator()->id;

			$admins = $event->getAdmins();

			foreach ($admins as $admin) {
				$targets[] = $admin->uid;
			}

			// Remove self, unique it, and reset the targets array
			$targets = array_values(array_unique(array_diff($targets, array(ES::user()->id))));
		}

		// Send notification now
		// events.guest.makeadmin
		// events.guest.revokeadmin
		// events.guest.reject
		// events.guest.approve
		// events.guest.remove
		// events.guest.going
		// events.guest.maybe
		// events.guest.notgoing
		// events.guest.request
		// events.guest.withdraw
		ES::notify('events.guest.' . $action, $targets, $emailOptions, $systemOptions);
	}

	/**
	 * Removes a system notification item.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function removeNotification($action)
	{
		// Query by actor_id if actions are these listed because these notifications is 1 actor to multiple targets
		// going
		// maybe
		// notgoing
		// request
		// withdraw

		if (in_array($action, array('going', 'maybe', 'notgoing', 'request', 'withdraw'))) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->delete('#__social_notifications');

			$sql->where('actor_type', SOCIAL_TYPE_USER);
			$sql->where('actor_id', $this->uid);
			$sql->where('cmd', 'events.guest.' . $action);

			$db->setQuery($sql);
			$db->query();
		}

		// These actions doesn't need to remove notification
		// makeadmin
		// revokeadmin
		// reject
		// approve
		// remove
	}

	/**
	 * Creates event stream item for event guest related action.
	 * For event item stream item, see SocialEvent::createStream();
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function createStream($action)
	{
		// To prevent unexpected callees on creating stream.
		$allowed = array('going', 'notgoing', 'makeadmin');

		if (!in_array($action, $allowed)) {
			return false;
		}

		$event = $this->getEvent();

		// Load up the stream library
		$stream = ES::stream();

		// Get the stream template
		$tpl = $stream->getTemplate();

		// Set the verb
		$tpl->setVerb($action);

		// Set the context
		// Since this is a "user" action, we set the context id to the guest node id, and the context type to guests
		$tpl->setContext($this->id, 'guests');

		// Set the privacy rule
		$tpl->setAccess('core.view');

		// Set the cluster
		$tpl->setCluster($event->id, $event->cluster_type, $event->type);

		// Set the actor
		$tpl->setActor($this->uid, $this->type);

		// Add stream template.
		$stream->add($tpl);
	}

	/**
	 * Removes event stream item for event guest related action.
	 * For event item stream item, see SocialEvent::createStream();
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function removeStream($action)
	{
		// To prevent unexpected callees on creating stream.
		$allowed = array('going', 'notgoing', 'makeadmin');

		if (!in_array($action, $allowed)) {
			return false;
		}

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream');
		$sql->column('id');
		$sql->where('cluster_id', $this->cluster_id);
		$sql->where('cluster_type', SOCIAL_TYPE_EVENT);
		$sql->where('context_type', 'guests');
		$sql->where('verb', $action);
		$sql->where('actor_id', $this->uid);
		$sql->where('actor_type', $this->type);

		$db->setQuery($sql);
		$ids = $db->loadColumn();

		if (empty($ids)) {
			return;
		}

		$sql->clear();

		$sql->delete('#__social_stream_item');
		$sql->where('uid', $ids, 'IN');

		$db->setQuery($sql);
		$db->query();

		$sql->clear();

		$sql->delete('#__social_stream');
		$sql->where('id', $ids, 'IN');

		$db->setQuery($sql);
		$db->query();
	}

	/**
	 * Allows caller to make a user as an admin
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function makeAdmin()
	{
		$state = parent::makeAdmin();

		$this->createNotification('makeadmin');

		// Create a stream for this
		$this->createStream('makeadmin', $this->uid, $this->type);

		return $state;
	}

	public function revokeAdmin()
	{
		$state = parent::revokeAdmin();

		$this->createNotification('revokeadmin');

		// Don't need to remove makeadmin notification

		// Delete makeAdmin stream
		$this->removeStream('makeadmin');

		return $state;
	}

	/**
	 * Set the status to attending
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function going()
	{
		// If already is going, then don't do anything
		if ($this->isGoing()) {
			return true;
		}

		$this->state = SOCIAL_EVENT_GUEST_GOING;

		$state = $this->store();

		if (!$state) {
			return false;
		}

		// Perform event trigger for this action
		$this->triggerResponse(__FUNCTION__);

		// Remove other state notification
		$this->removeNotification('notgoing');
		$this->removeNotification('maybe');

		// Send notification to event owner that the user will be attending the event
		$this->createNotification('going');

		// Remove notgoing response stream
		// $this->removeStream('notgoing');

		// Create stream
		$this->createStream('going');

		// Assign points for the user
		ES::points()->assign('events.going', 'com_easysocial', $this->uid);

		return $state;
	}

	/**
	 * Set the status to maybe
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function maybe()
	{
		// If already is maybe, then don't do anything
		if ($this->isMaybe()) {
			return true;
		}

		$this->state = SOCIAL_EVENT_GUEST_MAYBE;

		$state = $this->store();

		if (!$state) {
			return false;
		}

		// Perform event trigger for this action
		$this->triggerResponse(__FUNCTION__);

		// Remove other state notification
		$this->removeNotification('notgoing');
		$this->removeNotification('going');


		// Send notification to event owner that the user set the status to "maybe"
		$this->createNotification('maybe');

		// Remove other response streams avoid spanning multiple stream items
		$this->removeStream('going');
		$this->removeStream('notgoing');

		return $state;
	}

	/**
	 * Set the status to not going
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function notGoing($hasAttendBefore = false)
	{
		// If already is not going, then don't do anything
		if ($this->isNotGoing()) {
			return true;
		}

		// Allow settings to decide if guest do not want to go, directly remove them from the event.
		// That part is controlled in controller/events

		$this->state = SOCIAL_EVENT_GUEST_NOT_GOING;

		$state = $this->store();

		if (!$state) {
			return false;
		}

		// Perform event trigger for this action
		$this->triggerResponse(__FUNCTION__);

		// Remove other state notification
		$this->removeNotification('going');
		$this->removeNotification('maybe');

		// Send notification to event owner that the user set the status to "not going"
		$this->createNotification('notgoing');

		// Remove "going" stream to avoid spanning multiple stream items
		$this->removeStream('going');

		// Create stream
		// $this->createStream('notgoing');

		// Only assign the point for the user if they attend the event before
		if ($hasAttendBefore) {
			ES::points()->assign('events.notgoing', 'com_easysocial', $this->uid);
		}

		return $state;
	}

	/**
	 * Same as going but different notification.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function approve()
	{
		// If the user request to join the event, means that he/she wants to go, hence if admin approve a pending request, the state should be going
		$this->state = SOCIAL_EVENT_GUEST_GOING;

		$state = $this->store();

		if (!$state) {
			return false;
		}

		// Notification
		$this->createNotification('approve');

		// Create stream
		$this->createStream('going');

		// Perform event trigger for this action
		$this->triggerResponse('going');

		return $state;
	}

	/**
	 * Rejects a guest request to join the event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function reject()
	{
		$state = $this->delete();

		if (!$state) {
			return false;
		}

		// Notification
		$this->createNotification('reject');

		return $state;
	}

	/**
	 * Request to join an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function request()
	{
		// If already is pending, then don't do anything
		if ($this->isPending()) {
			return true;
		}

		$this->state = SOCIAL_EVENT_GUEST_PENDING;

		$state = $this->store();

		if (!$state) {
			return false;
		}

		// Notification
		$this->createNotification('request');

		return $state;
	}

	/**
	 * Deletes a guest record for the requests
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function withdraw()
	{
		$state = $this->delete();

		if (!$state) {
			return false;
		}

		$this->removeNotification('going');
		$this->removeNotification('maybe');

		$this->createNotification('withdraw');

		$this->removeStream('going');
		$this->removeStream('notgoing');
		$this->removeStream('request');

		return $state;
	}

	public function remove()
	{
		// Determine whether the deleting user have attend this event before
		$event = $this->getEvent();
		$hasAttendBefore = $event->isAttending($this->uid);

		// Delete the attendee now
		$state = $this->delete();

		if (!$state) {
			return false;
		}

		// Notification
		$this->createNotification('remove');

		// Remove stream
		$this->removeStream('going');
		$this->removeStream('notgoing');
		$this->removeStream('makeadmin');

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onEventGuestAfterRemoved', [$this]);

		// Only remove the RSVP attend point if the user attend the event before
		if ($hasAttendBefore) {
			ES::points()->assign('events.notgoing', 'com_easysocial', $this->uid);
		}

		return $state;
	}

	public function getEvent()
	{
		return ES::event($this->cluster_id);
	}

	/**
	 * Method to update the cached sef alias when there
	 * is changes on the alias column
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateAliasSEFCache()
	{
		// Do nothing for now to satisfy __call function.
	}

	/**
	 * Method to delete the cached sef alias when item being removed.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function deleteSEFCache()
	{
		// Do nothing for now to satisfy __call function.
	}

	public function __call($method, $args)
	{
		$user = ES::user($this->uid);

		if (!is_callable(array($user, $method))) {
			throw new Exception("No such method", 1);
		}

		return call_user_func_array(array($user, $method), $args);
	}

	/**
	 * Formats data that can be exported
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $extended = false)
	{
		static $cache = array();

		$key = $this->id . $viewer->id . (int) $extended;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		// Load user
		$user = parent::toExportData($viewer, $extended);
		$user->state = $this->state;
		$user->owner = $this->owner;
		$user->admin = $this->admin;
		$user->invited_by = $this->invited_by;
		$user->created = $this->created;

		$cache[$key] = $user;

		return $cache[$key];
	}

	/**
	 * Retrieve the invitor user data
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function getInvitor($userId)
	{
		static $invites = array();

		if (!isset($invites[$userId])) {
			$node = ES::table('ClusterNode');
			$node->load(array('uid' => $userId, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $this->id));

			// Get the invitor
			$invitor = ES::user($node->invited_by);
			$invites[$userId] = $invitor;
		}

		return $invites[$userId];
	}
}
