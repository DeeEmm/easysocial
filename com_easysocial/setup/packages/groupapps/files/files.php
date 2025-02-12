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

class SocialGroupAppFiles extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_FILES) {
			return;
		}

		return false;
	}

	/**
	 * Determines if the app should be displayed in the list
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function appListing($view, $id, $type)
	{
		$group = ES::group($id);

		if (!$group->canAccessFiles()) {
			return false;
		}

		if (!$group->canViewItem()) {
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
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != SOCIAL_TYPE_FILES) {
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
	 * Processes notifications for files
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('files.group.uploaded');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		if ($item->type == 'likes' && $item->context_type == 'files.group.uploaded') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);
			return;
		}

		if ($item->type == 'comments' && $item->context_type == 'files.group.uploaded') {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);
			return;
		}
	}

	/**
	 * Processes when user likes a file
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('files.group.uploaded');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Set the default element.
		$uid = $likes->uid;
		$data = explode('.', $likes->type);
		$element = $data[0];
		$verb = $data[2];

		if ($likes->type == 'files.group.uploaded') {

			// Get the owner of the post.
			$stream = ES::table('Stream');
			$stream->load($likes->stream_id);

			// Since we have the stream, we can get the group id
			$group = ES::group($stream->cluster_id);

			// Get the actor
			$actor = ES::user($likes->created_by);

			$systemOptions  = array(
				'context_type' => $likes->type,
				'context_ids' => $stream->cluster_id,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// Notify the owner first
			if ($likes->created_by != $stream->actor_id) {
				ES::notify('likes.item', array($stream->actor_id), false, $systemOptions, $group->notification);
			}

			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'group', $verb, array(), array($stream->actor_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions, $group->notification);
		}
	}

	/**
	 * Processes when user comments on a file
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('files.group.uploaded');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the stream object
		$stream = ES::table('Stream');
		$stream->load($comment->uid);

		$segments = explode('.', $comment->element);
		$element = $segments[0];
		$verb = $segments[2];

		// Load up the stream object
		$stream = ES::table('Stream');
		$stream->load($comment->stream_id);

		// Get the group object
		$group = ES::group($stream->cluster_id);

		// Get the comment actor
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

		$emailOptions = array(
			'title' => 'APP_GROUP_FILES_EMAILS_COMMENT_ITEM_SUBJECT',
			'template' => 'apps/group/files/comment.file.item',
			'comment' => $commentContent,
			'group' => $group->getName(),
			'permalink' => $stream->getPermalink(true, true),
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true)
		);

		$systemOptions = array(
			'content' => $commentContent,
			'context_type' => $comment->element,
			'context_ids' => $stream->cluster_id,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);


		// Notify the note owner
		if ($comment->created_by != $stream->actor_id && !$comment->isChild()) {
			ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions, $group->notification);
		}

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'group', $verb, array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title'] = 'APP_GROUP_FILES_EMAILS_COMMENT_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/group/files/comment.file.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $group->notification);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/group/files/comment.file.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions, $group->notification);
		}
	}

	/**
	 * Trigger for onPrepareDigest
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareDigest(SocialStreamItem &$item)
	{
		if ($item->context != SOCIAL_TYPE_FILES) {
			return;
		}

		$actor = $item->actor;

		$params = ES::registry($item->params);

		// Try to get the file object
		$obj = $params->get('file');

		// Default variables
		$content = '';
		$files = array();

		if (is_object($obj)) {

			// Get the file object
			$file = ES::table('File');
			$file->load($obj->id);

			if (!$file->id) {
				return;
			}

			$files[] = $file;

		} else {
			$params = ES::registry($item->contextParams[0]);
			$fileItems = $params->get('file');
			$content = $item->content;

			foreach ($fileItems as $fileId) {
				$file = ES::table('File');
				$state = $file->load((int) $fileId);

				if ($state) {
					$files[] = $file;
				}
			}
		}


		$item->title = '';
		$item->preview = '';
		$item->link = $item->getPermalink(true, true);

		if ($item->verb == 'uploaded') {
			$count = count($files);
			$item->title = JText::sprintf(ES::string()->computeNoun('COM_ES_APP_FILES_DIGEST_UPLOADED_TITLE', $count), $actor->getName(), $count);
		}
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if ($item->context != SOCIAL_TYPE_FILES) {
			return;
		}

		// group access checking
		$group = ES::group($item->cluster_id);

		if (!$group) {
			return;
		}

		if (!$group->canViewItem()) {
			return;
		}

		$access = $group->getAccess();
		if ($viewer->isSiteAdmin() || $group->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get the file object from the context params
		$params = ES::registry($item->contextParams[0]);
		$streams = $params->get('file');

		if (!$streams) {
			return;
		}

		$files = array();

		foreach ($streams as $id) {
			$file = ES::table('File');
			$file->load((int) $id);

			if (!$file->id) {
				continue;
			}

			$files[] = $file->toExportData($viewer);
		}

		// Only proceed if the file still exist
		if (!$files) {
			return;
		}

		$item->contentObj = $files;
		$item->targets = $group;

		// actions.
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, $item->uid);
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, array('url' => $item->getPermalink(false, false, false), 'clusterId' => $item->cluster_id), $item->uid);
		$item->show = true;
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_FILES) {
			return;
		}

		// group access checking
		$group = ES::group($item->cluster_id);

		if (!$group) {
			return;
		}

		if (!$group->canViewItem()) {
			return;
		}

		$access = $group->getAccess();
		if ($this->my->isSiteAdmin() || $group->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		// Define standard stream looks
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->color = '#2969B0';
		$item->label = JText::_('COM_EASYSOCIAL_STREAM_CONTEXT_TITLE_FILES_TOOLTIP', true);

		if ($item->verb == 'uploaded') {
			$this->prepareUploadedStream($item);
		}
	}

	/**
	 * Prepares the stream item for new file uploads
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function prepareUploadedStream(&$item)
	{
		$params = ES::registry($item->params);

		// Get the group object
		$group = ES::group($params->get('group'));

		// Do not allow user to repost files
		$item->repost = false;

		// Try to get the file object
		$obj = $params->get('file');

		// Default variables
		$content = '';
		$files = array();

		if (is_object($obj)) {

			// Get the file object
			$file = ES::table('File');
			$file->load($params->get('file')->id);

			if (!$file->id) {
				return;
			}

			$files[] = $file;

		} else {
			$params = ES::registry($item->contextParams[0]);
			$fileItems = $params->get('file');
			$content = $item->content;

			foreach ($fileItems as $fileId) {
				$file = ES::table('File');
				$state = $file->load((int) $fileId);

				if ($state) {
					$files[] = $file;
				}
			}
		}

		if (!$files) {
			return;
		}

		// Get the actor
		$actor = $item->actor;

		// actions.
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, $item->uid);
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_GROUP, array('url' => $item->getPermalink(false, false, false), 'clusterId' => $item->cluster_id), $item->uid);

		$this->set('content', $content);
		$this->set('actor', $actor);
		$this->set('files', $files);
		$this->set('cluster', $group);

		// Load up the contents now.
		$item->title = parent::display('themes:/site/streams/files/title.cluster');
		$item->preview = parent::display('themes:/site/streams/files/preview');

		// Append the opengraph tags
		$item->addOgDescription();
	}

	/**
	 * Prepares the photos in the story edit form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStoryEditForm(&$story, &$stream)
	{
		// preparing data for story edit.
		$data = array();

		// get all photos from this stream uid.
		$model = ES::model('Files');
		$files = $model->getStreamFiles($stream->id, true);

		if ($files) {
			$data['files'] = $files;
		}

		$plugin = $this->onPrepareStoryPanel($story, true, $data);

		$story->panelsMain = array($plugin);
		$story->panels = array($plugin);
		$story->plugins = array($plugin);

		$contents = $story->editForm(false, $stream->id);

		return $contents;
	}

	/**
	 * Processes a story edit save.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onAfterStoryEditSave(SocialTableStream &$stream)
	{
		if ($stream->context_type != SOCIAL_TYPE_FILES) {
			return;
		}

		$files = $this->input->get('files', array(), 'array');

		if (!$files) {
			return;
		}

		$model = ES::model('Files');
		$state = $model->updateStreamFiles($stream->id, $files, true);

		return true;
	}

	/**
	 * Prepares what should appear in the story form.
	 *
	 * @since   2.0.14
	 * @access  public
	 */
	public function onPrepareStoryPanel($story, $isEdit = false, $data = array())
	{
		$params = $this->getParams();

		// Determine if the user can use this feature
		if (!$params->get('enable_uploads', true)) {
			return;
		}

		// Check if this user profile type allow file upload
		if (!$this->my->canCreateFiles($this)) {
			return;
		}

		// Get the event object
		$group = ES::group($story->cluster);

		if (!$group->canAccessFiles() || !$this->getApp()->hasAccess($group->category_id)) {
			return;
		}

		// Ensure that the user really has access to upload files in a group
		if (!$group->canCreateFiles()) {
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('files', 'panel');

		// Get the allowed extensions
		$allowedExtensions = $params->get('allowed_extensions', 'zip,txt,pdf,gz,php,doc,docx,ppt,xls');
		$maxFileSize = $group->getAccess()->get('files.maxsize') . 'M';

		// We need to attach the button to the story panel
		$theme = ES::themes();
		$theme->set('title', $plugin->title);

		$plugin->button->html = $theme->output('site/story/files/button');
		$plugin->content->html = $theme->output('site/story/files/form', array('data' => $data, 'isEdit' => $isEdit));

		// Attachment script
		$script = ES::script();
		$script->set('allowedExtensions', $allowedExtensions);
		$script->set('maxFileSize', $maxFileSize);
		$script->set('type', SOCIAL_TYPE_GROUP);
		$script->set('uid', $story->cluster);

		$plugin->script = $script->output('site/story/files/plugin');

		return $plugin;
	}

	/**
	 * Processes after the story is saved so that we can generate a stream item for this
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onAfterStorySave(SocialStream &$stream, SocialTableStreamItem $streamItem, &$template)
	{
		$files = $this->input->get('files', array(), 'array');

		if (!$files) {
			return;
		}

		// We need to set the context id's for the files shared in this stream.
		$params = ES::registry();
		$params->set('file', $files);

		$streamItem->verb = 'uploaded';
		$streamItem->params = $params->toString();
		$streamItem->store();

		// We need to notify the group members
		$this->notify($stream, $streamItem, $template);
	}

	/**
	 * Notify the group members
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function notify($stream, $streamItem, $template)
	{
		// Load the group
		$group = ES::group($template->cluster_id);

		// Get the actor
		$actor = ES::user($streamItem->actor_id);

		// Get a list of group members
		$model = ES::model('Groups');
		$targets = $model->getMembers($group->id, array('exclude' => $actor->id, 'state' => SOCIAL_STATE_PUBLISHED));

		// If there is no group members, we skip this
		if (!$targets) {
			return;
		}

		// Get the stream item's permalink
		$permalink = ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'external' => true), true);

		// format stream content
		$parseBBCodeOptions = array('escape' => false, 'links' => true, 'code' => true);
		$streamContent = ES::string()->normalizeContent($template->content, $parseBBCodeOptions);

		// Prepare the email params
		$mailParams = array();
		$mailParams['actor'] = $actor->getName();
		$mailParams['posterAvatar'] = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['posterLink'] = $actor->getPermalink(true, true);

		// Get the file object
		$params = ES::registry($streamItem->params);
		$fileParam = $params->get('file');

		$file = ES::table('File');
		$file->load($fileParam[0]);

		$mailParams['message'] = $file->name;
		$mailParams['group'] = $group->getName();
		$mailParams['groupLink'] = $group->getPermalink(true, true);
		$mailParams['permalink'] = $permalink;
		$mailParams['title'] = 'APP_GROUP_STORY_EMAILS_NEW_FILE_IN_GROUP';
		$mailParams['template'] = 'apps/group/files/new.files';
		$mailParams['streamContent'] = $streamContent;

		// Prepare the system notificatioin params
		$systemParams = array();
		$systemParams['context_type'] = 'group';
		$systemParams['title'] = JText::sprintf('APP_GROUP_STORY_FILES_UPLOADED_IN_GROUP', $actor->getName(), $group->getName());
		$systemParams['url'] = ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $streamItem->uid;
		$systemParams['context_ids'] = $group->id;
		$systemParams['content'] = $streamContent;

		// Try to send the notification
		$state = ES::notify('groups.file.upload', $targets, $mailParams, $systemParams, $group->notification);
	}

	/**
	 * Export Notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onNotificationExport(&$item, &$content, $viewer)
	{
		$allowed = array('files.group.uploaded');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		// Get the stream for this files
		$stream = ES::stream();
		$object = $stream->getItem($item->uid);
		$object = $object[0];

		$content->type = 'stream';
		$content->object = $object->toExportData($viewer);
	}
}
