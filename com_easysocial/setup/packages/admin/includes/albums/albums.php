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

class SocialAlbums extends EasySocial
{
	/**
	 * Static variable for caching.
	 * @var	SocialAlbums
	 */
	private static $instance = null;

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
	 * The adapter for albums
	 * @var	string
	 */
	public $adapter = null;

	/**
	 * The table mapping for the album.
	 * @var	SocialTableAlbum
	 */
	public $data = null;

	public function __construct($uid = null, $type = null, $key = null)
	{
		parent::__construct();

		$this->data = ES::table('Album');

		if ($key instanceof SocialTableAlbum) {
			$this->data = $key;
		} else {
			$this->data->load($key);
		}

		$this->uid = $uid ? $uid : $this->data->uid;
		$this->type = $type ? $type : $this->data->type;

		// Get the adapter
		$this->adapter = $this->getAdapter($this->type);
	}

	/**
	 * Method to instantiate a new instance of this library.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory($uid , $type , $key = null)
	{
		return new self($uid , $type , $key);
	}

	/**
	 * Maps back the call method functions to the adapter.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$refArray = [];

		if ($args) {
			foreach ($args as &$arg) {
				$refArray[] =& $arg;
			}
		}

		$callback = [$this->adapter, $method];

		if (!$this->adapter || !is_object($this->adapter)) {
			return false;
		}

		return call_user_func_array($callback, $refArray);
	}

	/**
	 * This will group up albums by date.
	 *
	 * @since	1.0
	 * @deprecated 1.2
	 */
	public function groupAlbums($rows)
	{
		$this->groupAlbumsByDate($result);
	}

	/**
	 * This will group up albums by date.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function groupAlbumsByDate($rows)
	{
		if(!$rows)
		{
			return $rows;
		}

		$albums 	= array();

		foreach($rows as $row)
		{
			$datestr = ($row->assigned_date && $row->assigned_date !== '0000-00-00 00:00:00') ? $row->assigned_date : $row->created;

			$date = ES::date($datestr);
			$format = JText::_('COM_EASYSOCIAL_ALBUMS_GROUP_DATE_FORMAT');
			$index = $date->format($format);

			if(!isset($albums[ $index ]))
			{
				$albums[ $index ]	= array();
			}

			$albums[ $index ][]	= $row;
		}

		return $albums;
	}

	/**
	 * Retrieves photos
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function getPhotos($albumId = null, $options = array())
	{
		if (is_null($albumId)) {
			$albumId = $this->data->id;
		}

		if (!$albumId) {
			return array();
		}

		$start = isset($options['start']) ? $options['start'] : 0;
		$limit = isset($options['limit']) && $options['limit'] != 0 ? $options['limit'] : $this->config->get('photos.pagination.photo');

		$model = ES::model('photos');

		$counter = 0;

		$nextStart = $start;

		$photos = array();

		$isPrivacyRequired = isset($options['privacy']) ? $options['privacy'] : false;

		$nocover = isset($options['nocover']) ? $options['nocover'] : false;

		$sort = isset($options['sort']) ? $options['sort'] : $this->config->get('photos.layout.ordering');

		$inclusion = isset($options['inclusion']) ? $options['inclusion'] : false;

		$isUnlocked = isset($options['isUnlocked']) ? $options['isUnlocked'] : false;

		// lets cache the photos meta here.
		$photosIds = array();

		while ($counter < $limit) {

			$tmpLimit = $isPrivacyRequired ? $limit + 1 : $limit;
			$newPhotos = $model->getPhotos(array('album_id' => $albumId, 'start' => $nextStart, 'limit' => $tmpLimit , 'state' => SOCIAL_STATE_PUBLISHED, 'privacy' => $isPrivacyRequired, 'nocover' => $nocover, 'sort' => $sort, 'inclusion' => $inclusion, 'isUnlocked' => $isUnlocked));

			$photosCount = count($newPhotos);

			// If photosCount is 0, means there are no more photos left to load
			if ($photosCount === 0) {
				$nextStart = -1;
				break;
			}

			foreach($newPhotos as $photo) {
				$photosIds[] = $photo->id;
			}

			// if privacy invoke, then we need to pop the last element
			if ($isPrivacyRequired && $photosCount > $limit) {
				array_pop($newPhotos);
			}

			foreach ($newPhotos as $photo) {
				if ($isPrivacyRequired) {
					//this mean in the sql, we already injected the privacy checking. so no lib checking required here.
					$photos[] = $photo;
					$counter++;
				} else {

					if ($photo->viewable()) {
						// Add this photo into the photos list if privacy is true
						$photos[] = $photo;

						// Add the counter if privacy is true
						$counter++;
					}
				}

				// Add the nextStart count regardless of the privacy
				$nextStart++;

				// If before the loop ends but we already reach the limit that we need, then break here and we will have the correct nextStart value
				if ($counter >= $limit) {
					break;
				}
			}

			if ($isPrivacyRequired && $photosCount <= $limit) {
				$nextStart = -1;
				break;
			}
		}

		if ($photosIds) {
			// lets cache photos meta here.
			ES::cache()->cachePhotos($photosIds);
		}

		return array('photos' => $photos, 'nextStart' => $nextStart);
	}

	private $renderItemOptions = array(
		'viewer'       => null,
		'layout'       => 'item',
		'view'		   => 'mine',
		'limit'        => 'auto',
		'canReorder'   => false,
		'canUpload'    => false,
		'showToolbar'  => true,
		'showInfo'     => true,
		'showStats'    => false,
		'showPhotos'   => true,
		'showResponse' => true,
		'showTags'     => true,
		'showForm'     => true,
		'showLoadMore' => true,
		'showViewButton' => false,
		'photoItem'    => array(
			'size' => 'large',
			'viewer'       => null,
			'layout'       => 'item',
			'showToolbar'  => true,
			'showInfo'     => true,
			'showStats'    => true,
			'showResponse' => false,
			'showTags'     => false,
			'showForm'     => true,
			'openInPopup'  => true
		)
	);

	/**
	 * Generates the theme output for albums view
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderItem($options = array())
	{
		// Default values
		$album = $this->data;
		$overridePhotoItems = false;

		// Determine if album is passed in options
		if (isset($options['album'])) {
			$album = $options['album'];
		}

		if (isset($options['overridePhotoItems'])) {
			$overridePhotoItems = $options['overridePhotoItems'];
		}

		// Set the default settings for opening photos in a popup
		$this->renderItemOptions['photoItem']['openInPopup'] = true;

		// Built preset options
		$presetOptions = array('canUpload' => $this->canUpload());

		// Normalize render options
		$options = array_merge($this->renderItemOptions, $presetOptions, $options);

		if (!empty($options['photoItem'])) {
			$options['photoItem'] = array_merge($this->renderItemOptions['photoItem'], $options['photoItem']);
		}

		// Inherit photo item's viewer from album if it is not given
		if (empty($options['photoItem']['viewer'])) {
			$options['photoItem']['viewer'] = $options['viewer'];
		}

		// Set the layout for the photo
		$options['photoItem']['layout']	= $options['layout'];

		// Photos cannot be uploaded to core albums
		if ($album->core) {
			$options['canUpload'] = false;
		}

		// Get album privacy
		$privacy = ES::privacy();

		// Get album creator
		$creator = ES::user($album->user_id);

		// Get album viewer
		$viewer = ES::user($options['viewer']);

		$viewAllMode = false;

		if (isset($options['view']) && $options['view'] == 'all') {
			$viewAllMode = true;
		}

		// Get the photo options
		$photoOptions = array();

		if ($options['limit'] !== 'auto') {
			$photoOptions['limit'] = $options['limit'];
		}

		//privacy
		if (isset($options['privacy'])) {
			$photoOptions['privacy'] = $options['privacy'];
		}

		// Album cover type
		if ($album->core == 2) {
			$photoOptions['nocover'] = false;
		}

		// Photos ordering
		if (isset($options['ordering'])) {
			$photoOptions['sort'] = $options['ordering'];
		}

		// Get album phtoos
		$photos = array();

		if ($overridePhotoItems) {
			$photos = $overridePhotoItems;
		} else {
			$photos = $album->getPhotos($photoOptions);
		}

		// // Add opengraph data for each photos
		if (!$viewAllMode && $photos['photos']) {

			if (isset($photos['photos'][0]) && $photos['photos'][0]) {
				$photo = $photos['photos'][0];

				$obj = new stdClass();
				$obj->image = $photo->getSource('large');

				if ($album->caption) {
					$obj->description = $album->caption;
				}

				ES::meta()->setMetaObj($obj);
			}
		}

		$likes = null;
		$repost = null;
		$comments = null;
		$tags = null;

		// check the album is it got cluster type e.g. event or group
		$albumGroupId = ($album->type != SOCIAL_APPS_GROUP_USER) ? $album->uid : '0';
		$albumGroupType = ($album->type != SOCIAL_APPS_GROUP_USER) ? $album->type : SOCIAL_APPS_GROUP_USER;

		// If this is not in view all albums, we should display the necessary actions
		if (!$viewAllMode) {

			$permalink = $album->getPermalink(true, false, 'item', false);

			$commentOptions = array('url' => $permalink);
			$likeOptions = array();

			// Get the cluster id for this album to generate the comment form and likes button
			$commentOptions['clusterId'] = $album->uid;
			$likeOptions['clusterId'] = $album->uid;

			$likes = ES::likes($album->id, SOCIAL_TYPE_ALBUM , 'create', $albumGroupType, null, $likeOptions);
			$repost = ES::repost($album->id, SOCIAL_TYPE_ALBUM, $albumGroupType, $albumGroupId, $albumGroupType);
			$comments = ES::comments($album->id, SOCIAL_TYPE_ALBUM , 'create', $albumGroupType, $commentOptions);

			// Get a list of tags from this album
			$tags = $album->getTags(true);
		}

		// Build the user alias
		$userAlias = $creator->getAlias();

		// Generate item layout
		$theme = ES::themes();

		// Determines if the current document is RTL
		$rtl = $this->doc->getDirection() == 'rtl' ? true : false;

		// Construct the upload url
		$uploadUrl = ESR::raw('index.php?option=com_easysocial&controller=photos&task=upload&format=json&tmpl=component&albumId=' . $album->id . '&layout=' . $options['layout'] . '&uid=' . $this->uid . '&type=' . $this->type . '&createStream=1'. '&' . ES::token() . '=1');

		// Get the location if there is any
		$album->location = $album->getLocation();

		// Privacy for albums
		$privacyUseHtml = ($album->id) ? false : true;
		$theme->set('privacy', $privacy);
		$theme->set('privacyUseHtml', $privacyUseHtml);

		$theme->set('uploadUrl', $uploadUrl);
		$theme->set('rtl', $rtl);
		$theme->set('lib', $this);
		$theme->set('options', $options);
		$theme->set('userAlias', $userAlias);
		$theme->set('album', $album);
		$theme->set('tags', $tags);
		$theme->set('creator', $creator);
		$theme->set('likes', $likes);
		$theme->set('repost', $repost);
		$theme->set('comments', $comments);
		$theme->set('photos', $photos['photos']);
		$theme->set('nextStart', $photos['nextStart']);
		$theme->set('photoOrdering', $this->config->get('photos.layout.ordering','desc'));

		// Retrieve the album from request
		$id = $this->input->get('id', 0, 'int');

		// Get the unique id and type
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'string');

		if ($type == SOCIAL_TYPE_USER && !$uid) {
			$uid = $this->my->id;
		}

		$lib = ES::albums($uid, $type, $id);

		$verifyPassword = $lib->verifyPassword();
		$hasPassword = $album->hasPassword();
		$isMine = $album->isMine();

		$protected = false;

		if (isset($options['protected']) && $options['protected']) {
			$protected = true;
		}

		if ((($hasPassword && !$verifyPassword) && !$isMine) || $protected) {
			return $theme->output('site/albums/layouts/protected');
		}

		return $theme->output('site/albums/layouts/default');
	}

	/**
	 * Get the cluster of this album
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getCluster()
	{
		static $cluster = null;

		$idx = $this->type . $this->uid;

		if (!isset($cluster[$idx])) {
			if ($this->type == SOCIAL_APPS_GROUP_USER) {
				$cluster[$idx] = false;
			} else {
				$cluster[$idx] = ES::cluster($this->type, $this->uid);
			}
		}

		return $cluster[$idx];
	}

	/**
	 * Get the cluster of this album
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function isClusterAlbum()
	{

		if (!$this->type) {
			return false;
		}

		if ($this->type == SOCIAL_APPS_GROUP_USER) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the album's adapter
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getAdapter($type)
	{
		$file 	= dirname(__FILE__) . '/adapters/' . strtolower($type) . '.php';

		jimport('joomla.filesystem.file');

		if (!JFile::exists($file)) {
			return false;
		}

		require_once($file);

		$className = 'SocialAlbumsAdapter' . ucfirst($type);
		$adapter = new $className($this);

		return $adapter;
	}

	/**
	 * Favourite the album
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function favourite($userId = null)
	{
		$user = ES::user($userId);
		$model = ES::model('Albums');

		$state = $model->addFavourite($this->data->id, $user->id);

		if ($state && $user->id != $this->data->user_id) {

			$emailOptions = array(
				'actor' => $user->getName(),
				'title' => 'COM_EASYSOCIAL_EMAILS_ALBUM_FAVOURITE_SUBJECT',
				'template' => 'site/albums/new.favourite',
				'permalink' => $this->data->getPermalink(true, true),
				'albumTitle' => $this->data->get('title'),
				'albumPermalink' => $this->data->getPermalink(false, true),
				'albumCover' => $this->data->getCover(),
				'actorAvatar' => $user->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $user->getPermalink(true, true)
			);

			$systemOptions = array(
				'context_type' => 'albums.user.favourite',
				'context_ids' => $this->data->id,
				'url' => $this->data->getPermalink(false, false, 'item', false),
				'actor_id' => $user->id,
				'uid' => $this->data->id,
				'aggregate' => true
			);

			ES::notify('albums.favourite', array($this->data->user_id), $emailOptions, $systemOptions);
		}

		return $state;
	}

	/**
	 * Unfavourite the album
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function unfavourite($userId = null)
	{
		$user = ES::user($userId);
		$model = ES::model('Albums');

		$state = $model->removeFavourite($this->data->id, $user->id);

		return $state;
	}

	/**
	 * Determine if the album can be favourite
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canFavourite()
	{
		static $canFavourite = array();

		if (!isset($canFavourite[$this->data->id])) {
			$canFavourite[$this->data->id] = false;

			$creator = $this->data->getCreator();

			if (!$this->isClusterAlbum() && ($this->isOwner() || ($this->data->isUserAlbum() && $creator->isFriends($this->my->id)))) {
				$canFavourite[$this->data->id] = true;
			}
		}

		return $canFavourite[$this->data->id];
	}

	/**
	 * Export Album data
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

		$includePhotos = isset($options['includePhotos']) ? $options['includePhotos'] : true;

		$albumOptions = array('cover');

		if ($includePhotos) {
			$albumOptions = array_merge($albumOptions, array('photos'));
		}

		$album = $this->data->toExportData($viewer, $albumOptions);
		$album['author'] = $this->data->getCreator()->toExportData($viewer);

		// Re-decorate the photos
		if ($includePhotos) {
			$photos = $album['photos'];
			$album['photos'] = array(
				'total' => (string) $this->data->getTotalPhotos(),
				'items' => $photos
			);
		}

		// check the album is it got cluster type e.g. event or group
		$albumGroupType = ($this->data->type != SOCIAL_APPS_GROUP_USER) ? $this->data->type : SOCIAL_APPS_GROUP_USER;

		$permalink = $this->data->getPermalink(true, false, 'item', false);

		// Construct the options
		$commentOptions = array('url' => $permalink, 'clusterId' => $this->data->uid);
		$likeOptions = array('clusterId' => $this->data->uid);

		$likes = ES::likes($this->data->id, SOCIAL_TYPE_ALBUM , 'create', $albumGroupType, null, $likeOptions);
		$comments = ES::comments($this->data->id, SOCIAL_TYPE_ALBUM , 'create', $albumGroupType, $commentOptions);

		// repost
		$albumClusterId = ($this->data->type != SOCIAL_APPS_GROUP_USER) ? $this->data->uid : '0';
		$repost = ES::repost($this->data->id, SOCIAL_TYPE_ALBUM, $albumGroupType, $albumClusterId, $albumGroupType);

		$album['comments'] = $comments;
		$album['likes'] = $likes;
		$album['repost'] = $repost;

		$album['isFavourited'] = $this->data->isFavourite($viewer->id) ? "1" : "0";

		// Construct permission access
		$permission = array(
			'isOwner' => $this->isOwner(),
			'canEdit' => $this->editable() && $this->isOwner(),
			'canUpload' => $this->canUpload() && !$this->data->core,
			'canFavourite' => $this->canFavourite(),
			'canDelete' => $this->deleteable(),
			'canInteract' => true // able to post comment or react to the album
		);

		$album['permission'] = $permission;

		$privacy = ES::privacy();

		// Get privacy
		$album['privacy'] = $privacy->form($this->data->id, SOCIAL_TYPE_ALBUM, $this->data->uid, 'albums.view', false, null, array(), array('iconOnly' => true));

		$album = (object) $album;

		$cache[$key] = $album;

		return $cache[$key];
	}
}
