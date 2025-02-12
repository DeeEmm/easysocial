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

class SocialPageAppFiles extends SocialAppItem
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

		// for page files app, there is no standalone page.
		// this mean the only place that user can comment / react is via
		// stream. Thats also mean, if we reach here, mean something
		// is not right. just return false.

		return false;
	}

	/**
	 * Determines if the app should be displayed in the list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function appListing($view, $id, $type)
	{
		$page = ES::page($id);

		if (!$page->canAccessFiles()) {
			return false;
		}

		if (!$page->canViewItem()) {
			return false;
		}

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	2.0
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
		$page = ES::page($params->get('page'));

		if (!$page) {
			return;
		}

		$item->cnt = 1;

		if ($page->type != SOCIAL_PAGES_PUBLIC_TYPE) {
			if (!$page->isMember(ES::user()->id)) {
				$item->cnt = 0;
			}
		}

		return true;
	}

	/**
	 * Processes notifications for files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$allowed = array('files.page.uploaded');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		if ($item->type == 'likes' && $item->context_type == 'files.page.uploaded') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);
			return;
		}

		if ($item->type == 'comments' && $item->context_type == 'files.page.uploaded') {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);
			return;
		}
	}

	/**
	 * Processes when user likes a file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		$allowed = array('files.page.uploaded');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Set the default element.
		$uid = $likes->uid;
		$data = explode('.', $likes->type);
		$element = $data[0];
		$verb = $data[2];

		if ($likes->type == 'files.page.uploaded') {

			// Get the owner of the post.
			$stream = ES::table('Stream');
			$stream->load($likes->stream_id);

			// Since we have the stream, we can get the page id
			$page = ES::page($stream->cluster_id);

			// Get the actor
			$actor = ES::user($likes->created_by);

			$systemOptions  = array(
				'context_type' => $likes->type,
				'context_ids' => $stream->id,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// [Page Compatibility] Only notify if the liker is not a page admin
			// Notify the owner first
			if (!$page->isAdmin($likes->created_by)) {
				ES::notify('likes.item', $page->getAdmins(), false, $systemOptions, $page->notification);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'page', $verb, array(), array($stream->actor_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions, $page->notification);

			return;
		}
	}

	/**
	 * Processes when user comments on a file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('files.page.uploaded');

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

		// Get the page object
		$page = ES::page($stream->cluster_id);

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
			'title' => 'APP_PAGE_FILES_EMAILS_COMMENT_ITEM_SUBJECT',
			'template' => 'apps/page/files/comment.file.item',
			'comment' => $commentContent,
			'page' => $page->getName(),
			'permalink' => $stream->getPermalink(true, true),
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true)
		);

		$systemOptions = array(
			'content' => $commentContent,
			'context_type' => $comment->element,
			'context_ids' => $stream->id,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		// [Page Compatibility] Only notify if the commentator is not a page admin
		// Notify the owner first
		if (!$page->isAdmin($comment->created_by) && !$comment->isChild()) {
			ES::notify('comments.item', $page->getAdmins(), $emailOptions, $systemOptions, $page->notification);
		}

		// Get a list of recipients to be notified for this stream item.
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'page', $verb, array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title'] = 'APP_PAGE_FILES_EMAILS_COMMENT_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/page/files/comment.file.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $page->notification);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/page/files/comment.file.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions, $page->notification);
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

		// page access checking
		$page = ES::page($item->cluster_id);

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		$access = $page->getAccess();
		if ($viewer->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

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
		$item->targets = $page;
		$item->setActorAlias($page);

		$options = array('url' => $item->getPermalink(), 'clusterId' => $page->id, 'url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)));

		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $options, $item->uid);
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, $options);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->show = true;
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != SOCIAL_TYPE_FILES) {
			return;
		}

		// page access checking
		$page = ES::page($item->cluster_id);

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		$access = $page->getAccess();
		if ($this->my->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		$params = ES::registry($item->contextParams[0]);

		// Do not allow user to repost files
		$item->repost = false;

		$items = $params->get('file');
		$total = count($items);

		if (!$items) {
			return;
		}

		$files = array();

		foreach ($items as $id) {
			$file = ES::table('File');
			$state = $file->load($id);

			if ($state) {
				$files[] = $file;
			}
		}

		// Only proceed if the file still exist
		if (!$files) {
			return;
		}

		$plurality = $total > 1 ? '_PLURAL' : '_SINGULAR';

		$item->setActorAlias($page);

		$this->set('files', $files);
		$this->set('cluster', $page);
		$this->set('plurality', $plurality);
		$this->set('total', $total);

		$options = array('url' => $item->getPermalink());

		// Set the cluster id so that we know the comment is belong to this cluster
		$options['clusterId'] = $page->id;
		$options['url'] = ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false));

		// Load the comments
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $options, $item->uid);

		// Load up the other contents now.
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, $options);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = parent::display('themes:/site/streams/files/page/title');
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
		$page = ES::page($story->cluster);

		if (!$page->canAccessFiles() || !$this->getApp()->hasAccess($page->category_id)) {
			return;
		}

		// Ensure that the user really has access to upload files in a page
		if (!$page->canCreateFiles()) {
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('files', 'panel');

		// Get the allowed extensions
		$allowedExtensions = $params->get('allowed_extensions', 'zip,txt,pdf,gz,php,doc,docx,ppt,xls');
		$maxFileSize = $page->getAccess()->get('files.maxsize') . 'M';

		// We need to attach the button to the story panel
		$theme = ES::themes();
		$theme->set('title', $plugin->title);

		$plugin->button->html = $theme->output('site/story/files/button');
		$plugin->content->html = $theme->output('site/story/files/form', array('data' => $data, 'isEdit' => $isEdit));

		// Attachment script
		$script = ES::script();
		$script->set('allowedExtensions', $allowedExtensions);
		$script->set('maxFileSize', $maxFileSize);
		$script->set('type', SOCIAL_TYPE_PAGE);
		$script->set('uid', $story->cluster);

		$plugin->script = $script->output('site/story/files/plugin');

		return $plugin;
	}

	/**
	 * Processes after the story is saved so that we can generate a stream item for this
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function onAfterStorySave(SocialStream &$stream , SocialTableStreamItem $streamItem, &$template)
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
		// Load the page
		$page = ES::page($template->cluster_id);

		// Get the actor
		$actor = ES::user($streamItem->actor_id);

		// Get a list of page members
		$model = ES::model('pages');
		$targets = $model->getMembers($page->id, array('exclude' => $actor->id, 'state' => SOCIAL_STATE_PUBLISHED));

		// If there is no page members, we skip this
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
		$mailParams['page'] = $page->getName();
		$mailParams['pageLink'] = $page->getPermalink(true, true);
		$mailParams['permalink'] = $permalink;
		$mailParams['title'] = 'APP_PAGE_STORY_EMAILS_NEW_FILE_IN_PAGE';
		$mailParams['template'] = 'apps/page/files/new.files';
		$mailParams['streamContent'] = $streamContent;

		// Prepare the system notificatioin params
		$systemParams = array();
		$systemParams['context_type'] = 'page';
		$systemParams['title'] = JText::sprintf('APP_PAGE_STORY_FILES_UPLOADED_IN_PAGE', $actor->getName(), $page->getName());
		$systemParams['url'] = ESR::stream(array('id' => $streamItem->uid, 'layout' => 'item', 'sef' => false));
		$systemParams['actor_id'] = $actor->id;
		$systemParams['uid'] = $streamItem->uid;
		$systemParams['context_ids'] = $page->id;
		$systemParams['content'] = $streamContent;

		// Try to send the notification
		$state = ES::notify('pages.file.upload', $targets, $mailParams, $systemParams, $page->notification);
	}

	/**
	 * Export Notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onNotificationExport(&$item, &$content, $viewer)
	{
		$allowed = array('files.page.uploaded');

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
