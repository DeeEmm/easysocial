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

class SocialGroupAppTasks extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_TASKS) {
			return;
		}

		return false;
	}

	/**
	 * Determines if the app should appear on the sidebar
	 *
	 * @since	2.0.18
	 * @access	public
	 */
	public function appListing($view, $id, $type)
	{
		// We should not display the task on the app if it's disabled
		$group = ES::group($id);

		if (!$group->canAccessTasks()) {
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
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('tasks.group.createMilestone');

		if (!in_array($comment->element, $allowed)) {
			return;
		}
		// Get the verb
		$segments = explode('.', $comment->element);
		$verb = $segments[2];

		// Get the milestone
		$milestone = ES::table('Milestone');
		$milestone->load($comment->uid);

		// Get the group
		$group = ES::group($milestone->uid);

		// Get a list of recipients
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'tasks', 'group', $verb, array(), array($milestone->owner_id, $comment->created_by));

		// Get the actor of the likes
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

		// okay since comment on group task can be made to 'task.group.createmilestones' and can only be commented via stream item,
		// also, currently milestone page do not display any comments, thus the link have to go to stream item page to see the comment.
		// @2014-07-02, Sam

		$emailOptions = array(
			'title' => 'APP_GROUP_TASKS_EMAILS_COMMENTED_ON_YOUR_MILESTONE_TITLE',
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'template' => 'apps/group/tasks/comment.milestone',
			'permalink' => FRoute::stream(array('layout' => 'item', 'id' => $comment->stream_id, 'external' => true, 'xhtml' => true)),
			'comment' => $commentContent
		);

		$systemOptions = array(
			'title' => '',
			'content' => $comment->comment,
			'context_type' => $comment->element,
			'url' => FRoute::stream(array('layout' => 'item', 'id' => $comment->stream_id)),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		// Notify the owner first
		if ($comment->created_by != $milestone->owner_id && !$comment->isChild()) {
			ES::notify('comments.item', array($milestone->owner_id), $emailOptions, $systemOptions, $group->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'tasks', 'group', $verb, array(), array($milestone->owner_id, $comment->created_by));

		$emailOptions['title'] = 'APP_GROUP_TASKS_EMAILS_COMMENTED_ON_USERS_MILESTONE_TITLE';
		$emailOptions['template'] = 'apps/group/tasks/comment.milestone.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $group->notification);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/group/tasks/comment.milestone.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions, $group->notification);
		}
	}

	/**
	 * Triggered after a group is deleted
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterDelete(SocialGroup &$group)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete all milestones related to this group
		$sql->delete('#__social_tasks_milestones');
		$sql->where('type', SOCIAL_TYPE_GROUP);
		$sql->where('uid', $group->id);

		$db->setQuery($sql);
		$db->Query();

		// Delete all tasks related to this group
		$sql->clear();
		$sql->delete('#__social_tasks');
		$sql->where('type', SOCIAL_TYPE_GROUP);
		$sql->where('uid', $group->id);

		$db->setQuery($sql);
		$db->Query();
	}

	/**
	 * Processes when someone likes the stream of a milestone
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('tasks.group.createMilestone');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the verb
		$segments = explode('.', $likes->type);
		$verb = $segments[2];

		if ($likes->type == 'tasks.group.createMilestone') {

			// Get the milestone
			$milestone = ES::table('Milestone');
			$milestone->load($likes->uid);

			// Get the group
			$group = ES::group($milestone->uid);

			// Get a list of recipients
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'tasks', 'group', $verb, array(), array($milestone->owner_id, $likes->created_by));

			// okay since likes on group task can be made to 'task.group.createmilestones' and can only be liked via stream item,
			// also, currently milestone page do not display any likes, thus the link have to go to stream item page to see the likes.
			// @2014-07-02, Sam

			$systemOptions = array(
				'title' => '',
				'context_type' => $likes->type,
				'url' => ESR::stream(array('layout' => 'item', 'id' => $likes->stream_id)),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// Notify the owner first
			if ($likes->created_by != $milestone->owner_id) {
				ES::notify('likes.item', array($milestone->owner_id), false, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'tasks', 'group', $verb, array(), array($milestone->owner_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);
		}


	}

	/**
	 * Processes notification for groups
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

		// Get the group id
		$group = ES::group($item->uid);

		if (in_array($item->type, array('likes', 'comments'))) {
			// Check if context_type is correct
			$segments = explode('.', $item->context_type);

			if (count($segments) === 3 && $segments[0] === 'tasks' && $segments[1] === 'group') {
				$hook = $this->getHook('notification', $item->type);
				$hook->execute($item);
				return;
			}
		}

		if ($item->cmd === 'group.task.completed') {
			// Get the milestone data
			$id = $item->context_ids;
			$task = ES::table('Task');
			$task->load($id);

			$milestone = ES::table('Milestone');
			$milestone->load($task->milestone_id);

			$item->title = JText::sprintf('APP_GROUP_TASKS_NOTIFICATIONS_USER_COMPLETED_TASK', $actor->getName(), $milestone->title);
			$item->content = $task->title;
		}

		if ($item->cmd === 'group.task.create') {
			// Get the milestone data
			$id = $item->context_ids;
			$task = ES::table('Task');
			$task->load($id);

			$milestone = ES::table('Milestone');
			$milestone->load($task->milestone_id);

			$item->title = JText::sprintf('APP_GROUP_TASKS_NOTIFICATIONS_USER_CREATED_TASK', $actor->getName(), $milestone->title);
			$item->content = $task->title;
		}

		if ($item->cmd === 'group.milestone.create') {
			// Get the milestone data
			$id = $item->context_ids;
			$milestone = ES::table('Milestone');
			$milestone->load($id);

			$item->title = JText::sprintf('APP_GROUP_TASKS_NOTIFICATIONS_USER_CREATED_MILESTONE', $actor->getName(), $group->getName());
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
		$allowed = array('group.milestone.create', 'group.task.create', 'group.task.completed', 'comments.item', 'comments.replied', 'likes.item', 'comments.involved', 'likes.involved');

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
		// Determine if this is for cluster group
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
		$items = $this->input->get('tasks_items', array(), 'array');
		$milestoneId = $this->input->get('tasks_milestone', 0, 'int');

		$tasks_due = $this->input->get('tasks_due', '', 'default');
		$dueDate = $tasks_due ? ES::date($tasks_due)->toSql() : null;

		// Load up the milestone object
		$milestone = ES::table('Milestone');
		$milestone->load($milestoneId);

		if (!$items || empty($items) || !$milestone->id) {
			return;
		}

		// Get the group object
		$group = ES::group($streamTemplate->cluster_id);

		if (!$group->canAccessTasks() || !$group->canCreateTasks()) {
			return;
		}

		// Set the verb of the stream
		$streamTemplate->setVerb('createTask');

		$tasks = [];

		// We need to store the tasks item now.
		$taskId = '';

		foreach ($items as $item) {
			if (!$item) {
				continue;
			}

			// Store the task now
			$task = ES::table('Task');
			$task->title = $item;
			$task->state = SOCIAL_STATE_PUBLISHED;
			$task->uid = $group->id;
			$task->type = SOCIAL_TYPE_GROUP;
			$task->user_id = ES::user()->id;
			$task->milestone_id = $milestone->id;

			if ($dueDate) {
				$task->due = $dueDate;
			}

			$task->store();

			$taskId = $task->id;
			$tasks[] = $task;
		}

		// Set the context of the task
		if (count($items) === 1 && $taskId) {
			$streamTemplate->setContext($taskId, 'tasks');
		}

		$params = ES::registry();
		$params->set('tasks', $tasks);
		$params->set('group', $group);
		$params->set('milestone', $milestone);

		// Set the params on the stream
		$streamTemplate->setParams($params);

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
		$params = $this->getApp()->getParams();

		if (!$params->get('story_form', true)) {
			return;
		}

		// Get the group data
		$group = ES::group($story->cluster);

		// Ensure that the current user has access to create tasks
		if (!$group->canAccessTasks() || !$group->canCreateTasks() || !$this->getApp()->hasAccess($group->category_id)) {
			return;
		}

		$tasks = ES::model('Tasks');
		$milestones	= $tasks->getMilestones($group->id, SOCIAL_TYPE_GROUP);

		$theme = ES::themes();

		// Create plugin object
		$plugin = $story->createPlugin('tasks', 'panel');

		$title = JText::_('COM_EASYSOCIAL_STORY_TASK');
		$plugin->title = $title;

		$theme->set('title', $plugin->title);

		$button = $theme->output('site/story/tasks/button');

		// If there is no milestone, do not need to display the tasks embed in the story form.
		if (!$milestones) {
			$permalink 	= $this->getApp()->getPermalink('canvas', array('groupId' => $group->id, 'customView' => 'form'));

			// We need to attach the button to the story panel
			$theme->set('permalink', $permalink);
			$theme->set('cluster', $group);

			$form = $theme->output('site/story/tasks/empty');

			$plugin->setHtml($button, $form);

			return $plugin;
		}

		// We need to attach the button to the story panel
		$theme->set('milestones', $milestones);

		$form = $theme->output('site/story/tasks/form');

		// Attachment script
		$script = ES::get('Script');

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/tasks/plugin'));

		return $plugin;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	jos_social_stream, boolean
	 * @return  0 or 1
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != SOCIAL_TYPE_TASKS) {
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params = ES::registry($item->params);
		$group = ES::group($params->get('group'));

		if (!$group) {
			return;
		}

		$item->cnt = 1;

		if ($group->type != SOCIAL_GROUPS_PUBLIC_TYPE) {
			if (!$group->isMember(ES::user()->id)) {
				$item->cnt = 0;
			}
		}

		return true;
	}

	/**
	 * Trigger for onPrepareDigest
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareDigest(SocialStreamItem &$item)
	{
		if ($item->context != SOCIAL_TYPE_TASKS) {
			return;
		}

		$actor = $item->actor;

		$item->title = '';
		$item->link = '';

		$params = $item->getParams();

		$maxLength = 50;

		if ($item->verb == 'createTask') {

			$items = $params->get('tasks');
			$tasks = array();

			$content = '';

			if ($items) {
				foreach ($items as $taskItem) {

					$task = ES::table('Task');
					$task->load($taskItem->id);

					$content = ($content == '') ? $task->title : $content . ', ' . $task->title;
				}

				$showEllipse = ESJString::strlen($content) > $maxLength ? true : false;

				$content = ESJString::substr($content, 0, $maxLength);

				if ($showEllipse) {
					$content .= '...';
				}
			}

			$item->link = $item->getPermalink(true, true);
			$item->title = JText::sprintf('COM_ES_APP_TASKS_DIGEST_CREATE_TASK_TITLE', $actor->getName(), $content);
		}

		if ($item->verb == 'createMilestone') {

			$milestone = ES::table('Milestone');
			$milestone->bind($params->get('milestone'));

			$item->link = $milestone->getPermalink(true, true);
			$item->title = JText::sprintf('COM_ES_APP_TASKS_DIGEST_CREATE_MILESTONE_TITLE', $actor->getName(), $milestone->title);

		}
	}

	/**
	 * Triggered when the prepare stream is rendered
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_TASKS) {
			return;
		}

		// Ensure that the group is valid
		$group = ES::group($item->cluster_id);

		if (!$group) {
			return;
		}

		// Determines if the viewer can view the group's items
		if (!$group->canViewItem()) {
			return;
		}

		// Stream attributes
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->repost = false;

		$params = $item->getParams();

		if ($item->verb == 'createTask') {
			$this->prepareCreatedTaskStream($item, $includePrivacy, $params);
		}

		if ($item->verb == 'createMilestone') {
			$this->prepareCreateMilestoneStream($item, $includePrivacy, $params);
		}
	}

	/**
	 * Renders the stream item for tasks
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function prepareCreatedTaskStream(SocialStreamItem $item, $includePrivacy = true, $params = null)
	{
		// Get the tasks available from the cached data
		$items = $params->get('tasks');
		$tasks = [];

		$taskId = '';

		foreach ($items as $taskItem) {
			$task = ES::table('Task');
			$task->load($taskItem->id);

			$tasks[] = $task;
			$taskId = $task->id;
		}

		// Get the milestone
		$milestone = ES::table('Milestone');
		$milestone->bind($params->get('milestone'));

		// Get the group data
		$group = $item->getCluster();
		$permalink = $milestone->getPermalink();

		$this->set('item', $item);
		$this->set('permalink', $permalink);
		$this->set('milestone', $milestone);
		$this->set('total', count($tasks));
		$this->set('actor', $item->actor);
		$this->set('group', $group);
		$this->set('cluster', $group);
		$this->set('tasks', $tasks);

		$item->likes = ES::likes($taskId, $item->context, $item->verb, SOCIAL_TYPE_GROUP, $item->uid, array('clusterId' => $item->cluster_id));
		$item->comments = ES::comments($taskId, $item->context, $item->verb, SOCIAL_TYPE_GROUP,  array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $item->cluster_id), $item->uid);

		$item->title = parent::display('themes:/site/streams/tasks/group/create.task.title');
		$item->preview = parent::display('themes:/site/streams/tasks/task.preview');

		// Append the likes and comments if it's aggregated
		if (!$item->contextIds[0]) {

			$item->likes = ES::likes($taskId, $item->context, $item->verb, SOCIAL_TYPE_GROUP, $item->uid);
			$item->comments = ES::comments($taskId, $item->context, $item->verb, SOCIAL_TYPE_GROUP,  array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $item->cluster_id), $item->uid);
		}
	}

	/**
	 * Renders the stream item when a new milestone is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function prepareCreateMilestoneStream(SocialStreamItem $item, $includePrivacy = true, $params = null)
	{
		// Load the milestone
		$milestone = ES::table('Milestone');
		$milestone->bind($params->get('milestone'));

		$group = $item->getCluster();

		// Get the actor
		$actor = $item->actor;
		$app = $this->getApp();
		$permalink = $milestone->getPermalink();

		$access = $group->getAccess();
		if ($this->my->isSiteAdmin() || $group->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $milestone->getEditPermalink();
		}

		$this->set('permalink', $permalink);
		$this->set('milestone', $milestone);
		$this->set('actor', $actor);
		$this->set('group', $group);
		$this->set('item', $item);

		$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_TYPE_GROUP,  array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $item->cluster_id), $item->uid);

		$item->title = parent::display('themes:/site/streams/tasks/group/create.milestone.title');
		$item->preview = parent::display('themes:/site/streams/tasks/milestone.preview');
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
		if ($item->context_type == 'tasks.group.createMilestone') {

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

		if ($item->cmd === 'group.task.completed' || $item->cmd === 'group.task.create' || $item->cmd === 'group.milestone.create') {
			$milestoneId = $item->context_ids;

			if ($item->cmd != 'group.milestone.create') {
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
