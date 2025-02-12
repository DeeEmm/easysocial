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

require_once(__DIR__ . '/adapters/abstract.php');

class SocialVideo extends EasySocial
{
	public $table = null;

	// Determines the current type request
	public $uid = null;
	public $type = null;
	public $adapter = null;

	// Allowed video types
	private $allowed = array('video/mp4', 'video/ogg', 'video/webm', 'video/x-flv', 'video/3gpp', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/x-m4v', 'video/avi', 'video/x-matroska');

	public function __construct($uid = null, $type = null, $key = null)
	{
		parent::__construct();

		if ($uid instanceof SocialTableVideo) {
			$this->uid = $uid->uid;
			$this->type = $uid->type;
			$this->table = $uid;
		} else {

			// If uid and type isn't supplied, we assume that it is for the current user.
			if (is_null($uid)) {
				$uid = $this->my->id;
			}

			if (is_null($type) || !$type) {
				$type = SOCIAL_TYPE_USER;
			}

			$this->uid = $uid;
			$this->type = $type;
			$this->table = ES::table('Video');

			if ($key) {
				$this->load($key);
			}
		}

		$this->adapter = $this->getAdapter();
	}

	/**
	 * Loads the video table
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function load($key)
	{
		if ($key instanceof SocialTableVideo) {
			$this->table = $key;
			return true;
		}

		if (is_object($key) || is_array($key)) {
			$this->table->bind($key);
			return true;
		}

		if (is_int($key) || is_string($key)) {
			$this->table->load($key);
			return true;
		}
	}

	/**
	 * Magic method to access table's property
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function __get($property)
	{
		if (!property_exists($this, $property) && isset($this->table->$property)) {
			return $this->table->$property;
		}
	}

	/**
	 * Magic method to route calls to adapter
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array([$this->adapter, $method], $arguments);
	}

	/**
	 * Allow caller to bind data to the table
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function bind($data)
	{
		return $this->table->bind($data);
	}

	/**
	 * Method to check if video's title already exists or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function titleExists($title, $userId, $ignoreId = 0)
	{
		$model = ES::model('Videos');

		return $model->isTitleExists($title, $userId, $ignoreId);
	}

	/**
	 * Creates a new video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function save($data, $file = array(), $options = array())
	{
		// Ensure that the site's language file is loaded
		ES::language()->loadSite();

		// Determines if this is a new video
		$isNew = $this->isNew();

		// Set the current user id only if this is a new video, otherwise whenever the video is edited,
		// the owner get's modified as well.
		if ($isNew) {
			$this->table->user_id = $this->my->id;
		}

		// Format the link if this is video link
		if (isset($data['link']) && $data['link']) {
			$data['link'] = $this->format($data['link']);
		}

		// we need to check if system should remove previous uploaded video file or not when
		// editing the video #3869
		$isPreviousUpload = false;
		if (!$isNew && $this->table->isUpload()) {
			$isPreviousUpload = true;
		}

		// Map the video data
		$this->table->bind($data);

		// make sure title has no leading / ending space
		$this->table->title = ESJString::trim($this->table->title);

		// replace two or more spacing in between words into one spacing only.
		$this->table->title = preg_replace('#\s{2,}#',' ',$this->table->title);

		// set the isnew flag
		$this->table->isnew = $isNew;

		// Ensure that the duration is properly normalized
		if ($this->table->duration && is_float($this->table->duration)) {
			$this->table->duration = round($this->table->duration);
		}

		// default to user type.
		if (!$this->table->uid && !$this->table->type) {
			$this->table->uid = $this->uid ? $this->uid : $this->my->id;
			$this->table->type = $this->type ? $this->type : SOCIAL_TYPE_USER;
		}

		// If this is a new video, ensure that the requester is allowed to upload videos
		$allowCreation = $this->allowCreation();

		if ($isNew && !$allowCreation) {
			$this->setError('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_ADDING_VIDEOS');
			return false;
		}

		// Determines if the requester has exceeded their limit
		$exceededLimit = $this->hasExceededLimit();

		// If the video is a new video, and their limits exceeded, do not allow them to create video
		if ($isNew && $exceededLimit) {
			$this->setError(JText::_("COM_EASYSOCIAL_VIDEOS_EXCEEDED_LIMIT"));
			return false;
		}

		// check for video title uniqueness accross user.
		// since title in video also used as permalink alias,
		// we need to unsure the uniqueness of the title from a user.
		$check = true;
		$i = 0;

		$originalTitle = $this->table->title;

		do {
			$titleExists = $this->titleExists($this->table->title, $this->table->user_id, $this->table->id);

			$check = false;

			if ($titleExists) {
				$this->table->title = $originalTitle . '-' . ++$i;
				$check = true;
			}
		} while ($check);

		// If the title is still invalid, let's just use date time as the title
		if (!$this->table->title) {
			$date = ES::date();
			$this->table->title = $date->format("Ymd-His");
		}

		// Set the video to be under pending processing state since this is a new video
		if (!$this->table->id) {
			// if this is a new video, we need to set this as unpublished first, in just case if
			// user decide to cancel this video creation. #4032
			// $this->table->state = SOCIAL_VIDEO_PENDING;
			$this->table->state = SOCIAL_VIDEO_UNPUBLISHED;
		}

		// if this is an edit action and there is a video file, we need to set the state to pending as well. #597
		// clear the thumbnail as well so that the next encoding proess will recapture the snapshot of the video.
		if (!$isNew && $file) {

			// clear the existing thumbnail from storage
			$this->deleteThumbnail();

			// remove existing video file
			$this->deleteStorage();

			$this->table->state = SOCIAL_VIDEO_PENDING;
			$this->table->thumbnail = '';
			$this->table->path = '';

			// We need to set the storage back to 'joomla' #785
			// So that it will reupload to amazon
			$this->table->storage = SOCIAL_STORAGE_JOOMLA;
		}

		// Video links
		if ($this->table->isLink()) {
			$this->table->path = $data['link'];

			// Grab the video data
			$crawler = ES::crawler();
			$scrape = $crawler->scrape($this->table->path);

			// Before we proceed, we need to ensure the scraped data exist oembed data.
			// If not exists, throw the appropriate error message to the user.
			if (!isset($scrape->oembed) || !$scrape->oembed || !isset($scrape->oembed->html) || !$scrape->oembed->html || empty($scrape)) {

				$message = JText::_('COM_EASYSOCIAL_VIDEO_LINK_EMBED_NOT_SUPPORTED');

				if (isset($scrape->oembedError) && $scrape->oembedError) {
					$message = $scrape->oembedError;
				}

				$this->setError($message);
				return false;
			}

			// Set the video params with the scraped data
			$this->table->params = json_encode($scrape);

			// Set the video's duration
			$this->table->duration = @$scrape->oembed->duration;
		}

		// If upload from story form means everything is checked out and no need to validate again.
		$fromStory = isset($options['story']) ? $options['story'] : null;

		// Validate the video
		if ((!$isNew && $file || $isNew) && !$fromStory) {
			$valid = $this->validate($file, $options);

			if (!$valid) {
				return false;
			}
		}

		// If this video belongs to Page, we need to set the post_as to 'page'
		// So that the author will always be the Page itself
		if ($this->table->type == SOCIAL_TYPE_PAGE && is_null($this->table->post_as)) {

			// Determine if the video is posted by admin
			$page = ES::page($this->table->uid);

			if ($page->isAdmin($this->table->user_id, false)) {
				$this->table->post_as = SOCIAL_TYPE_PAGE;
			} else {
				$this->table->post_as = SOCIAL_TYPE_USER;
			}
		}

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$this->table->description) {
			$this->table->description = '';
		}

		if (!$this->table->path) {
			$this->table->path = '';
		}

		if (!$this->table->params) {
			$this->table->params = '';
		}

		if (!$this->table->original) {
			$this->table->original = '';
		}

		if (!$this->table->thumbnail) {
			$this->table->thumbnail = '';
		}

		if (!$this->table->file_title) {
			$this->table->file_title = '';
		}

		if (!$this->table->duration) {
			$this->table->duration = '';
		}

		if (!$this->table->featured) {
			$this->table->featured = 0;
		}

		if (!$this->table->size) {
			$this->table->size = 0;
		}

		if (!$this->table->hits) {
			$this->table->hits = 0;
		}

		// Save the video
		$state = $this->table->store();

		// send notification here only for the clusters when the user submit the video through the single video creation form
		// if submit the video through story form, it will proceed this notification from the app
		$isFromStory = isset($options['isFromStory']) ? $options['isFromStory'] : null;
		$isUploadFileProcess = isset($options['isUploadFileProcess']) ? $options['isUploadFileProcess'] : null;

		if (!$isFromStory && $state && $isNew && (!$isUploadFileProcess)) {
			$this->notify();
		}

		// if user update the video source from uplood to external link, we should
		// remove the old video files from storage. #3869
		if ($state && $isPreviousUpload === true && $this->table->isLink()) {
			// lets remove the physical file.
			$this->deleteStorage();

			// clear original path and title
			$this->table->original = '';
			$this->table->file_title = '';
			$this->table->store();
		}

		// Bind the video location
		if (isset($data['location']) && $data['location'] && isset($data['latitude']) && $data['latitude'] && isset($data['longitude']) && $data['longitude']) {

			// Create a location for this video
			$location = ES::table('Location');
			$location->load(array('uid' => $this->table->id, 'type' => SOCIAL_TYPE_VIDEO));

			$location->uid = $this->table->id;
			$location->type = SOCIAL_TYPE_VIDEO;
			$location->user_id = $this->my->id;
			$location->address = $data['location'];
			$location->latitude = $data['latitude'];
			$location->longitude = $data['longitude'];

			$location->store();
		}

		// If the user edit and remove the location
		if (!$this->isNew() && $this->hasLocation() && empty($data['location'])) {
			$location = ES::table('Location');
			$location->load(array('uid' => $this->table->id, 'type' => SOCIAL_TYPE_VIDEO));

			$location->delete();
		}

		// Bind the tags
		if (isset($data['tags'])) {
			$this->insertTags($data['tags'], SOCIAL_TYPE_USER);
		}

		// Bind hashtags
		if (isset($data['hashtags'])) {
			$this->insertTags($data['hashtags'], 'tags');
		}

		$privacyData = '';
		if (isset($data['privacy'])) {

			$privacyData = new stdClass();
			$privacyData->rule = 'videos.view';
			$privacyData->value = $data['privacy'];
			$privacyData->custom = $data['privacyCustom'];

			// We always set video's owner to be actor of the privacy #2203
			$privacyData->userId = $this->table->user_id;

			$this->insertPrivacy($privacyData);
		}

		// check if we should create stream or not.
		$createStream = (isset($options['createStream']) && $options['createStream'] && $isNew) ? true : false;

		if ($createStream) {
			$this->createStream('create', $privacyData);
		}

		// Give points and badge to the author for creating a new video.
		if ($isNew) {
			ES::badges()->log('com_easysocial', 'videos.create', $this->getAuthor()->id, '');

			// Directly give points for link video. #3953
			if ($this->table->isLink()) {
				ES::points()->assign('video.upload', 'com_easysocial', $this->getAuthor()->id);
			}
		}

		// Process link videos
		if ($this->table->isLink()) {

			$this->processLinkVideo();

			// if this is a external videos, let index it into joomla smart search
			$this->syncIndex();
		}

		// If the video source is upload, we need to perform additional stuffs
		if ($this->isUpload()) {

			$fromStory = isset($options['story']) ? $options['story'] : false;

			// Determines if the saving process should verify the uploaded file or not.
			if ($file) {
				$this->table->size = $file['size'];

				if (!$fromStory) {

					// Ensure that this is not being edited
					$valid = $this->isVideoValid($file);

					if (!$valid) {
						$this->setError(JText::_("COM_EASYSOCIAL_VIDEOS_INVALID_VIDEO_FILE_PROVIDED"));

						return false;
					}

					// Set the original file title.
					$this->table->file_title = $file['name'];

					// Copy the file to the correct folder
					$path = $this->copyFileFromTmp($file);

					// Store the original video path
					$this->table->original = $path;
				}
			}

			// need to store the privacy value temporary into params
			if ($privacyData) {
				$privacyData->processed = 0;
				$this->table->params = json_encode($privacyData);
			}

			// Re-save the video object to get the correct path
			$state = $this->table->store();

			if ($state) {

				// Log the usage when the video is preparing to be encode
				if ($this->table->state == SOCIAL_VIDEO_PENDING) {
					ES::storage()->syncUsage($this->table->user_id);
				}

				if ($this->table->state == SOCIAL_VIDEO_PUBLISHED) {
					$this->syncIndex();
				}
			}
		}

		// Trigger plugins
		JPluginHelper::importPlugin('system');

		ESDispatcher::trigger('onAfterVideoSave', ['easysocial.videos', &$this->table, $isNew]);

		return $state;
	}

	/**
	 * Insert tags for this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function insertTags($tags = array(), $type = SOCIAL_TYPE_USER)
	{
		if (!is_array($tags)) {
			$tags = str_replace(',', '', $tags);
			$tags = explode('#', $tags);
		}

		$tag = ES::tag($this->table->id, SOCIAL_TYPE_VIDEO);
		$results = $tag->insert($tags, $type);

		if ($type == SOCIAL_TYPE_USER && $tags) {
			foreach ($tags as $userId) {

				if ($userId == $this->my->id) {
					continue;
				}

				$user = ES::user($userId);

				// Set the email options
				$emailOptions = array(
						'title' => 'COM_EASYSOCIAL_EMAILS_TAGGED_IN_VIDEO_SUBJECT',
						'template' => 'site/videos/tagged',
						'videoTitle' => $this->getTitle(),
						'videoThumbnail' => $this->getThumbnail(),
						'videoPermalink' => $this->getPermalink(true, true),
						'actor' => $this->my->getName(),
						'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
						'actorLink' => $this->my->getPermalink(true, true)
				);

				$systemOptions = array(
						'context_type' => 'tagging',
						'context_ids' => $this->id,
						'uid' => $this->id,
						'url' => $this->getPermalink(true, true),
						'actor_id' => $this->my->id,
						'target_id' => $user->id,
						'aggregate' => false
				);

				// Notify user
				ES::notify('videos.tagged', array($user->id), $emailOptions, $systemOptions);
			}
		}

		return $results;
	}

	/**
	 * Insert privacy for this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function insertPrivacy($privacy)
	{
		$privacyLib = ES::privacy();
		$privacyLib->add($privacy->rule, $this->table->id, SOCIAL_TYPE_VIDEOS, $privacy->value, $privacy->userId, $privacy->custom);

		// we need to further update privacy access on these medias table. #3289
		$access = $privacyLib->toValue($privacy->value);

		$model = ES::model('privacy');
		$model->updateMediaAccess(SOCIAL_TYPE_VIDEOS, $this->table->id, $access, $privacy->custom);

		// If this item isn't new, we also need to update it's stream item if there are any
		if (!$this->isNew()) {
			$model->updateStreamAccess(SOCIAL_TYPE_VIDEOS, $this->table->id, $access, $privacy->custom, 'create');
		}

		// now we need to reload the this->table
		if ($this->table->id) {
			$this->table->load($this->table->id);
		}
	}

	/**
	 * Determines if the user can remove a tag from the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function canRemoveTag($tag = null)
	{
		// Site admins should always be able to delete tags
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Video owners should be able to remove tags
		$author = $this->getAuthor();

		if ($author->id == $this->my->id) {
			return true;
		}

		$taggedUser = $tag->getEntity();
		$tagCreator = $tag->getCreator();

		// Allow user to delete their own tag
		if ($taggedUser instanceof SocialUser && $taggedUser->id == $this->my->id) {
			return true;
		}

		// Allow tag creator to remove tag
		if ($tagCreator instanceof SocialUser && $tagCreator->id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user can upload videos
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function canUpload()
	{
		// Check if the feature is enabled
		if (!$this->config->get('video.enabled')) {
			return false;
		}

		if (!$this->config->get('video.uploads')) {
			return false;
		}

		// Check if the ffmpeg is specified correctly
		if (!$this->config->get('video.ffmpeg')) {
			return false;
		}

		// Site admin always able to upload the videos
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// if this is editing #4574
		if ($this->isUpload() && $this->table->id && $this->table->user_id == $this->my->id) {
			return true;
		}

		// Check if the ACL is allowing this based on the adapter type
		if (!$this->allowUpload()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user can embed videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	 public function canEmbed()
	 {
		// Check if the feature is enabled or not
		if (!$this->config->get('video.enabled')) {
			return false;
		}

		if (!$this->config->get('video.embeds')) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// if this is editing #4574
		if ($this->isLink() && $this->table->id && $this->table->user_id == $this->my->id) {
			return true;
		}

		// Check if profile ACL is allowing this
		if (!$this->allowEmbed()) {
			return false;
		}

		return true;

	}

	/**
	 * Determine if file type is a video
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function isVideoFile($file)
	{
		// Check for validity of the video
		if (!in_array($file['type'], $this->allowed)) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the file that is uploaded is valid
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isVideoValid($file)
	{
		$adapter = $this->getAdapter();

		// Check for upload limit
		$maxSize = $adapter->getUploadLimit();
		$maxSize = ES::math()->convertBytes($maxSize);

		if ($file['size'] > $maxSize) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_VIDEOS_EXCEEDED_ALLOWED_FILESIZE', $this->getUploadLimit()));
			return false;
		}

		// Check for validity of the video
		if (!$this->isVideoFile($file)) {
			$this->setError('COM_EASYSOCIAL_VIDEOS_INVALID_VIDEO_FILE');
			return false;
		}

		return true;
	}

	/**
	 * Checks for valid video url
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isValidUrl($url)
	{
		$pattern = "/(^|\\s)(https?:\\/\\/)?(([a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.([a-z]{2,6}))|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(:[0-9]{1,5})?(\\/.*)?/uism";

		$match = preg_match($pattern, $url);

		if (!$match) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if this is a new video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isNew()
	{
		return $this->table->id ? (bool) $this->table->isnew : true;
	}

	/**
	 * Copies the file from the temporary folder
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function copyFileFromTmp($file)
	{
		// Get the storage path for this video
		$storagePath = $this->getStoragePath();

		// We need to rename the original file name.
		$storagePath .= '/' . md5($file['name']);

		// Copy the original video file into the storage path
		$state = JFile::copy($file['tmp_name'], $storagePath);

		return $storagePath;
	}

	/**
	 * Retrieves the cluster that is associated with the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getCluster()
	{
		$cluster = null;

		if ($this->uid && $this->type && $this->type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($this->type, $this->uid);
		}

		return $cluster;
	}

	/**
	 * Retrieves the container path
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getContainer()
	{
		$container = ltrim($this->config->get('video.storage.container'), '/');
		$path = JPATH_ROOT . '/' . $container;

		if (!JFolder::exists($path)) {
			JFolder::create($path);
		}

		if ($this->table->id) {
			$container .= '/' . $this->table->id;
			$path .= '/' . $this->table->id;

			if (!JFolder::exists($path)) {
				JFolder::create($path);
			}
		}

		return $container;
	}

	/**
	 * Ensures that the container folder exists
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getStorageUrl()
	{
		$container = $this->getContainer();
		$url = rtrim(JURI::root(), '/') . '/' . $container;

		return $url;
	}

	/**
	 * Ensures that the container folder exists
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getStoragePath()
	{
		$container = $this->getRelativeStoragePath();
		$storagePath = JPATH_ROOT . '/' . $container;

		if (!JFolder::exists($storagePath)) {
			JFolder::create($storagePath);
		}

		return $storagePath;
	}

	/**
	 * Retrieves the relative path
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getRelativeStoragePath()
	{
		$container = ltrim($this->config->get('video.storage.container'), '/');

		if ($this->table->id) {
			$container .= '/' . $this->table->id;
		}

		return $container;
	}

	/**
	 * Gets the log file path
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getLogFilePath()
	{
		$storagePath = $this->getStoragePath();
		$logFilePath = $storagePath . '/' . md5($this->table->id) . '.log';

		return $logFilePath;
	}

	/**
	 * Retrieves the video item
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getItem()
	{
		return $this->table;
	}

	/**
	 * Retrieves the path to the video file
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getFile()
	{
		$container = '/' . str_ireplace('\\', '/', $this->getContainer());
		$file = basename($this->table->path);

		$relative = $container . '/' . $file;

		// Default url
		$url = rtrim(JURI::root(), '/') . $relative;

		if ($this->storage != SOCIAL_STORAGE_JOOMLA) {
			$storage = ES::storage($this->storage);
			$url = $storage->getPermalink($relative);
		}

		return $url;
	}

	/**
	 * Retrieves the path to the video file
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getRelativeFilePath()
	{
		return $this->table->path;
	}

	/**
	 * Determines if this video belongs to cluster
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isCreatedInCluster()
	{
		if ($this->table->uid && $this->table->type && $this->table->type != SOCIAL_TYPE_USER) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the video is in pending processing mode
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isPendingProcess()
	{
		return $this->table->state == SOCIAL_VIDEO_PENDING;
	}

	/**
	 * Determines if the video item is being processed.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isProcessing()
	{
		return $this->table->state == SOCIAL_VIDEO_PROCESSING;
	}

	/**
	 * Determines if the video is published
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isPublished()
	{
		return $this->table->state == SOCIAL_VIDEO_PUBLISHED;
	}

	/**
	 * Determines if the video is published
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isUnpublished()
	{
		return $this->table->state == SOCIAL_VIDEO_UNPUBLISHED;
	}


	/**
	 * Determines if this video is an upload source
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isUpload()
	{
		return $this->table->isUpload();
	}

	/**
	 * Determines if this video is a link source
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function isLink()
	{
		return $this->table->isLink();
	}

	/**
	 * Creates a new log file
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function createLogFile()
	{
		$name = md5($this->id);
		$name = md5($this->command);
		$path = SOCIAL_TMP . '/' . $name;
		$contents = '';

		JFile::write($path, $contents);

		$this->logFile = $path;

		return $this->logFile;
	}

	/**
	 * Processes a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function process()
	{
		// If the video is already in the midst of processing, we shouldn't allow them to process this again
		if ($this->isProcessing()) {
			$this->setError(JText::_("COM_EASYSOCIAL_VIDEOS_ALREADY_PROCESSING"));
			return false;
		}

		// Only process the video uploads
		if ($this->table->isUpload()) {
			$this->processUploadedVideo();
		}

		// Only process linked videos
		if ($this->table->isLink()) {
			$this->processLinkVideo();
		}
	}

	/**
	 * Process link videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function processLinkVideo()
	{
		$this->snapshot();
	}

	/**
	 * Takes a snapshot of the video to be used as the thumbnail
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function snapshot($dimension = '1280x720', $length = '00:00:01')
	{
		if ($this->isUpload()) {
			$ffmpeg = ES::ffmpeg();
			$ffmpeg->input($this->table->original);

			// Get the storage path to the video
			$fileName = md5($this->table->title) . '.jpg';

			// Construct the storage path
			$storage = $this->getStoragePath() . '/' . $fileName;

			if (JFile::exists($storage)) {
				JFile::delete($storage);
			}

			// Set the thumbnail at the first second
			$ffmpeg->thumb($this->table->original, $storage, $length);

			// Returns the relative path
			$relativePath = $this->getRelativeStoragePath() . '/' . $fileName;

			$this->table->thumbnail = $relativePath;
			$this->table->store();

			return $relativePath;
		}

		if ($this->isLink()) {
			// Get the thumbnail of the video
			$params = json_decode($this->table->params);

			// Get the storage path to the video
			$storage = $this->getStoragePath();

			// Since nowadays those video provider already support opengraph and most likely they put HD image in this section
			$opengraphImage = isset($params->opengraph->image) && $params->opengraph->image ? true : false;

			$thumbnail = false;

			// Get the thumbnail url
			if ($opengraphImage) {
				$thumbnail = $params->opengraph->image;
			} else if (isset($params->oembed->thumbnail)) {
				$thumbnail = $params->oembed->thumbnail;
			} else if (isset($params->oembed->thumbnail_url)) {
				$thumbnail = $params->oembed->thumbnail_url;
			}

			// If this is facebook video, we retrieve the thumbnail differently
			if ($this->isFacebookEmbed() && !$thumbnail) {
				$thumbnail = $this->getFacebookThumbnail();
			}

			if (!$thumbnail) {
				$this->table->state = SOCIAL_VIDEO_PUBLISHED;
				$this->table->store();

				return false;
			}

			// replace the html_entities characters
			$thumbnail = str_replace('&amp;', '&', $thumbnail);

			// Crawl the image now.
			$connector = ES::get('Connector');
			$connector->addUrl($thumbnail);
			$connector->connect();

			// Get the result and parse them.
			$contents = $connector->getResult($thumbnail);

			// We need to sanitize the image file name
			$imageFileName = md5(basename($thumbnail));

			// Store the image file now
			$tmpStorage = $storage . '/' . $imageFileName;

			// Save the file
			JFile::write($tmpStorage, $contents);

			// Load the image now
			$image = ES::image();
			$image->load($tmpStorage);

			// Get the extension for the file
			$extension = $image->getExtension();
			$newStorage = $storage . '/' . $imageFileName . $extension;

			// Rename the file
			JFile::move($tmpStorage, $newStorage);

			// Ensure that image is valid
			if (!$image->isValid()) {
				JFile::delete($storage);
				return false;
			}

			$relativePath = $this->getRelativeStoragePath() . '/' . $imageFileName . $extension;

			// Save the thumbnail
			$this->table->thumbnail = $relativePath;
			$this->table->state = SOCIAL_VIDEO_PUBLISHED;
			$this->table->store();
		}
	}

	/**
	 * Retrieve thumbnail from non-oembed source
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getFacebookThumbnail()
	{
		$videoId = $this->getFacebookVideoId($this->path);

		$url = 'https://graph.facebook.com/' . $videoId . '/picture?redirect=false';

		// Crawl the image now.
		$connector = ES::get('Connector');
		$connector->addUrl($url);
		$connector->connect();

		// Get the result and parse them.
		$contents = $connector->getResult($url);

		$obj = json_decode($contents);
		$thumbnail = isset($obj) && isset($obj->data) && isset($obj->data) ? $obj->data->url : '';

		return $thumbnail;
	}

	/**
	 * Get video id from facebook url
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getFacebookVideoId($url)
	{
		// remove extra slash (if any)
		$url = rtrim($url,'/');

		$tmp = explode('/', $url);

		// https://www.facebook.com/username/videos/1234567890
		if (strtolower($tmp[count($tmp) - 2] == 'videos')) {
			return $tmp[count($tmp) - 1];
		}

		// http://www.facebook.com/photo.php?v=1234567890
		$tmp = parse_url($url);

		parse_str($tmp['query'], $query);

		if (!empty($query['v'])) {
			return $query['v'];
		}

		return false;
	}

	/**
	 * Process uploaded videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function processUploadedVideo()
	{
		// Mark the video as processing
		$this->table->processing();

		// Set the audio bitrate
		$bitrate = $this->config->get('video.audiobitrate');

		// We want to output the completed stuff to our own log files
		$logFile = $this->getLogFilePath();

		// Set the duration of the video
		$duration = $this->extractDuration();
		$this->table->duration = $duration->raw();

		// Get the framerate of the video
		$framerate = $this->extractFramerate();

		// Generate a unique name for this file
		$fileName = $this->generateFileName();

		// Get the path to the output.
		$storagePath = $this->getStoragePath() . '/' . $fileName;

		// Set the output file
		$this->table->path = $this->getRelativeStoragePath() . '/' . $fileName;

		// Get the video size to resize to
		$size = $this->config->get('video.size');

		// Get the truncation length in seconds
		$maxLength = $this->getTruncationLength();
		$maxVideoDuration = null;

		if ($maxLength) {
			$maxLengthDuration = new SocialVideoDuration($maxLength);
			$maxVideoDuration = $maxLengthDuration->format();

			// If there is a maximum length imposed, the duration
			$this->table->duration = $maxLengthDuration->raw();
		}

		// Load up the ffmpeg library
		$ffmpeg = ES::ffmpeg();
		$ffmpeg->resize($this->table->original, $storagePath, $bitrate, $size, $logFile, $framerate, $maxVideoDuration);

		// Set the thumbnail
		if (!$this->table->thumbnail) {
			$this->snapshot();
		}

		// Update the video object now.
		$this->table->store();
	}

	/**
	 * Generate a random file name
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function generateFileName()
	{
		$name = substr(md5(microtime()),rand(0,26), 8);

		return $name . '.mp4';
	}

	/**
	 * Retrieves the duration of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getDuration()
	{
		if (!$this->table->duration) {
			$duration = JText::_('COM_ES_VIDEO_DURATION_NOT_AVAILABLE');

			return $duration;
		}

		$duration = new SocialVideoDuration($this->table->duration);

		// Since duration is always stored in seconds, we need to format this
		return $duration->format();
	}

	/**
	 * Retrieves the location for the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getLocation()
	{
		static $location = null;

		if (is_null($location)) {
			$location = ES::location($this->table->id, SOCIAL_TYPE_VIDEO);
		}

		return $location;
	}

	/**
	 * Retrieves the likes library for this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getLikes($verb = '', $streamId = '')
	{
		if (!$verb) {
			$verb = 'create';
		}

		$options = array();

		if ($this->type != SOCIAL_TYPE_USER) {
			$options['clusterId'] = $this->uid;
		}

		$likes = ES::likes();
		$likes->get($this->table->id, SOCIAL_TYPE_VIDEOS, $verb, $this->type, $streamId, $options);

		return $likes;
	}

	/**
	 * Retrieve the likes count for a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getLikesCount($verb = '', $streamId = '')
	{
		$likes = $this->getLikes($verb, $streamId);

		return $likes->getCount();
	}

	/**
	 * Retrieves the comment library for this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getComments($verb = '', $streamId = '')
	{
		if (!$verb) {
			$verb = 'create';
		}

		$options = array();
		$options['clusterId'] = $this->uid;
		$options['url'] = $this->getPermalink(true, null, null, false, false, false);

		$privacy = ES::user()->getPrivacy();
		if (!$privacy->validate('story.post.comment', $this->table->user_id, SOCIAL_TYPE_USER)) {
			$options['hideForm'] = true;
		}

		// Generate comments for the video
		$comments = ES::comments($this->table->id, SOCIAL_TYPE_VIDEOS, $verb, $this->type, $options, $streamId);

		return $comments;
	}

	/**
	 * Retrieves the comments count
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getCommentsCount($verb = '', $streamId = '')
	{
		if (!$verb) {
			$verb = 'create';
		}

		$comments = $this->getComments($verb, $streamId);

		return $comments->getCount();
	}

	/**
	 * Retrieves the related stream id for a particular verb
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getStreamId($verb)
	{
		return $this->getVideoStreamId($this->table->id, $verb);
	}

	/**
	 * Retrieves the creation date of a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getCreatedDate()
	{
		$date = ES::date($this->table->created);

		return $date;
	}

	/**
	 * Retrieves the bookmarks library associated to this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getSharing()
	{
		$options = array('text' => JText::_('COM_EASYSOCIAL_VIDEOS_SHARE'));
		$options['url'] = $this->getExternalPermalink();
		$sharing = ES::sharing($options);

		return $sharing;
	}

	/**
	 * Retrieves the privacy library associated to this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getPrivacy()
	{
		static $privacy = null;

		if (is_null($privacy)) {
			$privacy = ES::privacy($this->id, SOCIAL_TYPE_VIDEOS);
		}

		return $privacy;
	}

	/**
	 * Retrieves the privacy library for this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getPrivacyButton()
	{
		$privacy = $this->getPrivacy();

		/* TODO: need to come back here once the stream for video created. */
		$streamId = $this->getVideoStreamId($this->table->id, 'create');

		$button = $privacy->form($this->table->id, SOCIAL_TYPE_VIDEOS, $this->table->uid, 'videos.view', false, $streamId);

		return $button;
	}

	/**
	 * Retrieves the view all videos link
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getAllVideosLink($filter = '', $xhtml = false)
	{
		$options = array();

		if ($filter) {
			$options['filter'] = $filter;
		}

		if ($this->uid && $this->type) {
			$options['uid'] = $this->adapter->getAlias();
			$options['type'] = $this->type;
		}

		$url = FRoute::videos($options, $xhtml);

		return $url;
	}

	/**
	 * Determines if the photo should be associated with the stream item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getVideoStreamId($videoId, $verb)
	{
		static $_cache = array();

		$db = ES::db();
		$sql = $db->sql();

		$idx = $videoId . '.' . $verb;

		if (!isset($_cache[$idx])) {

			$sql->select('#__social_stream_item', 'a');
			$sql->column('a.uid');
			$sql->where('a.context_type', SOCIAL_TYPE_VIDEOS);
			$sql->where('a.context_id', $videoId);

			if ($verb == 'upload') {
				$sql->where('a.verb', 'share');
				$sql->where('a.verb', 'upload', '=', 'OR');
			} else if($verb == 'add') {
				$sql->where('a.verb', 'create');
			} else {
				$sql->where('a.verb', $verb);
			}

			$db->setQuery($sql);
			$_cache[$idx] = (int) $db->loadResult();
		}

		$uid = $_cache[$idx];

		if (!$uid) {
			return;
		}

		return $uid;
	}

	/**
	 * Retrieves the reports library for this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getReports()
	{
		// Generate the reports
		$options = [
			'title' => 'COM_EASYSOCIAL_VIDEOS_REPORTS_DIALOG_TITLE',
			'description' => 'COM_EASYSOCIAL_VIDEOS_REPORTS_DIALOG_DESC',
			'extension' => 'com_easysocial',
			'type' => SOCIAL_TYPE_VIDEO,
			'uid' => $this->table->id,
			'itemTitle' => $this->getTitle(),
			'actorid' => $this->user_id
		];

		$reports = ES::reports($options);

		return $reports;
	}

	/**
	 * Retrieves a list of users tags associated with the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getEntityTags()
	{
		if (!$this->table->id) {
			return array();
		}

		$model = ES::model('Tags');
		$tags = $model->getTags($this->table->id, SOCIAL_TYPE_VIDEO, 'entity');

		return $tags;
	}

	/**
	 * Retrieves a list of tags associated with the video
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTags($stringOnly = false)
	{
		if (!$this->table->id) {
			return false;
		}

		$model = ES::model('Tags');
		$tags = $model->getTags($this->table->id, SOCIAL_TYPE_VIDEO, 'hashtag');

		if (!$tags) {
			return false;
		}

		// Process the hashtags to only return the strings
		// eg : #hashtag1,#hashtag2,#hashtag3
		if ($stringOnly) {

			$text = '';

			foreach ($tags as $tag) {
				$title = $tag->getTitle();
				$text .= $title;
				$text .= ',';
			}

			$text = rtrim($text, ',');

			return $text;
		}

		$result = array();

		// Process the link
		if ($tags) {
			$uid = '';
			$type = '';

			if ($this->table->type != 'user') {
				$cluster = ES::cluster($this->table->type, $this->table->uid);
				$uid = $cluster->getAlias();
				$type = $this->table->type;
			}

			foreach ($tags as $tag) {

				$options = array('hashtag' => $tag->title);

				if ($uid &&  $type) {
					$options['uid'] = $uid;
					$options['type'] = $type;
				}

				$tag->permalink = ESR::videos($options);
			}
		}

		return $tags;
	}

	/**
	 * Retrieves the title of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getTitle()
	{
		return JText::_($this->table->title);
	}

	/**
	 * Retrieves the description of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getDescription($showDefault = true)
	{
		// Load site's language file.
		ES::language()->loadSite();

		$desc = ESJString::trim($this->table->description);

		if (!$desc && $showDefault) {
			return JText::_('COM_EASYSOCIAL_VIDEOS_NO_DESCRIPTION_AVAILABLE');
		}

		$isRestApi = ES::input()->get('rest', false, 'bool');

		if ($isRestApi) {
			return ES::string()->normalizeRestContent($desc);
		}

		// Only process this if the description doesn't have those HTML tag
		if (strpos($desc, '<p>') === false && strpos($desc, '<br />') === false && strpos($desc, '<br>') === false) {
			$desc = nl2br($desc);
		}

		return $desc;
	}

	/**
	 * Retrieves the embed codes for the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getEmbedCodes($autoplay = false)
	{
		if ($this->isLink()) {
			return $this->getLinkEmbedCodes($autoplay);
		}

		return $this->getUploadEmbedCodes();
	}

	/**
	 * Generates the embed codes for linked videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getLinkEmbedCodes($autoplay = false)
	{
		// Get the video width and height
		$height = $this->config->get('video.size');
		$width = '1920';

		if ($height == '720') {
			$width = '1280';
		}

		if ($height == '480') {
			$width = '854';
		}

		$params = json_decode($this->table->params);
		$codes = $params->oembed->html;

		if ($params->oembed && isset($params->oembed->width)) {
			$codes = str_ireplace('width="' . $params->oembed->width . '"', 'width="' . $width . '"', $codes);
			$codes = str_ireplace('height="' . $params->oembed->height . '"', 'height="' . $height . '"', $codes);
		}

		// Twitch NEW API have to append the parent parmeter specific for the current domain - 2020
		if ($this->isTwitchEmbed()) {

			preg_match_all('/src=\"(.*?)\"/i', $codes, $matches);

			if ($matches && isset($matches[1])) {

				$url = $matches[1][0];

				$domain = rtrim(JURI::root(), '/');
				$domain = str_ireplace(array('http://', 'https://'), '', $domain);

				// remove the subdomin directory
				if (strstr($domain, '/') !== false) {
					$domain = explode('/', $domain);
					$domain = $domain[0];
				}

				$codes = str_ireplace($url, $url . '&parent=' . $domain, $codes);
			}
		}

		if ($autoplay) {
			preg_match_all('/src=\"(.*?)\"/i', $codes, $matches);

			if ($matches && isset($matches[1])) {
				$url = $matches[1][0];

				// Append autoplay
				$codes = str_ireplace($url, $url . '&autoplay=1', $codes);
			}
		}

		if ($this->isYouTubeEmbed()) {

			if ($this->config->get('youtube.nocookie', false)) {
				$codes = str_replace('youtube.com/', 'youtube-nocookie.com/', $codes);
			}

			// Render same channel YouTube by default
			if ($this->config->get('video.youtube.related', true)) {

				preg_match_all('/src=\"(.*?)\"/i', $codes, $matches);

				if ($matches && isset($matches[1])) {
					$url = $matches[1][0];

					$codes = str_ireplace($url, $url . '&rel=0', $codes);
				}
			}
		}

		return $codes;
	}

	/**
	 * Generates the embed codes for uploaded videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getUploadEmbedCodes()
	{
		$theme = ES::themes();

		// We need to generate a unique id for each videos that are embedded on the page
		$uid = uniqid();

		$logo = false;

		if ($this->config->get('video.layout.player.logo')) {
			$logo = $this->getPlayerLogo();
		}

		$watermark = false;

		if ($this->config->get('video.layout.player.watermark')) {
			$watermark = $this->getPlayerWatermark();
		}

		$theme->set('watermark', $watermark);
		$theme->set('logo', $logo);
		$theme->set('uid', $uid);
		$theme->set('video', $this);

		$output = $theme->output('site/videos/player/default');

		return $output;
	}

	/**
	 * Retrieves the logo for the player
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getPlayerLogo()
	{
		static $logo = null;

		if (is_null($logo)) {
			$path = JPATH_ROOT . '/images/easysocial_override/video_logo.png';

			// Default logo
			$logo = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/videos/logo.png';

			if (JFile::exists($path)) {
				$logo = rtrim(JURI::root(), '/') . '/images/easysocial_override/video_logo.png';
			}
		}

		return $logo;
	}

	/**
	 * Retrieves the watermark for the player
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getPlayerWatermark()
	{
		static $watermark = null;

		if (is_null($watermark)) {
			$assets = ES::assets();
			$template = $assets->getJoomlaTemplate();
			$path = JPATH_ROOT . '/images/easysocial_override/video_watermark.png';

			// Default watermark
			$watermark = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/videos/watermark.png';

			if (JFile::exists($path)) {
				$watermark = rtrim(JURI::root(), '/') . '/images/easysocial_override/video_watermark.png';
			}
		}

		return $watermark;
	}

	/**
	 * Retrieves the entity adapter
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getAdapter()
	{
		if (!$this->type) {
			return false;
		}

		$file = __DIR__ . '/adapters/' . $this->type . '.php';

		if (!file_exists($file)) {
			return false;
		}

		require_once($file);

		$className = 'SocialVideoAdapter' . ucfirst($this->type);
		$obj = new $className($this->uid, $this->type, $this->table);

		return $obj;
	}

	/**
	 * Retrieves the thumbnail of a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getThumbnail()
	{
		if (!$this->table->thumbnail) {

			// Try to get image from the link itself
			if ($this->table->source == 'link') {
				if (isset($this->table->params) && $this->table->params) {
					$params = json_decode($this->table->params);

					if (isset($params->thumbnail) && $params->thumbnail) {
						return $params->thumbnail;
					}

					if (isset($params->images) && $params->images) {

						if (is_array($params->images)) {
							return $params->images[0];
						}

						return $params->images;
					}

					if (isset($params->oembed) && isset($params->oembed->thumbnail) && $$params->oembed->thumbnail) {
						return $params->oembed->thumbnail;
					}
				}
			}

			// Default thumbnail
			return $this->getDefaultThumbnail();
		}

		$thumbnail = $this->table->thumbnail;
		$isCustom = false;

		if ($this->table->thumbnail_custom) {
			// for now custom thumbnail always serve from local.
			$thumbnail = $this->table->thumbnail_custom;
			$isCustom = true;
		}

		$container = str_ireplace('\\', '/', $this->getContainer());
		$file = basename($thumbnail);

		$relative = '/' . $container . '/' . $file;

		// Default url
		$url = rtrim(JURI::root(), '/') . $relative;

		// Storage service
		if (!$isCustom && $this->table->storage != SOCIAL_STORAGE_JOOMLA) {
			$storage = ES::storage($this->table->storage);
			$url = $storage->getPermalink($relative);
		}

		return $url;
	}

	/**
	 * Determine if the thumbnail is custom or original.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function isThumbnailCustom()
	{
		return $this->table->thumbnail_custom ? true : false;
	}

	/**
	 * Retrieves the default thumbnail for video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getDefaultThumbnail()
	{
		$override = JPATH_ROOT . '/templates/' . $this->app->getTemplate() . '/html/com_easysocial/defaults/videos/cover.png';
		$overrideUri = rtrim(JURI::root(), '/') . '/templates/' . $this->app->getTemplate() . '/html/com_easysocial/defaults/videos/cover.png';

		if (JFile::exists($override)) {
			return $overrideUri;
		}

		$default = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/defaults/videos/cover.jpg';

		return $default;
	}

	/**
	 * Retrieves the thumbnail of a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getThumbnailFileName()
	{
		if ($this->table->thumbnail_custom) {
			return basename($this->table->thumbnail_custom);
		}

		return basename($this->table->thumbnail);
	}

	/**
	 * Retrieves the thumbnail of a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getRelativeThumbnailPath()
	{
		$container = $this->getContainer();
		$path = $container . '/' . basename($this->table->thumbnail);

		return $path;
	}

	/**
	 * Retrieves the permalink to edit a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getCreateLink($xhtml = true)
	{
		$options = array('layout' => 'form');

		if ($this->table->id) {
			$options['id'] = $this->table->id;
		}

		if ($this->uid && $this->type) {
			$cluster = ES::cluster($this->type, $this->uid);
			$options['uid'] = $cluster->getAlias();
			$options['type'] = $this->type;
		}

		$url = FRoute::videos($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the permalink to edit a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getEditLink($xhtml = true)
	{
		$options = array('layout' => 'form');

		if ($this->table->id) {
			$options['id'] = $this->table->id;
		}

		if ($this->uid && $this->type) {
			$cluster = ES::cluster($this->type, $this->uid);
			$options['uid'] = $cluster->getAlias();
			$options['type'] = $this->type;
		}

		$url = FRoute::videos($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the permalink of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getAlias()
	{
		return $this->table->getAlias();
	}

	/**
	 * Retrieves the permalink of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $uid = null, $utype = null, $from = false, $external = false, $sef = true, $adminSef = false)
	{
		return $this->table->getPermalink($xhtml, $uid, $utype, $from, $external, $sef, $adminSef);
	}

	/**
	 * Retrieves the external permalink of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getExternalPermalink($format = null)
	{
		return $this->table->getExternalPermalink($format);
	}

	/**
	 * Retrieves the hits for the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getHits()
	{
		return $this->table->hits;
	}

	/**
	 * Retrieves the author of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getAuthor()
	{
		$author = ES::user($this->table->user_id);

		return $author;
	}

	/**
	 * Retrieves the post actor for video
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getVideoCreator($obj)
	{
		if ($this->post_as == SOCIAL_TYPE_PAGE && !is_null($obj)) {
			return $obj;
		}

		return $this->getAuthor();
	}

	/**
	 * Retrieves the category of the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getCategory()
	{
		static $_cache = array();

		$idx = $this->table->category_id;

		if (!isset($_cache[$idx])) {
			$category = ES::table('VideoCategory');
			$category->load($this->table->category_id);

			$_cache[$idx] = $category;
		}

		return $_cache[$idx];
	}

	/**
	 * Exports the video data in a std class object
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function export()
	{
		$data = (object) $this->table;

		return $data;
	}

	/**
	 * Extracts thumbnail from a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function extractThumbnail()
	{
		$ffmpeg = ES::ffmpeg();
		$ffmpeg->input($this->table->original);

		// Set the thumbnail at the first second
		$ffmpeg->thumb('1280x720', '00:00:01');

		// Get the storage path to the video
		$fileName = md5($this->table->title) . '.jpg';

		// Construct the storage path
		$storage = $this->getStoragePath() . '/' . $fileName;

		// Capture the first second of the video
		$ffmpeg->output($storage);
		$ffmpeg->execute();

		// Returns the relative path
		$relativePath = $this->getRelativeStoragePath() . '/' . $fileName;

		return $relativePath;
	}

	/**
	 * Extracts the duration using ffmpeg
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function extractDuration()
	{
		$ffmpeg = ES::ffmpeg();
		$ffmpeg->input($this->table->original);

		$output = $ffmpeg->execute();

		$duration = $this->matchDuration($output);

		return $duration;
	}

	/**
	 * Extract the video's framerate
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function extractFramerate()
	{
		$ffmpeg = ES::ffmpeg();
		$ffmpeg->input($this->table->original);

		$output = $ffmpeg->execute();

		// We default to 25 fps
		$fps = 25;

		$pattern = '/Video: .*?, (\d+\.?\d*) fps,/is';
		preg_match($pattern, $output, $matches);

		if ($matches && isset($matches[1])) {
			$fps = $matches[1];
		}

		return $fps;
	}

	public static function trimResult($var)
	{
		if (empty($var)) {
			return false;
		}

		return true;
	}

	/**
	 * Converts the given duration into seconds
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function convertToSeconds($hour, $minute, $second)
	{
		$seconds = 0;

		if ($hour != '00') {
			$seconds += intval($hour) * 60 * 60;
		}

		if ($minute != '00') {
			$seconds += intval($minute) * 60;
		}

		$seconds += intval($second);

		return $seconds;
	}

	/**
	 * Matches the output from ffmpeg and retrieves the duration
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function matchDuration($contents)
	{
		$duration = new SocialVideoDuration();

		// Regex to match the total duration of the original video
		$pattern = '/Duration: ([0-9]{2}):([0-9]{2}):([0-9]{2})\.[0-9]{2}/is';
		preg_match($pattern, $contents, $matches);

		if ($matches) {
			list($str, $hour, $minute, $second) = $matches;

			$seconds = $this->convertToSeconds($hour, $minute, $second);
			$duration->set($seconds);
		}

		return $duration;
	}

	/**
	 * Checks the status
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function status()
	{
		// Get the log file's path
		$logFilePath = $this->getLogFilePath();

		if (!JFile::exists($logFilePath)) {
			// if log file is not exists, skip this.
			return 0;
		}

		$contents = file_get_contents($logFilePath);

		// Regex to match the total duration of the original video
		$duration = $this->matchDuration($contents);

		// Get total seconds for the duration
		$totalDurationSeconds = $duration->raw();

		// If the total duration is empty, we skip the process
		if (empty($totalDurationSeconds)) {
			return 0;
		}

		// Determines if this is already complete
		$pattern = '/muxing overhead\:/is';
		preg_match($pattern, $contents, $complete);

		if ($complete) {
			return true;
		}

		// Get all the frames that is being converted currently
		$pattern = '/frame= (.*)/uim';
		preg_match($pattern, $contents, $frames);

		// Default processed duration
		$currentDurationSeconds = 0;

		if ($frames) {
			$frames = explode("\r", $frames[0]);

			// The last frame always needs to -2 because the last line is always a return carriage
			$frames = array_filter($frames, array($this, 'trimResult'));
			$totalFrames = count($frames);
			$index = count($frames) - 1;
			$lastFrame = $frames[$index];

			// Get the current time of the last frame
			if ($lastFrame) {
				$pattern = '/time=([0-9]{2}):([0-9]{2}):([0-9]{2})\.([0-9]{2})/is';
				preg_match($pattern, $lastFrame, $time);

				if (empty($time)) {
					return 'ignore';
				}

				if (count($time) >= 5) {
					list($time, $hour, $minute, $second, $milisecond) = $time;
				} else {
					list($time, $hour, $minute, $second) = $time;
				}

				$currentDurationSeconds = $this->convertToSeconds($hour, $minute, $second);
			}
		}

		$progress = round(($currentDurationSeconds / $totalDurationSeconds) * 95);

		return $progress;
	}

	/**
	 * Publishes the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function publish($options = array())
	{
		$privacyData = '';

		if ($this->table->params) {
			$privacyData = json_decode($this->table->params);

			//lets perform some testing here before we proceed.
			if (isset($privacyData->rule)) {
				if ($privacyData->processed) {
					$privacyData = '';
				} else {
					//this mean its a new video. so we need to remove the privacy data.
					$this->table->params = '';
				}
			} else {
				$privacyData = '';
			}
		}

		$this->table->state = SOCIAL_VIDEO_PUBLISHED;

		// @points: Give points to the author for uploading a new video.
		if ($this->isNew()) {
			ES::points()->assign('video.upload', 'com_easysocial', $this->getAuthor()->id);
			ES::badges()->log('com_easysocial', 'videos.create', $this->getAuthor()->id, '');
		}

		$state = $this->table->store();

		// Generate a new stream item when the video is published.
		$createStream = isset($options['createStream']) ? $options['createStream'] : true;

		if ($createStream) {
			$this->createStream('create', $privacyData);
		}

		// update video isnew flag to false
		$this->table->isnew = 0;
		$this->table->store();

		// trigger video smart search plugin for indexing.
		$this->syncIndex();

		return $state;
	}

	/**
	 * Sync's the user record with Joomla smart search
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function syncIndex()
	{
		// Determines if this is a new account
		$isNew = $this->isNew();

		// Trigger our own finder plugin
		JPluginHelper::importPlugin('finder');

		ESDispatcher::trigger('onFinderAfterSave', array('easysocial.videos', &$this->table, $isNew));
		ESDispatcher::trigger('onFinderChangeState', array('easysocial.videos', $this->table->id, $this->table->state));
	}

	/**
	 * Unpublishes the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function unpublish()
	{
		$this->table->state = SOCIAL_VIDEO_UNPUBLISHED;

		$state = $this->table->store();

		// @TODO: Give points to the author for creating a new video

		// @TODO: Should we delete the stream as well?

		// Trigger our own finder plugin
		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderChangeState', array('easysocial.videos', $this->table->id, $this->table->state));

		return $state;
	}

	/**
	 * Sets a video as featured
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function setFeatured()
	{
		$this->table->featured = SOCIAL_VIDEO_PUBLISHED;
		$state = $this->table->store();

		// Generate a stream item for this featured
		$this->createStream('featured');

		// @points: video.featured
		ES::points()->assign('video.featured', 'com_easysocial', $this->getAuthor()->id);

		return $state;
	}

	/**
	 * Removes a featured video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function removeFeatured()
	{
		$this->table->featured = false;
		$state = $this->table->store();

		// Generate a stream item for this featured
		$this->removeStream('featured');

		// @points: video.unfeatured
		ES::points()->assign('video.unfeatured', 'com_easysocial', $this->getAuthor()->id);

		return $state;
	}

	/**
	 * Generates a new stream item for the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function createStream($verb, $privacy = '')
	{
		// When a video is published, we should generate a stream for it.
		$stream = ES::stream();

		$template = $stream->getTemplate();
		$actor = $this->getAuthor();

		// Set the actor of the stream item
		$template->setActor($actor->id, SOCIAL_TYPE_USER);

		// Set the context
		$template->setContext($this->table->id, SOCIAL_TYPE_VIDEOS);

		// Set the verb
		$template->setVerb($verb);

		// If this is created within a cluster, it should be mapped to the respective cluster
		if ($this->table->uid && $this->table->type && $this->table->type != SOCIAL_TYPE_USER) {
			$template->setCluster($this->table->uid, $this->table->type);

			// Set the correct post as for page videos
			if ($this->table->type == SOCIAL_TYPE_PAGE) {
				$template->setPostAs($this->table->post_as);
			}
		}

		// Set stream privacy
		if ($privacy) {

			$value = $privacy->value;
			if (is_string($value)) {
				$privacyLib = ES::privacy();
				$value = $privacyLib->toValue($value);
			}

			$template->setAccess('videos.view', $value, $privacy->custom);
		} else {
			$template->setAccess('videos.view');
		}

		// Generate the stream item now.
		$result = $stream->add($template);

		// update video isnew flag to false
		$this->table->isnew = 0;
		$this->table->store();

		return $result;
	}

	/**
	 * Removes a stream item given the verb
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function removeStream($verb, $actorId = '')
	{
		// When a video is published, we should generate a stream for it.
		$stream = ES::stream();

		$result = $stream->delete($this->table->id, SOCIAL_TYPE_VIDEOS, $actorId, $verb);

		return $result;
	}

	/**
	 * Deletes the video from the site
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function delete()
	{
		// Remove the comments related to this video
		$comments = $this->getComments();
		$comments->delete();

		// Remove the likes related to this video
		$likes = ES::likes();
		$likes->delete($this->id, SOCIAL_TYPE_VIDEOS, 'create', $this->type, null, true);

		// Assign points when a video is deleted
		ES::points()->assign('video.remove', 'com_easysocial', $this->getAuthor()->id);

		// Remove the stream items related to this video
		$this->removeStream('featured');
		$this->removeStream('create');

		// Remove the search results
		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderAfterDelete', array('easysocial.videos', $this->table));

		$this->deleteNotifications();

		// Remove any tags that are associated to this video
		$tag = ES::tag($this->table->id, SOCIAL_TYPE_VIDEO);
		$tag->cleanTags();

		// Remove from the database
		$state = $this->table->delete();

		// Remove files related to this video
		$this->deleteStorage();

		return $state;
	}

	/**
	 * Delete notification that related to the video
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function deleteNotifications()
	{
		$url = $this->getPermalink(true, false, false);

		$db = ES::db();
		$sql = $db->sql();

		$query = 'DELETE FROM `#__social_notifications`';
		$query .= ' WHERE `url` = ' . $db->Quote($url);

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Delete video files from the site
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function deleteStorage()
	{
		$path = $this->getStoragePath();
		$state = true;

		// If stored it from Amazon s3, we need to remove it from Amazon as well
		if ($this->storage !== SOCIAL_STORAGE_JOOMLA) {
			$relativePath = $this->getRelativeStoragePath();
			$storage = ES::storage($this->storage);
			$state = $storage->delete($relativePath, true);
		}

		// Ensure that its folder is deleted from the local server regardless its storage type
		if (JFolder::exists($path)) {
			JFolder::delete($path);
		}

		ES::storage()->syncUsage($this->user_id);

		return $state;
	}

	/**
	 * Delete video thumbnail from the site
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function deleteThumbnail()
	{
		$relativePath = $this->getRelativeThumbnailPath();
		$path = JPATH_ROOT . '/' . $relativePath;

		if (!JFile::exists($path)) {
			return false;
		}

		$state = JFile::delete($path);

		return $state;
	}

	/**
	 * Determines if the video contains a location
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function hasLocation()
	{
		$location = $this->getLocation();

		if ($location->hasAddress()) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the truncation duration for videos if necessary
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getTruncationLength()
	{
		static $data = array();

		if (!isset($data[$this->user_id])) {
			// Check the user's acl to see if they are restricted
			$user = ES::user($this->user_id);
			$acl = $user->getAccess();

			// Determine if we should truncate videos from this user
			if (!$acl->get('videos.truncate', false)) {
				$data[$this->user_id] = false;
				return $data[$this->user_id];
			}

			$data[$this->user_id] = $acl->get('videos.truncatelength');
		}

		return $data[$this->user_id];
	}

	/**
	 * Validates the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function validate($file = [], $options = [])
	{
		// Ensure that it has a category
		if (!$this->table->category_id) {
			$this->setError('COM_EASYSOCIAL_INVALID_CATEGORY_PROVIDED');
			return false;
		}

		// Ensure that the video has a title
		if (!$this->table->title) {
			$this->setError('COM_EASYSOCIAL_VIDEOS_INVALID_TITLE');
			return false;
		}

		// If this is a video link, ensure a link is provided
		if ($this->isLink() && !$this->table->path) {
			$this->setError('COM_EASYSOCIAL_VIDEOS_ENTER_VIDEO_URL');
			return false;
		}

		// Ensure that the video link is valid
		if ($this->isLink() && $this->table->path && !$this->isValidUrl($this->table->path)) {
			$this->setError('COM_EASYSOCIAL_VIDEOS_ENTER_VALID_URL');
			return false;
		}

		$skipFileValidation = isset($options['skipFileValidation']) ? $options['skipFileValidation'] : false;

		if ($skipFileValidation) {
			return true;
		}

		// If this is a new video we want to validate the file
		if ($this->isUpload() && $this->isNew() && empty($file['tmp_name'])) {
			$this->setError('COM_EASYSOCIAL_VIDEOS_UPLOAD_FILE');
			return false;
		}

		// If file is provided, we need to test if it is valid
		if ($this->isUpload() && $file) {

			$valid = $this->isVideoValid($file);

			if (!$valid) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Format the url correctly.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function format($link=false)
	{
		if (preg_match("#https?://#", $link) === 0){
			$link = 'https://' . $link;
		}

		if (strpos($link, 'youtu.be') > 0) {
			$link = preg_replace('~^https?://youtu\.be/([a-z\d#$%^&*()+=\_\-\[\]\';,.\/{}|":<>?\~\\\\]+)$~i', 'https://www.youtube.com/watch?v=$1', $link);

			// Convert the second question mark to & if detected the URL contain ?list or ?t
			$link = str_replace('?list=', '&list=', $link);
			$link = str_replace('?t=', '&t=', $link);
		}

		if (strpos($link, 'youtube.com/shorts/') > 0) {
			// https://youtube.com/shorts/4NZ2LttVSoA?feature=share
			// Get this URL from the YouTube mobile app > your channel > manage video > Click share from your uploaded video item
			$pattern = '/youtube.com\/shorts\/([^?]*)/is';

			preg_match($pattern, $link, $matches);

			if ($matches && isset($matches[1]) && $matches[1]) {
				$link = 'https://www.youtube.com/watch?v=' . $matches[1];
			}
		}

		return $link;
	}

	/**
	 * Render video headers
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderHeaders()
	{
		$obj = new stdClass();

		$title = ES::string()->escape($this->getTitle());

		$obj->title = $title;
		$obj->description = $this->description;
		$obj->image = $this->getThumbnail();
		$obj->url = $this->getExternalPermalink();
		$obj->video = $this;

		ES::meta()->setMetaObj($obj);
	}

	/**
	 * function to determine if this embed is a facebook video or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isFacebookEmbed()
	{
		if ($this->isLink() && (stristr($this->path, '://www.facebook.com') !== false || stristr($this->path, '://fb.watch') !== false)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the video is featured
	 *
	 * @since   3.2
	 * @access  public
	 */
	public function isFeatured()
	{
		return (bool) $this->table->featured;
	}

	/**
	 * function to determine if this embed is a facebook video or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isTwitterEmbed()
	{
		if ($this->isLink() && stristr($this->path, '://twitter.com') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * function to determine if this embed is a gfycat video or not.
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isGfycatEmbed()
	{
		if ($this->isLink() && stristr($this->path, '://gfycat.com') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * function to determine if this embed is a Tiktok or not.
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public function isTiktok()
	{
		if ($this->isLink() && stristr($this->path, '.tiktok.com') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * function to determine if this embed is a twitch video or not.
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function isTwitchEmbed()
	{
		if ($this->isLink() && (stristr($this->path, 'twitch.tv') !== false || stristr($this->path, 'clips.twitch.tv') !== false)) {
			return true;
		}

		return false;
	}


	/**
	 * function to determine if this embed is a YouTube video or not.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isYouTubeEmbed()
	{
		if ($this->isLink() && (stristr($this->path, 'youtube.com') !== false || stristr($this->path, 'youtu.be') !== false)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if we should render this video using embedded code
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function renderEmbedExternalVideo()
	{
		if ($this->isTwitterEmbed()) {
			return true;
		}

		if ($this->isGfycatEmbed()) {
			return true;
		}

		if ($this->isTiktok()) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the embed video size is huge during load
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isLargeEmbed()
	{
		// Only support link embed
		if (!$this->isLink()) {
			return false;
		}

		// So far only facebook has this issue
		if (!$this->isFacebookEmbed()) {
			return false;
		}

		// In mobile will have issue with autoplay if we implement this method
		// In addition, the embed size in mobile seems not as impactful as desktop version.
		if ($this->isMobile()) {
			return false;
		}

		return true;
	}

	/**
	 * Determine facebook embed ratio for mobile.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFacebookEmbedRatioInMobile()
	{
		$ratio = '';

		if ($this->isMobile() && $this->isLink() && $this->isFacebookEmbed()) {
			$ratio = $this->getRatioString();
		}

		return $ratio;
	}


	public function getRatioString()
	{
		$params = json_decode($this->table->params);

		$height = false;

		if (property_exists($params->oembed, 'height') && $params->oembed->height) {
			$width = $params->oembed->width;
			$height = $params->oembed->height;
		} else if (property_exists($params->opengraph, 'video_height') && $params->opengraph->video_height) {
			$width = $params->opengraph->video_width;
			$height = $params->opengraph->video_height;
		}

		if ($height) {
			if ($width == $height) {
				return 'is-1by1';
			}

			// for now we assume if height is greatr than width, its 9x16 format.
			if ($height > $width) {
				return 'is-9by16';
			}
		}

		return '';
	}

	/**
	 * Retrieve the navigation for the single video item
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getNavigation()
	{
		static $data = [];

		if (!isset($data[$this->id])) {
			if (!$this->config->get('video.layout.item.navigation')) {
				$data[$this->id] = false;
				return $data[$this->id];
			}

			$model = ES::model('Videos');
			$data[$this->id] = $model->getNavigation($this);
		}

		return $data[$this->id];
	}

	/**
	 * Exports video data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $options = array())
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$extended = ES::normalize($options, 'extended', true);

		$creator = $this->getAuthor();

		if ($this->isCreatedInCluster() && $this->table->post_as === 'page') {
			$creator = ES::cluster(SOCIAL_TYPE_PAGE, $this->table->uid);
		}

		$result = array(
			'id' => $this->id,
			'title' => $this->getTitle(),
			'description' => $this->getDescription(),
			'category' => $this->getCategory()->toExportData($viewer, $options),
			'duration' => $this->getDuration(),
			'created' => $this->table->created,
			'hits' => $this->getHits(),
			'isExternal' => $this->isLink(),
			'isFeatured' => $this->table->featured,
			'thumbnail' => $this->getThumbnail(),
			'tagged' => $this->getEntityTags(),
			'hashtags' => $this->getTags(),
			'isOwner' => $this->getAuthor()->id === $viewer->id,
			'uid' => $this->uid,
			'type' => $this->type,
			'externalPermalink' => $this->getExternalPermalink(),
			'formattedCreatedDate' => $this->getCreatedDate()->format(JText::_('COM_EASYSOCIAL_DATE_DMY'))
		);

		$link = $this->getFile();

		if ($this->isLink()) {
			$link = $this->table->path;

			// Convert this to mobile link [experimental]
			$link = preg_replace('~^https?://www\.youtube\.com/([a-z\d#$%^&*()+=\_\-\[\]\';,.\/{}|":<>?\~\\\\]+)$~i', 'https://m.youtube.com/$1', $link);
		}

		$result['link'] = $link;


		if ($extended) {

			$streamId = $this->getStreamId('create');

			// Retrieve the comments library
			$comments = $this->getComments('create', $streamId);

			// Retrieve the likes library
			$likes = $this->getLikes('create', $streamId);

			$result['likes'] = $likes;
			$result['comments'] = $comments;
			$result['author'] = $creator->toExportData($viewer);
		}



		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}

	/**
	 * Remove custom thumbnail and revert to use original thumbnail.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function revertThumbnail()
	{
		if ($this->table->thumbnail_custom) {
			// remove the custom thumbnail file.
			$prevStorage = $this->getStoragePath() . '/' . basename($this->table->thumbnail_custom);

			if (JFile::exists($prevStorage)) {
				JFile::delete($prevStorage);
			}
		}

		$this->table->thumbnail_custom = '';
		$state = $this->table->store();

		return $state;
	}

	/**
	 * Process uploaded file to be used as the thumbnail
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function processCustomThumbnail($file)
	{

		if ($this->isUpload()) {

			$thumbWidth = '1280';
			$thumbHeight = '720';

			// Get the storage path to the video
			$fileName = $file['name'];
			$fileName = md5($fileName) . '_custom.jpg';

			// Construct the storage path
			$storage = $this->getStoragePath() . '/' . $fileName;

			$prevStorage = null;
			if ($this->table->thumbnail_custom) {
				$prevStorage = $this->getStoragePath() . '/' . basename($this->table->thumbnail_custom);
			}

			$source = $file['tmp_name'];

			$lib = ES::image();
			$lib->load($source, $fileName);

			if (!$lib->isValid()) {
				throw ES::exception('COM_EASYSOCIAL_PHOTOS_INVALID_FILE_PROVIDED');
			}

			// get the image dimension based on the original thumbnail
			$oriPath = $this->getStoragePath() . '/' . basename($this->table->thumbnail);
			$oriInfo = getimagesize($oriPath);
			$oriWidth = $oriInfo[0];
			$oriHeight = $oriInfo[1];

			// we will create the new thumbnail based on this ratio
			$ratio = $oriWidth / $oriHeight;

			$width = $lib->getWidth();
			$height = $lib->getHeight();

			$targetHeight = $height;
			$targetWidth = $width;

			if ($ratio < 1) {
				$targetWidth = $height * $ratio;
			} else {
				$targetHeight = $width / $ratio;
			}

			$lib->resize($targetWidth, $targetHeight, false);
			$state = $lib->save($storage);

			if (!$state) {
				throw ES::exception('COM_EASYSOCIAL_PHOTOS_UPLOAD_ERROR_STORING_DB');
			}

			if ($state) {

				// Returns the relative path
				$relativePath = $this->getRelativeStoragePath() . '/' . $fileName;

				$this->table->thumbnail_custom = $relativePath;
				$this->table->store();

				// now delete the previous one.

				if ($prevStorage && JFile::exists($prevStorage)) {
					JFile::delete($prevStorage);
				}

				$url = rtrim(JURI::root(), '/') . '/' . $relativePath;
				return $url;
			}
		}

		return true;
	}

	private function thumbnailRatio($a, $b)
	{
		$gcd = function($a, $b) use (&$gcd) {
			return ($a % $b) ? $gcd($b, $a % $b) : $b;
		};
		$g = $gcd($a, $b);
		return array($a/$g, $b/$g);
	}

	/**
	 * Render repost link
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function reposts($clusterId = 0, $clusterType = '')
	{
		$repost = ES::repost($this->table->id, SOCIAL_TYPE_VIDEOS, $this->table->type, $clusterId, $clusterType);

		return $repost;
	}

	/**
	 * Notify user when the video is created
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function notify($scheduled = false)
	{
		$allowed = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT);

		// We only process notification for cluster item
		if (!in_array($this->type, $allowed)) {
			return false;
		}

		// Only notify followers if the video created by Page user
		if ($this->type == SOCIAL_TYPE_PAGE && $this->post_as == SOCIAL_TYPE_USER) {
			return false;
		}

		$cluster = ES::cluster($this->type, $this->uid);

		if (!$cluster->id) {
			return false;
		}

		$options = array();
		$options['id'] = $this->id;
		$options['userId'] = $this->user_id;
		$options['title'] = $this->title;
		$options['description'] = $this->getDescription();
		$options['permalink'] = $this->getPermalink();

		if ($scheduled) {
			$options['scheduled'] = $scheduled;
		}

		$cluster->notifyMembers('video.create', $options);

		return true;
	}
}

class SocialVideoDuration
{
	public $seconds = null;

	public function __construct($seconds = '')
	{
		$this->seconds = $seconds;
	}

	public function set($seconds)
	{
		$this->seconds = $seconds;
	}

	public function raw()
	{
		return $this->seconds;
	}

	public function format()
	{
		$empty = new DateTime('@0');

		$seconds = (int) $this->seconds;
		$current = new DateTime('@' . $seconds);

		$diff = $empty->diff($current)->format('%H:%I:%S');;

		return $diff;
	}
}
