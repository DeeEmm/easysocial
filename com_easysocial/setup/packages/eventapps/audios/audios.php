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

class SocialEventAppAudios extends SocialAppItem
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
		if ($context != SOCIAL_TYPE_AUDIOS) {
			return;
		}

		$audio = ES::table('Audio');
		$audio->load($uid);

		$lib = ES::audio($audio);

		if (!$lib->isViewable()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if audios should be enabled for a given event
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function enabled(SocialEvent &$event)
	{
		$params = $event->getParams();

		if (!$params->get('audios', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the app has stream filter
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function hasStreamFilter()
	{
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return parent::hasStreamFilter();
		}

		$event = ES::event($id);

		if (!$this->enabled($event)) {
			return false;
		}

		return parent::hasStreamFilter();
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = [];

		if (!$params->get('uploadAudios', true)) {
			$excludeVerb[] = 'create';
		}

		if (!$params->get('featuredAudios', true)) {
			$excludeVerb[] = 'featured';
		}

		if ($excludeVerb !== false) {
			$exclude['audios'] = $excludeVerb;
		}
	}


	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'audios') {
			return false;
		}

		$params = ES::registry($item->params);
		$event = ES::event($params->get('event'));

		if (!$event) {
			return;
		}

		$item->cnt = 1;

		if (!$event->isOpen() && !$event->isMember($this->my->id)) {
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
		if ($item->context != SOCIAL_TYPE_AUDIOS) {
			return;
		}

		// Determines if the viewer can view the stream item from this event
		$event = $item->getCluster();

		if (!$event) {
			return;
		}

		if (!$event->canViewItem()) {
			return;
		}

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if (!$this->enabled($event)) {
			return;
		}

		// Get the audio
		$audio = ES::audio($item->cluster_id, SOCIAL_TYPE_EVENT, $item->contextId);

		// Ensure that the audio is really published
		if (!$audio->isPublished()) {
			return;
		}

		$item->contentObj = $audio->toExportData($viewer);
		$item->targets = $event;

		$access = $event->getAccess();
		if ($viewer->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		$item->comments = $audio->getComments($item->verb, $item->uid);
		$item->likes = $audio->getLikes($item->verb, $item->uid);
		$item->show = true;
	}

	/**
	 * Generates the stream item for audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != SOCIAL_TYPE_AUDIOS) {
			return;
		}

		// Determines if the viewer can view the stream item from this event
		$event = $stream->getCluster();

		if (!$event) {
			return;
		}

		if (!$event->canViewItem()) {
			return;
		}

		// Decorate the stream item with the neccessary design
		$stream->color = '#5580BE';
		$stream->fonticon = 'ies-play';
		$stream->label = JText::_('COM_ES_AUDIO_STREAM_TITLE_AUDIO', true);
		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;

		if (!$this->enabled($event)) {
			return;
		}

		// Get the audio
		$audio = ES::audio($stream->cluster_id, SOCIAL_TYPE_EVENT, $stream->contextId);

		// Ensure that the audio is really published
		if (!$audio->isPublished()) {
			return;
		}

		$access = $event->getAccess();
		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $stream->actor->id == $this->my->id)) {
			$stream->editable = true;
			$stream->appid = $this->getApp()->id;
		}

		// Get the actor
		$actor = $stream->getActor();

		$perspective = $stream->getPerspective() == 'EVENTS' ? 'CLUSTERS' : 'STREAM';

		$this->set('perspective', $perspective);
		$this->set('stream', $stream);
		$this->set('audio', $audio);
		$this->set('actor', $actor);
		$this->set('event', $event);

		// Update the stream title
		$stream->title = parent::display('themes:/site/streams/audios/event/title.' . $stream->verb);
		$stream->preview = parent::display('themes:/site/streams/audios/preview');

		// Assign the comments library
		$stream->comments = $audio->getComments($stream->verb, $stream->uid);

		// Assign the likes library
		$stream->likes = $audio->getLikes($stream->verb, $stream->uid);

		// If the audio has a album art, add the opengraph tags
		$albumArt = $audio->getAlbumArt();

		if ($albumArt) {
			$stream->addOgImage($albumArt);
		}

		$stream->addOgDescription($audio->getDescription(false));
	}

	/**
	 * Prepares the audio in the story edit form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStoryEditForm(&$story, &$stream)
	{

		// preparing data for story edit.
		$data = array();

		// get audio from this stream uid.
		$model = ES::model('Audios');
		$audio = $model->getStreamAudio($stream->id);

		if ($audio) {
			$data['audio'] = $audio;
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
		// Only process audios
		if ($stream->context_type != 'audios') {
			return;
		}

		// Determine the type of the audio
		$data = array();
		$data['id'] = $this->input->get('audios_id', 0, 'int');
		$data['genre_id'] = $this->input->get('audios_genre', 0, 'int');
		$data['description'] = $this->input->get('audios_description', '', 'default');
		$data['iEncoding'] = $this->input->get('audios_isEncoding', false, 'bool');
		$data['link'] = $this->input->get('audios_link', '', 'default');
		$data['title'] = $this->input->get('audios_title', '', 'default');
		$data['source'] = $this->input->get('audios_type', '', 'default');

		$model = ES::model('audios');
		$state = $model->updateStreamAudio($stream->id, $data);

		ES::storage()->syncUsage($stream->actor_id);

		return true;
	}


	/**
	 * Generates the story form for audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareStoryPanel(SocialStory $story, $isEdit = false, $data = array())
	{
		// Get the event id
		$eventId = $story->cluster;

		// Ensure that the event allows users to upload audios
		$event = ES::event($eventId);

		if (!$event->allowAudios()) {
			return;
		}

		// Get the audio adapter
		$adapter = ES::audio($eventId, SOCIAL_TYPE_EVENT);

		if ($isEdit && isset($data['audio']) && $data['audio']) {
			$adapter = $data['audio'];
		}

		if (!$event->canAccessAudios() || !$event->canCreateAudios() || !$adapter->allowCreation()) {
			return;
		}

		// Get a list of audio genres
		$options = array('pagination' => false, 'ordering' => 'ordering');

		$model = ES::model('Audios');
		$genres = $model->getGenres($options);

		// Create a new plugin for this audio
		$plugin = $story->createPlugin('audios', 'panel');

		$title = JText::_('COM_ES_AUDIO');
		$plugin->title = $title;

		// Get the maximum upload filesize allowed
		$uploadLimit = $adapter->getUploadLimit();

		$supportedProviders = $adapter->getSupportedProviders();
		$supportedProviders = implode(', ', $supportedProviders);

		$theme = ES::themes();
		$theme->set('genres', $genres);
		$theme->set('uploadLimit', $uploadLimit);
		$theme->set('audio', $adapter);
		$theme->set('isEdit', $isEdit);
		$theme->set('title', $title);
		$theme->set('supportedProviders', $supportedProviders);

		$button = $theme->output('site/story/audios/button');
		$form = $theme->output('site/story/audios/form');

		$script = ES::script();
		$script->set('uploadLimit', $uploadLimit);
		$script->set('type', SOCIAL_TYPE_EVENT);
		$script->set('uid', $eventId);
		$script->set('audio', $adapter);
		$script->set('isEdit', $isEdit);

		$plugin->setHtml($button, $form);
		$plugin->setScript($script->output('site/story/audios/plugin'));

		return $plugin;
	}

	/**
	 * Processes after a story is saved on the site. When the story is stored, we need to create the necessary audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onBeforeStorySave(SocialStreamTemplate &$template, SocialStream &$stream, $content)
	{
		if ($template->context_type != 'audios') {
			return;
		}

		// Check if user is really allowed to do this?
		$cluster = ES::cluster($template->cluster_type, $template->cluster_id);

		if (!$cluster->canCreateAudios()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_CLUSTER_NOT_ALLOWED_TO_POST_UPDATE'), 500);
		}

		// Determine the type of the audio
		$data = array();
		$data['source'] = $this->input->get('audios_type', '', 'word');
		$data['title'] = $this->input->get('audios_title', '', 'default');
		$data['description'] = $this->input->get('audios_description', '', 'default');
		$data['link'] = $this->input->get('audios_link', '', 'default');
		$data['genre_id'] = $this->input->get('audios_genre', 0, 'int');
		$data['uid'] = $template->cluster_id;
		$data['type'] = $template->cluster_type;

		// Save options for the audio library
		$saveOptions = array();

		// Determine this audio created from the story form
		$saveOptions['isFromStory'] = true;

		// If this is a link source, we just load up a new audio library
		if ($data['source'] == 'link') {
			$audio = ES::audio($template->cluster_id, SOCIAL_TYPE_EVENT);
		}

		// If this is an audio upload, the id should be provided because audio are created first.
		if ($data['source'] == 'upload') {
			$id = $this->input->get('audios_id', 0, 'int');

			$audio = ES::audio($template->cluster_id, SOCIAL_TYPE_EVENT, $id);

			// audio library needs to know that we're storing this from the story
			// $saveOptions['story'] = true;

			// We cannot publish the audio if auto encoding is disabled
			if ($this->config->get('audio.autoencode')) {
				$data['state'] = SOCIAL_AUDIO_PUBLISHED;
			}
		}

		// Check if user is really allowed to upload audios
		if ($audio->id && !$audio->canEdit()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_EDITING'), 500);
		}

		// Try to save the audio
		$state = $audio->save($data, array(), $saveOptions);

		// We need to update the context
		$template->context_type = SOCIAL_TYPE_AUDIOS;
		$template->context_id = $audio->id;
	}

	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		// Only process audios
		if ($template->context_type != SOCIAL_TYPE_AUDIOS) {
			return;
		}

		// Determine the type of the audio
		$data = array();
		$data['source'] = $this->input->get('audios_type', '', 'word');

		// Change the isNew to false
		$table = ES::table('Audio');
		$table->load($streamItem->context_id);
		$table->isnew = $data['source'] == 'upload' && !$this->config->get('audio.autoencode') ? 1 : 0;

		// Set this audio as scheduled so it would not displayed on audio listing.
		if ($streamItem->isScheduled()) {
			$table->scheduled = SOCIAL_AUDIO_SCHEDULED;
		}

		$table->store();

		// If this is an audio upload, the id should be provided because audios are created first.
		if ($data['source'] == 'upload') {
			if (!$this->config->get('audio.autoencode')) {
				$streamItem->notice = JText::_('COM_ES_AUDIO_UPLOAD_SUCCESS_AWAIT_PROCESSING_STORY');
			} else {
				// Load the audio
				$audio = ES::audio($table->uid, $table->type, $table->id);

				// Get the status of the audio
				$status = $audio->status();

				// Published the audio
				if ($status === true && $audio->isNew()) {
					$audio->publish(array('createStream' => true));
				}
			}

			ES::storage()->syncUsage($this->my->id);
		}

		// Lets create the scheduled item.
		$scheduled = ES::Scheduler();
		$scheduled->create($streamItem->uid, $template);

		$isScheduled = $streamItem->isScheduled() ? $stream->scheduled : false;

		// Process notification.
		$audio = ES::audio($table->uid, $table->type, $table->id);
		$audio->notify($isScheduled);
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		// Deduct points when the user unliked an audio
		if ($likes->type == 'audios.event.create' || $likes->type == 'audios.event.featured') {

			$audioTable = ES::table('Audio');
			$audioTable->load($likes->uid);

			$audio = ES::audio($audioTable);

			// since when liking own audio no longer get points,
			// unlike own audio should not deduct point too. #3471
			if ($likes->created_by != $audio->user_id) {

				ES::points()->assign('audio.reaction.remove.owner', 'com_easysocial', $audio->user_id);

				ES::points()->assign('audio.unlike', 'com_easysocial', $this->my->id);
			}
		}
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		$allowed = array('audios.event.create', 'audios.event.featured');

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

		$audioTable = ES::table('Audio');
		$audioTable->load($likes->uid);

		$audio = ES::audio($audioTable);

		// Get the permalink to the audio
		$systemOptions['context_ids'] = $audio->id;
		$systemOptions['url'] = $audio->getPermalink(false);
		$verb = 'create';

		// For single audio items on the stream
		if ($likes->type == 'audios.event.create') {
			$verb = 'create';
		}

		if ($likes->type == 'audios.event.featured') {
			$verb = 'featured';
		}

		ES::badges()->log('com_easysocial', 'audios.react', $likes->created_by, '');

		if ($isNew && $likes->created_by != $audio->user_id) {

			// Assign points to the audio owner
			ES::points()->assign('audio.reaction.add.owner', 'com_easysocial', $audio->user_id);

			// assign points when the liker is not the audio owner. #3471
			ES::points()->assign('audio.like', 'com_easysocial', $likes->created_by);

			// Notify the owner of the audio first
			ES::notify('likes.item', array($audio->user_id), false, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, 'audios', 'event', $verb, array(), array($audio->user_id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions);

		return;
	}

	/**
	 * Renders the notification item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if ($item->cmd == 'event.audio.create') {
			$hook = $this->getHook('notification', 'updates');
			$hook->execute($item);

			return;
		}

		// Someone posted a comment on the audio
		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.involved' || $item->cmd == 'comments.replied') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// Someone likes an audio
		if ($item->cmd == 'likes.item') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		return;
	}

	/**
	 * Determine if cmd is allowed for notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array(
			'event.audio.create',
			'comments.item',
			'comments.involved',
			'comments.replied',
			'likes.item'
		);

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
			'audios.event.create',
			'audios.event.featured',
			'groups'
		);

		return in_array($context, $allowed);
	}

	/**
	 * Triggered after a comment is deleted
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterDeleteComment(SocialTableComments &$comment)
	{
		$allowed = array('audios.event.create', 'audios.event.featured');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// For single audio item on the stream
		$table = ES::table('Audio');
		$table->load($comment->uid);

		$audio = ES::audio($table);
		$author = $audio->getAuthor();

		// Assign points to the audio owner
		if ($comment->created_by != $author->id) {
			ES::points()->assign('audio.comment.remove.owner', 'com_easysocial', $author->id);
		}

		// Assign points when a comment is deleted for an audio
		ES::points()->assign('audio.comment.remove', 'com_easysocial', $comment->created_by);
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('audios.event.create', 'audios.event.featured');

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
		$emailOptions = array(
			'template' => 'site/audios/comment.audio.item',
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
		$ownerTitle = 'APP_USER_AUDIO_EMAILS_COMMENT_AUDIO_ITEM_SUBJECT';
		$involvedTitle = 'APP_USER_AUDIO_EMAILS_COMMENT_AUDIO_INVOLVED_SUBJECT';

		$audioTable = ES::table('Audio');
		$audioTable->load($comment->uid);

		$audio = ES::audio($audioTable);

		$author = $audio->getAuthor();

		// Assign points to the audio owner
		if ($actor->id != $author->id) {
			ES::points()->assign('audio.comment.add.owner', 'com_easysocial', $author->id);
		}


		$emailOptions['permalink'] = $audio->getPermalink(true, true);
		$systemOptions['url'] = $audio->getPermalink(false, false, 'item', false);

		// Default email title should be for the owner
		$emailOptions['title'] = $ownerTitle;

		// Assign points for the author for posting a comment
		ES::points()->assign('audios.comment.add', 'com_easysocial', $comment->created_by);
		ES::badges()->log('com_easysocial', 'audios.comment', $comment->created_by, '');

		// Notify the owner of the audio first
		if ($audio->user_id != $comment->created_by && !$comment->isChild()) {
			ES::notify('comments.item', array($audio->user_id), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'audios', 'event', 'create', array(), array($audio->user_id, $comment->created_by));

		$emailOptions['title'] = $involvedTitle;
		$emailOptions['template'] = 'site/audios/comment.audio.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'site/audios/comment.audio.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
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
		$target->type = 'audios';
		$target->endpoint = 'audio.item';
		$target->query_string = 'audio.item&id=' . $target->id;

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
		if ($streamItem->context_type != 'audios') {
			return false;
		}

		$audio = ES::table('Audio');
		$audio->load($scheduled->context_id);

		$lib = ES::audio($audio);

		// Make sure the audio was ready before publishing.
		if (!$lib->isPublished()) {
			return false;
		}

		// Update audio scheduled flag.
		$audio->scheduled = SOCIAL_AUDIO_UNSCHEDULED;
		$audio->isnew = 1;
		$audio->store();

		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}
}
