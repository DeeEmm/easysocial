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

class SocialTableFile extends SocialTable
{
	public $id = null;
	public $collection_id = 0;
	public $name = null;
	public $hits = 0;
	public $hash = null;
	public $sub = null;
	public $uid = null;
	public $type = null;
	public $created = null;
	public $user_id = null;
	public $size = null;
	public $mime = null;
	public $state = 0;
	public $storage = null;

	public function __construct($db)
	{
		parent::__construct('#__social_files', 'id', $db);
	}

	/**
	 * Override parent's implementation.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function loadByType($uid, $type)
	{
		$db = ES::db();

		$query = 'SELECT * FROM ' . $db->nameQuote($this->_tbl)
				. ' WHERE ' . $db->nameQuote('uid') . '=' . $db->Quote($uid)
				. ' AND ' . $db->nameQuote('type') . '=' . $db->Quote($type);

		$db->setQuery($query);
		$obj = $db->loadObject();

		return parent::bind($obj);
	}

	/**
	 * Returns the formatted file size
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSize($format = 'kb')
	{
		$size = $this->size;

		if (!$size) {
			return 0;
		}

		switch ($format) {
			case 'kb':
			default:
				$size = round($this->size / 1024);
				break;
		}

		return $size;
	}

	/**
	 * Retrieves the icon type.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getIconClass()
	{
		// Image files
		if ($this->mime == 'image/jpeg') {
			return 'album';
		}

		// Zip files
		if ($this->mime == 'application/zip') {
			return 'zip';
		}

		// Txt files
		if ($this->mime == 'text/plain') {
			return 'text';
		}

		// SQL files
		if ($this->mime == 'text/x-sql') {
			return 'sql';
		}

		// Php files
		if ($this->mime == 'text/x-php') {
			return 'php';
		}

		if ($this->mime == 'text/x-sql') {
			return 'sql';
		}

		if ($this->mime == 'application/pdf') {
			return 'pdf';
		}

		return 'unknown';
	}

	/**
	 * Determines if this file is preview-able.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function hasPreview()
	{
		$allowed = array('image/jpeg', 'image/png', 'image/gif');

		if (in_array($this->mime, $allowed)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine is this file is play-able as a media.
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function isFilePlayable($file = null)
	{
		// reconstruct file data
		if (is_null($file)) {
			$file = array();
			$file['name'] = $this->name;
			$file['type'] = $this->mime;
		}

		if ($this->isVideoFile($file)) {
			return true;
		}

		if ($this->isAudioFile($file)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the file is a video file
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function isVideoFile($file = null)
	{
		// reconstruct file data
		if (is_null($file)) {
			$file = array();
			$file['name'] = $this->name;
			$file['type'] = $this->mime;
		}

		$video = ES::video();
		$isVideo = $video->isVideoFile($file);

		return $isVideo;
	}

	/**
	 * Determine if the file is an audio file
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function isAudioFile($file = null)
	{
		// reconstruct file data
		if (is_null($file)) {
			$file = array();
			$file['name'] = $this->name;
			$file['type'] = $this->mime;
		}

		$audio = ES::audio();
		$isAudio = $audio->isAudioFile($file);

		return $isAudio;
	}

	/**
	 * Determines if the current user is the owner of this item.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isOwner($userId)
	{
		if ($this->user_id == $userId) {
			return true;
		}

		return false;
	}

	/**
	 * Resizes an image file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function resize($width, $height)
	{
		$config = ES::config();

		$width = (int) $width;
		$height = (int) $height;

		// Get the storage path to this image
		$imageName = $this->hash;
		$path = $this->getStoragePath() . '/' . $imageName;

		$image = ES::image();
		$image->load($path);

		// Determine if this image is a gif and if we should process it.
		if ($config->get('photos.gif.enabled') && $image->isAnimated()) {

			// Image in comments only contain one variation
			$sizes = array(
				'resized' => array(
					'width'  => $width,
					'height' => $height
				)
			);

			// Try to process the gif now
			$gif = $image->saveGif($this->getStoragePath(true), $imageName, $sizes);

			// Post processing for the gif image
			if ($gif) {
				$md5 = md5(ES::date()->toSql());
				$storage = SOCIAL_TMP . '/' . $md5 . '.zip';
				JFile::write($storage, $gif);

				// Extract the zip file
				jimport('joomla.filesystem.archive');
				$zipAdapter = ESArchive::getAdapter('zip');
				$zipAdapter->extract($storage, $this->getStoragePath());

				// cleanup tmp storage
				JFile::delete($storage);

				// Delete original image
				JFile::delete($path);

				// Rename the process file with the original name
				$gifFileName = $image->generateFileName('resized', $imageName, '.gif');

				JFile::move($this->getStoragePath() . '/' . $gifFileName, $this->getStoragePath() . '/' . $imageName);

				return true;
			}
		}

		// Perform a normal resizing
		$image->resize($width, $height);

		// Get the extension name of the image
		$extName = JFile::getExt($path);

		// Remove the dot with the extension name such as '.png' to insert _2 for tmp path
		$tmpPath = str_replace('.' . $extName, '', $path);
		$tmpPath = $tmpPath . '_2';

		// After insert, concatenate back its extension name before save
		$tmpPath = $tmpPath . '.' . $extName;

		// Save the image
		$state = $image->save($tmpPath);

		// Delete the main file first
		JFile::delete($path);

		// Rename the temporary stored file to the original file name
		JFile::move($tmpPath, $path);

		return $state;
	}

	/**
	 * Gets the formatted date of the uploaded date.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getCreator()
	{
		$creator = ES::user($this->user_id);

		return $creator;
	}

	/**
	 * Gets the formatted date of the uploaded date.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getUploadedDate()
	{
		$date = ES::date($this->created);

		return $date;
	}

	/**
	 * Override store
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function store($updateNulls = null)
	{
		$state = parent::store();

		// Sync storage usage
		ES::storage()->syncUsage($this->user_id);

		return $state;
	}

	/**
	 * Override parent's delete behavior
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function delete($pk = null, $appendPath = '', $delStream = true, $usageSync = true)
	{
		// Get the storage path
		$path = $this->getStoragePath(true);

		if ($appendPath) {
			$path .= '/' . $appendPath;
		}

		$path = $path . '/' . $this->hash;

		$storage = ES::storage($this->storage);
		$state = $storage->delete($path);

		if ($delStream) {
			// Delete the stream item related to this file
			ES::stream()->delete($this->id, SOCIAL_TYPE_FILES);
		}

		if (!$state) {
			$this->setError(JText::_('Unable to delete the file from ' . $storage));
			return false;
		}

		// Once the file is deleted, delete the record from the database.
		$state = parent::delete();

		if ($state) {
			// deduct point when user remove file.
			ES::points()->assign('files.remove', 'com_easysocial', $this->user_id);
		}

		if ($usageSync) {
			ES::storage()->syncUsage($this->user_id);
		}

		return true;
	}

	/**
	 * Determines if the file is delete-able by the user.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteable($id = null)
	{
		$user = ES::user($id);

		if ($this->user_id == $user->id) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the absolute uri to the item.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getURI()
	{
		$config = ES::config();

		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return $storage->getPermalink($path);
		}

		$path = $this->getStoragePath(true);
		$path = $path . '/' . $this->hash;
		$uri = rtrim(JURI::root(), '/') . $path;

		return $uri;
	}

	/**
	 * Gets the content of the file.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getContents()
	{
		$config = ES::config();

		$path = ltrim($config->get(strtolower($this->type) . '_uploads_path') , '\\/');
		$path = SOCIAL_MEDIA . '/' . $path . '/' . $this->uid . '/' . $this->hash;

		$contents = file_get_contents($path);

		return $contents;
	}

	/**
	 * Copies the temporary file from the table `#__social_uploader` and place the item in the appropriate location.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function copyFromTemporary($id, $deleteSource = true, $hashFileName = false)
	{
		$uploader = ES::table('Uploader');
		$uploader->load($id);

		// Bind the properties from uploader over.
		$this->name = $uploader->name;
		$this->mime = $uploader->mime;
		$this->size = $uploader->size;
		$this->user_id = $uploader->user_id;
		$this->created = $uploader->created;

		jimport('joomla.filesystem.file');

		$fileName = JFile::makeSafe($uploader->name);
		$this->hash = $fileName;

		if ($hashFileName) {
			$this->hash = md5($this->hash . JFactory::getDate()->toSql());
		}

		// Re-append the extension for playable file. #4159
		if ($this->isFilePlayable()) {
			$this->hash .= '.' . JFile::getExt($fileName);
		}

		// Lets figure out the storage path.
		$config = ES::config();

		$path = $this->getStorageContainer();

		// Test if the folder exists for this upload type.
		if (!ES::makeFolder($path)) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER', $path));
			return false;
		}

		if ($this->sub) {
			$path = $path . '/' . $this->sub;

			if (!ES::makeFolder($path)) {
				$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER', $path));
				return false;
			}
		}

		// Let's finalize the storage path.
		$storage = $path . '/' . $this->uid;

		if (!ES::makeFolder($storage)) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER' , $storage));
			return false;
		}

		// Once the script reaches here, we assume everything is good now.
		// Copy the files over.
		jimport('joomla.filesystem.file');

		// Copy the file over.
		$source = $uploader->path;
		$dest = $storage . '/' . $this->hash;

		// Try to copy the files.
		$state = JFile::copy($source, $dest);

		if (!$state) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_COPY_TO_DESTINATION_FOLDER', $dest));
			return false;
		}

		// Once it is copied, we should delete the temporary data.
		if ($deleteSource) {
			$uploader->delete();
		}

		// Generate a new record
		$this->store();

		// For images, we need to perform additional optimizations if needed
		if (ES::isImage($this->mime)) {
			$optimizer = ES::imageoptimizer();
			$optimizer->optimize($dest, $this->id, SOCIAL_TYPE_FILES);

			// Size could be different after optimization
			$this->size = filesize($dest);
			$this->store();
		}

		return $state;
	}

	/**
	 * Identical to the store method but it also stores the file properties.
	 * Maps a file object into the correct properties.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function storeWithFile($file)
	{
		// Check if file exists on the server
		if (!isset($file['tmp_name']) || empty($file)) {
			$this->setError(JText::_('COM_EASYSOCIAL_UPLOADER_FILE_NOT_FOUND'));
			return false;
		}

		// Get the name of the uploaded file.
		if (isset($file['name']) && !empty($file['name'])) {
			$this->name = ES::uploader()->normalizeFilename($file);
		}

		// Get the mime type of the file.
		if (isset($file['type']) && !empty($file['type'])) {
			$this->mime = ES::uploader()->normalizeFiletype($file);
		}

		// Get the file size.
		if (isset($file['size']) && !empty($file['size'])) {
			$this->size = $file['size'];
		}

		// If there's no type or the unique id is invalid we should break here.
		if (!$this->type || !$this->uid) {
			$this->setError(JText::_('COM_EASYSOCIAL_UPLOADER_COMPOSITE_ITEMS_NOT_DEFINED'));
			return false;
		}

		// Generate a random hash for the file.
		$this->hash = md5($this->name . $file['tmp_name']);

		// Try to store the item first.
		$state = $this->store();

		// Once the script reaches here, we assume everything is good now.
		// Copy the files over.
		jimport('joomla.filesystem.file');

		$storage = $this->getStoragePath();

		// Ensure that the storage path exists.
		ES::makeFolder($storage);

		$state = JFile::copy($file['tmp_name'] , $storage . '/' . $this->hash);

		if (!$state) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_COPY_TO_DESTINATION_FOLDER' , $typePath . '/' . $this->uid . '/' . $this->hash));
			return false;
		}

		return $state;
	}

	/**
	 * Returns the storage container path only (without the file in the path)
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function getStorageContainer($relative = false)
	{
		$config = ES::config();
		$path = '';

		if (!$relative) {
			$path = JPATH_ROOT;
		}

		if ($this->type == 'comments') {
			$path .= '/' . rtrim(ES::cleanPath($config->get('comments.storage')), '/');

			return $path;
		}

		$container = ES::cleanPath($config->get('files.storage.container'));
		$path .= '/' . $container . '/' . ES::cleanPath($config->get('files.storage.' . $this->type . '.container'));

		return $path;
	}

	/**
	 * Returns the file path
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getStoragePath($relative = false)
	{
		// Lets figure out the storage path.
		$path = $this->getStorageContainer($relative);

		if ($this->sub) {
			$path = $path . '/' . $this->sub;
		}

		// Let's finalize the storage path.
		$storage = $path . '/' . $this->uid;

		return $storage;
	}

	public function getHash($forceNew = false)
	{
		if (empty($this->hash) || $forceNew) {
			$key = $this->name . $this->size;

			if (empty($key)) {
				$key = uniqid();
			}

			$this->hash = md5($key);
		}

		return $this->hash;
	}

	/**
	 * Retrieves the permalink to the item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPermalink($xhtml = true)
	{
		$url = ESR::conversations(array('layout' => 'download' , 'fileid' => $this->id) , $xhtml);

		return $url;
	}

	/**
	 * Returns the download link for the file.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getDownloadURI($customView = '', $customTask = '')
	{
		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return $storage->getPermalink($path);
		}

		// We need to fix the path for groups!
		$view = $this->type;

		if ($this->type == SOCIAL_TYPE_GROUP) {
			$view = 'groups';
		}

		if ($this->type == SOCIAL_TYPE_EVENT) {
			$view = 'events';
		}

		if ($this->type == SOCIAL_TYPE_PAGE) {
			$view = 'pages';
		}

		// Default task
		$task = 'download';

		if ($this->type == SOCIAL_TYPE_USER) {
			$view = 'profile';
			$task = 'downloadFile';
		}

		if ($customView) {
			$view = $customView;
		}

		if ($customTask) {
			$task = $customTask;
		}

		$uri = ESR::raw('index.php?option=com_easysocial&view=' . $view . '&layout=' . $task . '&fileid=' . $this->id . '&tmpl=component');

		return $uri;
	}

	/**
	 * Return the preview uri of the source item
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPreviewURI()
	{
		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return $storage->getPermalink($path);
		}

		// We need to fix the path for groups!
		$type = $this->type;

		if ($type == 'group') {
			$type = 'groups';
		}

		if ($type == 'event') {
			$type = 'events';
		}

		if ($type == 'page') {
			$type = 'pages';
		}

		if ($type == 'user') {
			$type = 'profile';
		}

		if ($type == 'conversations') {
			return ESR::conversations(array('layout' => 'preview', 'fileid' => $this->id, 'external' => true));
		}

		return ESR::raw('index.php?option=com_easysocial&view=' . $type . '&layout=preview&fileid=' . $this->id . '&tmpl=component');
	}

	/**
	 * Ends the output and allow user to preview the file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function preview($appendPath = '')
	{
		$storage = $this->getStoragePath();

		if ($appendPath) {
			$storage .= '/' . $appendPath;
		}

		$file = $storage . '/' . $this->hash;

		jimport('joomla.filesystem.file');

		// If the file no longer exists, throw a 404
		if (!JFile::exists($file)) {
			throw ES::exception('File no longer exists', 404);
		}

		// Explicitly check for pdf type outside hasPreview() method
		// as the method is only intended for showing the file preview directly
		// such as image file. #4163
		if (!$this->hasPreview() && $this->mime != 'application/pdf') {
			return $this->download();
		}

		// Get the real file name
		$fileName = $this->name;

		// Get the file size
		$fileSize = filesize($file);

		// If pdf, we use pdf viewer
		if ($this->mime == 'application/pdf') {
			// Header content type
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . $fileName . '"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			readfile($file);
			exit;
		}

		header('Content-Description: File Transfer');
		header('Content-Type: ' . $this->mime);
		header('Content-Disposition: inline');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $fileSize);

		// http://dtbaker.com.au/random-bits/how-to-cache-images-generated-by-php.html
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
			   &&
		  (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($file))) {
		  // send the last mod time of the file back
		  header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT',
		  true, 304);
		}

		ob_clean();
		flush();
		readfile($file);
		exit;
	}

	/**
	 * Ends the output and allow user to download the file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function download($appendPath = '')
	{
		// Update the hit counter
		$this->hits += 1;
		$this->store();

		if ($this->storage != 'joomla') {
			$storage = ES::storage($this->storage);
			$path = $this->getStoragePath(true);
			$path = $path . '/' . $this->hash;

			return ES::redirect($storage->getPermalink($path));
		}

		$storage = $this->getStoragePath();

		if ($appendPath) {
			$storage .= '/' . $appendPath;
		}

		$file = $storage . '/' . $this->hash;

		jimport('joomla.filesystem.file');

		// If the file no longer exists, throw a 404
		if (!JFile::exists($file)) {
			throw ES::exception('File no longer exists', 404);
		}

		// Get the real file name
		$fileName = $this->name;

		// Get the file size
		$fileSize = filesize($file);

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'. $fileName . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . $fileSize);
		ob_clean();
		flush();
		readfile($file);
		exit;
	}

	/**
	 * Exports file data
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer)
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$result = array (
			'id' => $this->id,
			'name' => $this->name,
			'downloadName' => str_replace(' ', '_', $this->name),
			'size' => $this->getSize(),
			'creator' => $this->getCreator(),
			'downloadLink' => $this->getDownloadURI(),
			'collectionId' => $this->collection_id,
			'hits' => $this->hits,
			'hash' => $this->getHash(),
			'storage' => $this->storage,
			'previewLink' => $this->getPreviewURI(),
			'permalink' => $this->getPermalink(),
			'created' => $this->created,
			'hasPreview' => $this->hasPreview(),
			'isVideo' => $this->isVideoFile(),
			'isAudio' => $this->isAudioFile(),
			'previewUri' => $this->getURI()
		);

		// Double check the extension of the hashfile
		if ($result['isVideo'] || $result['isAudio']) {
			$ext = JFile::getExt($this->hash);

			if (!$ext) {
				$result['isVideo'] = false;
				$result['isAudio'] = false;
			}
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}
}
