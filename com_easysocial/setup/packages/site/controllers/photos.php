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

jimport('joomla.filesystem.file');

class EasySocialControllerPhotos extends EasySocialController
{
	/**
	 * Allows caller to upload photos
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function upload()
	{
		ES::requireLogin();
		ES::checkToken();

		// Determines which album should the photo be uploaded into
		$albumId = $this->input->get('albumId', 0, 'int');

		$album = ES::table('Album');
		$album->load($albumId);

		// check if the album belong to the user or not. #2759
		if (!$album->canUpload()) {
			return $this->view->exception(JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED'));
		}

		// Determines if the album is already finalized
		$isAlbumFinalized = $album->finalized;

		// If this is an user album and the current login is not the onwer of the album but the current logged in user is a SA, then we need to
		// use the album->uid as this will be the same as user_id column.
		$userId = ($album->type == SOCIAL_TYPE_USER && !$album->isMine($this->my->id) && $this->my->isSiteAdmin()) ? $album->uid : $this->my->id;

		// Currently, if admin upload a photo in Page's album
		// The actor always be the Page since only page admin able to upload photo in album
		$postAs = $album->type == SOCIAL_TYPE_PAGE ? $album->type : SOCIAL_TYPE_USER;

		// Determine if we should create a stream item for this upload
		$createStream = $this->input->get('createStream', false, 'bool');

		// determine if we need to enforce limit checking or not. skip the checking if user
		// is uploading avatar / cover photo.
		$skipLimitChecks = $album->isAvatar() || $album->isCover() ? true : false;

		$lib = ES::photo($album->uid, $album->type);

		try {
			$photo = $lib->upload($this->my, $album, [
				'user_id' => $userId,
				'post_as' => $postAs,
				'skipLimitChecks' => $skipLimitChecks,
				'generateTitle' => true
			]);
		} catch (Exception $e) {
			$this->view->setMessage($e->getMessage(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If album doesn't have a cover, set the photo as the cover.
		if (!$album->hasCover()) {
			$album->cover_id = $photo->id;

			// Store the album
			$album->store();
		}

		// Determines if a stream item should be generated
		$createStream = $this->input->get('createStream', false, 'bool');

		if ($createStream && $isAlbumFinalized) {
			$photo->addPhotosStream('create');
		}

		// if albums is not finalized yet, let set the photo to only_me
		if (!$isAlbumFinalized) {
			$lib = ES::privacy();
			$lib->add('photos.view', $photo->id, 'photos', 'only_me', null);
		}

		// Sleep process for 1 second to avoid creation date stacking with one another #3755
		sleep(1);

		return $this->view->call(__FUNCTION__, $photo);
	}

	/**
	 * Uploading photos via story form
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function uploadStory()
	{
		ES::requireLogin();
		ES::checkToken();

		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		if (!$uid && !$type) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED');
		}

		// Load up the photo library
		$lib = ES::photo($uid, $type);

		$albumsModel = ES::model('Albums');
		$defaultAlbum = $albumsModel->getDefaultAlbum($uid, $type, SOCIAL_ALBUM_STORY_ALBUM);

		try {
			$photo = $lib->upload($this->my, $defaultAlbum);
		} catch (Exception $e) {
			$this->view->setMessage($e->getMessage(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__, $photo);
	}


	/**
	 * Allows caller to update a photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_NOT_FOUND');
		}

		$lib = ES::photo($photo->uid, $photo->type, $photo);

		// Test if the user is really allowed to edit the photo
		if (!$lib->editable()) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_EDIT_PHOTO');
		}

		// Get the posted data
		$post = $this->input->post->getArray();

		// Should we allow the change of the album?
		$photo->title = $this->input->get('title', '', 'string');
		$photo->caption = $this->input->get('caption', '', 'raw');

		// Set the assigned_date if necessary
		$photoDate = $this->input->get('date', '', 'default');

		if ($photoDate) {
			$date = ES::date($photoDate);
			$photo->assigned_date = $date->toMySQL();
		}

		// Try to store the photo now
		$state = $photo->store();

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_ERROR_SAVING_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Bind the location for the photo if necessary
		if ($this->config->get('photos.location')) {
			$address = $this->input->get('address', '', 'string');
			$latitude = $this->input->get('latitude', '', 'default');
			$longitude = $this->input->get('longitude', '', 'default');

			$photo->bindLocation($address, $latitude, $longitude);
		}

		return $this->view->call(__FUNCTION__, $photo);
	}

	/**
	 * Allows caller to delete an album
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get id from request
		$ids = $this->input->get('ids', array(), 'array');

		if (!$ids) {
			$id = $this->input->get('id', 0, 'int');

			if ($id) {
				$ids[] = $id;
			}
		}

		if (!$ids) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		foreach ($ids as $id) {

			$photo = ES::table('Photo');
			$photo->load($id);

			if (!$photo->id) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Load the photo library
			$lib = ES::photo($photo->uid, $photo->type, $photo);

			// Test if the user is allowed to delete the photo
			if (!$lib->deleteable()) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_DELETE_PHOTO', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Try to delete the photo
			$state = $photo->delete();

			if (!$state) {
				$this->view->setMessage($photo->getError(), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		// Get the new cover
		$newCover = $photo->getAlbum()->getCoverObject();

		return $this->view->call(__FUNCTION__, $newCover);
	}

	/**
	 * Allows caller to rotate a photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function rotate()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		// Get photo
		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Determine if the user has access to rotate the photo
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		if (!$lib->canRotatePhoto()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_ROTATE_THIS_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Retrieve the photo album data currently editing
		$albumItem = $photo->getAlbum();
		$isEditingCurrentAvatar = false;

		// Ensure that user editing the avatar photo currently using it.
		if ($albumItem->isAvatar()) {

			// Load the editing photo album id
			$albumTbl = ES::table('Album');
			$albumExist = $albumTbl->load($albumItem->id);

			if ($albumExist) {
				$avatarModel = ES::model('avatars');
				$isEditingCurrentAvatar = $avatarModel->isEditingCurrentAvatar($albumTbl->uid, $photo->id, $photo->type);
			}
		}

		// here we need to check if the current photo are stored in amazon. if yes, lets download back to server
		// for further processing.
		if ($photo->storage != SOCIAL_STORAGE_JOOMLA) {

			// Get the relative path to the photo
			$photoFolder = $photo->getFolder(true, false);

			// call the api to retrieve back the data
			$storage = ES::storage($photo->storage);
			$storage->pull($photoFolder);
		}

		// Rotate photo
		$tmpAngle = $this->input->get('angle', 0, 'int');

		// Get the real angle now.
		$angle = $photo->getAngle() + $tmpAngle;

		// Update the angle
		$photo->updateAngle($angle);

		// Rotate the photo
		$image = ES::image();
		$image->load($photo->getPath('original'));

		// Rotate the new image
		$image->rotate($tmpAngle);

		// Save photo
		$photoLib = ES::get('Photos', $image);

		// Get the storage path
		$storage = $photoLib->getStoragePath($photo->album_id, $photo->id);

		// Prevent stock photo from being override
		$exclude = array('stock');

		// Rename the photo to avoid browser cache
		$paths = $photoLib->create($storage, $exclude, $photo->title . '_rotated_' . $angle);

		// Delete the previous images that are generated except the stock version
		$photo->deletePhotos(array('thumbnail', 'large', 'original'));

		// When a photo is rotated, we would also need to rotate the tags as well
		$photo->rotateTags($tmpAngle);

		// Create metadata about the photos
		foreach ($paths as $type => $fileName) {

			$meta = ES::table('PhotoMeta');
			$meta->photo_id = $photo->id;
			$meta->group = SOCIAL_PHOTOS_META_PATH;
			$meta->property = $type;
			$meta->value = '/' . $photo->album_id . '/' . $photo->id . '/' . $fileName;
			$meta->store();

			// We need to store the photos dimension here
			list($width, $height, $imageType, $attr) = getimagesize(JPATH_ROOT . $storage . '/' . $fileName);

			// Delete previous meta data first
			$photo->updateMeta(SOCIAL_PHOTOS_META_WIDTH, $type, $width);
			$photo->updateMeta(SOCIAL_PHOTOS_META_HEIGHT, $type, $height);
		}

		// Reload photo
		$newPhoto = ES::table('Photo');
		$newPhoto->load($id);

		// Once image is rotated, we'll need to update the photo source back to "joomla" because
		// we will need to re-upload the image again to remote server when synchroinization happens.
		$newPhoto->storage = SOCIAL_STORAGE_JOOMLA;
		$newPhoto->store();

		// Ensure that user editing his current user avatar image then only process this.
		if ($isEditingCurrentAvatar) {

			// Rotated image
			$rotatedImage = $newPhoto->getPath('original', false, true);

			$image = ES::image();
			$image->load($rotatedImage);

			// Load up the avatar library
			$avatar = ES::avatar($image, $newPhoto->uid, $newPhoto->type);

			// since this rotate process do not allow user to crop so do not need to specify any width/height for it.
			$avatar->crop();

			// Create the avatars now
			$avatar->store($newPhoto, array('addstream' => false));
		}

		return $this->view->call(__FUNCTION__, $newPhoto, $paths);
	}

	/**
	 * Allows caller to feature a photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function feature()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load up photo library
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		// Test if the person is allowed to feature the photo
		if (!$lib->featureable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_FEATURE_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If photo is previously not featured, it is being featured now.
		$isFeatured = !$photo->featured ? true : false;

		// Toggle the featured state
		$photo->toggleFeatured();

		return $this->view->call(__FUNCTION__, $isFeatured);
	}

	/**
	 * Allows caller to move a photo over to album
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function move()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$ids = $this->input->get('ids', array(), 'array');
		$albumId = $this->input->get('value', '', 'default');

		if ($id) {
			$ids[] = $id;
		}

		foreach ($ids as $id) {

			$photo = ES::table('Photo');
			$photo->load($id);

			// Only allow valid photos
			if (!$id || !$photo->id) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Get the target album id to move this photo to.
			if (!$albumId) {
				$albumId = $this->input->get('albumId', 0, 'int');
			}

			$album = ES::table('Album');
			$album->load($albumId);

			if (!$albumId || !$album->id) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_ALBUM_ID_PROVIDED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Load the library
			$lib = ES::photo($photo->uid, $photo->type, $photo);

			// Check if the user can actually manage this photo
			if (!$lib->canMovePhoto()) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_MOVE_PHOTO', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Load up the target album
			$albumLib = ES::albums($album->uid, $album->type, $album);

			// Check if the target album is owned by the user
			if (!$albumLib->isOwner()) {
				$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_MOVE_PHOTO', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Try to move the photo to the new album now
			if (!$photo->move($albumId)) {
				$this->view->setMessage($photo->getError(), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_PHOTO_MOVED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes a tag
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function deleteTag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load the tag object
		$id = $this->input->get('tag_id', 0, 'int');
		$tag = ES::table('PhotoTag');
		$tag->load($id);

		// Get posted data from request
		$post = $this->input->post->getArray();

		// Get the person that created the tag
		$creator = ES::user($tag->created_by);

		// Determines if the tag can be deleted
		if (!$tag->deleteable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_DELETE_TAG', ES_ERROR);
			$this->view->call(__FUNCTION__);
		}

		// Try to delete the tag
		if (!$tag->delete()) {
			$this->view->setMessage($tag->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Deduct points from the user that created the tag since the tag has been deleted.
		$photo->assignPoints('photos.untag', $creator->id);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Creates a new tag
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function createTag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the photo id from the request.
		$id = $this->input->get('photo_id', 0, 'int');

		// Load up the photo table
		$photo = ES::table('Photo');
		$photo->load($id);

		// Check if the photo id is valid
		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// Load up the photo library
		$lib = ES::photo($photo->uid, $photo->type, $photo);

		// Test if the user is really allowed to tag this photo
		if (!$lib->taggable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_TAG_PHOTO', ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// Get posted data from request
		$post = $this->input->post->getArray();

		// Bind the new data on the post
		$tag = ES::table('PhotoTag');
		$tag->bind($post);

		// If there's empty label and the uid is not supplied, we need to throw an error
		if (empty($tag->label) && !$tag->uid) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_EMPTY_TAG_NOT_ALLOWED', ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// Reset the id of the tag since this is a new tag, it should never contain an id
		$tag->id = null;
		$tag->photo_id = $photo->id;
		$tag->created_by = $this->my->id;

		// Try to save the tag now
		$state  = $tag->store();

		// Try to store the new tag.
		if (!$state) {
			$this->view->setMessage($tag->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, null, $photo);
		}

		// @points: photos.tag
		// Assign points to the current user for tagging items
		$photo->assignPoints('photos.tag', $this->my->id);

		// Only notify persons if the photo is tagging a person
		if ($tag->uid && $tag->type == 'person' && $tag->uid != $this->my->id) {

			// need to check if user that being tag allow to access the photo / albums or not.
			$notify = true;

			if ($photo->type != SOCIAL_TYPE_USER) {
				$cluster = ES::cluster($photo->type, $photo->uid);
				if ($cluster->isInviteOnly() && !$cluster->canViewItem($tag->uid)) {
					$notify = false;
				}
			}

			if ($notify) {
				// Set the email options
				$emailOptions = array(
					'title' => 'COM_EASYSOCIAL_EMAILS_TAGGED_IN_PHOTO_SUBJECT',
					'template' => 'site/photos/tagged',
					'photoTitle' => $photo->get('title'),
					'photoPermalink' => $photo->getPermalink(true, true),
					'photoThumbnail' => $photo->getSource('thumbnail'),
					'actor' => $this->my->getName(),
					'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
					'actorLink' => $this->my->getPermalink(true, true)
				);

				$systemOptions = array(
					'context_type' => 'tagging',
					'context_ids' => $photo->id,
					'uid' => $tag->id,
					'url' => $photo->getPermalink(false, false, 'item', false),
					'actor_id' => $this->my->id,
					'target_id' => $tag->uid,
					'aggregate' => false
				);

				// Notify user
				ES::notify('photos.tagged', array($tag->uid), $emailOptions, $systemOptions);
			}

			// Assign a badge to the user
			$photo->assignBadge('photos.tag', $this->my->id);

			// Assign a badge to the user that is being tagged
			if ($this->my->id != $tag->uid) {
				$photo->assignBadge('photos.superstar', $tag->uid);
			}
		}

		return $this->view->call(__FUNCTION__, $tag, $photo);
	}

	/**
	 * Allows caller to retrieve a list of tags
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getTags()
	{
		ES::checkToken();

		// Get the photo object.
		$id = $this->input->get('photo_id', 0, 'int');
		$photo = ES::table('Photo');
		$photo->load($id);

		if (!$id || !$photo->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Retrieve the list of tags for this photo
		$tags = $photo->getTags();

		return $this->view->call(__FUNCTION__, $tags);
	}

	/**
	 * Allows caller to remove a tag
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function removeTag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the tag object
		$id = $this->input->get('id', 0, 'int');

		$tag = ES::table('PhotoTag');
		$tag->load($id);

		if (!$id || !$tag->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_INVALID_TAG_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If user is not allowed to delete the tag, throw an error
		if (!$tag->deleteable()) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_NOT_ALLOWED_TO_DELETE_TAG', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try to delete the tag.
		$state = $tag->delete();

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_PHOTOS_ERROR_REMOVING_TAG', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allow caller to set profile photo based on the photo that they have
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function createAvatar()
	{
		ES::checkToken();
		ES::requireLogin();

		// Get the photo id
		$id = $this->input->get('id', 0, 'int');

		$photo = ES::photo(null, null, $id);
		$avatar = $photo->createAvatar();

		if (!$avatar) {
			$this->view->setMessage($photo->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		ES::storage()->syncUsage(ES::user()->id);

		return $this->view->call(__FUNCTION__, $avatar);
	}

	/**
	 * Allows caller to create an avatar by posted the $_FILE data
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function createAvatarFromFile()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the unique item id
		$uid = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		if (!$uid && !$type) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED');
		}

		// Load up the photo library
		$lib = ES::photo($uid, $type);
		$album = $lib->getDefaultAlbum();

		// if this photo upload from clusters page, then need to store the cluster owner user id
		$authorId = $uid;

		if (in_array($type, array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE))) {
			$authorId = $this->my->id;

			$cluster = ES::cluster($type, $uid);

			if ($cluster->creator_uid) {
				$authorId = $cluster->creator_uid;
			}
		}

		// Currently, if admin upload a photo for Page's avatar
		// The actor always be the Page since only page admin able to change avatar
		$postAs = $type == SOCIAL_TYPE_PAGE ? $type : SOCIAL_TYPE_USER;

		try {
			$photo = $lib->upload($this->my, $album, [
				'fileName' => 'avatar_file',
				'user_id' => $authorId,
				'post_as' => $postAs,
				'state' => SOCIAL_PHOTOS_STATE_TMP,
				'skipLimitChecks' => true,
				'assignBadge' => false
			]);
		} catch (Exception $e) {
			$this->view->setMessage($e->getMessage(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call('createAvatar', $photo);
	}

	/**
	 * Allows caller to create an avatar by posted the $_FILE data
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function createAvatarFromWebcam()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the unique item id
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		if (!$uid && !$type) {
			return $this->view->exception('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED');
		}

		// Load up the photo library
		$lib = ES::photo($uid, $type);
		$albumModel = ES::model('Albums');
		$album = $albumModel->getDefaultAlbum($uid, $type, SOCIAL_ALBUM_PROFILE_PHOTOS);

		// if this photo upload from clusters page, then need to store the cluster owner user id
		$authorId = $uid;

		if (in_array($type, [SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE])) {
			$authorId = $this->my->id;

			$cluster = ES::cluster($type, $uid);

			if ($cluster->creator_uid) {
				$authorId = $cluster->creator_uid;
			}
		}

		$filename = $this->input->get('file', '', 'default');
		$tmp = $this->jconfig->getValue('tmp_path');
		$filePath = $tmp . '/' . $filename;

		// Load the image
		$image = ES::image();
		$image->load($filePath);

		// Currently, if admin upload a photo for Page's avatar
		// The actor always be the Page since only page admin able to change avatar
		$postAs = $type == SOCIAL_TYPE_PAGE ? $type : SOCIAL_TYPE_USER;

		try {
			$photo = $lib->upload($this->my, $album, [
				'image' => $image,
				'user_id' => $authorId,
				'post_as' => $postAs,
				'state' => SOCIAL_PHOTOS_STATE_TMP,
				'skipLimitChecks' => true,
				'assignBadge' => false
			]);
		} catch (Exception $e) {
			$this->view->setMessage($e->getMessage(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Once the photo is created, delete the tmp file
		JFile::delete($filePath);

		return $this->view->call('createAvatar', $photo);
	}

	/**
	 * Check file size
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function checkFileSize()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load front end's language file
		if (ES::isFromAdmin()) {
			ES::language()->loadSite();
		}

		$filesData = $this->input->get('filesData', [], 'array');
		$totalsize = $this->input->get('totalsize', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('utype', SOCIAL_TYPE_USER, 'default');

		// Check for upload limit
		$lib = ES::photo($uid, $utype);
		$uploadLimit = $lib->getUploadFileSizeLimit();
		$maxUploadSize = ES::math()->convertBytes($uploadLimit);

		$message = '';
		$filesExeceedSize = [];

		// Check for individual file size
		foreach ($filesData as $file) {
			if ($file['size'] > $maxUploadSize) {
				$filesExeceedSize[] = $file['name'];
			}
		}

		if (!empty($filesExeceedSize)) {
			$message = JText::sprintf('COM_ES_ALBUMS_UPLOAD_EXCEEDED_UPLOAD_LIMIT_SIZE', implode('<br> &mdash; ', $filesExeceedSize), str_ireplace('M', 'MB', $uploadLimit));
		}

		// Check for storage limit
		$storage = ES::storage();
		$isLimit = $storage->isLimit($this->my->id, $totalsize);

		if ($isLimit) {
			$message = JText::_('COM_ES_STORAGE_INSUFFICIENT_STORAGE');
			return $this->ajax->reject($message);
		}

		return $this->ajax->resolve($message);
	}

	/**
	 * Check file size for copy to another album
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function checkFileSizeAvatarCopy()
	{
		ES::requireLogin();
		ES::checkToken();

		$photoId = $this->input->get('photoId', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('utype', SOCIAL_TYPE_USER, 'default');

		$lib = ES::photo($uid, $utype, $photoId);

		if (!$photoId || !$lib->data->id) {
			return $this->ajax->reject('Invalid Id');
		}

		$album = $lib->data->getAlbum();

		// We need to copy this image and put into the avatar album. But first, we check the size first.
		if (!$album->isAvatar()) {

			// Get the size
			$size = $lib->data->total_size;

			// Check for storage limit
			$storage = ES::storage();
			$isLimit = $storage->isLimit($this->my->id, $size);

			if ($isLimit) {
				$message = JText::_('COM_ES_STORAGE_INSUFFICIENT_STORAGE_COPY_TO_AVATAR');
				return $this->ajax->reject($message);
			}
		}

		return $this->ajax->resolve();
	}

	/**
	 * Check file size for copy to another album
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function checkFileSizeCoverCopy()
	{
		ES::requireLogin();
		ES::checkToken();

		$photoId = $this->input->get('photoId', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('utype', SOCIAL_TYPE_USER, 'default');

		$lib = ES::photo($uid, $utype, $photoId);

		if (!$photoId || !$lib->data->id) {
			return $this->ajax->reject('Invalid Id');
		}

		$album = $lib->data->getAlbum();

		// We need to copy this image and put into the avatar album. But first, we check the size first.
		if (!$album->isCover()) {

			// Get the size
			$size = $lib->data->total_size;

			// Check for storage limit
			$storage = ES::storage();
			$isLimit = $storage->isLimit($this->my->id, $size);

			if ($isLimit) {
				$message = JText::_('COM_ES_STORAGE_INSUFFICIENT_STORAGE_COPY_TO_COVER');
				return $this->ajax->reject($message);
			}
		}

		return $this->ajax->resolve();
	}
}
