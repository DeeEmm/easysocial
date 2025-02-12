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
jimport('joomla.filesystem.folder');

// Adapters abstract
require_once(__DIR__ . '/adapters/abstract.php');

class SocialPhoto
{
	/**
	 * The current unique owner of the item
	 * @var	int
	 */
	public $uid = null;

	/**
	 * The current unique string of the item
	 * @var	string
	 */
	public $type = null;

	/**
	 * The adapter for photo
	 * @var	string
	 */
	public $adapter = null;

	/**
	 * The album's library
	 * @var	SocialAlbums
	 */
	public $albumLib = null;

	/**
	 * The table mapping for the photo.
	 * @var	SocialTablePhoto
	 */
	public $data = null;

	/**
	 * The table mapping for exif data.
	 * @var SocialTablePhotoMeta
	 */
	public $exif = null;

	/**
	 * The error message
	 * @var	string
	 */
	public $error = null;

	public function __construct($uid = null, $type = null, $id = null)
	{
		if ($id instanceof SocialTablePhoto) {
			$this->data = $id;
		} else {
			$table = ES::table('Photo');
			$table->load($id);

			$this->data = $table;
		}

		// Get exif data
		$this->exif = $this->getExifData();

		$this->uid = $uid ? $uid : $this->data->uid;
		$this->type = $type ? $type : $this->data->type;
		$this->albumLib = $this->album();
		$this->adapter = $this->getAdapter($this->type);

		// Update renderItemOptions to reflect configurable options
		$config = ES::config();
		$this->renderItemOptions['resizeThreshold'] = $config->get('photos.layout.threshold');
	}

	public static function factory($id = null)
	{
		return new self($id);
	}

	private $renderItemOptions = array(
		'viewer' => null,
		'layout' => 'item',
		'size' => 'thumbnail',
		'template' => 'site/photos/albums/default',
		'showNavigation' => false,
		'showToolbar' => true,
		'showInfo' => true,
		'showStats' => true,
		'showResponse' => true,
		'showTags' => true,
		'showForm' => true,
		'resizeMode' => 'contain',
		'resizeThreshold' => 128,
		'resizeUsingCss' => true,
		'openInPopup' => false
	);

	/**
	 * Wraps the provided album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderItem($options = array())
	{
		// Flag to determine if resize mode was enforced
		$resizeModeEnforced = !empty($options['resizeMode']);

		// Normalize render options
		$options = array_merge($this->renderItemOptions, $options);

		// If resize mode was not enforced
		if (!$resizeModeEnforced) {
			$options['resizeMode'] = $options['layout'] == 'item' ? 'cover' : 'contain';
		}

		$viewer = ES::user($options['viewer']);
		$exif = $this->exif;

		// Get the album library
		$albumLib = $this->album();

		// check the photos is it got cluster type e.g. event or group
		$photoCluster = false;
		$photoClusterId = ($this->type != SOCIAL_APPS_GROUP_USER) ? $this->uid : '0';
		$photoClusterType = ($this->type != SOCIAL_APPS_GROUP_USER) ? $this->type : '';

		if ($photoClusterId && $photoClusterType) {
			$photoCluster = ES::cluster($photoClusterType, $photoClusterId);
		}

		if ($this->data->isFeatured()) {
			$options['size'] = SOCIAL_PHOTOS_LARGE;
		}

		$page = ES::page($this->uid);

		// Build user alias
		$creator = $this->data->getPhotoCreator($page);

		$model = ES::model('Albums');
		$totalPhotos = $model->getTotalPhotos($albumLib->data->id);

		if ($totalPhotos < 2) {
			$options['showNavigation'] = false;
		}

		$theme = ES::themes();
		$theme->set('lib', $this);
		$theme->set('tags', $this->data->getTags());
		$theme->set('comments', $this->comments());
		$theme->set('likes', $this->likes());
		$theme->set('shares', $this->reposts($photoClusterId, $photoClusterType));
		$theme->set('albumLib', $albumLib);
		$theme->set('album', $albumLib->data);
		$theme->set('photo', $this->data);
		$theme->set('creator', $creator);
		$theme->set('privacy', $this->privacy());
		$theme->set('options', $options);
		$theme->set('exif', $exif);
		$theme->set('clusterId', $photoClusterId);
		$theme->set('clusterType', $photoClusterType);
		$theme->set('clusterPrivate', $photoCluster && !$photoCluster->isOpen() ? true : false);

		$namespace = $options['template'];

		return $theme->output($namespace);
	}

	/**
	 * Retrieves the album's library
	 *
	 * @since	1.0
	 * @access	public
	 * @return	SocialAlbums
	 */
	public function album()
	{
		return ES::albums($this->data->uid, $this->data->type, $this->data->album_id);
	}

	public function creator()
	{
		return ES::user($this->data->user_id);
	}

	public function privacy()
	{
		// @TODO: Get proper photo privacy
		return ES::privacy();
	}

	/**
	 * Prepare the likes object for the photo item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function likes()
	{
		$verb = $this->getPhotoVerb();
		$context = SOCIAL_TYPE_PHOTO;
		$id = $this->data->id;

		$streamId = $this->getPhotoStreamId($id, $verb, false);

		$model = ES::model('Stream');
		$aggregated = $model->isAggregated($this->data->id, 'photos');

		if ($aggregated) {
			$streamId = '0';
		} else if ($verb == 'upload' && $streamId) {
			// Stream upload
			$context = SOCIAL_TYPE_STREAM;
			$id = $streamId;
		}

		$options = array();

		// We have to pass this parameter to tell the likes library, this is come from page
		if ($this->type == SOCIAL_TYPE_PAGE) {
			$options['clusterId'] = $this->uid;
		}

		// NOTE:
		// We do not need to do any checking or fix any relations here since liking a photo should always be liking a photo
		// Nothing needs to be done here.

		return ES::likes($id, $context, $verb, $this->type, $streamId, $options);
	}

	/**
	 * Prepare the comments object for the photo item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function comments()
	{
		// Get the verb to use.
		$verb = $this->getPhotoVerb();


		// The context should always be photos
		$context = SOCIAL_TYPE_PHOTO;

		// The object id should always be the photo id
		$id = $this->data->id;

		$streamId = $this->getPhotoStreamId($id, $verb, false);

		if ($verb == 'upload') {
			// we now this photo is uploaded via stream's story form.
			$model = ES::model('Stream');
			$aggregated = $model->isAggregated($this->data->id, 'photos');

			if ($aggregated) {
				$streamId = '0';
			} else if ($streamId) {
				$context = SOCIAL_TYPE_STREAM;
				$id = $streamId;
			}
		}

		// Get the permalink to the photo
		$permalink = $this->data->getPermalink(true, false, 'item', false);
		$options = array('url' => $permalink);

		// Get the cluster id for this photo to generate the comment form.
		if ($this->type != SOCIAL_TYPE_USER) {
			$options['clusterId'] = $this->uid;
		}

		$privacy = ES::user()->getPrivacy();

		if (!$privacy->validate('story.post.comment', $this->data->user_id, SOCIAL_TYPE_USER)) {
			$options['hideForm'] = true;
		}

		return ES::comments($id, $context, $verb, $this->type, $options, $streamId);
	}

	public function reposts($clusterId = 0, $clusterType = '')
	{
		// attempt to get stream id for this photo
		$verb = $this->getPhotoVerb();
		$streamId = $this->getPhotoStreamId($this->data->id, $verb, false);

		$repost = ES::repost($this->data->id, SOCIAL_TYPE_PHOTO, $this->type, $clusterId, $clusterType);
		if ($streamId) {
			$repost->setStreamId($streamId);
		}

		return $repost;
	}

	public function getPhotoStreamId($photoId, $verb, $validate = true)
	{
		static $_cache = [];

		$idx = $photoId . $verb . (int) $validate;

		if (!isset($_cache[$idx])) {
			$model = ES::model('Photos');
			$_cache[$idx] = $model->getPhotoStreamId($photoId, $verb, $validate);
		}

		return $_cache[$idx];
	}

	public function getPhotoVerb()
	{
		static $cache = array();

		if (!isset($cache[$this->data->id])) {

			$album	= ES::table('Album');
			$album->load($this->data->album_id);

			// uploadAvatar
			// updateCover
			// share
			// create

			$core = $album->core;
			$verb = 'add';

			if ($core == SOCIAL_ALBUM_PROFILE_PHOTOS) {
				$verb = 'uploadAvatar';
			}

			if ($core == SOCIAL_ALBUM_PROFILE_COVERS) {
				$verb = 'updateCover';
			}

			// When user upload photos in the story, it gets pushed to the story album
			if ($core == SOCIAL_ALBUM_STORY_ALBUM) {

				// Here we need to check if the photo already have a stream id related with 'add'.
				// If none, we use the 'upload'.
				$verb = 'upload';

				// in 3.0, we need to check whether the stream is exist for 'create' verb. #2575
				$streamId = $this->getPhotoStreamId($this->data->id, 'create');

				if ($streamId) {

					// Check for stream aggregation
					$sModel = ES::model('Stream');
					$totalItem = $sModel->getStreamItemsCount($streamId);

					// Single stream detected. We need to change the verb to 'add'
					if ($totalItem == 1) {
						$verb = 'add';
					}
				}
			}

			$cache[$this->data->id] = $verb;
		}

		// dump($cache[$this->data->id]);

		return $cache[$this->data->id];
	}

	/**
	 * Retrieves the total number of photos in an album
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getTotalAlbumPhotos($options = array())
	{
		$total = (int) $this->albumLib->data->getTotalPhotos($options);

		return $total;
	}

	/**
	 * Retrieves a list of photos from an album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getAlbumPhotos($options = array())
	{
		$photos = $this->albumLib->data->getPhotos($options);

		return $photos['photos'];
	}

	/**
	 * Sets error messages
	 *
	 * @since	1.2.11
	 * @access	public
	 */
	public function setError($message)
	{
		$this->error = $message;
	}

	/**
	 * Retrieves error messages
	 *
	 * @since	1.2.11
	 * @access	public
	 */
	public function getError()
	{
		return $this->error;
	}


	/**
	 * Retrieves error messages
	 *
	 * @deprecated 1.3
	 * @access	public
	 */
	public function getErrorMessage()
	{
		return $this->getError();
	}

	/**
	 * Maps back the call method functions to the adapter.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$refArray = array();

		if ($args) {
			foreach ($args as &$arg) {
				$refArray[]	=& $arg;
			}
		}

		return call_user_func_array([$this->adapter, $method], $refArray);
	}

	/**
	 * Retrieves the album's adapter
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getAdapter($type)
	{
		$type = empty($type) ? 'user' : $type;

		$file = dirname(__FILE__) . '/adapters/' . strtolower($type) . '.php';

		if (!file_exists($file)) {
			return false;
		}

		require_once($file);

		$className = 'SocialPhotoAdapter' . ucfirst($type);
		$adapter = new $className($this, $this->albumLib);

		return $adapter;
	}

	/**
	 * Get Exif data of the photos
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getExifData()
	{
		$model = ES::model('Photos');
		$data = $model->getMeta($this->data->id, 'exif');

		$exif = array();
		$mapArray = array(
			'aperture' => 'aperture',
			'exposure' => 'shutter-speed',
			'focalLength' => 'focal-length',
			'iso' => 'camera-iso',
			'camera' => 'camera-type'
		);

		foreach ($data as $item) {
			if (!array_key_exists($item->property, $mapArray)) {
				continue;
			}

			if (!$item->value) {
				continue;
			}

			$obj = new stdClass();
			$obj->property = $item->property;
			$obj->value = $item->value;
			$obj->class = $mapArray[$item->property];

			$exif[] = $obj;
		}

		return $exif;
	}

	/**
	 * Method to copy the photo to a different album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function copyToAlbum($destinationAlbum, $processGif = true)
	{
		// Get the source for this photo
		$originalPhoto = $this->data;
		$originalPath = $originalPhoto->getPath('original');

		// Construct file object
		$file = array('tmp_name' => $originalPath, 'name' => $this->data->title);

		// Load the image object
		$image = ES::image();
		$image->load($file['tmp_name'], $file['name']);

		// Bind the photo data now
		$photo = ES::table('Photo');
		$photo->uid = $originalPhoto->uid;
		$photo->type = $originalPhoto->type;
		$photo->user_id = ES::user()->id;
		$photo->caption = '';
		$photo->ordering = 0;
		$photo->state = SOCIAL_STATE_PUBLISHED;

		// Set the destination album
		$photo->album_id = $destinationAlbum->id;

		// Currently, if admin upload a photo in Page's album
		// The actor always be the Page since only page admin able to upload photo in album
		$photo->post_as = $originalPhoto->type == SOCIAL_TYPE_PAGE ? $originalPhoto->type : SOCIAL_TYPE_USER;

		// Generate a proper name for the file rather than using the file name
		$photo->title = $file['name'];

		// Set the creation date alias
		$photo->assigned_date = ES::date()->toMySQL();

		// Cleanup photo title.
		$photo->cleanupTitle();

		// Trigger rules that should occur before a photo is stored
		$photo->beforeStore($file, $image);

		// Try to store the photo.
		$state = $photo->store();

		if (!$state) {
			return false;
		}

		// If destinationAlbum doesn't have a cover, set the current photo as the cover.
		if (!$destinationAlbum->hasCover()) {
			$destinationAlbum->cover_id = $photo->id;

			// Store the destinationAlbum
			$destinationAlbum->store();
		}

		// Get the photos library
		$photoLib = ES::photos($image);

		// Get the storage path for this photo
		$storageContainer = ES::cleanPath(ES::config()->get('photos.storage.container'));
		$storage = $photoLib->getStoragePath($destinationAlbum->id, $photo->id);
		$paths = $photoLib->create($storage, array(), '', $processGif);

		// We need to calculate the total size used in each photo (including all the variants)
		$totalSize = 0;

		// Create metadata about the photos
		if ($paths) {

			foreach ($paths as $type => $fileName) {
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				// do not store the container path as this path might changed from time to time
				$tmpStorage = str_replace('/' . $storageContainer . '/', '/', $storage);
				$meta->value = $tmpStorage . '/' . $fileName;
				$meta->store();

				// We need to store the photos dimension here
				list($width, $height, $imageType, $attr) = getimagesize(JPATH_ROOT . $storage . '/' . $fileName);

				// Set the photo size
				$totalSize += filesize(JPATH_ROOT . $storage . '/' . $fileName);

				// Set the photo dimensions
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_WIDTH;
				$meta->property = $type;
				$meta->value = $width;
				$meta->store();

				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_HEIGHT;
				$meta->property = $type;
				$meta->value = $height;
				$meta->store();
			}
		}

		// Set the total photo size
		$photo->total_size = $totalSize;
		$photo->store();

		// After storing the photo, trigger rules that should occur after a photo is stored
		$photo->afterStore($file, $image);

		return $photo;
	}

	/**
	 * Export photo data
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $options = array())
	{
		static $cache = array();

		$key = $this->data->id . '.' . $viewer->id . serialize($options);

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		// for photos, we need to set the extended mode to true
		$extended = ES::normalize($options, 'extended', true);

		$properties = get_object_vars($this->data);

		$photo = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$photo[$key] = $value;
			}
		}

		$photo['sizes'] = array();

		$arraySize = array('large', 'thumbnail', 'original', 'stock');

		foreach ($arraySize as $size) {
			$metadata = array();
			$metadata['height'] = $this->data->getHeight($size);
			$metadata['width'] = $this->data->getWidth($size);

			$photo['sizes'][$size] = array();
			$photo['sizes'][$size]['url'] = $this->data->getSource($size);
			$photo['sizes'][$size]['metadata'] = $metadata;
		}

		$clusterId = $this->data->type != SOCIAL_TYPE_USER ? $this->data->uid : 0;
		$clusterType = $this->data->type != SOCIAL_TYPE_USER ? $this->data->type : '';

		$photo['permalink'] = $this->data->getPermalink(true, true);

		$exif = $this->getExifData();
		$formattedExif = false;

		if (!empty($exif)) {
			$formattedExif = new stdClass();

			foreach ($exif as $data) {
				$property = $data->property;
				$formattedExif->$property = $data->value;
			}
		}

		$photo['exif'] = $formattedExif;
		$photo['isFeatured'] = $this->data->isFeatured();

		// Construct permission access
		$permission = array(
			'isOwner' => $this->data->isMine(),
			'canEdit' => $this->data->editable(),
			'canFavourite' => $this->data->featureable(),
			'canDelete' => $this->data->deleteable(),
			'canInteract' => true // able to post comment or react to the album
		);

		$photo['permission'] = $permission;

		if ($extended) {
			$photo['author'] = $this->data->getCreator()->toExportData($viewer);
			$photo['comments'] = $this->comments();
			$photo['likes'] = $this->likes();
			$photo['repost'] = $this->reposts($clusterId, $clusterType);

			// Privacy
			$privacy = ES::privacy();
			$photo['privacy'] = $privacy->form($this->data->id, SOCIAL_TYPE_PHOTO, $this->data->uid, 'photos.view', false, null, array(), array('iconOnly' => true));
		}

		$photo = (object) $photo;

		$cache[$key] = $photo;

		return $cache[$key];
	}

	/**
	 * Method to use current photo as object's avatar
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function createAvatar()
	{
		$photo = $this->data;

		// Try to load the photo with the provided id.
		if (!$photo->id) {
			$this->setError('COM_EASYSOCIAL_PHOTOS_INVALID_ID_PROVIDED');
			return false;
		}

		if (!$this->canUseAvatar()) {
			$this->setError('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_USE_PHOTO_AS_AVATAR');
			return false;
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

		$album = $photo->getAlbum();

		// We need to copy this image and put into the avatar album, #2746
		if (!$album->isAvatar()) {

			// Retrieve the default avatar album for this node.
			$coverAlbum = $this->getDefaultAlbum();

			// Copy the photo now
			$photo = $this->copyToAlbum($coverAlbum, false);
		}

		// Get the image object for the photo
		// Use "original" not "stock" because it might be rotated before this.
		$image = $photo->getImageObject('original');

		// Need to rotate as necessary here because we're loading up using the stock photo and the stock photo
		// is as is when the user initially uploaded.
		// Updated: This is no longer needed as we stored the original photo as rotated photo. #5305
		// $image->rotate($photo->getAngle());

		// Store the image temporarily
		$tmp = ES::jConfig()->getValue('tmp_path');
		$tmpPath = $tmp . '/' . md5($photo->id) . $image->getExtension();

		// If the temporary file exists, we need to delete it first
		if (JFile::exists($tmpPath)) {
			JFile::delete($tmpPath);
		}

		$image->save($tmpPath);
		unset($image);

		// if this photo is stored remotely, we will need these downloaded files for later
		// cron action to sync the avatar. the clean up will be perform at avatar sync. @20170413 #681

		// If photo was stored remotely, we need to delete the downloaded files
		// if ($photo->isStoredRemotely()) {
		// 	$photo->deletePhotoFolder();
		// }

		$image = ES::image();
		$image->load($tmpPath);

		// Load up the avatar library
		$avatar = ES::avatar($image, $photo->uid, $photo->type);

		$input = ES::request();

		// Crop the image to follow the avatar format. Get the dimensions from the request.
		$width = $input->get('width', '', 'default');
		$height = $input->get('height', '', 'default');
		$top = $input->get('top', '', 'default');
		$left = $input->get('left', '', 'default');

		// We need to get the temporary path so that we can delete it later once everything is done.
		$avatar->crop($top, $left, $width, $height);

		// Create the avatars now
		$avatar->store($photo);

		// Delete the temporary file.
		JFile::delete($tmpPath);

		return $photo;
	}

	/**
	 * Method to use current photo as object's cover
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function createCover($x = 0, $y = 0, $isNew = false)
	{
		$photo = $this->data;

		// Check for required variables
		if (!$photo->id) {
			$this->setError('COM_EASYSOCIAL_PHOTOS_INVALID_PHOTO_ID_PROVIDED');
			return false;
		}

		// Check if the user is allowed to use this photo as a cover.
		if (!$this->allowUseCover()) {
			$this->setError('COM_EASYSOCIAL_PHOTOS_NO_PERMISSION_TO_USE_PHOTO_AS_COVER');
			return false;
		}

		// here we need to check if the current photo are stored in amazon. if yes, lets download back to server
		// for further processing.
		if ($photo->storage != SOCIAL_STORAGE_JOOMLA && !$isNew) {

			// Get the relative path to the photo
			$photoFolder = $photo->getFolder(true, false);

			// call the api to retrieve back the data
			$storage = ES::storage($photo->storage);
			$storage->pull($photoFolder);
		}

		$album = $photo->getAlbum();

		// We need to copy this image and put into the cover album, #2746
		if (!$album->isCover()) {

			// Check if there's a profile photos album that already exists.
			$model = ES::model('Albums');

			// Retrieve the user's default album
			$coverAlbum = $model->getDefaultAlbum($this->uid, $this->type, SOCIAL_ALBUM_PROFILE_COVERS);

			// Copy the photo now
			$photo = $this->copyToAlbum($coverAlbum, false);
		}

		// Load the cover
		$cover = ES::table('Cover');
		$state = $cover->load(array('uid' => $this->uid, 'type' => $this->type));

		// User does not have a cover.
		if (!$state) {
			$cover->uid = $this->uid;
			$cover->type = $this->type;
		}

		// Set the cover to pull from photo
		$cover->setPhotoAsCover($photo->id, $x, $y);

		// Save the cover.
		$cover->store();

		// @Add stream item when a new profile cover is uploaded
		if ($isNew) {
			$photo->addPhotosStream('updateCover');
		} else {
			// First check whether stream exists or not
			$streamId = $this->getPhotoStreamId($photo->id, 'updateCover');

			// If exists, update the date of existing stream
			if ($streamId) {
				$stream = ES::stream();
				$stream->updateCreated($streamId, null, 'updateCover');

				// Need to unhide the stream if the stream is hidden
				$model = ES::model('Stream');
				$state = $model->unhide($streamId, ES::user()->id);
			} else {

				// If not exists, just create a new one
				$photo->addPhotosStream('updateCover');
			}
		}

		// Set the photo state to 1 since the user has already confirmed to set it as cover
		$photo->state = SOCIAL_STATE_PUBLISHED;
		$photo->store();

		return $cover;
	}

	/**
	 * Handles photo uploads
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function upload(SocialUser $author, SocialTableAlbum $album, $options = [])
	{
		$config = ES::config();

		// Check if the photos is enabled
		if (!$config->get('photos.enabled') || !$this->canUploadPhotos()) {
			throw ES::exception('COM_EASYSOCIAL_ALBUMS_PHOTOS_DISABLED');
		}

		// Determines if file size and upload limits should be enforced
		$skipLimitChecks = ES::normalize($options, 'skipLimitChecks', false);

		// Determines if the person exceeded their upload limit
		if (!$skipLimitChecks && ($this->exceededDiskStorage() || $this->exceededUploadLimit() || $this->exceededDailyUploadLimit())) {
			throw ES::exception($this->getError());
		}

		// Check if the album id provided is valid
		if (!$album->id) {
			throw ES::exception('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED');
		}

		// Define uploader options
		$fileName = ES::normalize($options, 'fileName', 'file');

		$uploadOptions = [
			'name' => $fileName,
			'maxsize' => $this->getUploadFileSizeLimit()
		];

		$image = ES::normalize($options, 'image', null);
		$file = false;

		if (!$image) {
			// Get uploaded file
			$uploader = ES::uploader($uploadOptions);
			$file = $uploader->getFile(null, 'image');

			// If there was an error getting uploaded file, stop.
			if ($file instanceof SocialResponse) {
				throw ES::exception($file->message);
			}

			// Load the iamge object
			$image = ES::image();
			$image->load($file['tmp_name'], $file['name']);
		}

		// Reconstruct file data
		$originalPath = $image->image->dirname . '/' . $image->image->basename;

		if (!$file) {
			$file = [
				'tmp_name' => $originalPath,
				'name' => $image->image->filename,
				'size' => filesize($originalPath)
			];
		}

		// Ensure that the image file is truly valid
		if (!$image->isValid()) {
			throw ES::exception('COM_EASYSOCIAL_PHOTOS_INVALID_FILE_PROVIDED');
		}

		$authorId = ES::normalize($options, 'user_id', $author->id);

		$photo = ES::table('Photo');
		$photo->uid = $this->uid;
		$photo->type = $this->type;
		$photo->user_id = $authorId;
		$photo->album_id = $album->id;
		$photo->featured = 0;

		$photo->title = $file ? $file['name'] : $image->image->filename;

		// Generate a proper name for the file rather than using the file name
		$generateTitle = ES::normalize($options, 'generateTitle', false);

		if ($generateTitle) {
			$photo->title = $photo->generateTitle();
		}

		$photo->cleanupTitle();

		$photo->caption = ES::normalize($options, 'title', '');
		$photo->ordering = 0;
		$photo->state = ES::normalize($options, 'state', SOCIAL_STATE_PUBLISHED);

		$postAs = ES::normalize($options, 'post_as', SOCIAL_TYPE_USER);
		$photo->post_as = $postAs;

		// Set the creation date alias
		$photo->assigned_date = ES::date()->toMySQL();

		// Trigger rules that should occur before a photo is stored
		$photo->beforeStore($file, $image);

		// Try to store the photo.
		$state = $photo->store();

		if (!$state) {
			throw ES::exception('COM_EASYSOCIAL_PHOTOS_UPLOAD_ERROR_STORING_DB');
		}

		// Update the ordering of the photos in the album
		// $photosModel = ES::model('photos');
		// $photosModel->pushPhotosOrdering($album->id, $photo->id);

		$storage = ES::call('Photos', 'getStoragePath', array($album->id, $photo->id));
		$storageContainer = ES::cleanPath($config->get('photos.storage.container'));

		// Get the photos library
		$photoLib = ES::photos($image);
		$paths = $photoLib->create($storage);

		// We need to calculate the total size used in each photo (including all the variants)
		// $totalSize = 0;

		// Remove the storage container from the storage path as we only want to store the relative storage path
		$relativeStoragePath = str_replace('/' . $storageContainer . '/', '/', $storage);

		// Create metadata about the photos
		if ($paths) {
			$optimizer = ES::imageoptimizer();

			foreach ($paths as $type => $fileName) {

				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value = $relativeStoragePath . '/' . $fileName;
				$meta->store();

				// Optimize the image
				$absolutePath = JPATH_ROOT . $storage . '/' . $fileName;
				$optimizer->optimize($absolutePath, $meta->id, SOCIAL_TYPE_PHOTO);

				// We need to store the photos dimension here
				list($width, $height, $imageType, $attr) = getimagesize(JPATH_ROOT . $storage . '/' . $fileName);

				// Set the photo size
				// $totalSize += filesize(JPATH_ROOT . $storage . '/' . $fileName);

				// Set the photo dimensions
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_WIDTH;
				$meta->property = $type;
				$meta->value = $width;
				$meta->store();

				// Set the photo height
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_HEIGHT;
				$meta->property = $type;
				$meta->value = $height;
				$meta->store();
			}
		}

		// Set the total photo size
		$photo->total_size = $file['size'];
		$photo->store();

		// Assign badge to user
		$assignBadge = ES::normalize($options, 'assignBadge', true);

		if ($assignBadge) {
			$photo->assignBadge('photos.create', $photo->user_id);
		}

		// After storing the photo, trigger rules that should occur after a photo is stored
		$photo->afterStore($file, $image);

		return $photo;
	}
}
