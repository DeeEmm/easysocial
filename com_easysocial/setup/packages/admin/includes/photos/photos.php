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

// Import the required file and folder classes.
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SocialPhotos
{
	private $path = null;
	private $uid = null;
	private $type = null;

	static $sizes = [
		'thumbnail' => [
			'width' => SOCIAL_PHOTOS_THUMB_WIDTH,
			'height' => SOCIAL_PHOTOS_THUMB_HEIGHT
		],

		'large' => [
			'width' => SOCIAL_PHOTOS_LARGE_WIDTH,
			'height' => SOCIAL_PHOTOS_LARGE_HEIGHT
		]
	];

	private $image = null;

	public function __construct(SocialImage &$image)
	{
		$this->image = $image;
	}

	/**
	 * Factory maker for this class.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public static function factory($image)
	{
		$photo = new self($image);

		return $photo;
	}

	/**
	 * Returns the image resource
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getImage()
	{
		return $this->image;
	}

	/**
	 * Gets the storage path for photos folder
	 *
	 * @since	2.1
	 * @access	public
	 */
	public static function getStoragePath($albumId, $photoId, $createFolders = true)
	{
		// Get destination folder path.
		$config = ES::config();
		$container = ES::cleanPath($config->get('photos.storage.container'));
		$storage = JPATH_ROOT . '/' . $container;

		// Test if the storage folder exists
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		// Set the storage path to the album
		$storage = $storage . '/' . $albumId;

		// If it doesn't exist, create it.
		if ($createFolders) {
			ES::makeFolder($storage);
		}

		// Create a new folder for the photo
		$storage = $storage . '/' . $photoId;

		if ($createFolders) {
			ES::makeFolder($storage);
		}

		// Re-generate the storage path since we do not want to store the JPATH_ROOT
		$storage = '/' . $container . '/' . $albumId . '/' . $photoId;

		return $storage;
	}


	/**
	 * Creates the necessary images to be used as an avatar.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function create($path, $exclusion = [], $overrideFileName = '', $processGif = true)
	{
		// Files array store a list of files
		// created for this photo.
		$files = [];

		// Check whether we should process animated gif or not
		if ($processGif) {
			$state = $this->createGif($path, $overrideFileName);

			// If processed, do not proceed with the rest of the process
			if ($state) {
				return $state;
			}
		}

		// Create original image
		$filename = $this->image->generateFilename('original', $overrideFileName);
		$file = JPATH_ROOT . $path . '/' . $filename;
		$files['original'] = $filename;

		// Fake an operation queue
		// $this->image->rotate(0);
		$this->image->save($file);

		// Once the photo successfully uploaded, trigger onAfterPhotoUpload
		$dispatcher = ES::dispatcher();

		// Set the arguments
		$args = [&$this];

		// @trigger onAfterPhotoUpload
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onAfterPhotoUpload', $args);

		// Use original image as source image
		// for all other image sizes.
		$sourceImage = ES::image()->load($file);

		// Always exclude stock photo. #1722
		$exclusion[] = 'stock';

		// Create the rest of the image sizes
		foreach (self::$sizes as $name => $size) {

			if (in_array($name, $exclusion)) {
				continue;
			}

			// Clone an instance of the source image.
			// Otherwise subsequent resizing operations
			// in this loop would end up using the image
			// instance that was resized by the previous loop.
			$image = $sourceImage->cloneImage();

			$filename = $this->image->generateFilename($name, $overrideFileName);
			$file = JPATH_ROOT . $path . '/' . $filename;
			$files[$name] = $filename;

			$image->resize($size['width'], $size['height']);

			// Save image
			$image->save($file);

			// Free up memory
			unset($image);
		}

		return $files;
	}

	/**
	 * Method to proess animated gif
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function createGif($path, $overrideFileName)
	{
		$config = ES::config();

		if (!$config->get('photos.gif.enabled')) {
			return false;
		}

		// Check if the image is animated
		if (!$this->image->isAnimated()) {
			return false;
		}

		// Create stock image
		$filename = $this->image->generateFilename('stock', $overrideFileName);

		$this->image->copy(JPATH_ROOT . $path . '/' . $filename);

		// The process will use stock image stored above
		$gifZip = $this->image->saveGif($path, $filename);

		if (!$gifZip) {
			return false;
		}

		// Create a temporary storage for this file
		$md5 = md5(ES::date()->toSql());
		$storage = SOCIAL_TMP . '/' . $md5 . '.zip';
		$state = JFile::write($storage, $gifZip);

		// Extract the zip file
		jimport('joomla.filesystem.archive');

		$zipAdapter = ESArchive::getAdapter('zip');
		$zipAdapter->extract($storage, JPATH_ROOT . $path);

		$files = array();

		// Generate file name of each images so that it can be stored in the table
		foreach (self::$sizes as $name => $size) {
			$files[$name] = $this->image->generateFilename($name, '', '.gif');
		}

		// We need to generate for the original and stock image path as well
		$files['stock'] = $filename;

		// Create original image
		$filename = $this->image->generateFilename('original', '', '.gif');

		$file = $path . '/' . $filename;
		$files['original'] = $filename;

		$this->image->copy(JPATH_ROOT . $file);

		// cleanup
		JFile::delete($storage);

		// Once the gif photo successfully processed, trigger onAfterPhotoUpload
		$dispatcher = ES::dispatcher();

		// Set the arguments
		$args = [&$this];

		// @trigger onAfterPhotoUpload
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onAfterPhotoUpload', $args);

		return $files;
	}
}
