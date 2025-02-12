<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class DiscussionsControllerDiscussion extends SocialAppsController
{
	/**
	 * Displays the lock confirmation dialog
	 *
	 * @since    1.2
	 * @access    public
	 * @return
	 */
	public function confirmLock()
	{
		// Check for request forgeriess
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		$theme     = ES::themes();
		$output    = $theme->output('apps/event/discussions/canvas/dialog.lock');

		return $this->ajax->resolve($output);
	}

	/**
	 * Deletes a discussion
	 *
	 * @since    1.2
	 * @access    public
	 */
	public function delete()
	{
		// Check for request forgeries
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Get the discussion object
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Get the event object
		$eventId = $this->input->get('eventId', 0, 'int');
		$event = ES::event($eventId);


		if (!$event->isAdmin() && $discussion->created_by != $this->my->id && !$this->my->isSiteAdmin()) {
			return $this->redirect($event->getPermalink());
		}

		// Delete the discussion
		$discussion->delete();

		// @points: events.discussion.delete
		// Deduct points from the discussion creator when the discussion is deleted
		$points = ES::points();
		$points->assign('events.discussion.delete', 'com_easysocial', $discussion->created_by);

		ES::info()->set(JText::_('APP_EVENT_DISCUSSIONS_DISCUSSION_DELETED_SUCCESS'));

		// After deleting, we want to redirect to the discussions listing
		// $url = FRoute::events(array('layout' => 'item', 'id' => $event->getAlias() , 'appId' => $this->getApp()->id), false);
		$url = $event->getPermalink(false, false, 'item', true, array('appId' => $this->getApp()->id));

		// Perform a redirection
		$this->redirect($url);
	}

	/**
	 * Displays the delete confirmation dialog
	 *
	 * @since    1.2
	 * @access    public
	 * @return
	 */
	public function confirmDelete()
	{
		// Check for request forgeriess
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Get the discussion object
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Get the event object
		$eventId = $this->input->get('eventId', 0, 'int');
		$event = ES::event($eventId);

		$theme = ES::themes();

		$theme->set('appId', $this->getApp()->id);
		$theme->set('discussion', $discussion);
		$theme->set('event', $event);
		$output = $theme->output('apps/event/discussions/canvas/dialog.delete.discussion');

		return $this->ajax->resolve($output);
	}

	/**
	 * Executes the locking of a discussion
	 *
	 * @since    1.2
	 * @access    public
	 * @return
	 */
	public function lock()
	{
		// Check for request forgeriess
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Load the discussion
		$id = $this->input->get('id', 0, 'int');

		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Get the event
		$event = ES::event($discussion->uid);

		// Get the current logged in user.
		$my = ES::user();

		// Check if the viewer can really lock the discussion.
		if (!$event->getGuest()->isAdmin() && !$my->isSiteAdmin()) {
			return $this->redirect($event->getPermalink(false));
		}

		// Lock the discussion
		$discussion->lock();

		// Create a new stream item for this discussion
		$stream = ES::stream();

		// Get the stream template
		$tpl = $stream->getTemplate();

		// Set the actor
		$tpl->setActor($my->id, SOCIAL_TYPE_USER);

		// Set the context
		$tpl->setContext($discussion->id, 'discussions');

		// Set the cluster
		$tpl->setCluster($event->id, SOCIAL_TYPE_EVENT);

		// Set the verb
		$tpl->setVerb('locked');

		// Set the params to cache the group data
		$registry = ES::registry();
		$registry->set('event', $event);
		$registry->set('discussion', $discussion);

		$tpl->setParams($registry);

		// Add the stream
		$stream->add($tpl);

		return $this->ajax->resolve($discussion);
	}

	/**
	 * Creates a new discussion
	 *
	 * @since    1.2
	 * @access    public
	 * @param    string
	 * @return
	 */
	public function save()
	{
		// Check for request forgeriess
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Load up ajax lib
		$ajax = ES::ajax();

		// Load the discussion
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Get the current logged in user.
		$my = ES::user();

		// Get the group
		$eventId = $this->input->get('cluster_id', 0, 'int');
		$event = ES::event($eventId);

		// Only allow owner and admin to modify the
		if ($discussion->id && $discussion->created_by != $my->id && !$event->getGuest()->isAdmin() && !$my->isSiteAdmin()) {
			return $this->redirect($event->getPermalink(false));
		}

		// Check if the user is allowed to create a discussion
		if (!$event->getGuest()->isMember()) {
			ES::info()->set(JText::_('APP_EVENT_DISCUSSIONS_NOT_ALLOWED_CREATE'), SOCIAL_MSG_ERROR);

			// Perform a redirection
			return $this->redirect(FRoute::dashboard());
		}

		// Assign discussion properties
		$discussion->uid = $event->id;
		$discussion->type = SOCIAL_TYPE_EVENT;
		$discussion->title = $this->input->get('title', '', 'string');
		$discussion->content = $this->input->get('content', '', 'raw');

		// If discussion is edited, we don't want to modify the following items
		if (!$discussion->id)
		{
			$discussion->created_by = $my->id;
			$discussion->parent_id = 0;
			$discussion->hits = 0;
			$discussion->state = SOCIAL_STATE_PUBLISHED;
			$discussion->votes = 0;
			$discussion->lock = false;
		}

		// Lock the discussion
		$state = $discussion->store();

		if (!$state) {
			ES::info()->set(JText::_('APP_EVENT_DISCUSSIONS_DISCUSSION_CREATED_FAILED'));

			// Get the redirection url
			$url = FRoute::apps(array('layout' => 'canvas', 'customView' => 'form', 'uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $app->getAlias()), false);

			return $this->redirect($url);
		}

		// Process any files that needs to be created.
		$discussion->mapFiles();

		// If it is a new discussion, we want to run some other stuffs here.
		if (!$id) {
			// @points: events.discussion.create
			// Add points to the user that updated the event
			$points = ES::points();
			$points->assign('events.discussion.create', 'com_easysocial', $my->id);

			// Create a new stream item for this discussion
			$stream = ES::stream();

			// Get the stream template
			$tpl = $stream->getTemplate();

			// Someone just joined the group
			$tpl->setActor($my->id, SOCIAL_TYPE_USER);

			// Set the context
			$tpl->setContext($discussion->id, 'discussions');

			// Set the cluster
			$tpl->setCluster($event->id, SOCIAL_TYPE_EVENT);

			// Set the verb
			$tpl->setVerb('create');

			// Set the params to cache the group data
			$registry = ES::registry();
			$registry->set('event', $event);
			$registry->set('discussion', $discussion);

			$tpl->setParams($registry);

			// Add the stream
			$stream->add($tpl);
		}

		// Get the app
		$app = $this->getApp();

		// Get the redirection url
		$url = FRoute::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $app->getAlias(), 'discussionId' => $discussion->id), false);

		ES::info()->set(JText::_('APP_EVENT_DISCUSSIONS_DISCUSSION_CREATED_SUCCESS'));

		// Send notification to group members
		$options = array(
			'permalink' => FRoute::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $app->getAlias(), 'discussionId' => $discussion->id, 'external' => true), false),
			'discussionId' => $discussion->id,
			'discussionTitle' => $discussion->title,
			'discussionContent' => $discussion->getContent(),
			'userId' => $discussion->created_by
		);

		$event->notifyMembers('discussion.create', $options);

		// Perform a redirection
		$this->redirect($url);
	}

	/**
	 * Retrieves the list of discussions
	 *
	 * @since    1.2
	 * @access    public
	 */
	public function getDiscussions()
	{
		// Check for request forgeriess
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Load the discussion
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		// Check if the viewer can really browse discussions from this group.
		if (!$event->isOpen() && !$event->getGuest()->isGuest()) {
			ES::info()->set(JText::_('APP_EVENT_DISCUSSIONS_NOT_ALLOWED_VIEWING'), SOCIAL_MSG_ERROR);

			// Perform a redirection
			return $this->redirect(FRoute::dashboard());
		}

		// Get the current filter type
		$filter = $this->input->get('filter', 'all', 'word');
		$options = array();

		if ($filter == 'unanswered') {
			$options['unanswered'] = true;
		}

		if ($filter == 'locked') {
			$options['locked'] = true;
		}

		if ($filter == 'resolved') {
			$options['resolved'] = true;
		}

		// Get the current group app
		$app = $this->getApp();
		$params = $app->getParams();

		// Get total number of discussions to display
		$options['limit'] = $params->get('total', 10);

		$model = ES::model('Discussions');
		$discussions = $model->getDiscussions($event->id, SOCIAL_TYPE_EVENT, $options);
		$pagination  = $model->getPagination();

		$pagination->setVar('view', 'events');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $event->getAlias());
		$pagination->setVar('appId', $this->getApp()->id);
		$pagination->setVar('filter', $filter);


		$theme = ES::themes();

		$theme->set('params', $params);
		$theme->set('pagination', $pagination);
		$theme->set('app', $app);
		$theme->set('event', $event);
		$theme->set('discussions', $discussions);

		$contents = $theme->output('apps/event/discussions/events/default.list');

		$empty = empty($discussions);
		return $this->ajax->resolve($contents, $empty);
	}

	/**
	 * Executes the locking of a discussion
	 *
	 * @since    1.2
	 * @access    public
	 * @return
	 */
	public function unlock()
	{
		// Check for request forgeriess
		ES::checkToken();

		// Ensure that the user is logged in.
		ES::requireLogin();

		// Load the discussion
		$id = $this->input->get('id', 0, 'int');

		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Get the group
		$event = ES::event($discussion->uid);

		// Get the current logged in user.
		$my = ES::user();

		// Check if the viewer can really lock the discussion.
		if (!$event->getGuest()->isAdmin() && !$my->isSiteAdmin()) {
			return $this->redirect($event->getPermalink(false));
		}

		// Lock the discussion
		$discussion->unlock();

		return $this->ajax->resolve($discussion);
	}
}
