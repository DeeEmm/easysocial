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

class SocialAvatar
{
	private $path = null;
	private $uid = null;
	private $type = null;
	private $image = null;
	private $maps = array(
						'large' => '_large',
						'square' => '_square',
						'medium' => '_medium',
						'small' => '_small'
					);

	static $large = [
						'width' => SOCIAL_AVATAR_LARGE_WIDTH,
						'height' => null,
						'mode' => 'proportionate'
					];

	static $square = array(
						'width' => SOCIAL_AVATAR_SQUARE_LARGE_WIDTH,
						'height' => SOCIAL_AVATAR_SQUARE_LARGE_HEIGHT,
						'mode' => 'resize'
					);

	static $medium = array(
						'width' => SOCIAL_AVATAR_MEDIUM_WIDTH,
						'height' => SOCIAL_AVATAR_MEDIUM_HEIGHT,
						'mode' => 'resize'
					);

	static $small = array(
						'width' => SOCIAL_AVATAR_SMALL_WIDTH,
						'height' => SOCIAL_AVATAR_SMALL_HEIGHT,
						'mode' => 'resize'
					);

	public function __construct(SocialImage &$image, $id = null, $type = null)
	{
		// Set the current image object.
		$this->image = $image;
		$this->uid = $id;
		$this->type = $type;

		// Get the target location
		$this->location	= $this->getPath();
	}

	/**
	 * Factory maker for this class.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory($image, $id = null, $type = null)
	{
		$avatar = new self($image, $id, $type);

		return $avatar;
	}

	/**
	 * Crops an image
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function crop($top = null, $left = null, $width = null, $height = null)
	{
		// Use the current image that was already loaded
		$image = $this->image;

		// Get the width and height of the photo
		$imageWidth = $image->getWidth();
		$imageHeight = $image->getHeight();

		// Ensure that all these 4 value exists.
		if ($top && $left && $width && $height) {
			$actualX = round($imageWidth * $left);
			$actualY = round($imageHeight * $top);
			$actualWidth = round($imageWidth * $width);
			$actualHeight = round($imageHeight * $height);

			// Now we'll need to crop the image
			$image->crop($actualX, $actualY, $actualWidth, $actualHeight);

		} else {
			// If caller didn't provide a crop ratio, we crop the avatar to square
			// Get the correct positions
			if ($imageWidth > $imageHeight) {
				$x = round(($imageWidth - $imageHeight) / 2);
				$y = 0;
				$image->crop($x, $y, $imageHeight, $imageHeight);
			} else {
				$x = 0;
				$y = round(($imageHeight - $imageWidth) / 2);
				$image->crop($x, $y, $imageWidth, $imageWidth);
			}
		}

		// We want to store the temporary image somewhere so that the image library could manipulate this file.
		$tmpImagePath = md5(ES::date()->toMySQL()) . $image->getExtension();
		$jConfig = ES::jconfig();

		// Save the temporary cropped image
		$tmpImagePath = $jConfig->getValue('tmp_path') . '/' . $tmpImagePath;

		// Now, we'll want to save this temporary image.
		$image->save($tmpImagePath);

		// Unset the image to free up some memory
		unset($image);

		// Reload the image again to get the correct resource pointing to the cropped image.
		$image = ES::image();
		$image->load($tmpImagePath);

		$this->image = $image;

		return $tmpImagePath;
	}

	/**
	 * Get the size names
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function generateFileNames()
	{
		$names = array();

		foreach ($this->maps as $size => $postfix) {
			$names[$size] = $this->image->getName(true) . $postfix . $this->image->getExtension();
		}

		return $names;
	}

	/**
	 * Cleanup a folder
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function cleanup($avatarTable)
	{
		// Don't delete if the avatar is from gallery
		if (!empty($avatarTable->avatar_id)) {
			return true;
		}

		// Delete previous avatars.
		$paths = $avatarTable->getPaths(true);

		$storage = ES::storage($avatarTable->storage);
		$storage->delete($paths);

		return true;
	}

	/**
	 * Creates the necessary images to be used as an avatar.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function create(&$avatarTable = null, $options = array())
	{
		// Get a list of files to build.
		$names = $this->generateFileNames();

		if (is_string($avatarTable)) {
			$targetLocation = $avatarTable;
		} else {
			$targetLocation = !empty($targetLocation) ? $targetLocation : $this->getPath();
		}

		// Create a snapshot of the image
		$this->image->backup();

		foreach ($names as $size => $name) {
			$info = self::$$size;

			$width = $info['width'];
			$height = $info['height'];

			// Standard resize method to crop
			if ($info['mode'] == 'resize') {
				$this->image->resize($width, $height);
			}

			if ($info['mode'] == 'proportionate') {
				$this->image->width($width);
			}

			$path = $targetLocation . '/' . $name;

			if (JFile::exists($path)) {
				JFile::delete($path);
			}

			$this->image->save($path);

			// Reseets the image resource
			$this->image->reset();

			if ($avatarTable instanceof SocialTableAvatar) {
				$avatarTable->$size = $name;
			}
		}

		// Delete the tmp path once it's saved
		// Don't delete if options['deleteimage'] is specifically set to false
		if (!isset($options['deleteimage']) || $options['deleteimage'] != false) {
			$tmp = $this->image->getPath();

			if ($tmp) {
				JFile::delete($tmp);
			}
		}

		return $names;
	}

	/**
	 * Creates the necessary images to be used as an avatar.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store(SocialTablePhoto &$photo, $options = [], $isNewlyRegistered = false)
	{
		// setup the options.
		$createStream = isset($options['addstream']) ? $options['addstream'] : true;

		// Check if there's a profile photos album that already exists.
		$model = ES::model('Albums');

		// Create default album if necessary
		$album = $model->getDefaultAlbum($this->uid, $this->type, SOCIAL_ALBUM_PROFILE_PHOTOS);

		// Load avatar table
		$avatarTable = ES::table('Avatar');
		$exists = $avatarTable->load([
			'uid' => $this->uid, 'type' => $this->type
		]);

		// Cleanup previous avatars only if they exist.
		if ($exists) {
			$this->cleanup($avatarTable);
		}

		// Create the images
		$this->create($avatarTable, $options);

		// Set the avatar composite indices.
		$avatarTable->uid = $this->uid;
		$avatarTable->type = $this->type;

		// Link the avatar to the photo
		$avatarTable->photo_id = $photo->id;

		// Unlink the avatar from gallery item
		$avatarTable->avatar_id = 0;

		// Set the last modified time to now.
		$avatarTable->modified = ES::date()->toMySQL();

		// We need to always reset the avatar back to "joomla"
		$avatarTable->storage = SOCIAL_STORAGE_JOOMLA;

		// Store the avatar now
		$avatarTable->store();

		// @points: profile.avatar.update
		// Assign points to the current user for uploading their avatar
		$photo->assignPoints('profile.avatar.update', $this->uid);

		// @Add stream item when a new profile avatar is uploaded
		if ($createStream) {

			$lib = ES::photo($photo->uid, $photo->type, $photo);

			// First check whether stream exists or not
			$streamId = $lib->getPhotoStreamId($photo->id, 'uploadAvatar');

			// If exists, update the date of existing stream
			if ($streamId) {
				$stream = ES::stream();
				$stream->updateCreated($streamId, null, 'uploadAvatar');

				// Need to unhide the stream if the stream is hidden
				$model = ES::model('Stream');
				$state = $model->unhide($streamId, ES::user()->id);
			} else {

				// If not exists, just create a new one
				$photo->addPhotosStream('uploadAvatar');
			}
		}

		// Once the photo is finalized as the profile picture we need to update the state
		$photo->state = SOCIAL_STATE_PUBLISHED;

		// If album doesn't have a cover, set the current photo as the cover.
		if (!$album->hasCover()) {
			$album->cover_id = $photo->id;

			// Store the album
			$album->store();
		}

		// Prepare the dispatcher
		ES::apps()->load($this->type);

		if ($this->type == SOCIAL_TYPE_USER) {
			$node = ES::user($this->uid);

			// lets update user avatar sizes
			$userAvatars = $node->avatars;

			foreach ($userAvatars as $size => $value) {
				$node->avatars[$size] = $avatarTable->{$size};
			}

			// we need to update finder index for this user for the updated avatar.
			$node->syncIndex();

			// Do not update the goals yet for newly register user
			if (!$isNewlyRegistered) {

				// Update goals progress of the user
				$node->updateGoals('updateavatar');
			}

		} else {
			$node = ES::group($this->uid);
		}

		$args = array(&$photo, $node);
		$dispatcher = ES::dispatcher();

		// @trigger: onUserAvatarUpdate
		$dispatcher->trigger($this->type, 'onAvatarBeforeSave', $args);

		// Once it is created, store the photo as we need to update
		$state = $photo->store();

		// @trigger: onUserAvatarUpdate
		$dispatcher->trigger($this->type, 'onAvatarAfterSave', $args);

		return $state;
	}

	/**
	 * Gets the storage path for photos folder
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPath($createFolders = true)
	{
		// Directly return the stored location if exists
		if (!empty($this->location)) {
			return $this->location;
		}

		// Construct destination path
		$config = ES::config();

		// Get initial storage path
		$storage = JPATH_ROOT;

		$container = ES::cleanPath($config->get('avatars.storage.container'));

		// Append it with the container path
		$storage = $storage . '/' . $container;

		// Ensure that the folder exists
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		// Append it with the type
		$containerType = ES::cleanPath($config->get('avatars.storage.' . $this->type));
		$storage = $storage . '/' . $containerType;

		// Ensure that the folder exists
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		// Construct the last segment which contains the uid.
		$storage = $storage . '/' . $this->uid;

		// Ensure that the path exists.
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		return $storage;
	}

	/**
	 * Gets the storage path for photos folder
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getStoragePath($uid, $type, $createFolders = true)
	{
		// Construct destination path
		$config = ES::config();

		// Get initial storage path
		$storage = JPATH_ROOT;

		// Append it with the container path
		$storage = $storage . '/' . ES::cleanPath($config->get('avatars.storage.container'));

		// Ensure that the folder exists
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		// Append it with the type
		$storage = $storage . '/' . ES::cleanPath($config->get('avatars.storage.' . $type));

		// Ensure that the folder exists
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		// Construct the last segment which contains the uid.
		$storage = $storage . '/' . $uid;

		// Ensure that the path exists.
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		return $storage;
	}
}
