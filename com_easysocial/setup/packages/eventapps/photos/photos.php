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

class SocialEventAppPhotos extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_PHOTO) {
			return;
		}

		// Get the photo owner
		$photo = ES::table('Photo');
		$photo->load($uid);

		$lib = ES::photo($photo->uid, $photo->type, $photo);

		if (!$photo->viewable()) {
			return false;
		}

		return true;
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

		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// When user likes a single photo
		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.involved' || $item->cmd == 'comments.replied') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// When user likes a single photo
		if ($item->cmd == 'likes.item' || $item->cmd == 'likes.involved') {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When user is tagged in a photo
		if ($item->cmd == 'photos.tagged') {
			$hook = $this->getHook('notification', 'tagging');
			$hook->execute($item);
		}

		return;
	}

	/**
	 * Determine if the cmd is allowed for this notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array(
			'comments.item',
			'comments.replied',
			'comments.involved',
			'likes.item',
			'likes.involved',
			'photos.tagged',
			'likes.likes',
			'comments.comment.add'
		);

		return in_array($cmd, $allowed);
	}

	/**
	 * Determine if the context is allowed for the notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedContext($context)
	{
		$allowed = array(
			'photos.event.upload',
			'stream.event.upload',
			'photos.event.add',
			'albums.event.create',
			'photos.event.uploadAvatar',
			'photos.event.updateCover',
			'tagging'
		);

		return in_array($context, $allowed);
	}

	/**
	 * Fixed legacy issues where the app is displayed on apps list of a group.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function appListing($view, $id, $type)
	{
		return false;
	}

	/**
	 * Determine if the stream can be view by the user
	 *
	 * @since	3.2.9
	 * @access	private
	 */
	private function canViewItem($item)
	{
		// We only want to process related items
		if ($item->context != 'photos') {
			return false;
		}

		// Do not allow user to access photos if it's not enabled
		if (!$this->config->get('photos.enabled') && $item->verb != 'uploadAvatar' && $item->verb != 'updateCover') {
			return false;
		}

		// Load up the event
		$event = ES::event($item->cluster_id);

		if (!$event || !$event->id) {
			return false;
		}

		// Test if the viewer can really view the item
		if (!$event->canViewItem()) {
			return false;
		}

		return true;
	}

	/**
	 * Trigger for onPrepareStream
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onPrepareStream(SocialStreamItem &$item)
	{
		if (!$this->canViewItem($item)) {
			return;
		}

		// Get the element and context out
		$element = $item->context;
		$uid = $item->contextId;

		// Get the photo object
		$photoId = $item->contextId;
		$photo = $this->getPhotoObject($item);

		// Process actions on the stream
		$this->processActions($item);

		// Decorate the stream
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get the app params.
		$params = $this->getParams();

		if ($item->verb == 'uploadAvatar' && $params->get('stream_avatar', true)) {
			$this->prepareUploadAvatarStream($item);
		}

		if ($item->verb == 'updateCover' && $params->get('stream_cover', true)) {
			$this->prepareUpdateCoverStream($item);
		}

		// Photo stream types. Uploaded via the story form
		if ($item->verb == 'share' && $params->get('stream_share', true)) {
			$this->prepareSharePhotoStream($item);
		}

		if (($item->verb == 'add' || $item->verb == 'create') && $params->get('stream_upload', true)) {
			$this->preparePhotoStream($item);
		}

		// Append the opengraph tags
		$item->addOgDescription($item->content);

		return true;
	}

	/**
	 * Processes the stream actions
	 *
	 * @since   1.2
	 * @access  public
	 */
	private function processActions(SocialStreamItem &$item)
	{
		// Whether the item is shared or uploaded via the photo albums, we need to bind the repost here
		$repost = ES::get('Repost', $item->uid, SOCIAL_TYPE_STREAM, SOCIAL_APPS_GROUP_EVENT);
		$item->repost = $repost;

		$photoStreams = array('add', 'create', 'share');

		// lets check how many photos in this stream
		if (count($item->contextIds) == 1 && in_array($item->verb, $photoStreams)) {
			$photo 		= ES::table('Photo');
			$photo->load($item->contextIds[0]);

			// if single photos, we reset the repost and use photo id instead. #5730
			$repost = ES::get('Repost', $photo->id, SOCIAL_TYPE_PHOTO, SOCIAL_APPS_GROUP_EVENT);
			$repost->setStreamId($item->uid);

			$item->repost = $repost;
		}

		// For photo items that are shared on the stream
		if ($item->verb =='share') {

			// By default, we'll use the stream id as the object id
			$objectId = $item->uid;
			$objectType = SOCIAL_TYPE_STREAM;
			$commentUrl = ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false));

			// When there is only 1 photo that is shared on the stream, we need to link to the photo item
			// We will only alter the id
			// if (count($item->contextIds) == 1) {
			// 	$photo = ES::table('Photo');
			// 	$photo->load($item->contextIds[0]);

			// 	$objectId = $photo->id;
			// 	$objectType = SOCIAL_TYPE_PHOTO;
			// 	$commentUrl = $photo->getPermalink(true, false, 'item', false);
			// }

			// Append the likes action on the stream
			$likes = ES::likes();
			$likes->get($objectId, $objectType, 'upload', SOCIAL_APPS_GROUP_EVENT, $item->uid, array('clusterId' => $item->cluster_id));
			$item->likes    = $likes;

			// Append the comment action on the stream
			$comments = ES::comments($objectId, $objectType, 'upload', SOCIAL_APPS_GROUP_EVENT,  array('url' => $commentUrl, 'clusterId' => $item->cluster_id), $item->uid);
			$item->comments = $comments;

			return;
		}

		if ($item->verb == 'uploadAvatar' || $item->verb == 'updateCover') {
			$photo = $this->getPhotoFromParams($item);

			// Apply comments on the stream
			$commentParams = array('url' => $item->getPermalink(false, false, false));

			// Set the cluster id so that we know the comment is belong to this cluster
			$commentParams['clusterId'] = $item->cluster_id;

			// Append the likes action on the stream
			$item->likes = ES::likes()->get($photo->id, SOCIAL_TYPE_PHOTO, $item->verb, SOCIAL_APPS_GROUP_EVENT, $item->uid, array('clusterId' => $item->cluster_id));
			$item->comments = ES::comments($photo->id, SOCIAL_TYPE_PHOTO, $item->verb, SOCIAL_APPS_GROUP_EVENT, $commentParams, $item->uid);
		}

		// Here onwards, we are assuming the user is uploading the photos via the albums area.

		// If there is more than 1 photo uploaded, we need to link the likes and comments on the album
		// If there is more than 1 photo uploaded, we need to link the likes and comments on the album
		if (count($item->contextIds) > 1) {
			$photos = $this->getPhotoFromParams($item);
			$photo = false;

			if ($photos instanceof SocialTablePhoto) {
				$photo = $photos;
			}

			if (is_array($photos)) {
				$photo = $photos[0];
			}

			// If we can't get anything, skip this
			if (!$photo) {
				return;
			}

			$element = SOCIAL_TYPE_ALBUM;
			$uid = $photo->album_id;

			// Get the album object
			$album = ES::table('Album');
			$album->load($photo->album_id);

			// Format the likes for the stream
			$likes = ES::likes();
			$likes->get($photo->album_id, 'albums', 'create', SOCIAL_APPS_GROUP_EVENT, false, array('clusterId' => $item->cluster_id));
			$item->likes = $likes;

			// Apply comments on the stream
			// element = albums.group.create
			$commentParams = array('url' => $album->getPermalink(true, false, 'item', false));
			$comments = ES::comments($photo->album_id, 'albums', 'create', SOCIAL_APPS_GROUP_EVENT, $commentParams);

			// Stream id must be 0 for albums. #4984
			$comments->stream_id = 0;
			$item->comments = $comments;

			return;
		}
	}

	/**
	 * Retrieve the table object from the stream item params
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getPhotoObject(SocialStreamItem &$item, $privacy = null)
	{
		if (count($item->contextIds) > 0 && $item->verb != 'uploadAvatar' && $item->verb != 'updateCover') {

			$photos = array();

			// We only want to get a maximum of 5 photos if we have more than 1 photo to show.
			$ids = array_reverse($item->contextIds);
			$limit = 5;

			for ($i = 0; $i < count($ids) && $i < $limit; $i++) {
				$photo = ES::table('Photo');
				$photo->load($ids[$i]);

				$photos[] = $photo;
			}

			return $photos;
		}

		// Load up the photo object
		$photo = ES::table('Photo');

		// Get the context id.
		$id = $item->contextId;
		$photo->load($id);

		return $photo;
	}

	/**
	 * Prepares the stream items for photo uploads shared on the stream
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function prepareSharePhotoStream(SocialStreamItem &$item)
	{
		// Get photo objects
		$photos = $this->getPhotoFromParams($item);

		if (!$photos || !isset($photos[0])) {
			return;
		}

		// Get the stream object.
		$stream = ES::table('Stream');
		$stream->load($item->uid);

		// Get the first photo's album id.
		$albumId = $photos[ 0 ]->album_id;
		$album = ES::table('Album');
		$album->load($albumId);

		// Get total number of items uploaded.
		$count = count($item->contextIds);
		$totalPhotos = count($photos);
		$remainingPhotoCount = ($count > $totalPhotos) ? $count - $totalPhotos : 0;

		// Get the actor
		$actor = $item->actor;
		$event = ES::event($item->cluster_id);

		$access = $event->getAccess();

		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->appid = $this->getApp()->id;
		}

		// Get params of the app
		$app = ES::table('app');
		$app->loadByElement('photos', 'group', 'apps');
		$params = $app->getParams();

		$this->set('content', $stream->content);
		$this->set('event', $event);
		$this->set('total', $totalPhotos);
		$this->set('photos', $photos);
		$this->set('album', $album);
		$this->set('actor', $actor);
		$this->set('params', $params);
		$this->set('item', $item);
		$this->set('remainingPhotoCount', $remainingPhotoCount);

		// old data compatibility
		$verb = ($item->verb == 'create') ? 'add' : $item->verb;

		$item->title = parent::display('themes:/site/streams/photos/event/share.title');
		$item->preview = parent::display('themes:/site/streams/photos/preview');
	}

	/**
	 * Prepares the stream items for photo uploads
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function preparePhotoStream(SocialStreamItem &$item)
	{
		// Get photo objects
		$photos = $this->getPhotoObject($item);

		if (!$photos || !isset($photos[0])) {
			return;
		}

		$uid = $item->contextId;
		$element = $item->context;

		// Get the unique item and element to be used
		if (count($item->contextIds) > 1) {
			$uid = $photos[0]->album_id;
			$element = SOCIAL_TYPE_ALBUM;
		}

		// Get the first photo's album.
		$albumId = $photos[0]->album_id;
		$album = ES::table('Album');
		$album->load($albumId);

		// old data compatibility
		$item->verb = ($element != SOCIAL_TYPE_ALBUM && $item->verb == 'create') ? 'add' : $item->verb;

		// Get total number of items uploaded.
		$count = count($item->contextIds);
		$totalPhotos = count($photos);
		$remainingPhotoCount = ($count > $totalPhotos) ? $count - $totalPhotos : 0;

		// Get the actor
		$actor = $item->actor;
		$event = ES::event($item->cluster_id);

		$access = $event->getAccess();

		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		$item->comments = ES::comments($uid, $element, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)),'clusterId' => $item->cluster_id), $item->uid);

		$hasPassword = $album->hasPassword();

		//check if siteadmin, owner album, setting enable, has password, verify password
		$siteAdmin = ES::isSiteAdmin();
		$lib = ES::albums($item->actor->id, $item->comments->group, $albumId);

		$verifyPassword = $lib->verifyPassword();

		$isMine = $album->isMine();

		$isVerified = true;

		if (($hasPassword && !$verifyPassword) && !$isMine) {
			$isVerified = false;
		}

		// Get params of the app
		$params = $this->getParams();

		$this->set('item', $item);
		$this->set('count', $count);
		$this->set('event', $event);
		$this->set('totalPhotos', $totalPhotos);
		$this->set('count', $count);
		$this->set('photos', $photos);
		$this->set('album', $album);
		$this->set('actor', $actor);
		$this->set('params', $params);
		$this->set('remainingPhotoCount', $remainingPhotoCount);

		// old data compatibility
		$verb = ($item->verb == 'create') ? 'add' : $item->verb;

		$item->title = parent::display('themes:/site/streams/photos/event/add.title');

		if ($isVerified) {
			$item->preview = parent::display('themes:/site/streams/photos/preview');
		} else {
			$item->preview = parent::display('themes:/site/streams/photos/verify');
		}
	}

	/**
	 * Prepares the upload avatar stream
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function prepareUploadAvatarStream(SocialStreamItem &$item)
	{
		// Get the photo object
		$photo = $this->getPhotoFromParams($item);

		$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)),'clusterId' => $item->cluster_id), $item->uid);

		$this->set('photo', $photo);
		$this->set('actor', $item->actor);
		$this->set('item', $item);
		$this->set('event', $item->getCluster());

		$item->title = parent::display('themes:/site/streams/photos/event/avatar.title');
		$item->preview = parent::display('themes:/site/streams/photos/avatar.preview');
	}

	/**
	 * Prepares the upload avatar stream
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function prepareUpdateCoverStream(SocialStreamItem &$item)
	{
		$element = $item->context;
		$uid = $item->contextId;

		// Get the photo object
		$photo = $this->getPhotoFromParams($item);

		// Get the cover of the group
		$event = ES::event($item->cluster_id);
		$cover = $event->getCoverData();

		$item->comments = ES::comments($item->contextId, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)),'clusterId' => $item->cluster_id), $item->uid);

		$this->set('item', $item);
		$this->set('cover', $cover);
		$this->set('photo', $photo);
		$this->set('actor', $item->actor);

		$item->title = parent::display('themes:/site/streams/photos/event/cover.title');
		$item->preview = parent::display('themes:/site/streams/photos/cover.preview');
	}

	/**
	 * Processes a saved story.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterStorySave(&$stream, $streamItem, &$template)
	{
		$photos = $this->input->get('photos', '', 'array');

		// If there's no data, we don't need to do anything here.
		if (empty($photos)) {
			return;
		}

		if (empty($template->content)) {
			$template->content .= '<br />';
		}

		// Now that we know the saving is successfull, we want to update the state of the photo table.
		foreach ($photos as $photoId) {
			$table = ES::table('Photo');
			$table->load($photoId);

			$album = ES::table('Album');
			$album->load($table->album_id);

			$table->state = SOCIAL_STATE_PUBLISHED;

			if ($streamItem->isScheduled() && $streamItem->context_type == 'photos') {
				$table->state = SOCIAL_STATE_SCHEDULED;
			}

			$table->store();

			// Determine if there's a cover for this album.
			if (!$album->hasCover())
			{
				$album->cover_id = $table->id;
				$album->store();
			}

			// #910
			// if we detected the storage is amazon and local file deletion is enabled,
			// then we will temporary store the photo id for now for later email processsing.
			$imageSrc = '';
			if ($this->config->get('storage.photos', 'joomla') == 'amazon' && $this->config->get('storage.amazon.delete')) {
				$imageSrc = '[photo:' . $table->id . ']';
			} else {
				$imageSrc = $table->getSource('thumbnail');
			}

			$template->content 	.= '<img src="' . $imageSrc . '" width="128" />';
		}

		ES::storage()->syncUsage($this->my->id);

		// Lastly, create an entry in the scheduled table.
		$scheduled = ES::Scheduler();
		$scheduled->create($streamItem->uid, $template);

		return true;
	}

	/*
	 * Save trigger which is called after really saving the object.
	 */
	public function onAfterSave(&$data)
	{
		// for now we only support the photo added by person. later on we will support
		// for groups, events and etc.. the source will determine the type.
		$source = isset($data->source) ? $data->source : 'people';
		$actor = ($source == 'people') ? ES::get('People', $data->created_by) : '0';

		// save into activity streams
		$item = new StdClass();
		$item->actor_id = $actor->get('node_id');
		$item->source_type = $source;
		$item->source_id = $actor->id;
		$item->context_type = 'photos';
		$item->context_id = $data->id;
		$item->verb = 'upload';
		$item->target_id = $data->album_id;

		//$item   = get_object_vars($item);
		//ES::get('Stream')->addStream(array($item, $item, $item));
		ES::get('Stream')->addStream($item);
		return true;
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
		$model = ES::model('Photos');
		$photos = $model->getStreamPhotos($stream->id);

		if ($photos) {
			$data['photos'] = $photos;
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
		// new photos
		$photos	= $this->input->get('photos', array(), 'array');

		// If there's no data, we don't need to do anything here.
		if (!$photos) {
			return;
		}

		$model = ES::model('Photos');
		$state = $model->updateStreamPhotos($stream->id, $photos);

		ES::storage()->syncUsage($stream->actor_id);

		return true;
	}


	/**
	 * Prepares the story panel for groups story
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onPrepareStoryPanel($story, $isEdit = false, $data = array())
	{
		$event = ES::event($story->cluster);

		if (!$event->canCreatePhotos()) {
			return;
		}

		// Get current logged in user.
		$access = $event->getAccess();

		// Create the story plugin
		$plugin = $story->createPlugin("photos", "panel");

		// Load up the themes
		$theme = ES::get('Themes');
		$theme->set('title', $plugin->title);

		// check max photos upload here.
		if ($access->exceeded('photos.max', $event->getTotalPhotos())) {
			$theme->set('exceeded', JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_MAX_UPLOAD', $access->get('photos.uploader.max')));
		}

		// check max photos upload daily here.
		if ($access->exceeded('photos.maxdaily', $event->getTotalPhotos(true))) {
			$theme->set('exceeded', JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_DAILY_MAX_UPLOAD', $access->get('photos.uploader.maxdaily')));
		}

		$button = $theme->output('site/story/photos/button');
		$form = $theme->output('site/story/photos/form', array('data' => $data, 'edit' => $isEdit));

		// Attach the script files
		$script = ES::script();
		$maxSize = $access->get('photos.maxsize', 5);

		$script->set('type', SOCIAL_TYPE_EVENT);
		$script->set('uid', $event->id);
		$script->set('maxFileSize', $maxSize . 'M');
		$scriptFile = $script->output('site/story/photos/plugin');

		$plugin->setHtml($button, $form);
		$plugin->setScript($scriptFile);

		return $plugin;
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		$allowed = array('photos.event.upload', 'stream.event.upload', 'photos.event.uploadAvatar', 'photos.event.updateCover');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// Set the default element.
		$element = $likes->type;
		$uid = $likes->uid;

		// Get the photo object
		$photo = ES::table('Photo');

		if ($likes->type == 'stream.event.upload') {
			// $uid is the stream id, not the photo id.
			// Need to find the photo id back from stream table
			$streamItem = ES::table('streamitem');
			$streamItem->load(array('uid' => $uid));

			$photoId = $streamItem->context_id;

			$photo->load($photoId);

			// @points: photos.unlike
			// since when liking own video no longer get points,
			// unlike own video should not deduct point too. #3471
			if ($likes->created_by != $photo->user_id) {
				// Deduct points for the current user for unliking this item
				$photo->assignPoints('photos.unlike', ES::user()->id);

				// Assign points to the photo owner
				ES::points()->assign('photo.reaction.remove.owner', 'com_easysocial', $photo->user_id);
			}
		}

		if ($likes->type == 'photos.event.create' ||
			$likes->type == 'photos.event.upload' ||
			$likes->type == 'photos.event.uploadAvatar' ||
			$likes->type == 'photos.event.updateCover') {

			$photo->load($likes->uid);

			// since when liking own video no longer get points,
			// unlike own video should not deduct point too. #3471
			if ($likes->created_by != $photo->user_id) {
				$photo->assignPoints('photos.unlike', ES::user()->id);

				// Assign points to the photo owner
				ES::points()->assign('photo.reaction.remove.owner', 'com_easysocial', $photo->user_id);
			}
		}
	}
	/**
	 * Triggers after a like is saved
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		// @legacy
		// photos.user.add should just be photos.user.upload since they are pretty much the same
		$allowed = array('photos.event.upload', 'stream.event.upload', 'albums.event.create', 'photos.event.add', 'photos.event.uploadAvatar', 'photos.event.updateCover');

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// For likes on albums when user uploads multiple photos within an album
		if ($likes->type == 'albums.event.create') {

			// Since the uid is tied to the album we can get the album object
			$album = ES::table('Album');
			$album->load($likes->uid);

			// Get the actor of the likes
			$actor = ES::user($likes->created_by);

			$systemOptions = array(
				'context_type' => $likes->type,
				'context_ids' => $album->id,
				'url' => $album->getPermalink(false, false, 'item', false),
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);


			// Notify the owner of the photo first
			if ($likes->created_by != $album->user_id) {
				ES::notify('likes.item', array($album->user_id), false, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'albums', 'event', 'create', array(), array($album->user_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

		// For single photo items on the stream
		$allowed = array('photos.event.upload', 'stream.event.upload', 'photos.event.add', 'photos.event.uploadAvatar', 'photos.event.updateCover');
		if (in_array($likes->type, $allowed)) {

			// Get the actor of the likes
			$actor = ES::user($likes->created_by);

			$systemOptions = array(
				'context_type' => $likes->type,
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			);

			// If this item is multiple share on the stream, we need to get the photo id here.
			if ($likes->type == 'stream.event.upload') {

				// Since this item is tied to the stream, we need to load the stream object
				$stream = ES::table('Stream');
				$stream->load($likes->uid);

				// Get the photo object from the context id of the stream
				$model = ES::model('Stream');
				$origin = $model->getContextItem($likes->uid);

				$photo = ES::table('Photo');
				$photo->load($origin->context_id);

				$systemOptions['context_ids'] = $photo->id;
				$systemOptions['url'] = $stream->getPermalink(false, false, false);

				$element = 'stream';
				$verb = 'upload';
			}

			// For single photo items on the stream
			if ($likes->type == 'photos.event.upload' || $likes->type == 'photos.event.add' || $likes->type == 'photos.event.uploadAvatar' || $likes->type == 'photos.event.updateCover') {
				$photo = ES::table('Photo');
				$photo->load($likes->uid);

				$systemOptions['context_ids'] = $photo->id;
				$systemOptions['url'] = $photo->getPermalink(false, false, 'item', false);

				$element = 'photos';
				$verb = 'upload';
			}

			if ($likes->type == 'photos.event.uploadAvatar') {
				$verb = 'uploadAvatar';
			}

			if ($likes->type == 'photos.event.updateCover') {
				$verb = 'updateCover';
			}

			if ($isNew && $likes->created_by != $photo->user_id) {

				// Assign points to the photo owner
				ES::points()->assign('photo.reaction.add.owner', 'com_easysocial', $photo->user_id);

				// assign points when the liker is not the photo owner. #3471
				$photo->assignPoints('photos.like', $likes->created_by);

				// Notify the owner of the photo first
				ES::notify('likes.item', array($photo->user_id), false, $systemOptions);
			}

			// Get additional recipients since photos has tag
			$additionalRecipients = array();
			$this->getTagRecipients($additionalRecipients, $photo);

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'event', $verb, $additionalRecipients, array($photo->user_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

	}

	/**
	 * Triggered when a comment are deleted
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function onAfterDeleteComment(&$comment)
	{
		$allowed = array('photos.event.upload', 'albums.event.create', 'stream.event.upload', 'photos.event.add', 'photos.event.uploadAvatar', 'photos.event.updateCover');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		$photo = false;

		// If this item is multiple share on the stream, we need to get the photo id here.
		if ($comment->element == 'stream.event.upload') {
			$stream = ES::table('Stream');
			$stream->load($comment->uid);

			// Get the photo object from the context id of the stream
			$model = ES::model('Stream');
			$origin = $model->getContextItem($comment->uid);

			$photo = ES::table('Photo');
			$photo->load($origin->context_id);
		}

		// For single photo items on the stream
		if ($comment->element == 'photos.event.upload' || $comment->element == 'photos.event.add' || $comment->element == 'photos.event.uploadAvatar' || $comment->element == 'photos.event.updateCover') {
			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($comment->uid);
		}

		// Assign points to the photo owner
		if ($photo && $comment->created_by != $photo->user_id) {
			ES::points()->assign('photo.comment.remove.owner', 'com_easysocial', $photo->user_id);
		}

		// Assign points when a comment is deleted for photos
		ES::points()->assign('photos.comment.remove', 'com_easysocial', $comment->created_by);
	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = array('photos.event.upload', 'albums.event.create', 'stream.event.upload', 'photos.event.add', 'photos.event.uploadAvatar', 'photos.event.updateCover');

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

		// For likes on albums when user uploads multiple photos within an album
		if ($comment->element == 'albums.event.create') {

			// Since the uid is tied to the album we can get the album object
			$album = ES::table('Album');
			$album->load($comment->uid);

			$event = ES::event($album->uid);

			// Set the email options
			$emailOptions = array(
				'title' => 'APP_EVENT_PHOTOS_EMAILS_COMMENT_ALBUM_ITEM_SUBJECT',
				'template' => 'apps/event/photos/comment.album.item',
				'permalink' => $album->getPermalink(true, true),
				'comment' => $commentContent,
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true)
			);

			$systemOptions = array(
				'context_type' => $comment->element,
				'context_ids' => $comment->id,
				'url' => $album->getPermalink(false, false, 'item', false),
				'actor_id' => $comment->created_by,
				'uid' => $comment->uid,
				'aggregate' => true,
				'content' => $commentContent
			);

			// Notify the owner of the photo first
			if ($comment->created_by != $album->user_id && !$comment->isChild()) {
				ES::notify('comments.item', array($album->user_id), $emailOptions, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$unfilteredResults = $this->getStreamNotificationTargets($comment->uid, 'albums', 'event', 'create', array(), array($album->user_id, $comment->created_by));

			$recipients = array();
			foreach ($unfilteredResults as $userId) {
				if (!$event->isInviteOnly() || ($event->isInviteOnly() && $event->canViewItem($userId))) {
					$recipients[] = $userId;
				}
			}

			$emailOptions['title'] = 'APP_EVENT_PHOTOS_EMAILS_COMMENT_ALBUM_INVOLVED_SUBJECT';
			$emailOptions['template'] = 'apps/event/photos/comment.album.involved';

			if ($recipients && !$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/event/photos/comment.album.replied';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}

			return;
		}

		// For comments made on photos
		$allowed = array('photos.event.upload', 'stream.event.upload', 'photos.event.add', 'photos.event.uploadAvatar', 'photos.event.updateCover');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Set the email options
		$emailOptions = array(
			'template' => 'apps/event/photos/comment.photo.item',
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'comment' => $commentContent
		);

		$systemOptions = array(
			'context_type' => $comment->element,
			'context_ids' => $comment->id,
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true,
			'content' => $commentContent
		);

		$element = $comment->element;

		// Standard email subject
		$ownerTitle = 'APP_EVENT_PHOTOS_EMAILS_COMMENT_PHOTO_ITEM_SUBJECT';
		$involvedTitle = 'APP_EVENT_PHOTOS_EMAILS_COMMENT_PHOTO_INVOLVED_SUBJECT';

		// If this item is multiple share on the stream, we need to get the photo id here.
		if ($comment->element == 'stream.event.upload') {

			// Since this item is tied to the stream, we need to load the stream object
			$stream = ES::table('Stream');
			$stream->load($comment->uid);

			// Get the photo object from the context id of the stream
			$model = ES::model('Stream');
			$origin = $model->getContextItem($comment->uid);

			$photo = ES::table('Photo');
			$photo->load($origin->context_id);

			// Get the permalink to the photo
			$emailOptions['permalink'] = $stream->getPermalink(true, true);
			$systemOptions['url'] = $stream->getPermalink(false, false, false);

			$element = 'stream';
			$verb = 'upload';
		}

		// For single photo items on the stream
		if ($comment->element == 'photos.event.upload' || $comment->element == 'photos.event.add' || $comment->element == 'photos.event.uploadAvatar' || $comment->element == 'photos.event.updateCover') {
			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($comment->uid);

			// Get the permalink to the photo
			$emailOptions['permalink'] = $photo->getPermalink(true, true);
			$systemOptions['url'] = $photo->getPermalink(false, false, 'item', false);

			if ($comment->element === 'photos.event.add') {
				$element = 'photos';
				$verb = 'add';
			}
		}

		if ($comment->element == 'photos.event.uploadAvatar') {
			$verb = 'uploadAvatar';

			$ownerTitle = 'APP_EVENT_PHOTOS_EMAILS_COMMENT_PROFILE_PICTURE_ITEM_SUBJECT';
			$involvedTitle = 'APP_EVENT_PHOTOS_EMAILS_COMMENT_PROFILE_PICTURE_INVOLVED_SUBJECT';
		}

		if ($comment->element == 'photos.event.updateCover') {
			$verb = 'updateCover';

			$ownerTitle = 'APP_EVENT_PHOTOS_EMAILS_COMMENT_PROFILE_COVER_ITEM_SUBJECT';
			$involvedTitle = 'APP_EVENT_PHOTOS_EMAILS_COMMENT_PROFILE_COVER_INVOLVED_SUBJECT';
		}

		$emailOptions['title'] = $ownerTitle;

		// @points: photos.like
		// Assign points for the author for liking this item
		$photo->assignPoints('photos.comment.add', $comment->created_by);

		// Assign points to the photo owner
		if ($comment->created_by != $photo->user_id) {
			ES::points()->assign('photo.comment.add.owner', 'com_easysocial', $photo->user_id);
		}

		$event = ES::event($photo->uid);

		// Notify the owner of the photo first
		if ($photo->user_id != $comment->created_by && !$comment->isChild()) {
			ES::notify('comments.item', array($photo->user_id), $emailOptions, $systemOptions);
		}

		// Get additional recipients since photos has tag
		$additionalRecipients = array();
		$this->getTagRecipients($additionalRecipients, $photo);

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$unfilteredResults = $this->getStreamNotificationTargets($comment->uid, $element, 'event', $verb, $additionalRecipients, array($photo->user_id, $comment->created_by));

		$recipients = array();
		foreach ($unfilteredResults as $userId) {
			if (!$event->isInviteOnly() || ($event->isInviteOnly() && $event->canViewItem($userId))) {
				$recipients[] = $userId;
			}
		}

		$emailOptions['title'] = $involvedTitle;
		$emailOptions['template'] = 'apps/event/photos/comment.photo.involved';

		if ($recipients && !$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/event/photos/comment.photo.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}

		return;
	}


	/**
	 * Retrieves a list of tag recipients on a photo
	 *
	 * @since   1.2
	 * @access  private
	 */
	private function getTagRecipients(&$recipients, SocialTablePhoto &$photo, $exclusion = array())
	{
		// Get a list of tagged users
		$tags = $photo->getTags(true);

		if (!$tags) {
			return;
		}

		foreach ($tags as $tag) {
			if (!in_array($tag->uid, $exclusion)) {
				$recipients[] = $tag->uid;
			}

		}
	}

	/**
	 * Retrieve the table object from the stream item params
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getPhotoFromParams( SocialStreamItem &$item , $privacy = null, $limit = 5)
	{
		if (count($item->contextIds) > 0 && $item->verb != 'uploadAvatar' && $item->verb != 'updateCover') {

			$options = array(
				'inclusion' => $item->contextIds,
				'limit' => $limit
			);

			// Passing scheduled state to retrieve scheduled photos.
			if ($item->isScheduled()) {
				$options['state'] = SOCIAL_STATE_SCHEDULED;
			}

			$model = ES::model('photos');
			$photos = $model->getPhotos($options);

			return $photos;


			// $photos = array();

			// // We only want to get a maximum of 5 photos if we have more than 1 photo to show.
			// $ids = array_reverse($item->contextIds);
			// $limit = 5;

			// for ($i = 0; $i < count($ids) && $i < $limit; $i++) {
			// 	$photo = ES::table('Photo');
			// 	$photo->load($ids[$i]);

			// 	$photos[] = $photo;
			// }

			// return $photos;
		}

		// Load up the photo object
		$photo = ES::table( 'Photo' );

		// Get the context id.
		$id = $item->contextId;
		$photo->load( $id );

		return $photo;
	}

	/**
	 * Generate the stream for photo REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if (!$this->canViewItem($item)) {
			return;
		}

		// Prepare the stream
		$this->onPrepareStream($item);

		$limit = 5;

		// Get photo objects
		$photos = $this->getPhotoFromParams($item, null, 0);

		if (!$photos) {
			return;
		}

		if (!is_array($photos)) {
			$photos = array($photos);
		}

		$count = count($item->contextIds);
		$totalPhotos = count($photos) > $limit ? $limit : count($photos);
		$remainingPhotoCount = ($count > $totalPhotos) ? $count - $totalPhotos : 0;

		$albumId = $photos[0]->album_id;
		$album = ES::table('Album');
		$album->load($albumId);

		$items = array();

		foreach ($photos as $photo) {
			$photoObject = $photo->toExportData($viewer);

			if (($key = array_search($photoObject->id, $item->contextIds)) !== false) {
				unset($item->contextIds[$key]);
			}

			$photoObject->width = $photo->getWidth();
			$photoObject->height = $photo->getHeight();

			$orientation = 'landscape';

			if ($photoObject->width < $photoObject->height) {
				$orientation = 'portrait';
			}

			$photoObject->orientation = $orientation;
			$photoObject->extension = $photo->getExtension('large');

			$items[] = $photoObject;
		}

		$source = new stdClass();
		$source->total = $count;
		$source->previewTotal = $totalPhotos;
		$source->remainingPhotoCount = $remainingPhotoCount;
		$source->album = $album->toExportData($viewer);
		$source->previewItems = $items;

		$item->targets = $item->getCluster();
		$item->contentObj = $source;

		// in web, stream appearance is based on the item->title
		// in Rest api, it will based on item->show.
		$item->show = $item->title ? true : false;
	}

	/**
	 * Retrieve the content of the notification object
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

		// Shared through stream
		if ($item->context_type == 'stream.event.upload') {
			$streamId = $item->context_ids;

			if ($item->cmd == 'comments.item' || $item->cmd == 'comments.involved' || $item->cmd == 'likes.item' || $item->cmd == 'comments.replied') {
				$streamId = $item->uid;
			}

			$target->id = $streamId;
			$target->type = 'stream';
			$target->endpoint = 'stream.item';
			$target->query_string = 'stream.item&id=' . $target->id;

			$item->target = $target;

			return;
		}

		$uid = $item->uid;

		// $uid is photo for this context. Hence we need to get the album id instead.
		if ($item->context_type !== 'albums.event.create') {
			$photo = ES::table('Photo');
			$photo->load($item->uid);

			if ($photo->id) {
				$uid = $photo->album_id;
			}

			$target->nav = 'photo';
			$target->nav_id = $item->uid;
		}

		$target->id = $uid;
		$target->type = 'album';
		$target->endpoint = $target->type . '.item';
		$target->query_string = $target->type . '.item&id=' . $target->id;

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
		if ($streamItem->context_type != 'photos') {
			return false;
		}

		$photo = ES::table('Photo');
		$photo->load($scheduled->context_id);

		// Update video scheduled flag.
		$photo->state = SOCIAL_STATE_PUBLISHED;
		$photo->store();

		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}
}
