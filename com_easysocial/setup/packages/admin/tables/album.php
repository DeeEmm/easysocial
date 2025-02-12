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
ES::import('admin:/includes/stream/stream');
ES::import('admin:/includes/stream/dependencies');
ES::import('admin:/includes/indexer/indexer');

class SocialTableAlbum extends SocialTable implements ISocialIndexerTable, ISocialStreamItemTable
{
	public $id = '';
	public $cover_id = null;
	public $uid = null;
	public $type = '';
	public $user_id = null;
	public $title = '';
	public $caption = '';
	public $created = null;
	public $assigned_date = '';
	public $ordering = null;
	public $params = null;
	public $hits = null;
	public $notified = null;
	public $finalized = null;
	public $access = null;
	public $custom_access = null;
	public $field_access = null;
	public $chk_access = null;
	public $core = null;
	public $_uuid = null;
	static $_albums = array();
	private $cover = null;
	public $password = null;

	public function __construct($db)
	{
		// Create a unique id only for each table instance
		// This is to help controller implement the right element.
		$this->_uuid = uniqid();

		parent::__construct('#__social_albums', 'id', $db);
	}

	/**
	 * Determines if user is allowed to upload into this album
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function canUpload($authorId = null)
	{
		$user = ES::user($authorId);

		// check if the album belong to the user or not. #2759
		if ($this->type == SOCIAL_TYPE_USER && !$this->isMine($user->id) && !$user->isSiteAdmin()) {
			return false;
		}

		if ($this->type != SOCIAL_TYPE_USER) {
			$lib = ES::albums($this->uid, $this->type, $this->id);
			$cluster = $lib->getCluster();

			if (!$user->isSiteAdmin() && !$cluster->isMember()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Override bind method
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function bind($data, $ignore = array())
	{
		$state = parent::bind($data, $ignore);

		if (is_null($this->core)) {
			// standard album.
			$this->core = 0;
		}

		return $state;
	}

	/**
	 * Overrides parent's load implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($keys = null, $reset = true)
	{
		$state = false;
		$loaded = false;

		if (is_array($keys)) {
			$state = parent::load($keys, $reset);
		} else {
			if (!isset(self::$_albums[$keys])) {
				$state 					= parent::load($keys);
				self::$_albums[$keys]	= $this;
			} else {

				$value 	= self::$_albums[$keys];

				if (is_bool($value)) {
					$state 	= false;
				} else {
					$state = parent::bind($value);
				}
				$loaded = true;
			}
		}

		if ($state && !$loaded) {
			// Converts params into an object first
			if (empty($this->params)) {
				$this->params = new stdClass();
			} else {
				$this->params = ES::json()->decode($this->params);
			}
		}

		return $state;
	}

	/**
	 * Binds location for an album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bindLocation($address, $latitude, $longitude)
	{
		if ($address && $latitude && $longitude) {
			$location = ES::table('Location');
			$location->load(array('uid' => $this->id, 'type' => SOCIAL_TYPE_ALBUM));

			$location->uid = $this->id;
			$location->type = SOCIAL_TYPE_ALBUM;
			$location->user_id = ES::user()->id;
			$location->address = $address;
			$location->longitude = $longitude;
			$location->latitude = $latitude;

			return $location->store();
		}

		// Try to load for existing locations associated with this album
		$location = ES::table('Location');
		$exists = $location->load(array('uid' => $this->id, 'type' => SOCIAL_TYPE_ALBUM));

		if ($exists) {
			$location->delete();
		}
	}

	/**
	 *  load albums by batch
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadByBatch($ids)
	{
		$db = ES::db();
		$sql = $db->sql();

		$albumIds = array();

		foreach ($ids as $pid) {
			if (! isset(self::$_albums[$pid])) {
				$albumIds[] = $pid;
			}
		}

		if ($albumIds) {
			foreach ($albumIds as $pid) {
				self::$_albums[$pid] = false;
			}

			$query = '';
			$idSegments = array_chunk($albumIds, 5);
			//$idSegments = array_chunk($albumIds, count($albumIds));


			for ($i = 0; $i < count($idSegments); $i++) {
				$segment = $idSegments[$i];
				$ids = implode(',', $segment);

				$query .= 'select * from `#__social_albums` where `id` IN (' . $ids . ')';

				if (($i + 1)  < count($idSegments)) {
					$query .= ' UNION ';
				}

			}

			$sql->raw($query);
			$db->setQuery($sql);
			$results = $db->loadObjectList();

			if ($results) {
				foreach ($results as $row) {
					$tbl = ES::table('Album');
					$tbl->bind($row);

					if (empty($tbl->params)) {
						$tbl->params = new stdClass();
					} else {
						$tbl->params = ES::json()->decode($tbl->params);
					}

					self::$_albums[$row->id] = $tbl;
				}
			}
		}

	}


	/**
	 * Method to check if album's title already exists or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function titleExists($title, $userId, $ignoreId = 0)
	{
		$model = ES::model('Albums');
		return $model->isTitleExists($title, $userId, $ignoreId);
	}

	/**
	 * Overrides parent's store implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		// Detect if this is a new album
		$isNew 	= $this->id ? false : true;

		// always set the chk_access to 1
		// so that new photos created after 3.1
		// will not need to re-run the
		// privacy access migration.
		$this->chk_access = 1;

		// make sure title has no leading / ending space
		$this->title = ESJString::trim($this->title);

		if ($this->title) {
			// replace two or more spacing in between words into one spacing only.
			$this->title = preg_replace('#\s{2,}#',' ',$this->title);
		}

		// Set a default title if the title is not set.
		if (empty($this->title)) {
			$this->title = JText::_('COM_EASYSOCIAL_UNTITLED_ALBUM');
		}

		// we only test user albums
		// since title in album also used as permalink alias,
		// we need to unsure the uniqueness of the title from a user.
		if ($this->core == 0) {
			$check = true;
			$i = 0;
			do {
				if ($this->titleExists($this->title, $this->user_id, $this->id)) {
					$this->title = $this->title . '-' . ++$i;
					$check = true;
				} else {
					$check = false;
				}
			} while ($check);
		}

		// Convert params back into json string
		if (!is_string($this->params)) {
			$this->params = ES::json()->encode($this->params);
		}

		// Set the date to now if created is empty
		if (empty($this->created)) {
			$this->created = ES::date()->toSql();
		}

		if (!$this->core && (!$this->assigned_date || $this->assigned_date == '0000-00-00 00:00:00')) {
			// if this is a standard album and the assigned_date is empty, lets assign with created date.
			// #5006
			$this->assigned_date = $this->created;
		}

		// Update ordering column.
		$this->ordering = $this->getNextOrder(array('uid' => $this->uid , 'type' => $this->type));

		// Invoke paren't store method.
		$state 	= parent::store($updateNulls);

		if ($isNew && !$this->core && $this->type !== SOCIAL_TYPE_MARKETPLACE) {
			// @points: photos.albums.create
			// Add points for the author for creating an album
			$points = ES::points();
			$points->assign('photos.albums.create' , 'com_easysocial' , $this->user_id);
		}

		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderAfterSave', array('easysocial.albums', &$this, $isNew));

		return $state;
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
		$old = ES::table('Album');
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
	 * Notify user when album is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function notify()
	{
		$allowed = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT);

		// We only process notification for group item
		if (!in_array($this->type, $allowed)) {
			return false;
		}

		// Skip if notification has been sent out before
		if ($this->notified == SOCIAL_ALBUM_NOTIFIED) {
			return false;
		}

		// Load cluster
		$cluster = ES::cluster($this->type, $this->uid);

		if (!$cluster->id) {
			return false;
		}

		// Get the rule
		$rule = 'album.create';

		// Assign the data
		$mailData = array();
		$mailData['userId'] = $this->user_id;
		$mailData['title'] = $this->title;
		$mailData['description'] = $this->caption;
		$mailData['permalink'] = $this->getPermalink();
		$mailData['id'] = $this->id;

		// send
		$cluster->notifyMembers($rule, $mailData);

		// Update notified column
		$this->notified = SOCIAL_ALBUM_NOTIFIED;
		$this->store();

		return true;
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function syncIndex()
	{
		$indexer = ES::get('Indexer');

		$tmpl 	= $indexer->getTemplate();

		$creator 	= ES::user($this->uid);
		$userAlias 	= $creator->getAlias();

		// $url 	= FRoute::albums(array('id' => $this->getAlias() , 'userid' => $userAlias , 'layout' => 'item'));

		$url = $this->getPermalink();
		$url = '/' . ltrim($url , '/');
		$url = str_replace('/administrator/', '/', $url);

		$tmpl->setSource($this->id , SOCIAL_INDEXER_TYPE_ALBUMS , $this->uid , $url);

		$content = ($this->caption) ? $this->caption : $this->title;
		$tmpl->setContent($this->title, $content);

		if ($this->cover_id) {
			$photo = ES::table('Photo');
			$photo->load($this->cover_id);

			$thumbnail = $photo->getSource('thumbnail');
			if ($thumbnail) {
				$tmpl->setThumbnail($thumbnail);
			}
		}

		$date = ES::date();
		$tmpl->setLastUpdate($date->toMySQL());

		$state = $indexer->index($tmpl);
		return $state;
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteIndex()
	{
		$indexer = ES::get('Indexer');
		$indexer->delete($this->id, SOCIAL_INDEXER_TYPE_ALBUMS);
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function uuid()
	{
		return $this->_uuid;
	}

	/**
	 * Retrieves the likes count for this album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLikesCount()
	{
		static $likes = array();

		if (!$this->id) {
			return 0;
		}

		if (!isset($likes[$this->id])) {
			$likes[$this->id] = ES::get('Likes')->getCount($this->id, SOCIAL_TYPE_ALBUM, 'create', $this->type);
		}

		return $likes[$this->id];
	}

	/**
	 * Retrieves the comments count for this album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCommentsCount()
	{
		static $comments = array();

		if (!$this->id) {
			return 0;
		}

		if (!isset($comments[$this->id])) {
			$comments[$this->id] = ES::comments($this->id, SOCIAL_TYPE_ALBUM, 'create', $this->type)->getCount();
		}

		return $comments[$this->id];
	}

	/**
	 * Get the total number of tags for this album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTagsCount()
	{
		$model 	= ES::model('Albums');

		$tags 	= $model->getTotalTags($this->id);

		return $tags;
	}

	/**
	 * Method to retrieve album title
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Get the total number of tags for this album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isFavourite($userId)
	{
		$model = ES::model('Albums');
		$exists = $model->isFavourite($this->id, $userId);

		return $exists;
	}

	/**
	 * Retrieves a list of tags from all albums
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTags($usersOnly = false, $max = 0)
	{
		$model = ES::model('Albums');
		$tags = $model->getTags($this->id , $usersOnly, $max);

		return $tags;
	}

	/**
	 * Retrieves the storage path for this album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getStoragePath($relative = false)
	{
		// Rename temporary folder to the destination.
		jimport('joomla.filesystem.folder');

		// Get destination folder path.
		$config = ES::config();
		$path = '';

		if (!$relative) {
			$path = JPATH_ROOT;
		}

		$path = $path . '/' . ES::cleanPath($config->get('photos.storage.container'));

		// Ensure that the storage folder exists.
		if (!$relative) {
			ES::makeFolder($path);
		}

		// Build the storage path now with the album id
		$path = $path . '/' . $this->id;

		// Ensure that the final storage path exists.
		if (!$relative) {
			ES::makeFolder($path);
		}

		return $path;
	}

	/**
	 * Gets the total number of photos for an album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPhotos()
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Albums');
			$total[$this->id] = $model->getTotalPhotos($this->id);
		}

		return $total[$this->id];
	}

	/**
	 * Gets the last photo for an album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLastPhoto()
	{
		$model = ES::model('Albums');
		$photo = $model->getLastPhoto($this->id);

		return $photo;
	}

	/**
	 * Determines if the album is owned by the provided user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isMine($id = null)
	{
		$user = ES::user($id);
		$isOwner = $user->id == $this->uid;

		return $isOwner;
	}


	/**
	 * Determines if an album has a cover.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasCover($checkPrivacy = false)
	{
		static $_loaded = array();

		if (! $checkPrivacy) {
			if ($this->cover_id) {
				return true;
			}

			return false;
		}

		if ($this->cover_id) {

			if (isset($_loaded[$this->cover_id])) {
				return $_loaded[$this->cover_id];
			}

			$photo = ES::table('photo');
			$photo->load($this->cover_id);

			$_loaded[$this->cover_id] = $photo->viewable();

			return $_loaded[$this->cover_id];
		}

		return false;
	}

	/**
	 * Build's the album's alias
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlias()
	{
		$title 	= $this->title;

		if ($this->core) {
			$title 	= JText::_($this->title);
		}

		$aliasTitle = JFilterOutput::stringURLSafe($title);
		if (!$aliasTitle) {
			$aliasTitle = JFilterOutput::stringURLUnicodeSlug($title);
		}

		$alias 	= $this->id . ':' . $aliasTitle;
		return $alias;
	}

	/**
	 * Retrieves the cover photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCover($size = 'thumbnail')
	{
		static $covers = array();
		static $photos = array();

		$index = $this->id . '-' . $size;

		if (!isset($covers[$index])) {

			// If the album does not have a cover, load an empty photo object
			if (!$this->hasCover()) {

				// If the album does not have a cover, use the default album avatar
				$avatar = JURI::root() . 'media/com_easysocial/defaults/avatars/albums/large.png';

				// @TODO: Display according to it's own sizes
				return $avatar;
			}

			if (!isset($photos[$this->cover_id])) {

				$photo = ES::table('Photo');
				$photo->load($this->cover_id);

				// There are instance where the photo is unpublished.
				if (!$photo->state) {

					// Get new cover
					if ($this->hasPhotos()) {
						$options = array('limit' => 1);

						// cover albums
						if ($this->core == 2) {
							$options = array('nocover' => false);
						}

						$result = $this->getPhotos($options);
						$photo = $result['photos'][0];
					}
				}

				$photos[$this->cover_id] = $photo;
			}

			$covers[$index] = $photos[$this->cover_id]->getSource($size);
		}

		return $covers[$index];
	}

	/**
	 * Retrieves the cover photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCoverObject()
	{
		$covers = array();

		if (!isset($covers[$this->id])) {
			// If the album does not have a cover, load an empty photo object
			if (!$this->hasCover()) {
				// If the album does not have a cover, use the default album avatar
				$photo = ES::table('Photo');
				return $photo;
			}

			$photo 	= ES::table('Photo');
			$photo->load($this->cover_id);

			$covers[$this->id] = $photo;
		}

		return $covers[$this->id];
	}

	/**
	 * Retrieves the cover photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCoverUrl($source = 'thumbnail')
	{
		if (!is_null($this->cover)) {
			return $this->cover;
		}

		if (!$this->cover && $this->hasCover()) {
			$photo = ES::table('Photo');
			$photo->load($this->cover_id);

			$this->cover = $photo->getSource($source);
		} else {
			// @TODO: Make this configurable
			$this->cover = SOCIAL_DEFAULTS_URI . '/albums/cover.png';
		}

		return $this->cover;
	}

	/**
	 * Override parent's delete method
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		/*
		 * we need to delete the photos 1st and then only do ao parent::delete to avoid album getting onfindersave issue.
		 */

		// Delete the photos from the site first.
		$photosModel = ES::model('photos');
		$photosModel->deleteAlbumPhotos($this->id);

		// @points: photos.albums.remove
		// Deduct points for the author for deleting an album
		if ($this->type !== SOCIAL_TYPE_MARKETPLACE) {
			$points = ES::points();
			$points->assign('photos.albums.remove' , 'com_easysocial' , $this->user_id);
		}

		// Now, try to delete the folder that houses this photo.
		$config = ES::config();
		$storage = JPATH_ROOT . '/' . ES::cleanPath($config->get('photos.storage.container'));
		$storage = $storage . '/' . $this->id;

		jimport('joomla.filesystem.folder');

		$exists = JFolder::exists($storage);

		// Test if the folder really exists first before deleting it.
		if ($exists) {
			$state 	= JFolder::delete($storage);
		}

		// Delete the record from the database first.
		$state = parent::delete();

		// Delete likes related to the album
		$likes = ES::get('Likes');
		$likes->delete($this->id , SOCIAL_TYPE_ALBUM, 'create');

		// Album types
		$albumType = $this->type ? $this->type : SOCIAL_APPS_GROUP_USER;

		// Delete comments related to the album
		$comments = ES::comments($this->id, SOCIAL_TYPE_ALBUM, 'create', $albumType);
		$comments->delete();

		// Try to delete any notification for this album that was generated in cluster #5171
		if ($this->type !== SOCIAL_TYPE_USER) {
			$albumModel = ES::model('albums');
			$albumModel->deleteClusterNotifications($this->id, $this->type);
		}

		// Delete from smart search index
		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderAfterDelete', array('easysocial.albums' , $this));

		return $state;
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addAlbumStream($verb)
	{
		// for album, we only want to create stream when is a new album creation and not during update.

		if ($verb == 'create') {
			$stream = ES::stream();

			$template 	= $stream->getTemplate();
			$template->setActor($this->uid , $this->type);
			$template->setContext($this->id , SOCIAL_STREAM_CONTEXT_ALBUMS);
			$template->setVerb($verb);
			$template->setAccess('albums.view');
			$template->setDate($this->created);

			$stream->add($template);
		}
	}

	/**
	 * Generates a new stream method.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addStream($verb)
	{
		// do nothing. Please do not remove this function!
	}

	/**
	 * Deletes a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeStream()
	{
		$stream = ES::stream();
		return $stream->delete($this->id , SOCIAL_STREAM_CONTEXT_ALBUMS);
	}

	/**
	 * Determine if the album is a cover album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isCover()
	{
		return $this->core == SOCIAL_ALBUM_PROFILE_COVERS;
	}

	/**
	 * Determine if the album is an avatar album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isAvatar()
	{
		return $this->core == SOCIAL_ALBUM_PROFILE_PHOTOS;
	}

	/**
	 * Determines if this is a core album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isCore()
	{
		// If this is a system album like cover photos, profile pictures, they will not be able to delete them.
		$disallowed = array(SOCIAL_ALBUM_STORY_ALBUM , SOCIAL_ALBUM_PROFILE_COVERS , SOCIAL_ALBUM_PROFILE_PHOTOS);

		if (in_array($this->core, $disallowed)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if this is a story album
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isStory()
	{
		return $this->core == SOCIAL_ALBUM_STORY_ALBUM;
	}

	/**
	 * Tests if the album is editable by the provided user id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function editable($debug = false)
	{
		// Previously there is a isCore check here.
		// Restrictions limited to core albums should be
		// checked with $album->isCore(), not $album->editable().

		$lib = ES::albums($this->uid, $this->type, $this);
		return $lib->editable();
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function viewable($id = null)
	{
		// If id not given, use current logged in user.
		if (!$id) {
			$my = ES::user();
			$id = $my->id;
		}

		// Get the privacy object
		$privacy = ES::privacy($id);
		return $privacy->validate('albums.view', $this->id, 'albums', $this->uid);

		return true;
	}

	/**
	 * Determines if the album needs to display the date
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasDate()
	{
		if ($this->core) {
			return false;
		}

		return true;
	}

	/**
	 * Tests if the album is delete able by the provided user id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteable($id = null , $type = SOCIAL_TYPE_USER)
	{
		if ($this->isCore()) {
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

	/**
	 * Determines if the user is allowed to move the photo inside the album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isPhotoMoveable()
	{
		// If this is a system album like cover photos, profile pictures, they will not be able to move photos within this album.
		$disallowed = array(SOCIAL_ALBUM_STORY_ALBUM, SOCIAL_ALBUM_PROFILE_COVERS, SOCIAL_ALBUM_PROFILE_PHOTOS);

		if (in_array($this->core, $disallowed)) {
			return false;
		}

		// Allow site admins to move anything
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Allow owners to move the photo
		if ($this->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if user is allowed to delete the photo inside the album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isPhotoDeleteable()
	{
		$my = ES::user();

		// Admins are allowed to delete
		if ($my->isSiteAdmin()) {
			return true;
		}

		// If the owner of the album is the user.
		if ($this->album->user_id == $my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the assigned date of the album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isUserAlbum()
	{
		return $this->type == SOCIAL_TYPE_USER;
	}

	/**
	 * Assign points to a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assignPoints($rule, $actorId)
	{
		$points = ES::points();
		$points->assign($rule, 'com_easysocial', $actorId);
	}

	/**
	 * Retrieves a list of photos from the album
	 *
	 * @since	1.0.0
	 * @access	public
	 */
	public function getPhotos($options = array())
	{
		if (!$this->id) {
			return array('photos' => array(), 'nextStart' => -1);
		}

		$lib = ES::albums($this->uid, $this->type, $this->id);

		return $lib->getPhotos($this->id, $options);
	}

	public function hasPhotos()
	{
		return ($this->getTotalPhotos() > 0);
	}

	/**
	 * Description
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function hasPassword()
	{
		return $this->password;
	}

	/**
	 * Retrieves the permalink for the album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $external = false, $layout = 'item', $sef = true)
	{
		// Standard url options
		$options = array('id' => $this->getAlias() , 'layout' => $layout , 'uid' => $this->uid , 'type' => $this->type, 'sef' => $sef);

		switch ($this->type) {
			case SOCIAL_TYPE_GROUP:
				$options['uid'] = ES::group($this->uid)->getAlias();
				break;
			case SOCIAL_TYPE_PAGE:
				$options['uid'] = ES::page($this->uid)->getAlias();
				break;
			case SOCIAL_TYPE_EVENT:
				$options['uid'] = ES::event($this->uid)->getAlias();
				break;
			case SOCIAL_TYPE_USER:
			default:
				$options['uid'] = ES::user($this->uid)->getAlias();
				break;
		}


		if ($external) {
			$options['external'] = true;
		}

		return FRoute::albums($options , $xhtml);
	}

	/**
	 * Retrieves the edit permalink for the album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getEditPermalink($xhtml = true , $external = false , $layout = 'form')
	{
		$url= $this->getPermalink($xhtml , $external , $layout);

		return $url;
	}

	/**
	 * Retrieves the creator of the album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCreator()
	{
		// Special case for Page, the creator
		// will always be the Page itself
		if ($this->type == SOCIAL_TYPE_PAGE) {
			return ES::page($this->uid);
		}

		return ES::user($this->user_id);
	}

	/**
	 * Retrieves the location of the album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLocation()
	{
		static $locations 	= array();

		if (!isset($locations[$this->id])) {
			$location = ES::table('Location');
			$state = $location->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_ALBUM));

			if (!$state) {
				$locations[$this->id]	= $state;
			} else {
				$locations[$this->id]	= $location;
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
			$lib = ES::location($this->id, SOCIAL_TYPE_ALBUM);
			$locations[$this->id] = $lib;
		}

		return $locations[$this->id];
	}

	/**
	 * Retrieves the creation date of the album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCreationDate()
	{
		return $this->created;
	}

	/**
	 * Determines if this album has an assigned date.
	 *
	 * @since	1.2.8
	 * @access	public
	 */
	public function hasAssignedDate()
	{
		if ($this->assigned_date == '0000-00-00 00:00:00') {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the assigned date of the album
	 *
	 * @since	1.0
	 * @access	public
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
	 * Method to export the album data
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function export($flags = array())
	{
		$properties = get_object_vars($this);

		$album = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$album[$key] = $value;
			}
		}

		$album['permalink'] = $this->getPermalink(false);

		if (in_array('cover', $flags)) {
			if ($this->hasCover()) {
				$cover = ES::table('photo');
				$cover->load($this->cover_id);

				$album['cover'] = $cover->export();
			} else {
				$album['cover'] = array();
			}
		}

		if (in_array('photos', $flags)) {
			$album['photos'] = array();

			$model = ES::model('Photos');

			$result = $model->getPhotos(array('album_id' => $this->id , 'pagination' => false));
			$album['photos'] = array();

			if ($result) {
				foreach ($result as $photo) {
					$album['photos'][] = $photo->export();
				}
			}

		}

		return $album;
	}

	/**
	 * Alias for album export
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $flags = array())
	{
		$config = ES::config();
		$isPrivacyRequired = $config->get('privacy.enabled', true);

		$properties = get_object_vars($this);
		$album = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$album[$key] = $value;
			}
		}

		$extended = ES::normalize($flags, 'extended', true);

		$album['cover'] = array();

		if ($this->hasCover()) {
			$cover = ES::table('photo');
			$cover->load($this->cover_id);

			$doExport = ($isPrivacyRequired && !$cover->viewable()) ? false : true;
			if ($doExport) {
				$album['cover'] = $cover->toExportData($viewer, array('extended' => false));
			} else {
				$album['cover_id'] = 0;
			}
		}


		if (in_array('photos', $flags)) {
			$album['photos'] = array();

			$model = ES::model('Photos');

			$result = $model->getPhotos(array('album_id' => $this->id , 'pagination' => false));
			$album['photos'] = array();

			if ($result) {
				foreach ($result as $photo) {

					$doExport = ($isPrivacyRequired && !$photo->viewable()) ? false : true;
					if ($doExport) {
						$album['photos'][] = $photo->toExportData($viewer, array('extended' => true));
					}
				}
			}
		}

		$album['title'] = JText::_($album['title']);
		$album['caption'] = JText::_($album['caption']);
		$album['isFavourited'] = $this->isFavourite($viewer->id) ? "1" : "0";
		$album['photos'] = array('total' => (string) $this->getTotalPhotos());

		if ($extended) {
			$album['author'] = $this->getCreator()->toExportData($viewer);
			$album['comments']['total'] = (string) $this->getCommentsCount();
			$album['likes']['total'] = (string) $this->getLikesCount();
		}

		// Get externalpermalink
		$album['externalPermalink'] = $this->getPermalink(true, true);

		return $album;
	}

	/**
	 * Retrieve page title for the album
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getPageTitle($postfix = true)
	{
		$lib = ES::albums($this->uid, $this->type, $this);

		return $lib->getPageTitle('item', $postfix);
	}
}
