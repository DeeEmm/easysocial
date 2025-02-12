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

ES::import('admin:/includes/group/group');

class SocialUserAppPhotos extends SocialAppItem
{

	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'photos') {
			return;
		}

		// Get the photo owner
		$photo 	= ES::table('Photo');
		$photo->load($uid);

		$lib = ES::photo($photo->uid, $photo->type, $photo);

		if ($lib->isblocked() || !$lib->viewable()) {
			return false;
		}

		return true;
	}


	/**
	 * Determines if the viewer can delete the comments
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function canDeleteComment(SocialTableComments &$comment, SocialUser &$viewer)
	{
		$allowed = array('photos.user.create');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// Get the photo owner
		$photo 	= ES::table('Photo');
		$photo->load($comment->uid);

		if ($photo->user_id == $viewer->id) {
			return true;
		}

		return;
	}

	/**
	 * Responsible to generate the activity logs.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'photos') {
			return;
		}

		// Get the context id.
		$id = $item->contextId;

		// Load the profiles table.
		$photo = ES::table('Photo');
		$state = $photo->load($id);

		$album 	= ES::table('Album');
		$album->load($photo->album_id);

		// Get the actor
		$actor = $item->actor;
		$target = false;

		// Determines if the photo is shared on another person's timeline
		if ($item->verb == 'share' && $item->targets) {
			$target = $item->targets[0];
		}


		$term = $this->getGender($item->actor);

		$this->set('term', $term);
		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('album', $album);
		$this->set('photo', $photo);

		$count = count($item->contextIds);
		$this->set('count', $count);

		if ($item->verb == 'uploadAvatar') {
			$file = 'avatar.title';
		}

		if ($item->verb == 'updateCover') {
			$file = 'cover.title';
		}

		if ($item->verb == 'create' || $item->verb == 'add') {
			$file = 'add.title';
		}

		if ($item->verb == 'share') {
			$file = 'share.title';
		}

		$item->display 	= SOCIAL_STREAM_DISPLAY_MINI;
		$item->title	= parent::display('logs/' . $file);
		$item->content	= parent::display('logs/content');

		$privacyRule = 'photos.view';
		if ($item->verb == 'uploadAvatar' || $item->verb == 'updateCover') {
			$privacyRule = 'core.view';
		}

		if ($includePrivacy) {
			$my = ES::user();

			$sModel = ES::model('Stream');
			$aItem  = $sModel->getActivityItem($item->aggregatedItems[0]->uid, 'uid');

			$streamId = count($aItem) > 1 ? '' : $item->aggregatedItems[0]->uid;
			$item->privacy = ES::privacy($my->id)->form($photo->id, 'photos', $item->actor->id, $privacyRule, false, $streamId);
		}
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
		if($item->context_type != 'photos')
		{
			return false;
		}

		$item->cnt = 1;

		if($includePrivacy)
		{
			$my         = ES::user();
			$privacy	= ES::privacy($my->id);

			$sModel = ES::model('Stream');
			$aItem 	= $sModel->getActivityItem($item->id, 'uid');


			$uid 		= $aItem[0]->context_id;
			$rule 		= 'photos.view';
			$context 	= 'photos';

			if(count($aItem) > 0)
			{
				$uid 		= $aItem[0]->target_id;

				if($aItem[0]->target_id)
				{
					$rule 		= 'albums.view';
					$context 	= 'albums';
					$uid 		= $aItem[0]->target_id;
				}
			}

			if(!$privacy->validate($rule, $uid, $context, $item->actor_id))
			{
				$item->cnt = 0;
			}

		}

		return true;
	}

	/**
	 * Determine if the stream can be view by the user
	 *
	 * @since	3.2.9
	 * @access	private
	 */
	private function canViewItem($item, $includePrivacy = true)
	{
		// We only want to process related items
		if ($item->context != 'photos') {
			return false;
		}

		// Do not allow user to access photos if it's not enabled
		if (!$this->config->get('photos.enabled') && $item->verb != 'uploadAvatar' && $item->verb != 'updateCover') {
			return false;
		}

		$element = $item->context;
		$uid = $item->contextId;

		$useAlbum = count($item->contextIds) > 1 ? true : false;

		// Load the photo object
		$photo = ES::table('Photo');
		$photo->load((int) $item->contextId);

		$privacy = $this->my->getPrivacy();
		$privacyRule = ($useAlbum) ? 'albums.view' : 'photos.view';

		if ($item->verb == 'uploadAvatar' || $item->verb == 'updateCover') {
			$privacyRule = 'core.view';
		}

		if ($includePrivacy) {
			if ($privacyRule == 'photos.view') {
				// we need to check the photo's album privacy to see if user allow to view or not.
				if (!$privacy->validate('photos.view', $photo->id, SOCIAL_TYPE_PHOTO, $item->actor->id)) {
					return false;
				}

				// Also check for its album privacy
				if ($photo->album_id) {
					$uid = $photo->album_id;
					$element = 'albums';

					$table = ES::table('Album');
					$table->load($uid);

					if (!$table->isCover() && !$table->isAvatar() && !$privacy->validate($privacyRule, $uid, $element, $item->actor->id)) {
						return false;
					}
				}

			} else {

				if ($useAlbum && $privacyRule =='albums.view') {
					$uid = $photo->album_id;
					$element = 'albums';

					// Determine if the user can view the album
					if (!$privacy->validate($privacyRule, $uid, $element, $item->actor->id)) {
						return false;
					}

					$table = ES::table('Album');
					$table->load($uid);

					// Check for story album
					if ($table->isStory()) {
						$uid = $item->uid;
						$element = 'story';
						$privacyRule = 'core.view';
					}
				}

				// Determine if the user can view this current context
				if (!$privacy->validate($privacyRule, $uid, $element, $item->actor->id)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Trigger for onPrepareStream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if (!$this->canViewItem($item, $includePrivacy)) {
			return;
		}

		$privacy = $this->my->getPrivacy();
		$this->processActions($item , $privacy);

		// Get the single context id
		$id = $item->contextId;
		$albumId = '';

		$params = $this->getApp()->getParams();

		// Display a full stream
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Process user avatar updates
		if($item->verb == 'uploadAvatar' && $params->get('uploadAvatar', true)) {
			$this->prepareUploadAvatarStream($item, $privacy, $includePrivacy);
		}

		// Process user cover updates
		if ($item->verb == 'updateCover' && $params->get('uploadCover', true)) {
			$this->prepareUpdateCoverStream($item, $privacy, $includePrivacy);
		}

		// Photo stream types. Uploaded via the story form
		$photoStreams = array('add', 'create', 'share');

		// Old data compatibility
		$item->verb = $item->verb == 'create' ? 'add' : $item->verb;

		$useAlbum = count($item->contextIds) > 1 ? true : false;

		// Process photo streams for users
		if (in_array($item->verb , $photoStreams) && $params->get('uploadPhotos', true)) {
			$this->preparePhotoStream($item, $privacy , $includePrivacy, $useAlbum);
		}

		// Append the opengraph tags
		$item->addOgDescription($item->content);

		return;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = [];

		if(! $params->get('uploadAvatar', true)) {
			$excludeVerb[] = 'uploadAvatar';
		}

		if (! $params->get('uploadCover', true)) {
			$excludeVerb[] = 'updateCover';
		}

		if (! $params->get('uploadPhotos', true)) {
			$excludeVerb[] = 'add';
			$excludeVerb[] = 'create';
			$excludeVerb[] = 'share';
		}

		if ($excludeVerb !== false) {
			$exclude['photos'] = $excludeVerb;
		}
	}

	/**
	 * Process the stream actions
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function processActions(SocialStreamItem &$item , $privacy)
	{
		$group = $item->cluster_id ? $item->cluster_type : SOCIAL_APPS_GROUP_USER;

		// Whether the item is shared or uploaded via the photo albums, we need to bind the repost here
		$repost = ES::get('Repost', $item->uid, SOCIAL_TYPE_STREAM, $group);
		$item->repost = $repost;

		$photoStreams = array('add', 'create', 'share');

		// lets check how many photos in this stream
		if (count($item->contextIds) == 1 && in_array($item->verb, $photoStreams)) {
			$photo = ES::table('Photo');
			$photo->load($item->contextIds[0]);

			// if single photos, we reset the repost and use photo id instead. #5730
			$repost = ES::get('Repost', $photo->id, SOCIAL_TYPE_PHOTO, $group);
			$repost->setStreamId($item->uid);

			$item->repost = $repost;
		}

		// For photo items that are shared on the stream
		if ($item->verb =='share') {

			// By default, we'll use the stream id as the object id
			$objectId = $item->uid;
			$objectType = SOCIAL_TYPE_STREAM;
			$commentUrl = ESR::stream(array('layout' => 'item', 'id' => $item->uid));

			// When there is only 1 photo that is shared on the stream, we need to link to the photo item
			// We will only alter the id
			// if (count($item->contextIds) == 1) {
			// 	$photo = ES::table('Photo');
			// 	$photo->load($item->contextIds[0]);

			// 	$objectId = $photo->id;
			// 	$objectType = SOCIAL_TYPE_PHOTO;
			// 	$commentUrl = $photo->getPermalink();
			// }

			// Append the likes action on the stream
			$likes = ES::likes();
			$likes->get($objectId, $objectType, 'upload', $group, $item->uid);
			$item->likes = $likes;

			// Append the comment action on the stream
			$comments = ES::comments($objectId, $objectType, 'upload', $group,  array('url' => $commentUrl), $item->uid);
			$item->comments = $comments;

			return;
		}

		// If there is more than 1 photo uploaded, we need to link the likes and comments on the album
		if (count($item->contextIds) > 1) {

			$photo = false;
			$photos = $this->getPhotoFromParams($item, $privacy);

			if ($photos instanceof SocialTablePhoto) {
				$photo = $photos;
			}

			// There are possibility where viewer try to simulate to view a photo that they cannot see
			// We should prevent this app from generating errors.
			if (is_array($photos) && empty($photos)) {
				return;
			}

			if (is_array($photos)) {
				$photo = $photos[0];
			}

			// If we can't get anything, skip this
			if (!$photo) {
				return;
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
			$likes->get($photo->album_id, 'albums', 'create', $group);
			$item->likes = $likes;

			// Apply comments on the stream
			$commentParams = array('url' => $album->getPermalink());
			$comments = ES::comments($photo->album_id, 'albums', 'create', $group, $commentParams);
			$item->comments = $comments;

			return;
		}
	}

	/**
	 * Prepares the stream items for photo uploads
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preparePhotoStream(&$item, $privacy, $includePrivacy = true, $useAlbum = false)
	{
		// There could be more than 1 photo
		$photos = array();

		// The default element and uid
		$element = $item->context;
		$uid = $item->contextId;

		// Get photo objects
		$photos = $this->getPhotoFromParams($item, $privacy);

		if (!$photos) {
			return;
		}

		if ($this->my->isSiteAdmin() || $this->my->id == $item->actor->id) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		// Get the unique item and element to be used
		if (count($item->contextIds) > 1) {
			$uid = $photos[0]->album_id;
			$element = SOCIAL_TYPE_ALBUM;
			$useAlbum = true;
		}

		// Get the first photo's album id.
		$albumId = $photos[0]->album_id;

		// Determine the privacy rule to use.
		$privacyRule = ($useAlbum) ? 'albums.view' : 'photos.view';

		// Load up the album object
		$album = ES::table('Album');
		$album->load($albumId);

		// Determine if this album is story album
		if ($useAlbum && $album->isStory()) {
			$uid = $item->uid;
			$element = 'story';
			$privacyRule = 'core.view';
		}

		// Get the actor
		$actor = $item->actor;

		// Ensure that they are all unique
		$item->contextIds = array_unique($item->contextIds);

		$count = count($item->contextIds);
		$totalPhotos = count($photos);
		$remainingPhotoCount = ($count > $totalPhotos) ? $count - $totalPhotos : 0;

		$ids = array();

		foreach ($photos as $photo) {
			$ids[] = $photo->id;
		}

		// Determine if there is a target
		$target = $item->targets ? $item->targets[0] : '';

		// Get params
		$app = $this->getApp();
		$params = $app->getParams();
		$hasPassword = $album->hasPassword();

		//check if siteadmin, owner album, setting enable, has password, verify password
		$siteAdmin = ES::isSiteAdmin();
		$lib = ES::albums($item->actor->id, $item->post_as, $albumId);

		$verifyPassword = $lib->verifyPassword();

		$isMine = $album->isMine();

		$isVerified = true;

		if (($hasPassword && !$verifyPassword) && !$isMine) {
			$isVerified = false;
		}

		$this->set('target', $target);
		$this->set('totalPhotos', $totalPhotos);
		$this->set('ids', $ids);
		$this->set('count', $count);
		$this->set('photos', $photos);
		$this->set('album', $album);
		$this->set('actor', $actor);
		$this->set('content', $item->content);
		$this->set('params', $params);
		$this->set('item', $item);
		$this->set('remainingPhotoCount', $remainingPhotoCount);

		// old data compatibility
		$verb = ($item->verb == 'create') ? 'add' : $item->verb;

		// Do not allow user to edit an album stream. #3156
		if ($verb == 'add') {
			$item->editable = false;
		}

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = parent::display('themes:/site/streams/photos/user/' . $verb . '.title');

		if ($isVerified) {
			$item->preview = parent::display('themes:/site/streams/photos/preview');
		} else {
			$item->preview = parent::display('themes:/site/streams/photos/verify');
		}

		if ($includePrivacy) {
			$item->privacy 	= $privacy->form($uid, $element, $item->actor->id, $privacyRule, false, $item->uid, array(), array('iconOnly' => true));
		}
	}

	/**
	 * Retrieves the Gender representation of the language string
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function getGender(SocialUser $user)
	{
		// Get the term to be displayed
		$value = $user->getFieldData('GENDER');

		$term = 'NOGENDER';

		if ($value == 1) {
			$term = 'MALE';
		}

		if ($value == 2) {
			$term = 'FEMALE';
		}

		return $term;
	}

	/**
	 * Prepares the upload avatar stream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function prepareUploadAvatarStream(SocialStreamItem &$item, $privacy)
	{
		// Load the photo
		$photo = $this->getPhotoFromParams($item);
		$term = $this->getGender($item->actor);

		if ($photo->storage != SOCIAL_STORAGE_JOOMLA) {
			$ePhoto = ES::table('Photo');
			$ePhoto->load($item->contextId);

			if ($ePhoto->storage == SOCIAL_STORAGE_JOOMLA) {
				$photo = $ePhoto;
			}
		}

		$this->set('term', $term);
		$this->set('photo', $photo);
		$this->set('actor', $item->actor);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = parent::display('themes:/site/streams/photos/user/avatar.title');
		$item->preview = parent::display('themes:/site/streams/photos/avatar.preview');

		$item->privacy = $privacy->form($item->contextId, $item->context, $item->actor->id, 'core.view', false, $item->uid, array(), array('iconOnly' => true));
	}

	/**
	 * Generates the stream when user updated their profile cover
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function prepareUpdateCoverStream(SocialStreamItem &$item, $privacy)
	{
		$photo = $this->getPhotoFromParams($item);
		$cover = $item->actor->getCoverData();

		// There is a possibility that this cover is missing.
		if (!$cover) {
			return;
		}

		// Get the term to be displayed
		$term = $this->getGender($item->actor);

		$this->set('cover', $cover);
		$this->set('photo', $photo);
		$this->set('actor', $item->actor);
		$this->set('term', $term);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = parent::display('themes:/site/streams/photos/user/cover.title');
		$item->preview = parent::display('themes:/site/streams/photos/cover.preview');

		// The privacy should be tied to the photo item
		$item->privacy = $privacy->form($item->contextId, $item->context, $item->actor->id, 'core.view', false, $item->uid, array(), array('iconOnly' => true));
	}

	/**
	 * Retrieve the table object from the stream item params
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getPhotoFromParams(SocialStreamItem &$item , $privacy = null, $limit = 5)
	{
		if (count($item->contextIds) > 0 && $item->verb != 'uploadAvatar' && $item->verb != 'updateCover') {

			$ids = $item->contextIds;

			$options = array(
				'inclusion' => $ids,
				'limit' => 5,
				'privacy' => true,
			);

			// Passing scheduled state to retrieve scheduled photos.
			if ($item->isScheduled()) {
				$options['state'] = SOCIAL_STATE_SCHEDULED;
			}

			$model = ES::model('photos');
			$photos = $model->getPhotos($options);

			return $photos;


			// $photos = array();

			// $ids = array_reverse($item->contextIds);

			// $i = 0;

			// // We only want to get a maximum of 5 photos if we have more than 1 photo to show.
			// foreach ($ids as $id) {
			// 	if ($i >= 5) {
			// 		break;
			// 	}

			// 	$photo = ES::table('Photo');
			// 	$raw = isset($item->contextParams[$id]) ? $item->contextParams[$id] : '';

			// 	if ($raw) {
			// 		$obj = ES::json()->decode($raw);

			// 		dump($obj);

			// 		$fromArticleStream = isset($obj->articlestream) && $obj->articlestream ? true : false;

			// 		// Check for this photo stream item if posted from the article page
			// 		// If this photo stream coming from the article then don't bind the photo.
			// 		if (!$fromArticleStream) {
			// 			$photo->bind($obj);
			// 		}

			// 		if (!$photo->id) {
			// 			$photo->load($id);
			// 		}
			// 	} else {
			// 		$photo->load($id);
			// 	}

			// 	// Determine if the user can view this photo or not.
			// 	if (!$item->cluster_id && $privacy->validate('photos.view', $photo->id, SOCIAL_TYPE_PHOTO, $item->actor->id)) {
			// 		$photos[] = $photo;
			// 	} else if ($item->cluster_id) {
			// 		$photos[] = $photo;
			// 	}

			// 	$i++;
			// }

			// return $photos;
		}

		// Load up the photo object
		$photo = ES::table('Photo');

		// Get the context id.
		$id = $item->contextId;
		$raw = isset($item->contextParams[$id]) ? $item->contextParams[$id] : '';

		if ($raw) {
			$obj = ES::json()->decode($raw);
			$photo->bind($obj);

			if (!$photo->id) {
				$photo->load($id);
			}

			return $photo;
		}

		$photo->load($id);

		return $photo;
	}

	/**
	 * Processes a saved story.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterStorySave(&$stream , $streamItem , &$template)
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
			if (!$album->hasCover()) {
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

	/**
	 * Save trigger which is called after really saving the object.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterSave(&$data)
	{
		// for now we only support the photo added by person. later on we will support
		// for groups, events and etc.. the source will determine the type.
		$source		= isset($data->source) ? $data->source : 'people';
		$actor		= ($source == 'people') ? ES::get('People', $data->created_by) : '0';

		// save into activity streams
		$item   = new StdClass();
		$item->actor_id 	= $actor->get('node_id');
		$item->source_type	= $source;
		$item->source_id 	= $actor->id;
		$item->context_type = 'photos';
		$item->context_id 	= $data->id;
		$item->verb 		= 'upload';
		$item->target_id 	= $data->album_id;

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

		if ($state) {
			$model->updateStreamAccess($stream, $photos);
		}

		ES::storage()->syncUsage($stream->actor_id);

		return true;
	}


	/**
	 * Prepares the photos in the story form
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onPrepareStoryPanel($story, $isEdit = false, $data = array())
	{
		if (!$this->config->get('photos.enabled')) {
			return;
		}

		// Get user access
		$access = $this->my->getAccess();

		if (!$access->allowed('photos.create') || !$this->getApp()->hasAccess($this->my->profile_id)) {
			return;
		}

		// Create the story plugin
		$plugin = $story->createPlugin("photos", "panel");

		$theme = ES::themes();

		// Check max photos upload here.
		if ($access->exceeded('photos.uploader.max', $this->my->getTotalPhotos())) {
			$theme->set('exceeded', JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_MAX_UPLOAD', $access->get('photos.uploader.max')));
		}

		// check max photos upload daily here.
		if ($access->exceeded('photos.uploader.maxdaily', $this->my->getTotalPhotos(true))) {
			$theme->set('exceeded', JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_DAILY_MAX_UPLOAD', $access->get('photos.uploader.maxdaily')));
		}

		$theme->set('title', $plugin->title);

		$button = $theme->output('site/story/photos/button');
		$form = $theme->output('site/story/photos/form', array('data' => $data, 'edit' => $isEdit));

		// Attach the script files
		$script = ES::script();

		$script->set('type', SOCIAL_TYPE_USER);
		$script->set('uid', $this->my->id);
		$script->set('maxFileSize', $access->get('photos.uploader.maxsize') . 'M');
		$scriptFile = $script->output('site/story/photos/plugin');

		$plugin->setHtml($button, $form);
		$plugin->setScript($scriptFile);

		return $plugin;
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		if (!$likes->type) {
			return;
		}

		// Set the default element.
		$element = $likes->type;
		$uid = $likes->uid;

		// Get the photo object
		$photo = ES::table('Photo');

		if ($likes->type == 'stream.user.upload') {
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

		if ($likes->type == 'photos.user.create' ||
			$likes->type == 'photos.user.add' ||
			$likes->type == 'photos.user.upload' ||
			$likes->type == 'photos.user.uploadAvatar' ||
			$likes->type == 'photos.user.updateCover') {

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
	 * Retrieves a list of tag recipients on a photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function getTagRecipients(&$recipients, SocialTablePhoto &$photo, $exclusion = array())
	{
		// Get a list of tagged users
		$tags 	= $photo->getTags(true);

		if (!$tags) {
			return;
		}

		foreach ($tags as $tag) {

			if (!in_array($tag->uid, $exclusion)) {
				$recipients[]	= $tag->uid;
			}

		}
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		// @legacy
		// photos.user.add should just be photos.user.upload since they are pretty much the same
		$allowed = ['photos.user.upload', 'stream.user.upload', 'albums.user.create', 'photos.user.add', 'photos.user.uploadAvatar', 'photos.user.updateCover'];

		if (!in_array($likes->type, $allowed)) {
			return;
		}

		// For likes on albums when user uploads multiple photos within an album
		if ($likes->type == 'albums.user.create') {

			// Since the uid is tied to the album we can get the album object
			$album 	= ES::table('Album');
			$album->load($likes->uid);

			// Get the actor of the likes
			$actor = ES::user($likes->created_by);

			$systemOptions  = array(
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
			$recipients = $this->getStreamNotificationTargets($likes->uid, 'albums', 'user', 'create', array(), array($album->user_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

		// For single photo items on the stream
		$allowed = array('photos.user.upload', 'stream.user.upload', 'photos.user.add', 'photos.user.uploadAvatar', 'photos.user.updateCover');

		if (in_array($likes->type, $allowed)) {

			// Get the actor of the likes
			$actor = ES::user($likes->created_by);

			$systemOptions = [
				'context_type' => $likes->type,
				'actor_id' => $likes->created_by,
				'uid' => $likes->uid,
				'aggregate' => true
			];

			// If this item is multiple share on the stream, we need to get the photo id here.
			if ($likes->type == 'stream.user.upload') {

				// Since this item is tied to the stream, we need to load the stream object
				$stream = ES::table('Stream');
				$stream->load($likes->uid);

				// Get the photo object from the context id of the stream
				$model = ES::model('Stream');
				$origin = $model->getContextItem($likes->uid);

				$photo = ES::table('Photo');
				$photo->load($origin->context_id);

				$systemOptions['context_ids'] = $photo->id;

				// Get the permalink to the photo
				$systemOptions['url'] = $stream->getPermalink(false, false, false);

				$element 	= 'stream';
				$verb		= 'upload';
			}

			// For single photo items on the stream
			if ($likes->type == 'photos.user.upload' || $likes->type == 'photos.user.add' || $likes->type == 'photos.user.uploadAvatar' || $likes->type == 'photos.user.updateCover') {
				$photo = ES::table('Photo');
				$photo->load($likes->uid);

				$systemOptions['context_ids'] = $photo->id;
				$systemOptions['url'] = $photo->getPermalink(false, false, 'item', false);

				$element = 'photos';
				$verb = 'upload';
			}

			if ($likes->type == 'photos.user.uploadAvatar') {
				$verb = 'uploadAvatar';
			}

			if ($likes->type == 'photos.user.updateCover') {
				$verb = 'updateCover';
			}

			if ($isNew && $likes->created_by != $photo->user_id) {

				// Assign points to the photo owner
				ES::points()->assign('photo.reaction.add.owner', 'com_easysocial', $photo->user_id);

				// @points: photos.like
				// assign points when the liker is not the photo owner. #3471
				$photo->assignPoints('photos.like' , $likes->created_by);

				// Notify the owner of the photo first
				ES::notify('photos.likes', array($photo->user_id), false, $systemOptions);
			}

			// Get additional recipients since photos has tag
			$additionalRecipients 	= array();
			$this->getTagRecipients($additionalRecipients, $photo);

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'user', $verb, $additionalRecipients, array($photo->user_id, $likes->created_by));

			ES::notify('likes.involved', $recipients, false, $systemOptions);

			return;
		}

	}

	/**
	 * Triggered when a comment save occurs
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		$allowed = ['photos.user.upload', 'albums.user.create', 'stream.user.upload', 'photos.user.add', 'photos.user.uploadAvatar', 'photos.user.updateCover'];

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
		if ($comment->element == 'albums.user.create') {

			// Since the uid is tied to the album we can get the album object
			$album = ES::table('Album');
			$album->load($comment->uid);

			// Set the email options
			$emailOptions = array(
				'title' => 'APP_USER_PHOTOS_EMAILS_COMMENT_ALBUM_ITEM_SUBJECT',
				'template' => 'apps/user/photos/comment.album.item',
				'permalink' => $album->getPermalink(true, true),
				'comment' => $commentContent,
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true)
			);

			$systemOptions  = array(
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
				ES::notify('albums.comment.add', array($album->user_id), $emailOptions, $systemOptions);
			}

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($comment->uid, 'albums', 'user', 'create', array(), array($album->user_id, $comment->created_by));

			$emailOptions['title'] = 'APP_USER_PHOTOS_EMAILS_COMMENT_ALBUM_INVOLVED_SUBJECT';
			$emailOptions['template'] = 'apps/user/photos/comment.album.involved';

			if (!$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/user/photos/comment.album.replied';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}

			return;
		}

		// For comments made on photos
		$allowed = ['photos.user.upload', 'stream.user.upload', 'photos.user.add', 'photos.user.uploadAvatar', 'photos.user.updateCover'];

		if (in_array($comment->element, $allowed)) {

			// Set the email options
			$emailOptions = array(
				'template' => 'apps/user/photos/comment.photo.item',
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

			$element = $comment->element;

			// Standard email subject
			$ownerTitle = 'APP_USER_PHOTOS_EMAILS_COMMENT_PHOTO_ITEM_SUBJECT';
			$involvedTitle = 'APP_USER_PHOTOS_EMAILS_COMMENT_PHOTO_INVOLVED_SUBJECT';

			// If this item is multiple share on the stream, we need to get the photo id here.
			if ($comment->element == 'stream.user.upload') {

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
			if ($comment->element == 'photos.user.upload' || $comment->element == 'photos.user.add' || $comment->element == 'photos.user.uploadAvatar' || $comment->element == 'photos.user.updateCover') {
				// Get the photo object
				$photo = ES::table('Photo');
				$photo->load($comment->uid);

				// Get the permalink to the photo
				$emailOptions['permalink'] = $photo->getPermalink(true, true);
				$systemOptions['url'] = $photo->getPermalink(false, false, 'item', false);

				$element = 'photos';
				$verb = 'add';
			}

			if ($comment->element == 'photos.user.uploadAvatar') {
				$verb = 'uploadAvatar';

				$ownerTitle = 'APP_USER_PHOTOS_EMAILS_COMMENT_PROFILE_PICTURE_ITEM_SUBJECT';
				$involvedTitle = 'APP_USER_PHOTOS_EMAILS_COMMENT_PROFILE_PICTURE_INVOLVED_SUBJECT';
			}

			if ($comment->element == 'photos.user.updateCover') {
				$verb = 'updateCover';

				$ownerTitle = 'APP_USER_PHOTOS_EMAILS_COMMENT_PROFILE_COVER_ITEM_SUBJECT';
				$involvedTitle = 'APP_USER_PHOTOS_EMAILS_COMMENT_PROFILE_COVER_INVOLVED_SUBJECT';
			}

			$emailOptions['title'] = $ownerTitle;

			// @points: photos.like
			// Assign points for the author for liking this item
			$photo->assignPoints('photos.comment.add', $comment->created_by);

			// Assign points to the photo owner
			if ($comment->created_by != $photo->user_id) {
				ES::points()->assign('photo.comment.add.owner', 'com_easysocial', $photo->user_id);
			}

			// Notify the owner of the photo first
			if ($photo->user_id != $comment->created_by && !$comment->isChild()) {
				ES::notify('photos.comment.add', array($photo->user_id), $emailOptions, $systemOptions);
			}

			// Get additional recipients since photos has tag
			$additionalRecipients = array();
			$this->getTagRecipients($additionalRecipients, $photo);

			// Get a list of recipients to be notified for this stream item
			// We exclude the owner of the note and the actor of the like here
			$recipients = $this->getStreamNotificationTargets($comment->uid, $element, 'user', $verb, $additionalRecipients, array($photo->user_id, $comment->created_by));

			$emailOptions['title'] = $involvedTitle;
			$emailOptions['template'] = 'apps/user/photos/comment.photo.involved';

			if (!$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/user/photos/comment.photo.replied';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}

			return;
		}

	}

	/**
	 * Triggered when a comment are deleted
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function onAfterDeleteComment(&$comment)
	{
		$allowed = ['stream.user.upload', 'stream.page.upload', 'stream.group.upload', 'stream.event.upload',
		'photos.user.add','photos.page.add', 'photos.group.add', 'photos.event.add'];

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		// If this item is multiple share on the stream, we need to get the photo id here.
		if ($comment->element == 'stream.user.upload') {
			$stream = ES::table('Stream');
			$stream->load($comment->uid);

			// Get the photo object from the context id of the stream
			$model = ES::model('Stream');
			$origin = $model->getContextItem($comment->uid);

			$photo = ES::table('Photo');
			$photo->load($origin->context_id);
		}

		// For single photo items on the stream
		if ($comment->element == 'photos.user.upload' || $comment->element == 'photos.user.add' || $comment->element == 'photos.user.uploadAvatar' || $comment->element == 'photos.user.updateCover') {
			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($comment->uid);
		}

		// Assign points to the photo owner
		if ($comment->created_by != $photo->user_id) {
			ES::points()->assign('photo.comment.remove.owner', 'com_easysocial', $photo->user_id);
		}

		// Assign points when a comment is deleted for photos
		ES::points()->assign('photos.comment.remove', 'com_easysocial', $comment->created_by);
	}

	private function getUniqueUsers($item, $users, $ownerId)
	{
		// Exclude myself from the list of users.
		$index 			= array_search(ES::user()->id , $users);

		if($index !== false)
		{
			unset($users[ $index ]);

			$users 	= array_values($users);
		}

		// Add the author of the photo as the recipient
		if($item->actor_id != $ownerId)
		{
			$users[]	= $ownerId;
		}

		// Ensure that the values are unique
		$users		= array_unique($users);
		$users 		= array_values($users);

		// Exclude the stream creator and the current logged in user from the list.
		if($users)
		{
			for($i = 0; $i < count($users); $i++)
			{
				if($users[ $i ] == ES::user()->id)
				{
					unset($users[ $i ]);
				}
			}

			$users 	= array_values($users);
		}

		return $users;
	}

	/**
	 * Renders the notification item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// Comments
		if (($item->cmd == 'albums.comment.add' || $item->cmd == 'photos.comment.add' || $item->cmd == 'comments.item' || $item->cmd == 'comments.replied' || $item->cmd == 'comments.involved')) {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// Likes
		if (($item->cmd == 'photos.likes' || $item->cmd == 'likes.item' || $item->cmd == 'likes.involved')) {
			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// When user is tagged in a photo
		if ($item->cmd == 'photos.tagged') {
			$hook = $this->getHook('notification', 'tagging');
			$hook->execute($item);

			return;
		}

		// when user favourte an album
		if ($item->cmd == 'albums.favourite') {
			$hook = $this->getHook('notification', 'favourite');
			$hook->execute($item);
		}

		return;
	}

	/**
	 * Determine if the cmd is allowed in this application
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
			'comments.comment.add',
			'albums.favourite',
			'photos.comment.add',
			'albums.comment.add',
			'photos.likes'
		);

		return in_array($cmd, $allowed);
	}
	/**
	 * Determine if the context is allowed in this application
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedContext($context)
	{
		$allowed = array(
			'photos.user.upload',
			'stream.user.upload',
			'photos.user.add',
			'albums.user.create',
			'photos.user.uploadAvatar',
			'photos.user.updateCover',
			'tagging',
			'albums.user.favourite'
		);

		return in_array($context, $allowed);
	}

	/**
	 * Generate the stream for photo REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if (!$this->canViewItem($item, $includePrivacy)) {
			return;
		}

		// Prepare the stream
		$this->onPrepareStream($item, $includePrivacy);

		$privacy = $viewer->getPrivacy();

		// Get full photos from params for now as the pagination is reversed in stream
		$limit = 0;

		// Load the photo object
		$photo = ES::table('Photo');
		$photo->load((int) $item->contextId);

		// Get the album
		$albumId = $photo->album_id;
		$album = ES::table('Album');
		$album->load($albumId);

		// Get photo objects
		$photos = $this->getPhotoFromParams($item, $privacy, $limit);

		if (!$photos) {
			return;
		}

		if (!is_array($photos)) {
			$photos = array($photos);
		}

		$count = count($item->contextIds);
		$photosCount = count($photos);

		$totalPhotos = $photosCount > $limit ? $limit : $photosCount;

		// if ($album->isStory()) {
			$totalPhotos = $photosCount <= 5 ? $photosCount : 5;
		// }

		$remainingPhotoCount = ($count > $totalPhotos) ? $count - $totalPhotos : 0;

		$items = array();

		$photoSize = 'large';

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
			$photoObject->extension = '';

			$items[] = $photoObject;
		}

		$source = new stdClass();
		$source->total = $count;
		$source->previewTotal = $totalPhotos;
		$source->remainingPhotoCount = $remainingPhotoCount;
		$source->album = $album->toExportData($viewer);
		$source->previewItems = $items;
		$source->playlistIds = $item->contextIds;

		$item->contentObj = $source;
		$item->show = true;

		return;
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
		if ($item->context_type == 'stream.user.upload') {
			$target->id = $item->uid;
			$target->type = 'stream';
			$target->endpoint = 'stream.item';
			$target->query_string = 'stream.item&id=' . $target->id;

			$item->target = $target;

			return;
		}

		$uid = $item->uid;

		// $uid is photo for this context. Hence we need to get the album id instead.
		if ($item->context_type !== 'albums.user.create') {
			$photo = ES::table('Photo');
			$photo->load($item->uid);

			if ($photo->id) {
				$uid = $photo->album_id;
			}
		}

		$target->id = $uid;
		$target->type = 'album';
		$target->endpoint = $target->type . '.item';
		$target->query_string = $target->type . '.item&id=' . $target->id;

		// Single photo within the album
		if ($item->context_type !== 'albums.user.create') {
			$target->nav = 'photo';
			$target->nav_id = $item->uid;
		}

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
