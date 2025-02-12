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

ES::import('admin:/tables/table');
ES::import('admin:/includes/stream/dependencies');
ES::import('admin:/includes/indexer/indexer');

class SocialTablePhoto extends SocialTable implements ISocialIndexerTable, ISocialStreamItemTable
{
	public $id = null;
	public $uid = 0;
	public $type = '';
	public $user_id = null;
	public $album_id = null;
	public $title = null;
	public $caption = null;
	public $created = null;
	public $assigned_date = null;
	public $ordering = null;
	public $total_size = null;
	public $featured = null;
	public $storage = 'joomla';

	/**
	 * Use for photo privacy access
	 */
	public $access = null;
	public $custom_access = null;
	public $field_access = null;
	public $chk_access = null;

	/**
	 * The state of the photo. default is true
	 * @var string
	 */
	public $state = null;
	public $_uuid = null;
	static $_photos = array();
	static $_cache = null;

	/**
	 * The actor of this photo.
	 * @var bool
	 */
	public $post_as = null;

	public function __construct($db)
	{
		// Create a unique id only for each table instance
		// This is to help controller implement the right element.
		$this->_uuid = uniqid();

		//determide if load method should get from cache variable or not.
		if (is_null(self::$_cache)) {
			self::$_cache = false;
		}

		parent::__construct('#__social_photos', 'id', $db);
	}

	/**
	 * Overrides parent's load implementation
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function load($keys = null, $reset = true)
	{
		if (self::$_cache) {

			if (is_array($keys)) {
				return parent::load($keys, $reset);
			}

			if (!isset(self::$_photos[$keys])) {
				$state = parent::load($keys);
				self::$_photos[$keys] = $this;

				return $state;
			}

			if (is_bool(self::$_photos[$keys])) {
				return false;
			}

			return parent::bind(self::$_photos[$keys]);
		} else {
			return parent::load($keys, $reset);
		}
	}

	public function setCacheable($cache = false)
	{
		self::$_cache = $cache;
	}

	public function loadByBatch($ids)
	{
		$db = ES::db();
		$sql = $db->sql();

		$photoIds = array();
		$albumIds = array();

		foreach ($ids as $pid) {
			if (!isset(self::$_photos[$pid])) {
				$photoIds[] = $pid;
			}
		}

		if ($photoIds) {
			foreach ($photoIds as $pid) {
				self::$_photos[$pid] = false;
			}

			$query = '';
			$idSegments = array_chunk($photoIds, 5);

			for ($i = 0; $i < count($idSegments); $i++) {
				$segment = $idSegments[$i];
				$ids = implode(',', $segment);

				$query .= 'select * from `#__social_photos` where `id` IN (' . $ids . ')';

				if (($i + 1) < count($idSegments)) {
					$query .= ' UNION ';
				}
			}

			$sql->raw($query);
			$db->setQuery($sql);

			$results = $db->loadObjectList();

			if ($results) {
				foreach ($results as $row) {
					$albumIds[] = $row->album_id;
					self::$_photos[$row->id] = $row;
				}
			}
		}

		return $albumIds;

	}

	/**
	 * Rotates a photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   int     The angle to rotate the photo
	 * @return
	 */
	public function rotate($angle)
	{
		// Try to rotate the image
		$image = ES::image();
		$image->load($this->getPath('stock'));
		$image->rotate($angle);

		return $image;
	}

	/**
	 * Rotates tags in a photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   int     The angle to rotate the photo
	 * @return
	 */
	public function rotateTags($angle)
	{
		$model = ES::model('Photos');
		$tags = $model->getTags($this->id);

		foreach ($tags as $tag) {
			$oldTag = clone($tag);

			if ($angle == 90) {
				$tag->width = $oldTag->height;
				$tag->height = $oldTag->width;

				$tag->left = (1 - $oldTag->top) - $tag->width;
				$tag->top = $oldTag->left;
			}

			if ($angle == -90) {
				$tag->width = $oldTag->height;
				$tag->height = $oldTag->width;
				$tag->top = $oldTag->left;
				$tag->left = $oldTag->top;
			}

			$tag->store();
		}
	}

	/**
	 * Toggle's a photo featured state
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function toggleFeatured()
	{
		$this->featured = $this->featured ? false : true;

		return $this->store();
	}

	/**
	 * Determines if the photo is featured
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function isFeatured()
	{
		return (bool) $this->featured;
	}

	/**
	 * Determines if this image is stored externally
	 *
	 * @since   1.4
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function isStoredRemotely()
	{
		if ($this->storage == SOCIAL_STORAGE_JOOMLA) {
			return false;
		}

		return true;
	}

	/**
	 * Cleanup photo title
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function cleanupTitle()
	{
		$knownExtensions = array('.jpg', '.jpeg', '.gif', '.png', '.tiff', '.bmp');

		$this->title = str_ireplace($knownExtensions, '', $this->title);
	}

	/**
	 * Determines if the photo is owned by the provided user.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function isMine($id = null)
	{
		$user = ES::user($id);

		$isOwner = $user->id == $this->uid;

		return $isOwner;
	}

	/**
	 * Normalizes all meta value by ensuring that it's using the latest format
	 *
	 * @since   3.2.14
	 * @access  public
	 */
	public function normalizeMetaValue(&$meta)
	{
		$root = JPATH_ROOT;

		// For some site's the root path is just / and it's probably caused by chroot
		if ($root !== '/') {

			// Fixed legacy value when the data contains JPATH_ROOT values
			$meta->value = str_ireplace($root, '', $meta->value);
		}

		// Get the filename from the path
		$fileName = basename($meta->value);

		// Here we need to test if there is more than 3 segments of /path/album_id/photo_id/filename
		$parts = explode('/', $meta->value);

		if (count($parts) > 3) {
			$meta->value = '/' . $this->album_id . '/' . $this->id . '/' . $fileName;

			// If there is more than 3 segments in the path, we need to update the record
			if (count($parts) > 3) {

				$table = ES::table('PhotoMeta');
				$table->bind($meta);
				$table->value = $meta->value;
				$table->store();
			}
		}
	}

	/**
	 * Deletes the photo folder
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function deletePhotoFolder()
	{
		$config = ES::config();
		$container = ES::cleanPath($config->get('photos.storage.container'));

		$path = JPATH_ROOT . '/' . $container . '/' . $this->album_id . '/' . $this->id;

		$exists = JFolder::exists($path);

		if (!$exists) {
			return false;
		}

		$state = JFolder::delete($path);

		return $state;
	}

	/**
	 * Deletes the photos generated for this photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function deletePhotos($types)
	{
		$files = array();

		$config = ES::config();

		// Get the photo storage container
		$container = ES::cleanPath($config->get('photos.storage.container'));

		foreach ($types as $type) {
			$meta = ES::table('PhotoMeta');
			$meta->load(array('photo_id' => $this->id, 'group' => SOCIAL_PHOTOS_META_PATH, 'property' => $type));

			$this->normalizeMetaValue($meta);

			if ($type != 'stock') {

				$files[] = $container . $meta->value;
			}

			$meta->delete();
		}

		// Since remote storages doesn't store the "stock" photo, we need to manually delete it
		$storage = ES::storage($this->storage);
		$storage->delete($files);

		return true;
	}

	/**
	 * Assign points to a user
	 *
	 * @since   1.0
	 * @access  public
	 * @param   int         The actor's id
	 * @return
	 */
	public function assignPoints($rule, $actorId)
	{
		$points = ES::points();
		$points->assign($rule, 'com_easysocial', $actorId);
	}

	/**
	 * Creates a badge record
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function assignBadge($rule, $actorId)
	{
		if ($rule == 'photos.create') {
			// @badge: photos.create
			$badge  = ES::badges();
			$badge->log('com_easysocial', 'photos.create', $actorId, JText::_('COM_EASYSOCIAL_PHOTOS_BADGE_UPLOADED'));
		}

		if ($rule == 'photos.browse') {
			// @badge: photos.browse
			$badge  = ES::badges();
			$badge->log('com_easysocial', 'photos.browse', $actorId, JText::_('COM_EASYSOCIAL_PHOTOS_BADGE_BROWSE'));
		}

		if ($rule == 'photos.tag') {
			// @badge: photos.tag
			$badge  = ES::badges();
			$badge->log('com_easysocial', 'photos.tag', $actorId, JText::_('COM_EASYSOCIAL_PHOTOS_BADGE_TAG'));
		}

		if ($rule == 'photos.superstar') {
			// @badge: photos.superstar
			$badge  = ES::badges();
			$badge->log('com_easysocial', 'photos.superstar', $actorId, JText::_('COM_EASYSOCIAL_PHOTOS_BADGE_TAG'));
		}
	}

	/**
	 * Updates the metadata of the photo
	 *
	 * @since   1.2.11
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function updateMeta($group, $key, $value)
	{
		$meta = ES::table('PhotoMeta');
		$options = array('photo_id' => $this->id, 'group' => $group, 'property' => $key);
		$exists = $meta->load($options);

		if (!$exists) {

			// If the meta does not exist, create it
			$meta = ES::table('PhotoMeta');
			$meta->photo_id = $this->id;
			$meta->group = $group;
			$meta->property = $key;
			$meta->value = $value;
			$state  = $meta->store();

			return $state;
		}

		// If the meta exists, just reset it
		$meta->value = $value;

		// Store the new angle value
		$state = $meta->store();

		if (!$state) {
			$this->setError($meta->getError());
		}

		return $state;
	}

	/**
	 * Updates the angle of the photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function updateAngle($angle)
	{
		$meta = ES::table('PhotoMeta');
		$options = array('photo_id' => $this->id, 'group' => SOCIAL_PHOTOS_META_TRANSFORM, 'property' => 'rotation');
		$exists = $meta->load($options);

		if (!$exists) {
			$meta->photo_id = $this->id;
			$meta->group = SOCIAL_PHOTOS_META_TRANSFORM;
			$meta->property = 'rotation';
		}

		// Angle should not be more than 360.
		$angle = $angle >= 360 ? 0 : $angle;

		$meta->value = $angle;

		// Store the new angle value
		$state = $meta->store();

		if (!$state) {
			$this->setError($meta->getError());
		}

		return $state;
	}

	/**
	 * Get's the current
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getAngle()
	{
		$meta = ES::table('PhotoMeta');
		$options = array('photo_id' => $this->id, 'group' => SOCIAL_PHOTOS_META_TRANSFORM, 'property' => 'rotation');

		$meta->load($options);

		$currentAngle = $meta->value ? $meta->value : 0;

		return $currentAngle;
	}

	/**
	 * Override store method
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function store($updateNulls = false)
	{
		$isNew = $this->id ? false : true;

		// always set the chk_access to 1
		// so that new photos created after 3.1
		// will not need to re-run the
		// privacy access migration.
		$this->chk_access = 1;

		$state = parent::store();

		if ($isNew) {

			if ($this->type !== SOCIAL_TYPE_MARKETPLACE) {
				// @points: photos.upload
				// Add points for the author
				$points = ES::points();
				$points->assign('photos.upload', 'com_easysocial', $this->user_id);

				// @badge: photos.upload
				// Assign a badge for the user
				$this->assignBadge('photos.upload', $this->user_id);
			}

			$exif = ES::get('Exif');

			// Detect the photo caption and title if exif is available.
			if ($exif->isAvailable()) {
				// Store the meta for the angle of the photo to be 0 by default.
				$meta = ES::table('PhotoMeta');

				$meta->photo_id = $this->id;
				$meta->group = SOCIAL_PHOTOS_META_EXIF;
				$meta->property = 'angle';
				$meta->value = 0;

				$meta->store();
			}
		}

		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderAfterSave', array('easysocial.photos', &$this, $isNew));

		return $state;
	}

	/**
	 * Some desc
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function addPhotosStream($verb, $mysqldatestring = '', $aggregate = true, $originalVerb = false, $privacyValue = '', $privacyCustom = '', $privacyField = '')
	{
		// Load up the photo library
		$lib = ES::photo($this->uid, $this->type, $this);

		$streamId = $lib->getPhotoStreamId($this->id, $verb);
		$streamIdExists = false;

		if ($originalVerb) {
			$compositeKey = SOCIAL_TYPE_PHOTO . '.' . $this->type . '.' . $originalVerb;

			// Check for comments
			$comment = ES::table('Comments');
			$comment->load(array('uid' => $this->id, 'element' => $compositeKey));

			// Comment exists
			if ($comment->id) {

				// Determine if there is stream id for the comments
				$streamId = $comment->stream_id;
				if ($streamId) {
					$streamIdExists = true;
				} else {
					return false;
				}
			}

			// Check for reactions
			if (!$streamIdExists) {
				$likes = ES::table('Likes');
				$likes->load(array('uid' => $this->id, 'type' => $compositeKey));

				// Check for stream reactions
				if ($likes->id) {
					$streamId = $likes->stream_id;
					if ($streamId) {
						$streamIdExists = true;
					} else {
						return false;
					}
				}
			}
		}

		if ($streamId) {

			// Check for stream aggregation
			$sModel = ES::model('Stream');
			$totalItem = $sModel->getStreamItemsCount($streamId);

			// Only for non aggregate stream
			if ($totalItem == 1) {
				$streamItem = ES::table('streamItem');
				$streamItem->load(array('uid' => $streamId));

				return $streamItem;
			}

			// Stream already exists for this photo.
			if ($streamIdExists) {
				return false;
			}
		}

		// Check if the photo was belong to edited stream. #3066
		if (!$streamId && $originalVerb == 'upload') {
			$model = ES::model('Stream');
			$aggregated = $model->getAggregatedStreamId($this->id, 'photos');

			// Only check for aggregation
			if ($aggregated) {
				$streamId = $aggregated;
				$stream = ES::table('Stream');
				$stream->load($streamId);

				// Do nothing if the stream was edited.
				if ($stream->edited && $stream->edited != '0000-00-00 00:00:00') {
					return false;
				}
			}
		}

		// Generate new stream
		return $lib->createStream($verb, $mysqldatestring, $aggregate, $privacyValue, $privacyCustom);
	}

	public function addStream($verb)
	{
		// do nothing. do not remove this function!
		// this method is needed to fullfil the interface implmentation.
	}

	public function removeStream()
	{
		$stream = ES::stream();
		$stream->delete($this->id, SOCIAL_TYPE_PHOTO);
	}

	/**
	 * Determine if the photo already has a stream
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function hasStream($verb)
	{
		$lib = ES::photo($this->uid, $this->type, $this);
		$exists = $lib->hasStream($verb);

		return $exists;
	}

	/**
	 * Override parent's delete method
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function delete($pk = null)
	{
		// Delete the record from the database first.
		$state = parent::delete();

		// Now, try to delete the folder that houses this photo.
		$config = ES::config();

		// Check if this photo is used as a profile cover
		if ($this->isProfileCover()) {
			$cover = ES::table('Cover');
			$cover->load(array('photo_id' => $this->id));

			$cover->delete();
		}

		$album = $this->getAlbum();

		// Needs to create an instance of image to create
		// and instnance of photos
		$image = ES::image();
		$photoLib = ES::get('Photos', $image);
		$basePath = $photoLib->getStoragePath($this->album_id, $this->id);

		// Construct the base path to the photos folder
		$container = ES::cleanPath($config->get('photos.storage.container'));
		$basePath = '/' . $container . '/' . $this->album_id . '/' . $this->id;

		// @legacy
		// @since 1.2.7
		// To fix older version issues where the full path is stored for the photo
		$relative = str_ireplace(JPATH_ROOT, '', $basePath);

		// below checking is to make sure the computed relative path is not pointing to photos root folder.
		$photoContainer = ES::cleanPath($config->get('photos.storage.container'));
		$photoContainer = '/' . ltrim($photoContainer, '/');
		$photoContainer = rtrim($photoContainer, '/');
		$photoContainer = JPath::clean($photoContainer);

		$photoRelPath = '/' . ltrim($relative, '/');
		$photoRelPath = rtrim($photoRelPath, '/');
		$photoRelPath = JPath::clean($photoRelPath);

		if ($photoContainer != $photoRelPath) {
			$storage = ES::storage($this->storage);
			$storage->delete($relative, true);

			$storage->syncUsage($this->user_id);
		}

		$model = ES::model('Photos');

		// Delete the meta's related to this photo
		$model->deleteMeta($this->id);

		// Delete all tags associated with this photo
		$model->deleteTags($this->id);

		// Delete all system notification associated with this photo
		$model->deleteSystemNotifications($this->id);

		// we need to determine which verb to use when removing comments /reaction #2849
		$verb = 'add';

		if ($album->isStory()) {
			$verb = 'upload';
		}

		// Delete all comments associated with this photo
		$comments = ES::comments($this->id, SOCIAL_TYPE_PHOTO, $verb, $this->type);
		$comments->delete();

		// Delete all likes associated with this photo
		$likes = ES::get('Likes');
		$likes->delete($this->id, SOCIAL_TYPE_PHOTO, $verb, $this->type, null, true);

		// @points: photos.remove
		// Deduct points for the author
		if ($this->type !== SOCIAL_TYPE_MARKETPLACE) {
			$points = ES::points();
			$points->assign('photos.remove', 'com_easysocial', $this->user_id);
		}

		// Push the ordering of other photos
		$model->pushPhotosOrdering($this->album_id, 0, $this->ordering, '-');

		// Need to set cover to another photo
		if ($this->isCover()) {
			$album = $this->getAlbum();

			$album->cover_id = 0;

			if ($album->hasPhotos()) {
				$result = $album->getPhotos(array('limit' => 1));


				if ($result && (isset($result['photos']) && $result['photos'])) {
					$album->cover_id = $result['photos'][0]->id;
				}
			}

			$album->store();
		}

		if ($state) {
			// Delete from smart search index
			JPluginHelper::importPlugin('finder');
			ESDispatcher::trigger('onFinderAfterDelete', array('easysocial.photos', $this));
		}

		return $state;
	}

	/**
	 * Tests if the user is allowed to download the photo
	 *
	 * @deprecated  1.2
	 */
	public function downloadable($id = null)
	{
		$lib = ES::photo($this->uid, $this->type, $this);

		return $lib->downloadable();
	}

	/**
	 * Determiens if the user can view an album
	 *
	 * @deprecated  1.2
	 */
	public function viewable($id = null)
	{
		$lib = ES::photo($this->uid, $this->type, $this);
		return $lib->viewable();
	}

	/**
	 * Tests if the user is allowed to use this photo as their avatar.
	 *
	 * @deprecated  1.2
	 */
	public function canSetProfilePicture($id = null)
	{
		$lib = ES::photo($this->uid, $this->type, $this);

		return $lib->canSetProfilePicture();
	}

	/**
	 * Tests if the user is allowed to use this photo as cover
	 *
	 * @deprecated  1.2
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function canSetProfileCover($id = null)
	{
		$lib = ES::photo($this->uid, $this->type, $this);

		return $lib->canSetProfileCover();
	}

	/**
	 * Tests if the user is allowed to feature this photo
	 *
	 * @deprecated  1.2
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function featureable($id = null)
	{
		$lib = ES::photo($this->uid, $this->type, $this);

		return $lib->featureable();
	}

	/**
	 * Tests if the user is allowed to share this photo
	 *
	 * @deprecated  1.2
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function shareable($id = null)
	{
		$lib = ES::photo($this->uid, $this->type, $this);

		return $lib->shareable();
	}

	/**
	 * Tests if the user is allowed to tag on this photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function taggable($id = null)
	{
		$user = ES::user($id);

		if ($this->uid == $user->id || $user->isSiteAdmin()) {
			return true;
		}

		// @TODO: Test if this photo privacy allow friends to tag on the photo
		$privacyLib = ES::privacy($this->uid);

		if (!$privacyLib->validate('photo.tag', $user->id, SOCIAL_TYPE_USER)) {
			return false;
		}

		return true;
	}

	/**
	 * Tests if the album is delete able by the provided user id.
	 *
	 * @deprecated  1.2
	 * @since   1.0
	 * @access  public
	 * @param   int     User id.
	 * @return
	 */
	public function deleteable($id = null, $type = SOCIAL_TYPE_USER)
	{
		$lib = ES::photo($this->uid, $this->type, $this);

		return $lib->deleteable();
	}

	/**
	 * move photo to new different albums
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function move($newAlbumId , $where = '')
	{

		jimport('joomla.filesystem.folder');

		// If the current photo is the cover of the photo we need to update the cover
		// since the photo is already moved away
		if ($this->isCover()) {
			$this->setNewAlbumCover();
		}

		// Get the old album id as we need to move the old photo folder over
		$oldAlbumId = $this->album_id;

		// Get the path to the photos folder.
		$oldFolder = $this->getFolder();

		// Set the new album id.
		$this->album_id = $newAlbumId;

		// Get the new photo folder
		$newAlbumFolder = $this->getFolder(false);
		$newFolder = $this->getFolder();


		if (!JFolder::exists($newAlbumFolder)) {
			JFolder::create($newAlbumFolder);
		}

		// Save the photo with the new album
		$state = parent::store();

		if (!$state) {
			return $state;
		}

		JFolder::move($oldFolder, $newFolder);

		// Once the folder is moved, we also need to update all the metas.
		$model = ES::model('Photos');
		$metas = $model->getMeta($this->id, SOCIAL_PHOTOS_META_PATH);

		foreach ($metas as $meta) {

			$table = ES::table('PhotoMeta');
			$table->bind($meta);

			$fileName = basename($table->value);

			// Rebuild the new path
			$table->value = $newFolder . '/' . $fileName;

			$table->store();
		}

		// now we need to remove this photo from stream table.
		$model = ES::model('Photos');
		$model->delPhotoStream($this->id, $this->user_id, $oldAlbumId);
		return $state;
	}

	/**
	 * Set a new album cover
	 *
	 * @since   1.2
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function setNewAlbumCover()
	{
		$album = $this->getAlbum();

		if (!$album->hasPhotos()) {
			$album->cover_id = 0;
		} else {

			$result = $album->getPhotos(array('limit' => 1));
			$photo = $result['photos'][0];

			$album->cover_id = $photo->id;
		}

		$state = $album->store();

		return $state;
	}

	/**
	 * Retrieves the params for the photo item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getPhotoCreator($obj)
	{
		if ($this->post_as == SOCIAL_TYPE_PAGE && !is_null($obj)) {
			return $obj;
		}

		return $this->getCreator();
	}

	/**
	 * Determines if the user is allowed to move the photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function moveable($id = null, $type = SOCIAL_TYPE_USER)
	{
		$album = $this->getAlbum();

		// If this is a system album like cover photos, profile pictures, they will not be able to move photos within this album.
		$disallowed = array(SOCIAL_ALBUM_STORY_ALBUM, SOCIAL_ALBUM_PROFILE_COVERS, SOCIAL_ALBUM_PROFILE_PHOTOS);

		if (in_array($album->core, $disallowed)) {
			return false;
		}

		if ($type == SOCIAL_TYPE_USER) {
			$user = ES::user($id);

			// @TODO: Allow users with moderation / super admins to delete
			if ($this->uid == $user->id) {
				return true;
			}

			return false;
		}

		return false;
	}

	public function getCreator()
	{
		// @legacy
		// There could be instances where the user_id was empty
		if (!$this->user_id && $this->type == SOCIAL_TYPE_USER) {
			return ES::user($this->uid);
		}

		return ES::user($this->user_id);
	}

	/**
	 * Retrieves the creation date of the photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getCreationDate()
	{
		return $this->created;
	}

	/**
	 * Determines if this album has an assigned date.
	 *
	 * @since   1.2.8
	 * @access  public
	 */
	public function hasAssignedDate()
	{
		if ($this->assigned_date == '0000-00-00 00:00:00') {
			return false;
		}

		return true;
	}

	/**
	 * Generates a proper title for the photo
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function generateTitle()
	{
		$date = ES::date();

		// Sanitize filename, prevent filename collision.
		$title = JFile::makeSafe($this->title);

		// Check whether that is valid filename, if not we have to convert the filename to date time
		$title = strpos($title, '.') === false || strpos($title, '.') === 0 ? $date->format(JText::_('DATE_FORMAT_LC4')) : $title;

		// Replace the space to '-' if there is
		// And this one have to do it after JFile::makeSafe() method if not it will not remove all the umlaut character
		$title = str_replace(' ', '-', $title);

		return $title;
	}

	/**
	 * Retrieves the assigned date of the album
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getAssignedDate()
	{
		// if assigned date is empty, we use creation date.
		if ($this->assigned_date == '0000-00-00 00:00:00') {
			return $this->getCreationDate();
		}

		return $this->assigned_date;
	}

	/**
	 * Get a list of tags for this photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getTags($peopleOnly = false)
	{
		$model = ES::model('Photos');

		// Retrieve list of tags for this photo.
		$tags = $model->getTags($this->id, $peopleOnly);

		return $tags;
	}

	/**
	 * Retrieves the storage path for this album
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getStoragePath(SocialTableAlbum $album, $relative = false)
	{
		// Rename temporary folder to the destination.
		jimport('joomla.filesystem.folder');

		// Get destination folder path.
		$storage = $album->getStoragePath($relative);

		// Build the storage path now with the album id
		$storage = $storage . '/' . $this->id;

		// Ensure that the final storage path exists.
		if (!$relative) {
			ES::makeFolder($storage);
		}

		return $storage;
	}

	/**
	 * Retrieves the likes count for this photo
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getLikesCount()
	{
		static $likes = array();

		if (!isset($likes[$this->id])) {

			// Default context
			$type = SOCIAL_TYPE_PHOTO;
			$group = $this->type;
			$id = $this->id;

			$lib = ES::photo($this->uid, $this->type, $this);
			$verb = $lib->getPhotoVerb();

			// We need to determine if the album is a core one
			$album = $this->getAlbum();

			$streamId = $lib->getPhotoStreamId($id, $verb, false);

			if ($album->core == SOCIAL_ALBUM_STORY_ALBUM) {

				// We need to test if this photo contains single stream item or aggregated
				if ($verb == 'upload') {
					$model = ES::model('Stream');
					$aggregated = $model->isAggregated($this->id, 'photos');

					if ($aggregated) {
						$streamId = '0';
					} else {
						$type = SOCIAL_TYPE_STREAM;
						$id = $streamId;
					}
				}
			}

			$likes[$this->id] = ES::get('Likes')->getCount($id, $type, $verb, $group, array(), $streamId);
		}

		return $likes[$this->id];
	}

	/**
	 * Retrieves the comments count for this photo
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getCommentsCount()
	{
		static $comments = array();

		if (!isset($comments[$this->id])) {
			// Default context
			$context = SOCIAL_TYPE_PHOTO;
			$verb = 'add';
			$group = $this->type;
			$id = $this->id;

			$lib = ES::photo($this->uid, $this->type, $this);
			$verb = $lib->getPhotoVerb();

			// We need to determine if the album is a core one
			$album = $this->getAlbum();

			$streamId = $lib->getPhotoStreamId($id, $verb, false);

			if ($album->core == SOCIAL_ALBUM_STORY_ALBUM) {

				// We need to test if this photo contains single stream item or aggregated
				if ($verb == 'upload') {
					$model = ES::model('Stream');
					$aggregated = $model->isAggregated($this->id, 'photos');

					if ($aggregated) {
						$streamId = '0';
					} else {
						$context = SOCIAL_TYPE_STREAM;
						$id = $streamId;
					}
				}
			}

			$count = ES::comments($id, $context, $verb, $group, array(), $streamId)->getCount();

			$comments[$this->id] = $count;
		}

		return $comments[$this->id];
	}

	/**
	 * Get's the absolute path of the photo given the type.
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getFolder($includePhotoId = true, $absolutePath = true)
	{
		$config = ES::config();
		$storage = ES::cleanPath($config->get('photos.storage.container'));
		$path = '';

		if ($absolutePath) {
			$path = JPATH_ROOT;
		}

		$path .= '/' . $storage . '/' . $this->album_id;

		if ($includePhotoId) {
			$path .= '/' . $this->id;
		}

		return $path;
	}

	/**
	 * Get's the absolute path of the photo given the type.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getPath($type, $relative = false, $reset = false)
	{
		static $paths = array();

		$config = ES::config();

		$type = $this->mapImageType($type);

		// Get the photo storage container
		$container = $config->get('photos.storage.container');
		$container = ES::cleanPath($container);

		$key = $this->id . $type . (int) $relative;

		if (!isset($paths[$key]) || $reset) {
			$model = ES::model('Photos');

			// Retrieve information about the photo
			$metas = $model->getMeta($this->id, SOCIAL_PHOTOS_META_PATH);
			$result = new stdClass();

			if (!$metas) {
				$paths[$key] = false;

				return $paths[$key];
			}

			foreach ($metas as $meta) {

				$this->normalizeMetaValue($meta);

				// Default to use absolute
				$result->{$meta->property} = JPATH_ROOT . '/' . $container . $meta->value;

				if ($relative) {
					$result->{$meta->property} = '/' . $container . $meta->value;
				}


			}

			$paths[$key] = $result;
		}

		// If we still cannot locate the path, we should just return false
		if (!isset($paths[$key]->{$type})) {
			return false;
		}

		return $paths[$key]->{$type};
	}

	/**
	 * Allows caller to download a file.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function download()
	{
		// Get the original file path.
		$file = $this->getPath('original');

		// Make the path relative
		if ($this->storage != 'joomla') {
			$filePath = str_ireplace(JPATH_ROOT, '', $file);

			$storage = ES::storage($this->storage);

			// We must get the file name from the path itself to match the key on the s3
			$fileName = substr($filePath, strrpos($filePath, '/') + 1);

			return $storage->download($filePath, $fileName);
		}

		// Get the mime of the image
		$mime = $this->getMime('original');

		// cleanup photo title to ensure there are no duplicate image extension
		$this->cleanupTitle();

		// Get photo name and extension
		$name = $this->title . $this->getExtension();

		// Set the headers for the file transfer
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $mime);
		header("Content-Disposition: attachment; filename=\"". $name ."\";");
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));

		ob_clean();
		flush();
		readfile($file);
		exit;
	}

	/**
	 * Retrieves the photo extension .jpg | .png
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getExtension($size = 'stock')
	{
		// Use the stock photo to retrieve the extension
		$mime = $this->getMime($size);
		$extension = '.jpg';

		if ($mime == 'image/png') {
			$extension = '.png';
		}

		return $extension;
	}

	/**
	 * Retrieve the width of the photo
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getWidth($variation = 'original')
	{
		static $width = array();
		$key = $this->id . '.' . $variation;

		if (!isset($width[$key])) {

			$width[$key] = '';

			$model = ES::model('Photos');
			$meta = $model->getMeta($this->id, SOCIAL_PHOTOS_META_WIDTH, $variation);

			if ($meta) {
				$width[$key] = $meta[0]->value;
			}

			// Value not exists. Let's try to retrieve it again
			if (!$width[$key] && !$this->isStoredRemotely()) {
				$imageObject = $this->getImageObject($variation);

				if ($imageObject) {
					$width[$key] = $imageObject->getWidth();

					$meta = ES::table('PhotoMeta');
					$meta->load(array(
						'photo_id' => $this->id,
						'group' => SOCIAL_PHOTOS_META_WIDTH,
						'property' => $variation
					));

					if (!$meta->id) {
						$meta->photo_id = $this->id;
						$meta->group = SOCIAL_PHOTOS_META_WIDTH;
						$meta->property = $variation;
					}

					$meta->value = $width[$key];
					$meta->store();
				}
			}
		}

		return $width[$key];
	}

	/**
	 * Retrieve the height of the photo
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getHeight($variation = 'original')
	{
		static $height = array();
		$key = $this->id . '.' . $variation;

		if (!isset($height[$key])) {

			$height[$key] = '';

			$model = ES::model('Photos');
			$meta = $model->getMeta($this->id, SOCIAL_PHOTOS_META_HEIGHT, $variation);

			if ($meta) {
				$height[$key] = $meta[0]->value;
			}

			// Value not exists. Let's try to retrieve it again
			if (!$height[$key] && !$this->isStoredRemotely()) {
				$imageObject = $this->getImageObject($variation);

				if ($imageObject) {
					$height[$key] = $imageObject->getHeight();

					// Store the meta
					$meta = ES::table('PhotoMeta');
					$meta->load(array(
						'photo_id' => $this->id,
						'group' => SOCIAL_PHOTOS_META_HEIGHT,
						'property' => $variation
					));

					if (!$meta->id) {
						$meta->photo_id = $this->id;
						$meta->group = SOCIAL_PHOTOS_META_HEIGHT;
						$meta->property = $variation;
					}

					$meta->value = $height[$key];
					$meta->store();

				}
			}
		}

		return $height[$key];
	}

	/**
	 * Returns the mime type for this photo
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getMime($type)
	{
		$path = $this->getPath($type);

		if (!$path) {
			return '';
		}

		if (!file_exists($path)) {
			return '';
		}

		$info = getimagesize($path);

		return $info['mime'];
	}

	/**
	 * Gets the image object given the size of the image
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getImageObject($size)
	{
		$path = $this->getPath($size);

		// If this image is stored remotely, we need to download the files first.
		if ($this->isStoredRemotely()) {

			$album = $this->getAlbum();
			$relativePath = $this->getPath($size, true);

			// Ensure that the photo folder exists
			$parentRelativePath = JPATH_ROOT . dirname($relativePath);

			if (!JFolder::exists($parentRelativePath)) {
				JFolder::create($parentRelativePath);
			}

			$folderPath = dirname($relativePath);

			// Download the image from remote storage
			$storage = ES::storage($this->storage);
			$storage->pull($folderPath);

		}

		if (!JFile::exists($path)) {
			return false;
		}

		$image = ES::image();
		$image->load($path);

		return $image;
	}

	public function getUri($type)
	{
		$model = ES::model('Photos');
		$metas = $model->getMeta($this->id, SOCIAL_PHOTOS_META_PATH);
	}

	/**
	 * Re-map the image variation path
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function mapImageType($type)
	{
		// Prior to ES 2.0, we no longer use square and featured as image variation
		// Hence we need to re-map the size with different value. #4905
		$mapImageType = array(
			'square' => 'thumbnail',
			'featured' => 'large'
			);

		if ($type == 'square' || $type == 'featured') {
			$type = $mapImageType[$type];
		}

		return $type;
	}

	/**
	 * Retrieves the permalink to the image given the size
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getSource($type = 'thumbnail', $cache = false)
	{
		static $paths = array();

		$config = ES::config();

		$type = $this->mapImageType($type);

		$doQuery = true;

		if ($cache && isset($paths[$this->id])) {
			$doQuery = false;
		}

		if ($doQuery) {

			// Load the paths for this photo
			$model = ES::model('Photos');
			$metas = $model->getMeta($this->id, SOCIAL_PHOTOS_META_PATH);

			$obj = new stdClass();

			$path = ES::cleanPath($config->get('photos.storage.container'));

			$allowed = array('thumbnail', 'large', 'original', 'square', 'featured', 'medium');

			foreach ($metas as $meta) {

				$relative = $path . '/' . $this->album_id . '/' . $this->id . '/' . basename($meta->value);

				if ($this->storage != SOCIAL_STORAGE_JOOMLA && in_array($meta->property, $allowed)) {

					$storage = ES::storage($this->storage);
					$url = $storage->getPermalink($relative);
				} else {

					$url = ES::getUrl($relative, $this->storage);
				}

				$obj->{$meta->property} = $url;
			}

			$paths[$this->id] = $obj;
		}

		if (!isset($paths[$this->id]->$type)) {
			$paths[$this->id]->$type = false;
		}

		return $paths[$this->id]->$type;
	}

	/**
	 * Sync photo index
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function syncIndex()
	{
		$indexer = ES::get('Indexer');

		$item = $indexer->getTemplate();

		$url = $this->getPermalink();
		$url = '/' . ltrim($url, '/');
		$url = str_replace('/administrator/', '/', $url);

		$item->setSource($this->id, SOCIAL_INDEXER_TYPE_PHOTOS, $this->uid, $url);

		$content = ($this->caption) ? $this->caption : $this->title;
		$item->setContent($this->title, $content);

		$date = ES::date();
		$item->setLastUpdate($date->toMySQL());

		$state = $indexer->index($item);
		return $state;
	}

	/**
	 * Delete photo index
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function deleteIndex()
	{
		$indexer = ES::get('Indexer');
		$indexer->delete($this->id, SOCIAL_INDEXER_TYPE_PHOTOS);
	}

	/**
	 * Retrieves the location for this photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getLocation()
	{
		static $locations = array();

		if (!isset($locations[$this->id])) {
			$location = ES::table('Location');
			$state = $location->loadByType($this->id, SOCIAL_TYPE_PHOTO, $this->uid);

			if (!$state) {
				$locations[$this->id] = $state;
			} else {
				$locations[$this->id] = $location;
			}
		}

		return $locations[$this->id];
	}

	/**
	 * Retrieves the location of the album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLocationLib()
	{
		static $locations = array();

		if (!isset($locations[$this->id])) {
			$lib = ES::location($this->id, SOCIAL_TYPE_PHOTO);
			$locations[$this->id] = $lib;
		}

		return $locations[$this->id];
	}

	/**
	 * Retrieves the album for this photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getAlbum()
	{
		static $albums = array();

		if (!isset($albums[$this->album_id])) {

			$album = ES::table('Album');
			$album->load($this->album_id);

			$albums[$this->album_id] = $album;
		}

		return $albums[$this->album_id];
	}

	/**
	 * Determines if this photo is used as a profile cover
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function isProfileCover()
	{
		$model = ES::model('Photos');
		$isProfileCover = $model->isProfileCover($this->id, $this->uid, $this->type);

		return $isProfileCover;
	}

	/**
	 * Determines if this photo is cluster type
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isCluster()
	{
		$clusterTypes = array(SOCIAL_TYPE_PAGE, SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT);

		return in_array($this->type, $clusterTypes);
	}

	/**
	 * Determines if the current photo is a cover photo
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function isCover()
	{
		return ($this->getAlbum()->cover_id == $this->id);
	}

	/**
	 * Test whether the image has animation
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function isAnimated()
	{
		$img = $this->getPath('original');

		$file = @file_get_contents($img);

		if (!$file) {
			return false;
		}

		// Test if the image contain animation.
		$animated = preg_match('#(\x00\x21\xF9\x04.{4}\x00\x2C.*){2,}#s', $file);

		return $animated;
	}

	/**
	 * Constructs the alias for this photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getAlias()
	{
		$title = $this->title;

		$title = JFilterOutput::stringURLSafe($title);

		$alias = $this->id . ':' . $title;

		return $alias;
	}

	/**
	 * Method to update the cached sef alias when there
	 * is changes on the alias column
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateAliasSEFCache()
	{
		$old = ES::table('Photo');
		// to avoid getting the cache copy, we need to pass in an array
		$old->load(array('id' => $this->id));

		$oldAlias = $old->getAlias();
		$newAlias = $this->getAlias();

		if ($oldAlias != $newAlias) {
			ESR::updateSEFCache($this, $oldAlias, $newAlias);
		}
	}

	/**
	 * Method to delete the cached sef alias when item being removed.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function deleteSEFCache()
	{
		$alias = $this->getAlias();
		$state = ESR::deleteSEFCache($this, $alias);

		return $state;
	}

	/**
	 * Returns the permalink to the photo
	 *
	 * @since   1.0
	 * @access  public
	 * @return  string
	 */
	public function getPermalink($xhtml = true, $external = false, $layout = 'item', $sef = true, $adminSef = false)
	{
		$options = array('layout' => $layout, 'id' => $this->getAlias(), 'type' => $this->type, 'sef' => $sef, 'adminSef' => $adminSef);

		if ($this->isCluster()) {
			$options['uid'] = ES::cluster($this->type, $this->uid)->getAlias();
		}

		if ($this->type == SOCIAL_TYPE_USER) {
			$options['uid'] = ES::user($this->uid)->getAlias();
		}

		if ($external) {
			$options['external'] = true;
		}

		return ESR::photos($options, $xhtml);
	}

	/**
	 * Returns the permalink to edit the photo
	 *
	 * @since   1.0
	 * @access  public
	 * @return  string
	 */
	public function getEditPermalink($xhtml = true, $external = false)
	{
		$url = $this->getPermalink($xhtml, $external, 'form');

		return $url;
	}

	/**
	 * Export a photo (Legacy API)
	 *
	 * @since   3.1.0
	 * @access  public
	 */

	public function export()
	{
		$properties = get_object_vars($this);

		$photo = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$photo[$key] = $value;
			}
		}

		$photo['sizes'] = array();

		$arraySize = array('large', 'square', 'thumbnail', 'featured', 'original', 'stock');

		foreach ($arraySize as $size) {
			$photo['sizes'][$size] = array();

			$photo['sizes'][$size]['url'] = $this->getSource($size);
		}

		$photo['permalink'] = $this->getPermalink();

		return $photo;
	}

	/**
	 * Export the photo with REST
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $options = array())
	{
		static $cache = array();

		$key = $this->id . '.' . $viewer->id . serialize($options);

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$photo = ES::photo(null, null, $this->id);
		$cache[$key] = $photo->toExportData($viewer, $options);

		return $cache[$key];
	}

	public function uuid()
	{
		return $this->_uuid;
	}

	/**
	 * Determines if the user is allowed to edit this photo
	 *
	 * @deprecated  1.2
	 * @since   1.0
	 * @access  public
	 * @param   int     The user to check against (optional)
	 * @return  bool    True if success, false otherwise
	 */
	public function editable($id = null)
	{
		$lib = ES::photo($this->uid, $this->type, $this);

		return $lib->editable();
	}

	/**
	 * Maps the exif data to this photo
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function mapExif ($file)
	{
		$exif = ES::get('Exif');

		// Detect the photo caption and title if exif is available.
		if (!$exif->isAvailable()) {
			return;
		}

		$exif->load($file['tmp_name']);

		$title = $exif->getTitle();
		$caption = $exif->getCaption();

		if ($title) {
			$this->title = $title;
		}

		if ($caption) {

			// Ensure that it is a valid utf8 string
			if (function_exists('utf8_encode')) {
				$caption = utf8_encode($caption);
			}

			$this->caption = $caption;
		}

		// Store the photo again since the title or caption might change
		if ($title || $caption) {
			$this->store();
		}

		// Get the photo model
		$model = ES::model('Photos');

		// Get the location
		$locationCoordinates = $exif->getLocation();

		// Once we have the coordinates, we need to reverse geocode it to get the address.
		if ($locationCoordinates) {
			$geocode = ES::get('GeoCode');
			$address = $geocode->reverse($locationCoordinates->latitude, $locationCoordinates->longitude);

			$location = ES::table('Location');
			$location->loadByType($this->id, SOCIAL_TYPE_PHOTO, $this->uid);

			$location->address = $address;
			$location->latitude = $locationCoordinates->latitude;
			$location->longitude = $locationCoordinates->longitude;
			$location->user_id = $this->uid;
			$location->type = SOCIAL_TYPE_PHOTO;
			$location->uid = $this->id;

			$state = $location->store();
		}

		// Store custom meta data for the photo
		$model->storeCustomMeta($this, $exif);
	}

	/**
	 * Binds location for an photo
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bindLocation($address, $latitude, $longitude)
	{
		if ($address && $latitude && $longitude) {
			$location = ES::table('Location');
			$location->load(array('uid' => $this->id, 'type' => SOCIAL_TYPE_PHOTO));

			$location->uid = $this->id;
			$location->type = SOCIAL_TYPE_PHOTO;
			$location->user_id = ES::user()->id;
			$location->address = $address;
			$location->longitude = $longitude;
			$location->latitude = $latitude;

			return $location->store();
		}

		// Try to load for existing locations associated with this photo
		$location = ES::table('Location');
		$exists = $location->load(array('uid' => $this->id, 'type' => SOCIAL_TYPE_PHOTO));

		if ($exists) {
			$location->delete();
		}
	}

	/**
	 * Processes rules before storing an image
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function beforeStore($file, SocialImage $image)
	{
		// Test if exif library exists on the server
		$exif = ES::get('Exif');

		// Detect the photo caption and title if exif is available.
		if ($exif->isAvailable() && $image->hasExifSupport()) {

			// Load the image
			$exif->load($file['tmp_name']);

			// Get the title from the exif library
			$title = $exif->getTitle();

			// Get the caption from the exif library
			$caption = $exif->getCaption();

			// Get the creation date from the exif library
			$createdAlias = $exif->getCreationDate();

			if ($createdAlias) {
				$this->assigned_date = $createdAlias;
			}

			// Clean up the title and the caption
			$title = ES::string()->escape(strip_tags($title));
			$caption = ES::string()->escape(strip_tags($caption));

			if ($title) {
				$this->title = $title;
			}

			if ($caption) {
				$this->caption = $caption;
			}
		}
	}

	/**
	 * Processes rules after storing an image
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function afterStore($file, SocialImage $image)
	{
		$config = ES::config();

		// Load up exif library
		$exif = ES::get('Exif');

		// Push all the ordering of the photo down
		$model = ES::model('Photos');
		$model->pushPhotosOrdering($this->album_id, $this->id);

		// Detect location for the photo
		if ($exif->isAvailable() && $image->hasExifSupport()) {

			// Load the file
			$exif->load($file['tmp_name']);

			// Get the location
			$locationCoordinates = $exif->getLocation();

			// Once we have the coordinates, we need to reverse geocode it to get the address.
			if ($locationCoordinates) {

				$my = ES::user();
				$geocode = ES::get('GeoCode');
				$address = $geocode->reverse($locationCoordinates->latitude, $locationCoordinates->longitude);

				$location = ES::table('Location');
				$location->loadByType($this->id, SOCIAL_TYPE_PHOTO, $my->id);

				$location->address = $address;
				$location->latitude = $locationCoordinates->latitude;
				$location->longitude = $locationCoordinates->longitude;
				$location->user_id = $my->id;
				$location->type = SOCIAL_TYPE_PHOTO;
				$location->uid = $this->id;

				$state = $location->store();
			}

			// Store custom meta data for the photo
			$model->storeCustomMeta($this, $exif);
		}

		// Lets check if the photo storage is set to amazon and required immediate sync
		$storageType = $config->get('storage.photos', 'joomla');
		$amazonPhotoUpload = $config->get('storage.amazon.upload.photo');

		if ($storageType == 'amazon' && $amazonPhotoUpload) {
			$exception = null;
			$states = $this->syncAmazon($exception);

			if ($states !== -1) {

				$success = !in_array(false, $states);

				// If there are no errors, we want to update the storage for the photo
				if ($success) {

					$this->storage = $storageType;
					$state = $this->store();

					// if photo storage successfully updated to amazon, we need to update the cached object in stream_item.
					// Find and update the object from stream_item.
					$stream = ES::table('StreamItem');
					$options = array('context_type' => SOCIAL_TYPE_PHOTO, 'context_id' => $this->id);
					$exists = $stream->load($options);

					if ($exists) {
						$stream->params = json_encode($this);
						$stream->store();
					}
				}

				// Add this to the storage logs
				$log = ES::table('StorageLog');
				$log->object_id = $this->id;
				$log->object_type = 'photos';
				$log->target = $storageType;
				$log->state = $success;
				$log->created = ES::date()->toSql();

				if ($exception) {
					$log->message = $exception->message;
				}

				$log->store();
			}
		}

		ES::storage()->syncUsage($this->user_id);

		// Synchronize with our search index
		$indexer = ES::get('Indexer');
		$template = $indexer->getTemplate();
		$template->setContent($this->title, $this->caption);

		// Get the url of the photo
		$url = $this->getPermalink();
		$template->setSource($this->id, SOCIAL_INDEXER_TYPE_PHOTOS, $this->uid, $url);
		$template->setThumbnail($this->getSource('thumbnail'));

		$indexer->index($template);
	}

	/**
	 * Processes rules after storing an image
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function syncAmazon(&$exception)
	{
		$config = ES::config();

		$storageType = $config->get('storage.photos', 'joomla');

		if ($storageType == 'joomla') {
			return -1;
		}

		$storage = ES::storage($storageType);

		$album = ES::table('Album');
		$album->load($this->album_id);

		// If the album no longer exists, skip this
		if (!$album->id) {
			return -1;
		}

		$allowed = array('thumbnail', 'large', 'square', 'featured', 'medium', 'original', 'stock');

		// Get the base path for the album
		$basePath = $this->getStoragePath($album);
		$states = array();

		// Now we need to get all the available files for this photo
		$model = ES::model('Photos');
		$metas = $model->getMeta($this->id, SOCIAL_PHOTOS_META_PATH);

		foreach ($metas as $meta) {

			// To prevent some faulty data, we need to manually reconstruct the path here.
			$absolutePath = $meta->value;

			$file = basename($absolutePath);

			$container = ES::cleanPath($config->get('photos.storage.container'));

			// Reconstruct the path to the source file
			$source = JPATH_ROOT . '/' . $container . '/' . $album->id . '/' . $this->id . '/' . $file;

			if (!JFile::exists($source)) {
				$states[] = false;

			} else {
				// To prevent faulty data, manually reconstruct the path here.
				$dest = $container . '/' . $album->id . '/' . $this->id . '/' . $file;
				$dest = ltrim($dest, '/');

				// We only want to upload certain files
				if (in_array($meta->property, $allowed)) {

					$fileName = $this->title . $this->getExtension($meta->property);
					$mimeType = $this->getMime($meta->property);

					$state = $storage->push($fileName, $source, $dest, $mimeType);

					if ($state instanceof SocialResponse) {
						$exception = $state;
						$state = false;
					} else {
						// Delete the source file if successfull and configured to do so.
						$deleteSource = $config->get('storage.amazon.delete');
						if ($state && $deleteSource) {
							JFile::delete($source);
						}
					}

					$states[] = $state;
				}

			}
		}

		return $states;
	}
}
