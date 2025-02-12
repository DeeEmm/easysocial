<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/apps/apps');

class SocialEventAppTasks extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'tasks') {
			return;
		}

		// For tasks, there is not enough identifier information provided here
		// in order for us to load the task or cluster.
		// Since the data is being tempered by unwanted guest,
		// we can assume that anything beyond here is no longer accessible.
		return false;
	}

	/**
	 * Determines if the app should appear on the sidebar
	 *
	 * @since   2.0.18
	 * @access  public
	 */
	public function appListing($view, $id, $type)
	{
		// We should not display the task on the app if it's disabled
		$event = ES::event($id);

		if (!$event->canAccessTasks()) {
			return false;
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since	3.0
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		$params = $this->getParams();
		$excludeVerb = [];

		if (!$params->get('stream_task', true)) {
			$excludeVerb[] = 'createTask';
		}

		if (!$params->get('stream_milestone', true)) {
			$excludeVerb[] = 'createMilestone';
		}

		if ($excludeVerb !== false) {
			$exclude['tasks'] = $excludeVerb;
		}
	}

	/**
	 * Triggered after a comment is posted in a milestone
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('tasks.event.createMilestone', 'task.event.createTask');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the verb
		list($element, $group, $verb) = explode('.', $comment->element);

		$identifier = $verb == 'createMilestone' ? 'milestone' : 'task';

		// Get the milestone/task table
		$table = ES::table($identifier);
		$table->load($comment->uid);

		// Get the actor
		$actor = ES::user($comment->created_by);

		// Get the owner
		$owner = ES::user($table->owner_id);

		// Get the event
		$event = ES::event($table->uid);

		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($owner->id, $comment->created_by));

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
			'content' => $commentContent,
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
		$emailOptions['template'] = 'apps/event/tasks/comment.' . $identifier . '.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Processes when someone likes the stream of a milestone
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('tasks.event.createMilestone', 'tasks.event.createTask');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the verb
		list($element, $group, $verb) = explode('.', $likes->type);

		$identifier = $verb == 'createMilestone' ? 'milestone' : 'task';

		// Get the milestone/task table
		$table = ES::table($identifier);
		$table->load($likes->uid);

		// Get the actor
		$actor = ES::user($likes->created_by);
		$owner = $table->getOwner();

		$event = ES::event($table->uid);
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $group, $verb, array(), array($owner->id, $likes->created_by));

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
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $group, $verb, array(), array($owner->id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions);
	}

	/**
	 * Triggered after a event is deleted
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterDelete(SocialEvent &$event)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete all milestones related to this event
		$sql->delete('#__social_tasks_milestones');
		$sql->where('type', SOCIAL_TYPE_EVENT);
		$sql->where('uid', $event->id);

		$db->setQuery($sql);
		$db->Query();

		// Delete all tasks related to this event
		$sql->clear();
		$sql->delete('#__social_tasks');
		$sql->where('type', SOCIAL_TYPE_EVENT);
		$sql->where('uid', $event->id);

		$db->setQuery($sql);
		$db->Query();
	}

	/**
	 * Processes notification for events
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		// Get the actor
		$actor = ES::user($item->actor_id);

		// Get the event id
		$event = ES::event($item->uid);

		if (in_array($item->cmd, array('likes.item', 'likes.involved', 'comments.item', 'comments.replied', 'comments.involved')) && in_array($item->context_type, array('tasks.event.createMilestone', 'tasks.event.createTask'))) {

			$hook = $this->getHook('notification', $item->type);

			$hook->execute($item);

			return;
		}

		if ($item->cmd === 'events.task.completed') {
			// Get the milestone data
			$id = $item->context_ids;
			$task = ES::table('Task');
			$task->load($id);

			$milestone = ES::table('Milestone');
			$milestone->load($task->milestone_id);

			$item->title = JText::sprintf('APP_EVENT_TASKS_NOTIFICATIONS_USER_COMPLETED_TASK', $actor->getName(), $milestone->title);
			$item->content = $task->title;
		}

		if ($item->cmd === 'events.task.created') {
			// Get the milestone data
			$id = $item->context_ids;
			$task = ES::table('Task');
			$task->load($id);

			$milestone = ES::table('Milestone');
			$milestone->load($task->milestone_id);

			$item->title = JText::sprintf('APP_EVENT_TASKS_NOTIFICATIONS_USER_CREATED_TASK', $actor->getName(), $milestone->title);
			$item->content = $task->title;
		}

		if ($item->cmd === 'events.milestone.created') {

			// Get the milestone data
			$id = $item->context_ids;
			$milestone = ES::table('Milestone');
			$milestone->load($id);

			$item->title = JText::sprintf('APP_EVENT_TASKS_NOTIFICATIONS_USER_CREATED_MILESTONE', $actor->getName(), $event->getName());
			$item->content = $milestone->title;
		}
	}

	/**
	 * Determine if the cmd is allowed for the notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array(
			'events.task.completed',
			'events.task.created',
			'events.milestone.created',
			'comments.item',
			'comments.replied',
			'likes.item',
			'comments.involved',
			'likes.involved'
		);

		return in_array($cmd, $allowed);
	}

	/**
	 * Process after the story is saved
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		// Determine if this is for cluster event
		if (!$template->cluster_id) {
			return;
		}

		// // Now we only want to allow specific context
		$context = $template->context_type . '.' . $template->verb;
		$allowed = array('tasks.createTask');

		if (!in_array($context, $allowed)) {
			return;
		}

		$params = $this->getParams();

		// Throw some notice if the stream for new tasks is disabled
		if (!$params->get('stream_task', true)) {
			$streamItem->notice = JText::sprintf('APP_GROUP_TASKS_CREATED_SUCESS');
		}
	}

	/**
	 * Processes a saved story.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onBeforeStorySave(&$streamTemplate, &$streamItem, &$template)
	{
		// Get the link information from the request
		$items = $this->input->get('tasks_items', '', 'default');
		$milestoneId = $this->input->get('tasks_milestone', 0, 'int');
		$dueDate = $this->input->get('tasks_due', '', 'default');

		if ($dueDate) {
			$dueDate = ES::date($dueDate)->toSql();
		}

		$milestone = ES::table('Milestone');
		$milestone->load($milestoneId);

		if (!$items || empty($items) || !$milestone->id) {
			return;
		}

		// Get the event object
		$event = ES::event($streamTemplate->cluster_id);

		if (!$event->canAccessTasks() || !$event->canCreateTasks()) {
			return;
		}

		// Set the verb of the stream
		$streamTemplate->setVerb('createTask');

		$tasks = array();

		// We need to store the tasks item now.
		foreach ($items as $item) {
			if (!$item) {
				continue;
			}

			$task = ES::table('task');
			$task->title = $item;
			$task->state = SOCIAL_TASK_UNRESOLVED;
			$task->uid = $event->id;
			$task->type = SOCIAL_TYPE_EVENT;
			$task->user_id = ES::user()->id;
			$task->milestone_id = $milestone->id;
			$task->due = $dueDate;
			$task->store();

			$tasks[] = $task;
		}

		$params = ES::registry();
		$params->set('tasks', $tasks);
		$params->set('event', $event);
		$params->set('milestone', $milestone);

		$streamTemplate->setParams($params);

		return true;
	}

	/**
	 * Generates a panel for "tasks" on the story form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStoryPanel($story)
	{
		$params = $this->getApp()->getParams();
		$event = ES::event($story->cluster);

		if (!$event->canAccessTasks() || !$params->get('story_form', true) || !$this->getApp()->hasAccess($event->category_id)) {
			return;
		}

		$tasks = ES::model('Tasks');
		$milestones = $tasks->getMilestones($event->id, SOCIAL_TYPE_EVENT);

		$theme = ES::themes();

		// Create plugin object
		$plugin = $story->createPlugin('tasks', 'panel');

		$title = JText::_('COM_EASYSOCIAL_STORY_TASK');
		$plugin->title = $title;

		$theme->set('title', $plugin->title);

		// Get the button's styling
		$button = $theme->output('site/story/tasks/button');

		// Attachment script
		$script = ES::get('Script');
		$plugin->script = $script->output('site/story/tasks/plugin');

		// If there is no milestone, do not need to display the tasks embed in the story form.
		if (!$milestones) {
			$permalink = $this->getApp()->getPermalink('canvas', array('eventId' => $event->id, 'customView' => 'form'));

			// We need to attach the button to the story panel
			$theme->set('permalink', $permalink);
			$theme->set('cluster', $event);

			$form = $theme->output('site/story/tasks/empty');

			$plugin->setHtml($button, $form);

			return $plugin;
		}

		// We need to attach the button to the story panel
		$theme->set('milestones', $milestones);

		// Get the form for the app
		$form = $theme->output('site/story/tasks/form');

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/tasks/plugin'));

		return $plugin;
	}

	/**
	 * Triggered when the prepare stream is rendered
	 *
	 * @since    1.2
	 * @access    public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'tasks') {
			return;
		}

		// Event access checking
		$event = ES::event($item->cluster_id);

		if (!$event || !$event->canViewItem()) {
			return;
		}

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->repost = false;
		$verb = $item->verb;

		if ($verb == 'createTask') {
			$this->prepareCreatedTaskStream($item, $includePrivacy);
		}

		if ($verb == 'createMilestone') {
			$this->prepareCreateMilestoneStream($item, $includePrivacy);
		}
	}

	public function prepareCreatedTaskStream(SocialStreamItem $streamItem, $includePrivacy = true)
	{
		$params = ES::registry($streamItem->params);

		// Get the tasks available from the cached data
		$items = $params->get('tasks');
		$tasks = array();

		foreach ($items as $item) {
			$task = ES::table('Task');

			// We don't do bind here because we need to latest state from the database.
			// THe cached params might be an old data.
			$task->load($item->id);

			$tasks[] = $task;
		}

		// Get the milestone
		$milestone = ES::table('Milestone');
		$milestone->bind($params->get('milestone'));

		// Get the event data
		ES::load('event');

		$cluster = $streamItem->getCluster();

		$app = $this->getApp();
		$permalink = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $cluster->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $app->getAlias(), 'milestoneId' => $milestone->id));

		$this->set('permalink', $permalink);
		$this->set('item', $streamItem);
		$this->set('milestone', $milestone);
		$this->set('total', count($tasks));
		$this->set('actor', $streamItem->actor);
		$this->set('cluster', $cluster);
		$this->set('tasks', $tasks);

		$streamItem->comments = ES::comments($streamItem->contextId, SOCIAL_TYPE_POLLS, $streamItem->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => ESR::stream(array('layout' => 'item', 'id' => $streamItem->uid, 'sef' => false)), 'clusterId' => $streamItem->cluster_id), $streamItem->uid);

		$streamItem->title = parent::display('themes:/site/streams/tasks/event/create.task.title');

		$streamItem->preview = parent::display('themes:/site/streams/tasks/task.preview');

		// Append the opengraph tags
		$streamItem->addOgDescription(JText::sprintf('APP_EVENT_TASKS_STREAM_OPENGRAPH_CREATE_TASK', $streamItem->actor->getName(), $milestone->title, $cluster->getName()));
	}

	public function prepareCreateMilestoneStream(SocialStreamItem $streamItem, $includePrivacy = true)
	{
		$params = ES::registry($streamItem->params);

		$milestone = ES::table('Milestone');
		$milestone->bind($params->get('milestone'));

		// Get the event data
		$cluster = $streamItem->getCluster();

		// Get the actor
		$actor = $streamItem->actor;
		$app = $this->getApp();
		$permalink = $milestone->getPermalink();

		$access = $cluster->getAccess();
		if ($this->my->isSiteAdmin() || $cluster->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $streamItem->actor->id == $this->my->id)) {
			$streamItem->edit_link = $milestone->getEditPermalink();;
		}

		$this->set('item', $streamItem);
		$this->set('permalink', $permalink);
		$this->set('milestone', $milestone);
		$this->set('actor', $actor);
		$this->set('cluster', $cluster);

		$options = array();
		$options['clusterId'] = $streamItem->cluster_id;

		$streamItem->comments = ES::comments($streamItem->contextId, SOCIAL_TYPE_POLLS, $streamItem->verb, SOCIAL_APPS_GROUP_EVENT, $options, $streamItem->uid);

		$streamItem->title = parent::display('themes:/site/streams/tasks/event/create.milestone.title');
		$streamItem->preview = parent::display('themes:/site/streams/tasks/milestone.preview');

		// Append the opengraph tags
		$streamItem->addOgDescription(JText::sprintf('APP_EVENT_TASKS_STREAM_OPENGRAPH_CREATE_MILESTONE', $streamItem->actor->getName(), $cluster->getName()));
	}

	/**
	 * Export the notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onNotificationExport(&$item, &$content, $viewer)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		// comments and likes milestone notification
		if ($item->context_type == 'tasks.event.createMilestone') {

			// We need to get the stream id from the comments/likes
			if ($item->type == 'comments') {
				$model = ES::model('Comments');
				$comments = $model->getComments(array('element' => $item->context_type, 'uid' => $item->uid, 'limit' => 1));

				foreach ($comments as $comment) {
					$streamId = $comment->stream_id;
					break;
				}
			}

			if ($item->type == 'likes') {
				$model = ES::model('Likes');
				$likes = $model->getLikes($item->uid, $item->context_type);

				foreach ($likes as $like) {
					$streamId = $like->stream_id;
					break;
				}
			}

			$stream = ES::stream();
			$object = $stream->getItem($streamId);
			$object = $object[0];

			$content->type = 'stream';
			$content->object = $object->toExportData($viewer);

			return;
		}

		if ($item->cmd === 'events.task.completed' || $item->cmd === 'events.task.created' || $item->cmd === 'events.milestone.created') {
			$milestoneId = $item->context_ids;

			if ($item->cmd != 'events.milestone.created') {
				$id = $item->context_ids;
				$task = ES::table('Task');
				$task->load($id);

				$milestoneId = $task->milestone_id;
			}

			$milestone = ES::table('Milestone');
			$milestone->load($milestoneId);

			$content->type = 'milestone';
			$content->object = $milestone->toExportData($viewer);
		}

		return;
	}
}
