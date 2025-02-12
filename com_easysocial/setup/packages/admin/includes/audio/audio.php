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

class SocialAudio extends EasySocial
{
	public $table = null;

	// Determines the current type request
	public $uid = null;
	public $type = null;

	// Allowed audio types
	private $allowed = ['audio/mpeg3', 'audio/mpeg', 'audio/wav', 'audio/midi', 'audio/mp3', 'audio/x-wav', 'audio/x-m4a'];

	public function __construct($uid = null, $type = null, $key = null)
	{
		parent::__construct();

		if ($uid instanceof SocialTableAudio) {
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
			$this->table = ES::table('Audio');

			if ($key) {
				$this->load($key);
			}
		}

		$this->adapter = $this->getAdapter();
	}

	/**
	 * Loads the audio table
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function load($key)
	{
		if ($key instanceof SocialTableAudio) {
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
	 * @since   2.1
	 * @access  public
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
	 * @since   2.1
	 * @access  public
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array([$this->adapter, $method], $arguments);
	}

	/**
	 * Allow caller to bind data to the table
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function bind($data)
	{
		return $this->table->bind($data);
	}

	/**
	 * Method to check if audio's title already exists or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function titleExists($title, $userId, $ignoreId = 0)
	{
		$model = ES::model('Audios');
		return $model->isTitleExists($title, $userId, $ignoreId);
	}

	/**
	 * Creates a new audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function save($data, $file = array(), $options = array())
	{
		// Ensure that the site's language file is loaded
		ES::language()->loadSite();

		// Determines if this is a new audio
		$isNew = $this->isNew();

		// Set the current user id only if this is a new audio, otherwise whenever the audio is edited,
		// the owner get's modified as well.
		if ($isNew) {
			$this->table->user_id = $this->my->id;
		}

		// Map the audio data
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
			$this->table->uid = $this->my->id;
			$this->table->type = SOCIAL_TYPE_USER;
		}

		// Determines if the requester has exceeded their limit
		$exceededLimit = $this->hasExceededLimit();

		// If the audio is a new audio, and their limits exceeded, do not allow them to create audio
		if ($isNew && $exceededLimit) {
			$this->setError(JText::_("COM_ES_AUDIO_EXCEEDED_LIMIT"));
			return false;
		}

		// If this is a new audio, ensure that the requester is allowed to upload audios
		$allowCreation = $this->allowCreation();

		if ($isNew && !$allowCreation) {
			$this->setError('COM_ES_AUDIO_NOT_ALLOWED_CREATE_AUDIO');
			return false;
		}

		// check for video title uniqueness accross user.
		// since title in audio also used as permalink alias,
		// we need to unsure the uniqueness of the title from a user.
		$check = true;
		$i = 0;
		do {
			if ($this->titleExists($this->table->title, $this->table->user_id, $this->table->id)) {
				$this->table->title = $this->table->title . '-' . ++$i;
				$check = true;
			} else {
				$check = false;
			}
		} while ($check);


		// Set the audio to be under pending processing state since this is a new audio
		if (!$this->table->id || ($this->table->id && $this->isUpload() && $file)) {
			$this->table->state = SOCIAL_AUDIO_PENDING;
		}

		// Audio links
		if ($this->table->isLink()) {

			$this->table->path = $data['link'];

			// Grab the audio data
			$crawler = ES::crawler();
			$scrape = $crawler->scrape($this->table->path);

			// Set the audio params with the scraped data
			$this->table->params = json_encode($scrape);

			// Set the audio's duration
			// $this->table->duration = @$scrape->oembed->duration;
			$this->table->duration = ($scrape && isset($scrape->oembed) && isset($scrape->oembed->duration)) ? $scrape->oembed->duration : 0;

			$params = json_decode($this->table->params);

			if (isset($params->oembed->thumbnail) && (!isset($data['albumartData']) || empty($data['albumartData'])) ) {
				$this->table->albumart_source = 'audio';
			}
		}

		// If upload from story form means everything is checked out and no need to validate again.
		// this fromStory is set from audio controller under file uploads.
		$isFromStory = isset($options['isFromStory']) ? $options['isFromStory'] : false;

		// Validate the audio
		if ((!$isNew && $file || $isNew) && !$isFromStory) {

			$valid = $this->validate($file, $options);

			if (!$valid) {
				return false;
			}
		}

		// If this audio belongs to Page, we need to set the post_as to 'page'
		// So that the author will always be the Page itself
		if ($this->table->type == SOCIAL_TYPE_PAGE && is_null($this->table->post_as)) {
			$this->table->post_as = SOCIAL_TYPE_PAGE;
		}

		if (isset($options['processMetadata']) && $this->isUpload() && $this->allowEncode()) {
			$this->processMetadata($file);
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

		if (!$this->table->cover) {
			$this->table->cover = '';
		}

		if (!$this->table->file_title) {
			$this->table->file_title = '';
		}

		if (!$this->table->duration) {
			$this->table->duration = '';
		}

		if (!$this->table->artist) {
			$this->table->artist = '';
		}

		if (!$this->table->album) {
			$this->table->album = '';
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

		// Save the audio
		$state = $this->table->store();

		// send notification here only for the clusters without submit through story form
		// submit through story form will proceed this from the audio app
		// $isFromStory = isset($options['isFromStory']) ? $options['isFromStory'] : null;

		if (!$isFromStory && $state && $isNew) {
			$this->notify();
		}

		// Bind the tags
		if (isset($data['tags'])) {
			$this->insertTags($data['tags'], SOCIAL_TYPE_USER);
		}

		// Bind hashtags
		if (isset($data['hashtags'])) {
			$this->insertTags($data['hashtags'], 'tags');
		}

		// Bind the album art
		if ((isset($data['albumartData']) && $data['albumartData']) && $this->table->albumart_source == 'upload') {
			$this->insertAlbumArt($data['albumartData'], $isNew);
		}

		$privacyData = '';
		if (isset($data['privacy'])) {

			$privacyData = new stdClass();
			$privacyData->rule = 'audios.view';
			$privacyData->value = $data['privacy'];
			$privacyData->custom = $data['privacyCustom'];

			// Always set audio's owner to be actor of the privacy #2203
			$privacyData->userId = $this->table->user_id;

			$this->insertPrivacy($privacyData);
		}

		// Assign points and badge when there is a new audio created
		if ($isNew) {
			ES::points()->assign('audio.upload', 'com_easysocial', $this->getAuthor()->id);
			ES::badges()->log('com_easysocial', 'audios.create', $this->getAuthor()->id, '');
		}

		// check if we should create stream or not.
		$createStream = (isset($options['createStream']) && $options['createStream'] && $isNew && !$isFromStory) ? true : false;

		if ($createStream) {
			$this->createStream('create', $privacyData);
		}

		// Process link audios
		if ($this->table->isLink()) {
			// $this->getEmbedAlbumArt();
			$this->table->state = SOCIAL_AUDIO_PUBLISHED;
			$this->table->store();

			// if this is a external audios, let index it into joomla smart search
			$this->syncIndex();
		}

		// If the audio source is upload, we need to perform additional stuffs
		if ($this->isUpload()) {

			// Determines if the saving process should verify the uploaded file or not.
			if ($file) {

				$this->table->size = $file['size'];

				if (!$isFromStory) {

					// Ensure that this is not being edited
					$valid = $this->isAudioValid($file);

					if (!$valid) {
						$this->setError(JText::_("COM_ES_AUDIO_INVALID_AUDIO_FILE_PROVIDED"));

						return false;
					}

					// Set the original file title.
					$this->table->file_title = $file['name'];

					// Copy the file to the correct folder
					$path = $this->copyFileFromTmp($file);

					// Store the original audio path
					$this->table->original = $path;
				}
			}

			// need to store the privacy value temporary into params
			if ($privacyData) {
				$privacyData->processed = 0;
				$this->table->params = json_encode($privacyData);
			}

			// Re-save the audio object to get the correct path
			$state = $this->table->store();

			if ($state) {

				// Log the usage when the audio is preparing to be encode
				if ($this->table->state == SOCIAL_AUDIO_PENDING) {
					ES::storage()->syncUsage($this->table->user_id);
				}

				if ($this->table->state == SOCIAL_AUDIO_PUBLISHED) {
					$this->syncIndex();
				}
			}

			// if the audio encoder is disabled, we directly process this audio
			if (!$this->allowEncode()) {
				// override the createStream flag.
				$createStream = $this->isNew() ? true : $createStream;

				// We dont want to create stream if this is user uploading file
				$isUploading = isset($options['isUploading']) ? $options['isUploading'] : false;

				if ($isUploading) {
					$createStream = false;
				}

				$this->processUnencodedAudio($createStream);
			}
		}

		return $state;
	}

	/**
	 * Insert tags for this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function insertTags($tags = array(), $type = SOCIAL_TYPE_USER)
	{
		if (!is_array($tags)) {
			$tags = str_replace(',', '', $tags);
			$tags = explode('#', $tags);
		}

		$tag = ES::tag($this->table->id, SOCIAL_TYPE_AUDIO);
		$results = $tag->insert($tags, $type);

		if ($type == SOCIAL_TYPE_USER && $tags) {
			foreach ($tags as $userId) {

				if ($userId == $this->my->id) {
					continue;
				}

				$user = ES::user($userId);

				// Set the email options
				$emailOptions = array(
						'title' => 'COM_ES_AUDIO_EMAILS_TAGGED_IN_AUDIO_SUBJECT',
						'template' => 'site/audios/tagged',
						'audioTitle' => $this->getTitle(),
						'audioThumbnail' => $this->getAlbumArt(),
						'audioPermalink' => $this->getPermalink(true, true),
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
				ES::notify('audios.tagged', array($user->id), $emailOptions, $systemOptions);
			}
		}

		return $results;
	}

	/**
	 * Insert privacy for this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function insertPrivacy($privacy)
	{
		$privacyLib = ES::privacy();
		$privacyLib->add($privacy->rule, $this->table->id, SOCIAL_TYPE_AUDIOS, $privacy->value, $privacy->userId, $privacy->custom);

		// we need to further update privacy access on these medias table. #3289
		$access = $privacyLib->toValue($privacy->value);

		$model = ES::model('privacy');
		$model->updateMediaAccess(SOCIAL_TYPE_AUDIOS, $this->table->id, $access, $privacy->custom);

		// If this item isn't new, we also need to update it's stream item if there are any
		if (!$this->isNew()) {
			$model->updateStreamAccess(SOCIAL_TYPE_AUDIOS, $this->table->id, $access, $privacy->custom, 'create');
		}

		// now we need to reload the this->table
		if ($this->table->id) {
			$this->table->load($this->table->id);
		}
	}

	/**
	 * Determines if the user can remove a tag from the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function canRemoveTag($tag = null)
	{
		// Site admins should always be able to delete tags
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Audio owners should be able to remove tags
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
	 * Determines if user can embed audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function canEmbed()
	{
		if (!$this->config->get('audio.enabled')) {
			return false;
		}

		if (!$this->config->get('audio.embeds')) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// if this is editing #4574
		if ($this->isLink() && $this->table->id && $this->table->user_id == $this->my->id) {
			return true;
		}

		if (!$this->allowEmbed()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if this audio encoder is enabled
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function allowEncode()
	{
		return $this->config->get('audio.allowencode');
	}

	/**
	 * Determines if the current user can upload audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function canUpload()
	{
		// Check if the feature is enabled
		if (!$this->config->get('audio.enabled')) {
			return false;
		}

		if (!$this->config->get('audio.uploads')) {
			return false;
		}

		// Check if the ffmpeg is specified correctly
		if ($this->config->get('audio.allowEncode') && !$this->config->get('audio.encoder')) {
			return false;
		}

		// Site admin always able to upload the audios
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
	 * Determine if the user can add playlist or not
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function canAddPlaylist()
	{
		return true;
	}

	/**
	 * Determine if file type is an audio
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function isAudioFile($file)
	{
		// Check for validity of the audio
		if (!in_array($file['type'], $this->allowed)) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the file that is uploaded is valid
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isAudioValid($file)
	{
		$adapter = $this->getAdapter();

		// Check for upload limit
		$maxSize = $adapter->getUploadLimit();
		$maxSize = ES::math()->convertBytes($maxSize);

		if ($file['size'] > $maxSize) {
			$this->setError(JText::sprintf('COM_ES_AUDIO_EXCEEDED_ALLOWED_FILESIZE', $this->getUploadLimit()));
			return false;
		}

		// Check for validity of the audio
		if (!$this->isAudioFile($file)) {
			$this->setError('COM_ES_AUDIO_INVALID_AUDIO_FILE');
			return false;
		}

		return true;
	}

	/**
	 * Checks for valid audio url
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isValidUrl($url)
	{
		$pattern = "/(^|\\s)(https?:\\/\\/)?(([a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.([a-z]{2,6}))|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(:[0-9]{1,5})?(\\/.*)?/uism";

		$match = preg_match($pattern, $url);

		if (!$match) {
			return false;
		}

		if (stristr($url, 'spotify.com') === false && stristr($url, 'soundcloud.com') === false ) {
			return false;
		}

		if (stristr($url, 'spotify.com') !== false && !$this->config->get('audio.embed.spotify', true)) {
			return false;
		}

		if (stristr($url, 'soundcloud.com') !== false && !$this->config->get('audio.embed.soundcloud', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if this is a new audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isNew()
	{
		return $this->table->id ? (bool) $this->table->isnew : true;
	}

	/**
	 * Copies the file from the temporary folder
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function copyFileFromTmp($file)
	{
		// Get the storage path for this audio
		$storagePath = $this->getStoragePath();

		// We need to rename the original file name.
		if (!$this->allowEncode()) {
			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

			$storagePath .= '/' . md5($file['name']) . '.' . $ext;
		} else {
			$storagePath .= '/' . md5($file['name']);
		}

		// Copy the original audio file into the storage path
		$state = JFile::copy($file['tmp_name'], $storagePath);

		return $storagePath;
	}

	/**
	 * Retrieves the cluster that is associated with the audio
	 *
	 * @since   2.1
	 * @access  public
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
	 * @since   2.1
	 * @access  public
	 */
	public function getContainer()
	{
		$container = ltrim($this->config->get('audio.storage.container'), '/');
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
	 * Retrieve supported providers
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSupportedProviders()
	{
		$supported = array();

		$allowSpotify = $this->config->get('audio.embed.spotify', true);
		$allowSoundcloud = $this->config->get('audio.embed.soundcloud', true);

		if ($allowSpotify) {
			$supported[] = 'Spotify';
		}

		if ($allowSoundcloud) {
			$supported[] = 'Soundcloud';
		}

		return $supported;
	}

	/**
	 * Ensures that the container folder exists
	 *
	 * @since   2.1
	 * @access  public
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
	 * @since   2.1
	 * @access  public
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
	 * Retrieve a temporary path
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTmpStoragePath()
	{
		$date = ES::date();

		// Create a temporary folder for this session.
		$session = JFactory::getSession();
		$uid = md5($session->getId() . 'album_art');
		$path = SOCIAL_MEDIA . '/tmp/' . $uid . '_albumart';

		// If the folder exists, delete them first.
		if (JFolder::exists($path)) {
			JFolder::delete($path);
		}

		// Create folder if necessary.
		ES::makeFolder($path);

		// Re-generate the storage path since we do not want to store the JPATH_ROOT
		$path = str_replace(JPATH_ROOT, '', $path);

		return $path;

	}

	/**
	 * Retrieves the relative path
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getRelativeStoragePath()
	{
		$container = ltrim($this->config->get('audio.storage.container'), '/');

		if ($this->table->id) {
			$container .= '/' . $this->table->id;
		}

		return $container;
	}

	/**
	 * Gets the log file path
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getLogFilePath()
	{
		$storagePath = $this->getStoragePath();
		$logFilePath = $storagePath . '/' . md5($this->table->id) . '.log';

		return $logFilePath;
	}

	/**
	 * Retrieves the audio item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getItem()
	{
		return $this->table;
	}

	/**
	 * Retrieves the path to the audio file
	 *
	 * @since   2.1
	 * @access  public
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
	 * Retrieves the path to the audio file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getRelativeFilePath()
	{
		return $this->table->path;
	}

	/**
	 * Determines if this audio belongs to cluster
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isCreatedInCluster()
	{
		if ($this->table->uid && $this->table->type && $this->table->type != SOCIAL_TYPE_USER) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the audio is in pending processing mode
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isPendingProcess()
	{
		return $this->table->state == SOCIAL_AUDIO_PENDING;
	}

	/**
	 * Determines if the audio item is being processed.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isProcessing()
	{
		return $this->table->state == SOCIAL_AUDIO_PROCESSING;
	}

	/**
	 * Determines if the audio is published
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isPublished()
	{
		return $this->table->state == SOCIAL_AUDIO_PUBLISHED;
	}

	/**
	 * Determines if the audio is featured
	 *
	 * @since   3.2
	 * @access  public
	 */
	public function isFeatured()
	{
		return (bool) $this->table->featured;
	}

	/**
	 * Determines if this audio is an upload source
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isUpload()
	{
		return $this->table->isUpload();
	}

	/**
	 * Determines if this audio is a link source
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isLink()
	{
		return $this->table->isLink();
	}

	/**
	 * Creates a new log file
	 *
	 * @since   2.1
	 * @access  public
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
	 * Processes an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function process()
	{
		// If the audio is already in the midst of processing, we shouldn't allow them to process this again
		if ($this->isProcessing()) {
			$this->setError(JText::_("COM_ES_AUDIO_ALREADY_PROCESSING"));
			return false;
		}

		// Only process the audio uploads
		if ($this->table->isUpload()) {
			$this->processUploadedAudio();
		}
	}

	/**
	 * Process unencoded audio file
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function processUnencodedAudio($createStream = false)
	{
		// Set the output file
		$fileName = basename($this->table->original);

		$this->table->path = $this->getRelativeStoragePath() . '/' . $fileName;

		$this->table->state = SOCIAL_AUDIO_PUBLISHED;

		// Generate a new stream item when the audio is published.
		if ($createStream) {

			$privacyData = $this->getPrivacyData();

			$this->createStream('create', $privacyData);
		}

		// here we need to the isnew to false.
		$this->table->isnew = 0;

		$this->table->store();

		return;
	}

	/**
	 * Process uploaded audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function processUploadedAudio()
	{
		// Mark the audio as processing
		$this->table->processing();

		// We want to output the completed stuff to our own log files
		$logFile = $this->getLogFilePath();

		// Set the duration of the audio
		$duration = $this->extractDuration();
		$this->table->duration = $duration->raw();

		// Generate a unique name for this file
		$fileName = $this->generateFileName();

		// Get the path to the output.
		$storagePath = $this->getStoragePath() . '/' . $fileName;

		// Set the output file
		$this->table->path = $this->getRelativeStoragePath() . '/' . $fileName;

		// Get the bitrate value
		$bitrate = $this->config->get('audio.bitrate');

		// Load up the ffmpeg library
		$ffmpeg = ES::ffmpeg(SOCIAL_TYPE_AUDIO);
		$ffmpeg->encode($this->table->original, $storagePath, $logFile, $bitrate);

		// Process the album art
		if ($this->table->albumart_source == 'audio') {
			$this->processAlbumArt();
		}

		// Process the waveform
		// Comment this out first. For future use
		// $this->processWaveform();

		// Update the audio object now.
		$this->table->store();
	}

	/**
	 * Generate waveform image
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function processWaveform()
	{
		// Only applicable for uploaded audio
		if ($this->isUpload()) {
			$ffmpeg = ES::ffmpeg(SOCIAL_TYPE_AUDIO);

			// Image file name
			$fileName = md5($this->table->title) . '.png';

			$storage = $this->getStoragePath() . '/' . $fileName;

			if (JFile::exists($storage)) {
				JFile::delete($storage);
			}

			$ffmpeg->generateWaveform($this->table->original, $storage);
		}
	}

	/**
	 * Import the album art from the audio file [Leave it here for future]
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function processAlbumArt()
	{
		if ($this->isUpload()) {
			$ffmpeg = ES::ffmpeg(SOCIAL_TYPE_AUDIO);

			// Get the storage path to the audio cover
			$fileName = md5($this->table->title) . '.jpg';

			// Construct the storage path
			$storage = $this->getStoragePath() . '/' . $fileName;

			if (JFile::exists($storage)) {
				JFile::delete($storage);
			}

			$ffmpeg->getAlbumArt($this->table->original, $storage);

			// Returns the relative path
			$relativePath = $this->getRelativeStoragePath() . '/' . $fileName;

			if (JFile::exists($relativePath)) {
				$this->table->cover = $relativePath;
				$this->table->store();
			}

			return $relativePath;
		}
	}

	/**
	 * Determine if this audio has album art or not
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function hasAlbumArt()
	{
		return $this->table->cover;
	}

	/**
	 * Insert album art
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function insertAlbumArt($data, $isNew = true)
	{
		$albumartObj = ES::makeObject($data);

		$tmpPath = dirname($albumartObj->original->path);

		// We want to only get the thumbnail version
		$thumbnailPath = JPATH_ROOT . $albumartObj->thumbnail->path;
		$thumbnailFile =  md5($albumartObj->thumbnail->file) . '.jpg';

		if (JFile::exists($thumbnailPath)) {
			// Copy the photo from the temporary path to the storage folder.
			$state = JFile::copy($thumbnailPath, $this->getStoragePath() . '/' . $thumbnailFile);

			$relativePath = $this->getRelativeStoragePath() . '/' . $thumbnailFile;

			if (JFile::exists($relativePath)) {
				$this->table->cover = $relativePath;
				$this->table->store();

				// Once done, we delete the tmp folder
				JFolder::delete(JPATH_ROOT . $tmpPath);
			}

			// If the audio storage is not joomla,
			// We need to directly update the album art
			// Only if this is editing audio
			if (!$isNew && $this->table->storage != SOCIAL_STORAGE_JOOMLA) {
				$storage = ES::storage($this->table->storage);

				// Upload the album art of the audio
				$source = JPATH_ROOT . '/' . $this->getRelativeAlbumArtPath();
				$destination = '/' . $this->getRelativeAlbumArtPath();

				$storage->push($this->getAlbumArtFileName(), $source, $destination);

				if ($this->config->get('storage.amazon.delete')) {
					JFile::delete($source);
				}
			}

			return $relativePath;
		}
	}


	/**
	 * Import the metadata into the audio table
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function processMetadata($file)
	{
		$metadata = $this->importMetadata($file);

		if (!$metadata) {
			return;
		}

		if (isset($metadata['title'])) {
			$this->table->title = $metadata['title'];
		}

		if (isset($metadata['artist'])) {
			$this->table->artist = $metadata['artist'];
		}

		// if (isset($metadata['genre'])) {
		//  $model = ES::model('Audios');
		//  // If this genre not exist, we create new one
		//  if (!$model->isGenreExists($metadata['genre'])) {
		//      $genre = $this->createNewGenre($metadata['genre']);
		//      $this->table->genre_id = $genre->id;
		//  }

		// }

		if (isset($metadata['album'])) {
			$this->table->album = $metadata['album'];
		}
	}

	/**
	 * Import the metadata into the audio table
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function importMetadata($file)
	{
		$ffmpeg = ES::ffmpeg(SOCIAL_TYPE_AUDIO);

		// Get the storage path to the audio metadata
		$fileName = md5($file['name']) . '.txt';

		$metadataFile = $this->getStoragePath() . '/' . $fileName;
		$ffmpeg->audioMetadata($file['tmp_name'], $metadataFile);

		$contents = file_get_contents($metadataFile);
		$metaArray = explode("\n", $contents);

		$metadata = array();

		foreach ($metaArray as $meta) {
			$meta = explode('=', $meta);

			if (isset($meta[1])) {
				$metadata[$meta[0]] = $meta[1];
			}

		}

		if (empty($metadata)) {
			return false;
		}

		$metadata['filename'] = $file['name'];

		// delete the file
		JFile::delete($metadataFile);

		return $metadata;
	}

	/**
	 * Create new audio genre
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function createNewGenre($genreName)
	{
		// Get the genre
		$genre = ES::table('AudioGenre');

		$genre->title = $genreName;
		$genre->alias = strtolower($genreName);
		$genre->state = true;
		$genre->user_id = $this->my->id;

		$state = $genre->store();

		if ($state) {
			return $genre;
		}

		return false;
	}

	/**
	 * Generate a random file name
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function generateFileName()
	{
		$name = substr(md5(microtime()),rand(0,26), 8);

		return $name . '.mp3';
	}

	/**
	 * Retrieves the duration of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getDuration()
	{
		if (!$this->table->duration) {
			$duration = JText::_('N/A');

			return $duration;
		}

		$duration = new SocialAudioDuration($this->table->duration);

		// Since duration is always stored in seconds, we need to format this
		return $duration->format();
	}

	/**
	 * Retrieves the location for the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getLocation()
	{
		static $location = null;

		if (is_null($location)) {
			$location = ES::location($this->table->id, SOCIAL_TYPE_AUDIO);
		}

		return $location;
	}

	/**
	 * Retrieves the likes library for this audio
	 *
	 * @since   2.1
	 * @access  public
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
		$likes->get($this->table->id, SOCIAL_TYPE_AUDIOS, $verb, $this->type, $streamId, $options);

		return $likes;
	}

	/**
	 * Retrieve the likes count
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getLikesCount($verb = '', $streamId = '')
	{
		$likes = $this->getLikes($verb, $streamId);

		return $likes->getCount();
	}

	/**
	 * Retrieves the comment library for this audio
	 *
	 * @since   2.1
	 * @access  public
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

		// Generate comments for the audio
		$comments = ES::comments($this->table->id, SOCIAL_TYPE_AUDIOS, $verb, $this->type, $options, $streamId);

		return $comments;
	}

	/**
	 * Retrieves the comments count
	 *
	 * @since   2.1
	 * @access  public
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
	 * @since   2.1
	 * @access  public
	 */
	public function getStreamId($verb)
	{

		$model = ES::model('Audios');
		$streamId = $model->getStreamId($this->table->id, $verb);

		return $streamId;
	}

	/**
	 * Retrieves the creation date of an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getCreatedDate()
	{
		$date = ES::date($this->table->created);

		return $date;
	}

	/**
	 * Retrieves the bookmarks library associated to this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSharing()
	{
		$options = array('text' => JText::_('Share'));
		$options['url'] = $this->getExternalPermalink();
		$sharing = ES::sharing($options);

		return $sharing;
	}

	/**
	 * Retrieves the privacy library associated to this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPrivacy()
	{
		static $privacy = null;

		if (is_null($privacy)) {
			$privacy = ES::privacy($this->id, SOCIAL_TYPE_AUDIOS);
		}

		return $privacy;
	}

	/**
	 * Retrieves the privacy library for this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPrivacyButton()
	{
		$privacy = $this->getPrivacy();

		/* TODO: need to come back here once the stream for audio created. */
		$streamId = $this->getAudioStreamId($this->table->id, 'create');

		$button = $privacy->form($this->table->id, SOCIAL_TYPE_AUDIOS, $this->table->uid, 'audios.view', false, $streamId);

		return $button;
	}

	/**
	 * Retrieves the view all audios link
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAllAudiosLink($filter = '', $xhtml = false)
	{
		$options = array();

		if ($filter) {
			$options['filter'] = $filter;
		}

		if ($this->uid && $this->type) {
			$options['uid'] = $this->adapter->getAlias();
			$options['type'] = $this->type;
		}

		$url = FRoute::audios($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the playlist link
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPlaylistLink($listId, $xhtml = false)
	{
		$options = array();
		$options['listId'] = $listId;

		if ($this->uid && $this->type) {
			$options['uid'] = $this->adapter->getAlias();
			$options['type'] = $this->type;
		}

		$url = ESR::audios($options, $xhtml);

		return $url;
	}

	/**
	 * Determines if the photo should be associated with the stream item
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getAudioStreamId($audioId, $verb)
	{
		$db     = ES::db();
		$sql    = $db->sql();

		$sql->select('#__social_stream_item', 'a');
		$sql->column('a.uid');
		$sql->where('a.context_type', SOCIAL_TYPE_AUDIOS);
		$sql->where('a.context_id', $audioId);

		if ($verb == 'upload') {
			$sql->where('a.verb', 'share');
			$sql->where('a.verb', 'upload', '=', 'OR');
		} else if($verb == 'add') {
			$sql->where('a.verb', 'create');
		} else {
			$sql->where('a.verb', $verb);
		}

		$db->setQuery($sql);

		$uid    = (int) $db->loadResult();

		if (!$uid) {
			return;
		}

		return $uid;
	}

	/**
	 * Retrieves the reports library for this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getReports()
	{
		// Generate the reports
		$options = [
			'title' => 'COM_ES_AUDIO_REPORTS_DIALOG_TITLE',
			'description' => 'COM_ES_AUDIO_REPORTS_DIALOG_DESC',
			'extension' => 'com_easysocial',
			'type' => SOCIAL_TYPE_AUDIO,
			'uid' => $this->table->id,
			'itemTitle' => $this->getTitle(),
			'actorid' => $this->user_id
		];

		$reports = ES::reports($options);

		return $reports;
	}

	/**
	 * Retrieves a list of users tags associated with the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getEntityTags()
	{
		if (!$this->table->id) {
			return array();
		}

		$model = ES::model('Tags');
		$tags = $model->getTags($this->table->id, SOCIAL_TYPE_AUDIO, 'entity');

		return $tags;
	}

	/**
	 * Retrieves a list of tags associated with the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTags($stringOnly = false)
	{
		if (!$this->table->id) {
			return false;
		}

		$model = ES::model('Tags');
		$tags = $model->getTags($this->table->id, SOCIAL_TYPE_AUDIO, 'hashtag');

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

				$tag->permalink = ESR::audios($options);
			}
		}

		return $tags;
	}

	/**
	 * Retrieves the title of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTitle()
	{
		return JText::_($this->table->title);
	}

	/**
	 * Retrieves the params of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getParams()
	{
		$params = ES::registry($this->table->params);

		return $params;
	}

	/**
	 * Get provider of this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getLinkProvider()
	{
		if ($this->isUpload()) {
			return 'upload';
		}

		$providerName = $this->getParams()->get('oembed')->provider_name;

		return $providerName;
	}

	/**
	 * Retrieves the artist of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getArtist()
	{
		return $this->table->artist;
	}

	/**
	 * Retrieves the album of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAlbum()
	{
		return $this->table->album;
	}

	/**
	 * Retrieves the description of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getDescription($showDefault = true)
	{
		// Load site's language file.
		ES::language()->loadSite();

		$desc = ESJString::trim($this->table->description);

		if (!$desc && $showDefault) {
			return JText::_('COM_ES_AUDIO_NO_DESCRIPTION_AVAILABLE');
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
	 * Retrieves the embed codes for the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getEmbedCodes()
	{
		if ($this->isLink()) {
			return $this->getLinkEmbedCodes();
		}

		return $this->getUploadEmbedCodes();
	}

	/**
	 * Generates the embed codes for linked audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getLinkEmbedCodes()
	{
		// Get the audio width and height
		$height = $this->config->get('audio.size');
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

		return $codes;
	}

	/**
	 * Generates the embed codes for uploaded audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getUploadEmbedCodes($mini = false)
	{
		$theme = ES::themes();

		// We need to generate a unique id for each audios that are embedded on the page
		$uid = uniqid();

		$theme->set('uid', $uid);
		$theme->set('audio', $this);

		$namespace = 'site/audios/player/default';

		if ($mini) {
			$namespace = 'site/audios/player/default.mini';
		}

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Retrieves the entity adapter
	 *
	 * @since   2.1
	 * @access  public
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

		$className = 'SocialAudioAdapter' . ucfirst($this->type);
		$obj = new $className($this->uid, $this->type, $this->table);

		return $obj;
	}

	/**
	 * Retrieves the thumbnail of an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAlbumArt()
	{
		if (!$this->table->cover) {

			$albumArt = false;

			// Try to get image from the link itself
			if ($this->table->source == 'link' && $this->table->albumart_source == 'audio') {
				if (isset($this->table->params) && $this->table->params) {
					$params = json_decode($this->table->params);

					if (isset($params->oembed->thumbnail) && $params->oembed->thumbnail) {
						$albumArt = $params->oembed->thumbnail;
					}

					if (!$albumArt && isset($params->cover) && $params->cover) {
						$albumArt = $params->cover;
					}

					if (!$albumArt && isset($params->images) && $params->images) {

						$albumArt = $params->images;

						if (is_array($params->images)) {
							$albumArt = $params->images[0];
						}
					}
				}
			}

			if (!$albumArt) {
				$albumArt = $this->getDefaultAlbumart();
			}

			// process url protocol
			$uri = JURI::getInstance();

			if ($uri->getScheme() == 'https') {
				$albumArt = str_ireplace('http://', 'https://', $albumArt);
			}

			// Default cover
			return $albumArt;
		}

		$container = str_ireplace('\\', '/', $this->getContainer());
		$file = basename($this->table->cover);

		$relative = '/' . $container . '/' . $file;

		// Default url
		$url = rtrim(JURI::root(), '/') . $relative;

		// Storage service
		if ($this->table->storage != SOCIAL_STORAGE_JOOMLA) {
			$storage = ES::storage($this->table->storage);
			$url = $storage->getPermalink($relative);
		}

		return $url;
	}

	/**
	 * Retrieves the default thumbnail for audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getDefaultAlbumart()
	{
		if (!ES::iscli()) {
			$override = JPATH_ROOT . '/templates/' . $this->app->getTemplate() . '/html/com_easysocial/defaults/audios/cover.png';
			$overrideUri = rtrim(JURI::root(), '/') . '/templates/' . $this->app->getTemplate() . '/html/com_easysocial/defaults/audios/cover.png';

			if (JFile::exists($override)) {
				return $overrideUri;
			}
		}

		$default = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/defaults/audios/cover.jpg';
		return $default;
	}

	/**
	 * Retrieves the thumbnail of an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAlbumArtFileName()
	{
		return basename($this->table->cover);
	}

	/**
	 * Retrieves the thumbnail of an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getRelativeAlbumArtPath()
	{
		$container = $this->getContainer();
		$path = $container . '/' . basename($this->table->cover);

		return $path;
	}

	/**
	 * Retrieves the permalink to edit an audio
	 *
	 * @since   2.1
	 * @access  public
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

		$url = FRoute::audios($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the permalink to edit an audio
	 *
	 * @since   2.1
	 * @access  public
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

		$url = FRoute::audios($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the permalink of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAlias()
	{
		return $this->table->getAlias();
	}

	/**
	 * Retrieves the permalink of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPermalink($xhtml = true, $uid = null, $utype = null, $from = false, $external = false, $sef = true, $adminSef = false)
	{
		return $this->table->getPermalink($xhtml, $uid, $utype, $from, $external, $sef, $adminSef);
	}

	/**
	 * Retrieves the external permalink of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getExternalPermalink()
	{
		return $this->table->getExternalPermalink();
	}

	/**
	 * Retrieves the hits for the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getHits()
	{
		return $this->table->hits;
	}

	/**
	 * Retrieves the author of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAuthor()
	{
		static $data = array();

		if (!isset($data[$this->table->user_id])) {
			$data[$this->table->user_id] = ES::user($this->table->user_id);
		}

		return $data[$this->table->user_id];
	}

	/**
	 * Retrieves the post actor for audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAudioCreator($obj)
	{
		if ($this->post_as == SOCIAL_TYPE_PAGE && !is_null($obj)) {
			return $obj;
		}

		return $this->getAuthor();
	}

	/**
	 * Retrieves the genre of the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getGenre()
	{
		$genre = ES::table('AudioGenre');
		$genre->load($this->table->genre_id);

		return $genre;
	}

	/**
	 * Exports the audio data in a std class object
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function export()
	{
		$data = (object) $this->table;

		return $data;
	}

	/**
	 * Extracts the duration using ffmpeg
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function extractDuration()
	{
		$ffmpeg = ES::ffmpeg(SOCIAL_TYPE_AUDIO);
		$ffmpeg->input($this->table->original);

		$output = $ffmpeg->execute();

		$duration = $this->matchDuration($output);

		return $duration;
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
	 * @since   2.1
	 * @access  public
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
	 * @since   2.1
	 * @access  public
	 */
	public function matchDuration($contents)
	{
		$duration = new SocialAudioDuration();

		// Regex to match the total duration of the original audio
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
	 * @since   2.1
	 * @access  public
	 */
	public function status()
	{
		if (!$this->allowEncode()) {
			return;
		}

		// Get the log file's path
		$logFilePath = $this->getLogFilePath();

		if (!file_exists($logFilePath)) {
			return;
		}

		$contents = file_get_contents($logFilePath);

		// Regex to match the total duration of the original audio
		$duration = $this->matchDuration($contents);

		// Get total seconds for the duration
		$totalDurationSeconds = $duration->raw();

		// If the total duration is empty, we skip the process
		if (empty($totalDurationSeconds)) {
			return;
		}

		// Determines if this is already complete
		$pattern = '/muxing overhead/is';
		preg_match($pattern, $contents, $complete);

		if ($complete) {
			return true;
		}

		// Get all the sizes that is being converted currently
		$pattern = '/size= (.*)/is';
		preg_match($pattern, $contents, $sizes);

		// Default processed duration
		$currentDurationSeconds = 0;

		if ($sizes) {

			$sizes = explode("\r", $sizes[0]);

			// The last frame always needs to -2 because the last line is always a return carriage
			$sizes = array_filter($sizes, array($this, 'trimResult'));
			$totalFrames = count($sizes);
			$index = count($sizes) - 1;
			$lastFrame = $sizes[$index];

			// Get the current time of the last frame
			if ($lastFrame && strpos($lastFrame, 'time=') !== false) {

				$pattern = '/time=([0-9]{2}):([0-9]{2}):([0-9]{2})\.([0-9]{2})/is';
				preg_match($pattern, $lastFrame, $time);

				if (count($time) >= 5) {
					list($time, $hour, $minute, $second, $milisecond) = $time;
				} else {
					list($time, $hour, $minute, $second) = $time;
				}

				$currentDurationSeconds = $this->convertToSeconds($hour, $minute, $second);
			} else {
				return 'ignore';
			}
		}

		$progress = round(($currentDurationSeconds / $totalDurationSeconds) * 95);

		return $progress;
	}

	/**
	 * Allow caller to retrieve privacy data
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPrivacyData()
	{
		$privacyData = '';

		if ($this->table->params) {
			$privacyData = json_decode($this->table->params);

			//lets perform some testing here before we proceed.
			if (isset($privacyData->rule)) {
				if ($privacyData->processed) {
					$privacyData = '';
				} else {
					//this mean its a new audio. so we need to remove the privacy data.
					$this->table->params = '';
				}
			} else {
				$privacyData = '';
			}
		}

		return $privacyData;
	}

	/**
	 * Publishes the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function publish($options = array())
	{
		$privacyData = $this->getPrivacyData();

		$this->table->state = SOCIAL_AUDIO_PUBLISHED;

		$state = $this->table->store();

		// Generate a new stream item when the audio is published.
		$createStream = isset($options['createStream']) ? $options['createStream'] : true;

		if ($createStream) {
			$this->createStream('create', $privacyData);
		}

		// update video isnew flag to false
		$this->table->isnew = 0;
		$this->table->store();

		// trigger audio smart search plugin for indexing.
		$this->syncIndex();

		return $state;
	}

	/**
	 * Sync's the user record with Joomla smart search
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function syncIndex()
	{
		// Determines if this is a new account
		$isNew = $this->isNew();

		// Trigger our own finder plugin
		JPluginHelper::importPlugin('finder');

		ESDispatcher::trigger('onFinderAfterSave', array('easysocial.audios', &$this->table, $isNew));
		ESDispatcher::trigger('onFinderChangeState', array('easysocial.audios', $this->table->id, $this->table->state));
	}

	/**
	 * Unpublishes the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function unpublish()
	{
		$this->table->state = SOCIAL_AUDIO_UNPUBLISHED;

		$state = $this->table->store();

		// @TODO: Give points to the author for creating a new audio

		// @TODO: Should we delete the stream as well?

		// Trigger our own finder plugin
		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderChangeState', array('easysocial.audios', $this->table->id, $this->table->state));

		return $state;
	}

	/**
	 * Sets an audio as featured
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function setFeatured()
	{
		$this->table->featured = SOCIAL_AUDIO_PUBLISHED;
		$state = $this->table->store();

		// Generate a stream item for this featured
		$this->createStream('featured');

		// @points: audio.featured
		ES::points()->assign('audio.featured', 'com_easysocial', $this->getAuthor()->id);

		return $state;
	}

	/**
	 * Removes a featured audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function removeFeatured()
	{
		$this->table->featured = false;
		$state = $this->table->store();

		// Generate a stream item for this featured
		$this->removeStream('featured');

		// @points: audio.unfeatured
		ES::points()->assign('audio.unfeatured', 'com_easysocial', $this->getAuthor()->id);

		return $state;
	}

	/**
	 * Generates a new stream item for the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function createStream($verb, $privacy = '')
	{
		// When an audio is published, we should generate a stream for it.
		$stream = ES::stream();

		$template = $stream->getTemplate();
		$actor = $this->getAuthor();

		// Set the actor of the stream item
		$template->setActor($actor->id, SOCIAL_TYPE_USER);

		// Set the context
		$template->setContext($this->table->id, SOCIAL_TYPE_AUDIOS);

		// Set the verb
		$template->setVerb($verb);

		// If this is created within a cluster, it should be mapped to the respective cluster
		if ($this->table->uid && $this->table->type && $this->table->type != SOCIAL_TYPE_USER) {
			$template->setCluster($this->table->uid, $this->table->type);

			// If this is page audio, we should set the page itself as post actor
			// Only admin is able to add audio from the audio listing
			if ($this->table->type == SOCIAL_TYPE_PAGE) {
				$template->setPostAs(SOCIAL_TYPE_PAGE);
			}
		}

		// Set stream privacy
		if ($privacy) {

			$value = $privacy->value;
			if (is_string($value)) {
				$privacyLib = ES::privacy();
				$value = $privacyLib->toValue($value);
			}

			$template->setAccess('audios.view', $value, $privacy->custom);
		} else {
			$template->setAccess('audios.view');
		}

		// Generate the stream item now.
		$result = $stream->add($template);

		// update audio isnew flag to false
		$this->table->isnew = 0;
		$this->table->store();

		return $result;
	}

	/**
	 * Removes a stream item given the verb
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function removeStream($verb, $actorId = '')
	{
		// When an audio is published, we should generate a stream for it.
		$stream = ES::stream();

		$result = $stream->delete($this->table->id, SOCIAL_TYPE_AUDIOS, $actorId, $verb);

		return $result;
	}

	/**
	 * Deletes the audio from the site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function delete()
	{
		// Remove the comments related to this audio
		$comments = $this->getComments();
		$comments->delete();

		// Remove the likes related to this audio
		$likes = ES::likes();
		$likes->delete($this->id, SOCIAL_TYPE_AUDIOS, 'create', $this->type, null, true);

		// Assign points when an audio is deleted
		ES::points()->assign('audio.remove', 'com_easysocial', $this->getAuthor()->id);

		// Remove the stream items related to this audio
		$this->removeStream('featured');
		$this->removeStream('create');

		// Remove the search results
		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderAfterDelete', array('easysocial.audios', $this->table));

		// Remove any tags that are associated to this audio
		$tag = ES::tag($this->table->id, SOCIAL_TYPE_AUDIO);
		$tag->cleanTags();

		// We also need to remove from playlist.
		$model = ES::model('Lists');
		$model->deleteItem($this->table->id, SOCIAL_TYPE_AUDIO);

		// Remove from the database
		$state = $this->table->delete();

		// Remove files related to this audio
		$this->deleteStorage();

		return $state;
	}

	/**
	 * Delete audio files from the site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteStorage()
	{
		$path = $this->getStoragePath();

		// if the audio stored in local server, remove the file directly
		if ($this->storage == SOCIAL_STORAGE_JOOMLA) {

			if (!JFolder::exists($path)) {
				return false;
			}

			$state = JFolder::delete($path);
		} else {

			// if stored it from Amazon s3, we need to remove it from Amazon as well
			if ($path) {
				$relativePath = $this->getRelativeStoragePath();
				$storage = ES::storage($this->storage);
				$state = $storage->delete($relativePath, true);
			}
		}

		ES::storage()->syncUsage($this->user_id);

		return $state;
	}

	/**
	 * Allows caller to download a file.
	 *
	 * @since   2.1
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function download()
	{
		// Get the original file path.
		$file = $this->getStoragePath();
		$fileName = basename($this->table->path);

		$filePath = $file . '/' . $fileName;

		// Make the path relative
		if ($this->storage != 'joomla') {
			$filePath = str_ireplace(JPATH_ROOT, '', $file);

			$storage = ES::storage($this->storage);

			// We must get the file name from the path itself to match the key on the s3
			$fileName = substr($filePath, strrpos($filePath, '/') + 1);

			return $storage->download($filePath, $fileName);
		}

		// Set the headers for the file transfer
		header('Content-Description: File Transfer');
		header('Content-Type: audio/mpeg');
		header("Content-Disposition: attachment; filename=\"". $this->table->file_title ."\";");
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filePath));

		ob_clean();
		flush();
		readfile($filePath);
		exit;
	}

	/**
	 * Validates the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function validate($file = array(), $options = array())
	{
		// Ensure that it has a genre
		if (!$this->table->genre_id) {
			$this->setError('COM_ES_AUDIO_INVALID_CATEGORY_PROVIDED');
			return false;
		}

		// Ensure that the audio has a title
		if (!$this->table->title) {
			$this->setError('COM_ES_AUDIO_INVALID_TITLE');
			return false;
		}

		// If this is an audio link, ensure a link is provided
		if ($this->isLink() && !$this->table->path) {
			$this->setError('COM_ES_AUDIO_ENTER_AUDIO_URL');
			return false;
		}

		// Ensure that the audio link is valid
		if ($this->isLink() && $this->table->path && !$this->isValidUrl($this->table->path)) {
			$this->setError('COM_ES_AUDIO_ENTER_VALID_URL');
			return false;
		}

		$skipFileValidation = isset($options['skipFileValidation']) ? $options['skipFileValidation'] : false;

		if ($skipFileValidation) {
			return true;
		}

		// If this is a new audio we want to validate the file
		if ($this->isUpload() && $this->isNew() && empty($file['tmp_name'])) {
			$this->setError('COM_ES_AUDIO_UPLOAD_FILE');
			return false;
		}

		// If file is provided, we need to test if it is valid
		if ($this->isUpload() && $file) {

			$valid = $this->isAudioValid($file);

			if (!$valid) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Format the url correctly.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function format($link=false)
	{
		if (preg_match("#https?://#", $link) === 0){
			$link = 'https://' . $link;
		}

		return $link;
	}

	/**
	 * Render audio headers
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function renderHeaders()
	{
		$obj = new stdClass();

		$obj->title = $this->getTitle();
		$obj->description = $this->description;
		$obj->image = $this->getAlbumArt();
		$obj->url = $this->getExternalPermalink();
		$obj->audio = $this;

		ES::meta()->setMetaObj($obj);
	}

	/**
	 * function to determine if this embed is a facebook audio or not.
	 *
	 * @since	2.1
	 * @access  public
	 */
	public function isFacebookEmbed()
	{
		if ($this->isLink() && stristr($this->path, '://www.facebook.com') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Exports audio data
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

		$autor = $this->getAuthor();

		$result = array(
			'id' => $this->id,
			'title' => $this->getTitle(),
			'artist' => $this->getArtist(),
			'album' => $this->getAlbum(),
			'hits' => $this->getHits(),
			'description' => $this->getDescription(),
			'duration' => $this->getDuration(),
			'genre' => $this->getGenre()->toExportData($viewer, $options),
			'created' => $this->table->created,
			'isExternal' => $this->isLink(),
			'isFeatured' => $this->isFeatured(),
			'thumbnail' => $this->getAlbumArt(),
			'tagged' => $this->getEntityTags(),
			'hashtags' => $this->getTags(),
			'permalink' => $this->getPermalink(),
			'externalPermalink' => $this->getExternalPermalink(),
			'source' => strtolower($this->getLinkProvider())
		);

		if (!$this->isLink()) {
			$result['downloadLink'] = ESR::raw('index.php?option=com_easysocial&view=audios&layout=download&id=' . $this->getAlias() . '&tmpl=component');
		}

		$result['isOwner'] = $autor->id === $viewer->id;

		$link = $this->getFile();

		if ($this->isLink()) {
			$link = $this->table->path;
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
			$result['author'] = $autor->toExportData($viewer);
		}


		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}

	/**
	 * Determine if this is spotify podcast
	 *
	 * @since   3.1.0
	 * @access  public
	 */
	public function isSpotifyPodcast()
	{
		if ($this->getLinkProvider() !== 'Spotify') {
			return false;
		}

		if (stristr($this->table->path, 'open.spotify.com/episode/') === false) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve the navigation for the single audio item
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getNavigation()
	{
		static $data = [];

		if (!isset($data[$this->id])) {
			if (!$this->config->get('audio.layout.item.navigation')) {
				$data[$this->id] = false;
				return $data[$this->id];
			}

			$model = ES::model('Audios');
			$data[$this->id] = $model->getNavigation($this);
		}

		return $data[$this->id];
	}

	/**
	 * Render repost link
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function reposts($clusterId = 0, $clusterType = '')
	{
		$repost = ES::repost($this->table->id, SOCIAL_TYPE_AUDIOS, $this->table->type, $clusterId, $clusterType);

		return $repost;
	}

	/**
	 * Notify user when the audio is created
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

		$cluster->notifyMembers('audio.create', $options);

		return true;
	}
}

class SocialAudioDuration
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
		$current = new DateTime('@' . $this->seconds);

		$diff = $empty->diff($current)->format('%I:%S');;

		return $diff;
	}
}
