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

class SocialPageAppVideos extends SocialAppItem
{
	public $appListing = false;

	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_VIDEOS) {
			return;
		}

		$video = ES::table('Video');
		$video->load($uid);

		$lib = ES::video($video);

		if (!$lib->isViewable()) {
			return false;
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since   2.0
	 * @access  public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = [];

		if (!$params->get('uploadVideos', true)) {
			$excludeVerb[] = 'create';
		}

		if (!$params->get('featuredVideos', true)) {
			$excludeVerb[] = 'featured';
		}

		if ($excludeVerb !== false) {
			$exclude['videos'] = $excludeVerb;
		}
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'videos') {
			return false;
		}

		$params = ES::registry($item->params);
		$page = ES::page($params->get('page'));

		if (!$page) {
			return;
		}

		$item->cnt = 1;

		if (!$page->isOpen() && !$page->isMember($this->my->id)) {
			$item->cnt = 0;
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
		if ($item->context != SOCIAL_TYPE_VIDEOS) {
			return;
		}

		// Determines if the viewer can view the stream item from this page
		$page = $item->getCluster();

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		// Decorate the stream item with the neccessary design
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get the video
		$video = ES::video($item->cluster_id, SOCIAL_TYPE_PAGE, $item->contextId);

		// Ensure that the video is really published
		if (!$video->isPublished()) {
			return;
		}

		// Set the actor alias
		if ($item->post_as == SOCIAL_TYPE_PAGE) {
			$item->setActorAlias($page);
		}

		$access = $page->getAccess();
		if ($viewer->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		$item->contentObj = $video->toExportData($viewer);
		$item->targets = $page;
		$item->comments = $video->getComments($item->verb, $item->uid);
		$item->likes = $video->getLikes($item->verb, $item->uid);
		$item->show = true;
	}

	/**
	 * Generates the stream item for videos
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != SOCIAL_TYPE_VIDEOS) {
			return;
		}

		// Determines if the viewer can view the stream item from this page
		$page = $stream->getCluster();

		if (!$page) {
			return;
		}

		if (!$page->canViewItem()) {
			return;
		}

		// Decorate the stream item with the neccessary design
		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get the video
		$video = ES::video($stream->cluster_id, SOCIAL_TYPE_PAGE, $stream->contextId);

		// Ensure that the video is really published
		if (!$video->isPublished()) {
			return;
		}

		// Set the actor alias
		$actor = $stream->getPostActor($page);

		// Retrieve page alias
		$alias = $page->getAlias();

		$this->set('stream', $stream);
		$this->set('video', $video);
		$this->set('actor', $actor);
		$this->set('page', $page);
		$this->set('uid', $alias);
		$this->set('utype', SOCIAL_TYPE_PAGE);

		$access = $page->getAccess();
		if ($this->my->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $stream->actor->id == $this->my->id)) {
			$stream->editable = true;
			$stream->appid = $this->getApp()->id;
		}

		// Update the stream title
		$stream->title = parent::display('themes:/site/streams/videos/page/title.' . $stream->verb);
		$stream->preview = parent::display('themes:/site/streams/videos/preview');

		// For Page, we need to pass the page id in order to use custom author for comment
		$stream->comments = $video->getComments($stream->verb, $stream->uid);
		$stream->likes = $video->getLikes($stream->verb, $stream->uid);

		// If the video has a thumbnail, add the opengraph tags
		$thumbnail = $video->getThumbnail();

		if ($thumbnail) {
			$stream->addOgImage($thumbnail);
		}

		// Append the opengraph tags
		$stream->addOgDescription($video->getDescription(false));
	}

	/**
	 * Prepares the video in the story edit form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStoryEditForm(&$story, &$stream)
	{

		// preparing data for story edit.
		$data = array();

		// get video from this stream uid.
		$model = ES::model('Videos');
		$video = $model->getStreamVideo($stream->id);

		if ($video) {
			$data['video'] = $video;
		}

		$plugin = $this->onPrepareStoryPanel($story, true, $data);

		$story->panelsMain = array($plugin);
		$story->panels = array($plugin);
		$story->plugins = array($plugin);

		$contents = $story->editForm(false, $stream->id);

		// dump($contents);

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
		// Only process videos
		if ($stream->context_type != 'videos') {
			return;
		}

		// Determine the type of the video
		$data = array();
		$data['id'] = $this->input->get('videos_id', 0, 'int');
		$data['category_id'] = $this->input->get('videos_category', 0, 'int');
		$data['description'] = $this->input->get('videos_description', '', 'default');
		$data['iEncoding'] = $this->input->get('videos_isEncoding', false, 'bool');
		$data['link'] = $this->input->get('videos_link', '', 'default');
		$data['title'] = $this->input->get('videos_title', '', 'default');
		$data['source'] = $this->input->get('videos_type', '', 'default');

		$model = ES::model('videos');
		$state = $model->updateStreamVideo($stream->id, $data);

		ES::storage()->syncUsage($stream->actor_id);

		return true;
	}


	/**
	 * Generates the story form for videos
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStoryPanel(SocialStory $story, $isEdit = false, $data = array())
	{
		// Get the page id
		$pageId = $story->cluster;

		// Get the video adapter
		$adapter = ES::video($pageId, SOCIAL_TYPE_PAGE);

		$page = ES::page($pageId);

		if (!$adapter->canUpload() && !$adapter->canEmbed()) {
			return;
		}

		// Ensure that video creation is allowed
		if (!$page->canAccessVideos() || !$page->getCategory()->getAcl()->get('videos.create', true)) {
			return;
		}

		// In story panel, we only allow page admin and page members to share video
		if (!$page->isAdmin() && !$page->isMember()) {
			return;
		}

		// Get a list of video categories
		$model = ES::model('Videos');
		$options = array('pagination' => false, 'ordering' => 'ordering');

		if (!$this->my->isSiteAdmin()) {
			$options['respectAccess'] = true;
			$options['profileId'] = $this->my->getProfile()->id;
		}

		$categories = $model->getCategories($options);

		if ($isEdit && isset($data['video']) && $data['video']) {
			$adapter = $data['video'];
		}

		// Create a new plugin for this video
		$plugin = $story->createPlugin('videos', 'panel');

		$title = JText::_('COM_EASYSOCIAL_STORY_VIDEO');
		$plugin->title = $title;

		// Get the maximum upload filesize allowed
		$uploadLimit = $adapter->getUploadLimit();

		$theme = ES::themes();
		$theme->set('categories', $categories);
		$theme->set('uploadLimit', $uploadLimit);
		$theme->set('video', $adapter);
		$theme->set('isEdit', $isEdit);
		$theme->set('title', $plugin->title);

		$button = $theme->output('site/story/videos/button');
		$form = $theme->output('site/story/videos/form');

		$script = ES::script();
		$script->set('uploadLimit', $uploadLimit);
		$script->set('type', SOCIAL_TYPE_PAGE);
		$script->set('uid', $pageId);
		$script->set('video', $adapter);
		$script->set('isEdit', $isEdit);

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/videos/plugin'));

		return $plugin;
	}

	/**
	 * Processes after a story is saved on the site. When the story is stored, we need to create the necessary video
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onBeforeStorySave(SocialStreamTemplate &$template, SocialStream &$stream, $content)
	{
		if ($template->context_type != 'videos') {
			return;
		}

		// Check if user is really allowed to do this?
		$cluster = ES::cluster($template->cluster_type, $template->cluster_id);

		if (!$cluster->canCreateVideos()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_CLUSTER_NOT_ALLOWED_TO_POST_UPDATE'), 500);
		}

		// Determine the type of the video
		$data = array();
		$data['source'] = $this->input->get('videos_type', '', 'word');
		$data['title'] = $this->input->get('videos_title', '', 'default');
		$data['description'] = $this->input->get('videos_description', '', 'default');
		$data['link'] = $this->input->get('videos_link', '', 'default');
		$data['category_id'] = $this->input->get('videos_category', 0, 'int');
		$data['uid'] = $template->cluster_id;
		$data['type'] = $template->cluster_type;

		// The video author will follow the stream Post As value
		$data['post_as'] = $template->post_as ? $template->post_as : SOCIAL_TYPE_USER;

		// Save options for the video library
		$saveOptions = array();

		// Determine this video created from the story form
		$saveOptions['isFromStory'] = true;

		// If this is a link source, we just load up a new video library
		if ($data['source'] == 'link') {
			$video = ES::video($template->cluster_id, SOCIAL_TYPE_PAGE);
		}

		// If this is a video upload, the id should be provided because videos are created first.
		if ($data['source'] == 'upload') {
			$id = $this->input->get('videos_id', 0, 'int');

			$video = ES::video($template->cluster_id, SOCIAL_TYPE_PAGE);
			$video->load($id);

			// Video library needs to know that we're storing this from the story
			$saveOptions['story'] = true;

			// if autoencode is enabled, we know if comes here, the encoding process is finished.
			$data['state'] = SOCIAL_VIDEO_PUBLISHED;

			// in #4032, the fix is put the video in unpublished state (previously was pending)
			// here we set the state back to pending if admin disable the autoencode. #4194
			if (!$this->config->get('video.autoencode')) {
				$data['state'] = SOCIAL_VIDEO_PENDING;
			}
		}

		// Check if user is really allowed to upload videos
		if ($video->id && !$video->canEdit()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING'), 500);
		}

		// Try to save the video
		$state = $video->save($data, array(), $saveOptions);

		// We should set this to hide the stream from being displayed.
		$stream->hidden = true;

		// We need to update the context
		$template->context_type = SOCIAL_TYPE_VIDEOS;
		$template->context_id = $video->id;
	}

	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		if ($streamItem->context_type != 'videos') {
			return;
		}

		// Change the isNew to false
		$table = ES::table("Video");
		$table->load($streamItem->context_id);
		$table->isnew = 0;

		// Set this video as scheduled so it would not displayed on video listing.
		if ($streamItem->isScheduled()) {
			$table->scheduled = SOCIAL_VIDEO_SCHEDULED;
		}

		$table->store();

		// Determine the type of the video
		$data = array();
		$data['source'] = $this->input->get('videos_type', '', 'word');

		// If this is a video upload, the id should be provided because videos are created first.
		if ($data['source'] == 'upload') {

			if (!$this->config->get('video.autoencode')) {
				$streamItem->notice = JText::_('COM_ES_VIDEOS_UPLOAD_SUCCESS_AWAIT_PROCESSING_STORY');

				$table->isnew = 1;
				$table->store();
			} else {
				// Load the video
				$video = ES::video($table->uid, $table->type, $table->id);

				// Get the status of the video
				$status = $video->status();

				// Assign points to the video creator
				if ($status === true) {
					ES::points()->assign('video.upload', 'com_easysocial', $video->getAuthor()->id);
				}

				// Published the video
				if ($status === true && $video->isNew()) {
					$video->publish(array('createStream' => true));
				}
			}

			ES::storage()->syncUsage($table->user_id);
		}

		// Lets create the scheduled item.
		$scheduled = ES::Scheduler();
		$scheduled->create($streamItem->uid, $template);

		$isScheduled = $streamItem->isScheduled() ? $stream->scheduled : false;

		// Process notification.
		$video = ES::video($table->uid, $table->type, $table->id);
		$video->notify($isScheduled);
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		// Deduct points when the user unliked a video
		if ($likes->type == 'videos.page.create' || $likes->type == 'videos.page.featured') {

			$table = ES::table("Video");
			$table->load($likes->uid);

			$video = ES::video($table);

			// since when liking own video no longer get points,
			// unlike own video should not deduct point too. #3471
			if ($likes->created_by != $video->user_id) {
				ES::points()->assign('video.reaction.remove.owner', 'com_easysocial', $video->user_id);

				ES::points()->assign('video.unlike', 'com_easysocial', $this->my->id);
			}
		}
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		$allowed = array('videos.page.create', 'videos.page.featured');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Get the actor of the likes
		$actor = ES::user($likes->created_by);

		$systemOptions = array(
			'context_type' => $likes->type,
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		$videoTable = ES::table('Video');
		$videoTable->load($likes->uid);

		$video = ES::video($videoTable->uid, $videoTable->type, $videoTable);

		// Get the page
		$page = ES::page($video->uid);

		// Get the permalink to the video
		$systemOptions['context_ids'] = $video->id;
		$systemOptions['url'] = $video->getPermalink(false);

		// For single video items on the stream
		if ($likes->type == 'videos.user.create') {
			$verb = 'create';
		}

		if ($likes->type == 'videos.user.featured') {
			$verb = 'featured';
		}

		ES::badges()->log('com_easysocial', 'videos.react', $likes->created_by, '');

		// assign points when the liker is not the video owner.
		if ($isNew && $likes->created_by != $video->user_id) {
			// Assign points to the video owner
			ES::points()->assign('video.reaction.add.owner', 'com_easysocial', $video->user_id);

			ES::points()->assign('video.like', 'com_easysocial', $likes->created_by);
		}

		// Notify the owner of the video first
		if ($likes->created_by != $video->user_id && $video->post_as != SOCIAL_TYPE_PAGE) {
			ES::notify('likes.item', array($video->user_id), false, $systemOptions, $page->notification);
		}

		// If this video is post as Page, notify all the page admin
		if ($video->post_as == SOCIAL_TYPE_PAGE) {
			ES::notify('likes.item', $page->getAdmins($likes->created_by), false, $systemOptions, $page->notification);
		}

		$element = 'videos';
		$verb = 'create';

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'page', $verb, array(), array($video->user_id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions, $page->notification);

		return;
	}

	/**
	 * Renders the notification item
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if ($item->cmd == 'page.video.create') {
			$hook = $this->getHook('notification', 'updates');
			$hook->execute($item);

			return;
		}

		// Someone posted a comment on the video
		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.replied' || $item->cmd == 'comments.involved') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// Someone likes a video
		if ($item->cmd == 'likes.item') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		return;
	}

	/**
	 * Determine if the cmd is allowed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array('page.video.create', 'comments.item', 'comments.replied', 'comments.involved', 'likes.item');

		return in_array($cmd, $allowed);
	}

	/**
	 * Determine if the context is allowed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedContext($context)
	{
		$allowed = array(
			'videos.page.create',
			'videos.page.featured',
			'pages'
		);

		return in_array($context, $allowed);
	}

	/**
	 * Triggered after a comment is deleted
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterDeleteComment(SocialTableComments &$comment)
	{
		$allowed = array('videos.page.create', 'videos.page.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		$actor = ES::user($comment->created_by);

		// For single video item on the stream
		$table = ES::table("Video");
		$table->load($comment->uid);

		$video = ES::video($table);
		$author = $video->getAuthor();

		// Assign points when someone comments on the author's video is deleted
		if ($actor->id != $author->id) {
			ES::points()->assign('video.comment.remove.owner', 'com_easysocial', $author->id);
		}

		// Assign points when a comment is deleted for a video
		ES::points()->assign('video.comment.remove', 'com_easysocial', $comment->created_by);
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('videos.page.create', 'videos.page.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

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

		// Set the email options
		$emailOptions   = array(
			'template' => 'apps/page/videos/comment.video.item',
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'comment' => $commentContent
		);

		$systemOptions  = array(
			'context_type' => $comment->element,
			'context_ids' => $comment->id,
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true,
			'content' => $commentContent
		);

		// Standard email subject
		$ownerTitle = 'APP_PAGE_VIDEOS_EMAILS_COMMENT_VIDEO_OWNER_SUBJECT';
		$involvedTitle = 'APP_PAGE_VIDEOS_EMAILS_COMMENT_VIDEO_INVOLVED_SUBJECT';

		$videoTable = ES::table('Video');
		$videoTable->load($comment->uid);

		$video = ES::video($videoTable->uid, $videoTable->type, $videoTable);

		// Assign points when someone comments on the author's video
		$author = $video->getAuthor();

		if ($actor->id != $author->id) {
			ES::points()->assign('video.comment.add.owner', 'com_easysocial', $author->id);
		}

		// Get the page
		$page = ES::page($video->uid);

		$emailOptions['permalink'] = $video->getPermalink(true, true);
		$systemOptions['url'] = $video->getPermalink(false, false, 'item', false);

		$element = 'videos';
		$verb = 'create';

		// Default email title should be for the owner
		$emailOptions['title'] = $ownerTitle;

		// Assign points for the author for posting a comment
		ES::points()->assign('video.comment.add', 'com_easysocial', $comment->created_by);
		ES::badges()->log('com_easysocial', 'videos.comment', $comment->created_by, '');

		// Notify the owner of the video first
		if ($video->user_id != $comment->created_by && $video->post_as != SOCIAL_TYPE_PAGE && !$comment->isChild()) {
			ES::notify('comments.item', array($video->user_id), $emailOptions, $systemOptions, $page->notification);
		}

		// If this video is post as Page, notify all the page admin
		if ($video->post_as == SOCIAL_TYPE_PAGE && !$comment->isChild()) {
			ES::notify('comments.item', $page->getAdmins($comment->created_by), $emailOptions, $systemOptions, $page->notification);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'page', $verb, array(), array($video->user_id, $comment->created_by));

		$emailOptions['title'] = $involvedTitle;
		$emailOptions['template'] = 'apps/page/videos/comment.video.involved';

		if ($recipients && !$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions, $page->notification);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/page/videos/comment.video.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions, $page->notification);
		}

		return;
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->uid;
		$target->type = 'videos';
		$target->endpoint = 'video.item';
		$target->query_string = 'video.item&id=' . $target->id;

		$item->target = $target;
	}

	/**
	 * Publishing the scheduled stream.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onPublishScheduledAppStory(SocialTableStream &$stream, SocialTableStreamItem &$streamItem, SocialTableStreamScheduled &$scheduled)
	{
		if ($streamItem->context_type != 'videos') {
			return;
		}

		$video = ES::table('Video');
		$video->load($scheduled->context_id);

		$lib = ES::video($video);

		// Make sure the video was ready before publishing.
		if (!$lib->isPublished()) {
			return false;
		}

		// Update video scheduled flag.
		$video->scheduled = SOCIAL_VIDEO_UNSCHEDULED;
		$video->isnew = 1;
		$video->store();

		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}
}
