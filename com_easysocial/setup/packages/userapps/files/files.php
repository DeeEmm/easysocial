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

class SocialUserAppFiles extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'files') {
			return;
		}

		// for user files app, there is no standalone page.
		// this mean the only place that user can comment / react is via
		// stream. Thats also mean, if we reach here, mean something
		// is not right. just return false.

		return false;
	}


	/**
	 * Determines if the app should be displayed in the list
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function appListing($view, $userId, $type)
	{
		$user = ES::user($userId);

		// Check for the permission to view the files
		if (!$user->isViewer()) {
			return false;
		}

		return true;
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('files.user.create');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($likes->created_by);

		// Load the stream item
		$stream = ES::table('Stream');
		$stream->load($likes->stream_id);

		// Prepare the system notification params
		$systemParams = array();
		$systemParams['url'] = ESR::stream(array('id' => $stream->id, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $stream->id;
		$systemParams['context_type'] = $likes->type;

		// Only notify if the actor is not the poll's owner
		if ($likes->created_by != $stream->actor_id) {
			ES::notify('likes.item', array($stream->actor_id), array(), $systemParams);
		}

		return;
	}

	/**
	 * Triggered before comments notify subscribers
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('files.user.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Since the uid is tied to the album we can get the album object
		$stream = ES::table('Stream');
		$stream->load($comment->uid);

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

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

		// Set the email options
		$emailOptions   = array(
			'title' => 'APP_USER_FILES_EMAILS_COMMENT_STREAM_SUBJECT',
			'template' => 'apps/user/files/comment.status.item',
			'permalink' => $stream->getPermalink(true, true),
			'comment' => $commentContent,
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'target' => $owner->getName(),
			'targetLink' => $owner->getPermalink(true, true)
		);

		$systemOptions  = array(
			'context_type' => $comment->element,
			'context_ids' => $comment->id,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		// Notify the owner of the photo first
		if ($stream->actor_id != $comment->created_by && !$comment->isChild()) {
			ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'files', 'user', 'create', array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title']  = 'APP_USER_FILES_EMAILS_COMMENT_STREAM_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/user/files/comment.status.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/files/comment.status.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild()) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		if ($comment->element != 'files.user.create') {
			return;
		}

		// Here we know that comments associated with file is always
		// comment.id = notification.context_id
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithContextId($comment->id, $comment->element);
	}

	/**
	 * We do not want to display this in the activity log
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function hasActivityLog()
	{
		return false;
	}

	/**
	 * Processes after the story is saved so that we can generate a stream item for this
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onAfterStorySave(SocialStream &$stream , SocialTableStreamItem $streamItem , &$template)
	{
		$files = $this->input->get('files', array(), 'array');

		if (!$files) {
			return;
		}

		// We need to set the context id's for the files shared in this stream.
		$params = ES::registry();
		$params->set('files', $files);

		$streamItem->params = $params->toString();
		$streamItem->store();
	}

	/**
	 * Renders the notification item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowedContexts = array('files.user.create');

		if ($item->type == 'likes' && in_array($item->context_type, $allowedContexts)) {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);
			return;
		}

		$allowed = array('comments.item', 'comments.replied', 'comments.involved');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		// When someone likes on a status update
		$allowedContexts = array('files.user.create');

		if ($item->type == 'comments' && ($item->cmd == 'comments.involved' || $item->cmd == 'comments.replied' || $item->cmd == 'comments.item') && in_array($item->context_type, $allowedContexts)) {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);
			return;
		}
	}

	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != SOCIAL_TYPE_FILES) {
			return false;
		}

		// If this is a cluster stream, let check if user can view this stream or not.
		if ($item->cluster_id && $item->cluster_type) {
			$params = ES::registry($item->params);
			$group = ES::group($params->get('group'));

			if (!$group) {
				return;
			}

			$item->cnt = 1;

			if ($group->type != SOCIAL_GROUPS_PUBLIC_TYPE && !$group->isMember()) {
				$item->cnt = 0;
			}
		} else {
			// There is no need to validate against privacy for this item.
			$item->cnt = 1;
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
		if ($item->context != SOCIAL_TYPE_FILES) {
			return;
		}

		if ($item->verb == 'uploaded' && $item->contextIds) {
			$items = $item->contextIds;
		} else {
			$params = ES::registry($item->contextParams[0]);
			$items = $params->get('files');
		}

		$total = count($items);
		$files = array();

		if (!$items) {
			return;
		}

		if ($viewer->isSiteAdmin() || $viewer->id == $item->actor_id) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		foreach ($items as $id) {
			$file = ES::table('File');
			$file->load($id);

			// Skip it if the file has been deleted
			if (!$file->id) {
				continue;
			}

			$files[] = $file->toExportData($viewer);
		}

		// Do not show the stream if there are no files in it
		if (!$files) {
			return;
		}

		$item->contentObj = $files;

		// Apply actions on stream
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, array('url' => $item->getPermalink(false, false, false)), $item->uid);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->show = true;
	}

	/**
	 * Generates the stream item for files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true )
	{
		if ($item->context != SOCIAL_TYPE_FILES) {
			return;
		}

		if ($item->verb == 'uploaded' && $item->contextIds) {
			$items = $item->contextIds;
		} else {
			$params = ES::registry($item->contextParams[0]);
			$items = $params->get('files');
		}

		$total = count($items);
		$files = array();

		if (!$items) {
			return;
		}

		if ($this->my->isSiteAdmin() || $this->my->id == $item->actor_id) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		foreach ($items as $id) {
			$file = ES::table('File');
			$file->load($id);

			// Skip it if the file has been deleted
			if (!$file->id) {
				continue;
			}

			$files[] = $file;
		}

		// Do not show the stream if there are no files in it
		if (!$files) {
			return;
		}

		$plurality = $total > 1 ? '_PLURAL' : '_SINGULAR';

		$targets = $item->targets ? $item->targets[0] : false;

		$this->set('target', $targets);
		$this->set('content', $item->content);
		$this->set('plurality', $plurality);
		$this->set('total', $total);
		$this->set('files', $files);
		$this->set('actor', $item->actor);
		$this->set('item', $item);

		// Apply actions on stream
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, array('url' => $item->getPermalink(false, false, false)), $item->uid);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = parent::display('themes:/site/streams/files/title');
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
		$files = $model->getStreamFiles($stream->id);

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
		$state = $model->updateStreamFiles($stream->id, $files);

		return true;
	}

	/**
	 * Prepares what should appear in the story form.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onPrepareStoryPanel($story, $isEdit = false, $data = array())
	{
		$params = $this->getParams();
		$access = $this->my->getAccess();

		// Determine if the user can use this feature
		if (!$params->get('enable_uploads', true)) {
			return;
		}

		// Check for access
		if (!$this->my->canCreateFiles($this)) {
			return;
		}

		// Create plugin object
		$plugin	= $story->createPlugin('files', 'panel');

		// Get the allowed extensions
		$allowedExtensions = $params->get('allowed_extensions', 'zip,txt,pdf,gz,php,doc,docx,ppt,xls');
		$maxFileSize = $access->get('files.maxsize') . 'M';

		// We need to attach the button to the story panel
		$theme = ES::themes();
		$theme->set('title', $plugin->title);

		$plugin->button->html = $theme->output('site/story/files/button');
		$plugin->content->html = $theme->output('site/story/files/form', array('data' => $data, 'isEdit' => $isEdit));

		// Attachment script
		$script	= ES::script();
		$script->set('allowedExtensions', $allowedExtensions);
		$script->set('maxFileSize', $maxFileSize);
		$script->set('type', SOCIAL_TYPE_USER);
		$script->set('uid', $this->my->id);

		$plugin->script	= $script->output('site/story/files/plugin');

		return $plugin;
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if ($item->context_type != 'files.user.create') {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->uid;
		$target->type = 'stream';
		$target->endpoint = $target->type . '.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

		$item->target = $target;
	}
}
