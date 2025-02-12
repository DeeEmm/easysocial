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

class EasySocialControllerAudios extends EasySocialController
{
	/**
	 * Allows caller to render audios
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAudios()
	{
		ES::checkToken();

		// Get a list of filters
		$filter = $this->input->get('filter', '', 'word');
		$genre = $this->input->get('genreId', '', 'int');
		$sort = $this->input->get('sort', '', 'word');
		$isSortingRequest = $this->input->get('isSort', false, 'default');

		$hashtags = $this->input->get('hashtags', '', 'word');
		$hashtagFilterId = $this->input->get('hashtagFilterId', 0, 'int');

		// used in link emebed
		$rawUid = $this->input->get('uid', '', 'default');
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'word');

		$from = 'listing'; // to determine if the page is coming from all audios listing page or not.

		if ($uid && $type) {
			$from = $type;
		}

		// Prepare the options
		$options = array();

		// Determines if the current viewer is allowed to create new audio
		$adapter = ES::audio($uid, $type);

		// Set the filter
		$options['filter'] = $filter;
		$options['genre'] = $genre;

		if ($sort) {
			$options['sort'] = $sort;
		}

		// Determines if we should retrieve featured audios
		$options['featured'] = false;

		if ($filter == 'featured') {
			$options['featured'] = true;
		}

		if ($filter == 'mine') {
			$options['featured'] = '';
		}

		$cluster = null;

		// Determines if this is to retrieve audios from groups or events
		if ($uid && $type) {
			$options['uid'] = $uid;
			$options['type'] = $type;

			if ($type != SOCIAL_TYPE_USER) {
				$cluster = ES::cluster($type, $uid);
			}
		}

		if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {
			$options['userid'] = $uid;
			$options['filter'] = SOCIAL_TYPE_USER;
		}

		if ($filter == 'pending') {
			$options['userid'] = $this->my->id;
		}

		$tagsFilter = ES::Table('TagsFilter');

		if ($hashtagFilterId) {
			$options['includeFeatured'] = true;

			$tagsFilter->load($hashtagFilterId);

			$hashtags = $tagsFilter->getHashtag();
		}

		if ($hashtags) {
			$options['includeFeatured'] = true;
			$options['hashtags'] = $hashtags;
		}

		$model = ES::model('Audios');

		// Get the total numbers of audios to show on the page.
		$options['limit'] = ES::getLimit('audios_limit', 20);

		// Get a list of audios from the site
		$audios = $model->getAudios($options);
		$pagination = $model->getPagination();

		$pagination->setVar('view', 'audios');

		if ($filter && !$genre) {
			$pagination->setVar('filter', $filter);

			$user = ES::user($uid);
			$pagination->setVar('uid', $user->getAlias());
			$pagination->setVar('type', $type);
		}

		$activeGenre = false;

		if ($genre) {
			$audioGenre = ES::table('AudioGenre');
			$audioGenre->load($genre);

			$activeGenre = $audioGenre;

			$user = ES::user($uid);
			$pagination->setVar('uid', $user->getAlias());

			$pagination->setVar('type', $type);
			$pagination->setVar('genreId', $audioGenre->getAlias());
		}

		if ($sort) {
			$pagination->setVar('sort', $sort);
		}

		// If the current filter is not a featured filter, we should also pick up the featured audios
		// With an exception for hashtag filter
		$featuredAudios = array();

		if (!($hashtagFilterId || $hashtags) && $filter == 'all' && !$isSortingRequest) {
			$options['featured'] = true;
			$options['limit'] = false;
			$featuredAudios = $model->getAudios($options);
		}

		return $this->view->call(__FUNCTION__, $audios, $featuredAudios, $pagination, $filter, $adapter, $rawUid, $uid, $type, $hashtags, $tagsFilter, $isSortingRequest, $from, $genre, $activeGenre, $cluster);
	}

	/**
	 * Deletes an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table);

		// Ensure that the user is really allowed to delete the audio
		if (!$audio->canDelete()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_DELETE'), 500);
		}

		// Try to delete the audio now
		$state = $audio->delete();

		if (!$state) {
			throw ES::exception($audio->getError(), 500);
		}

		// Set the success message
		$this->view->setMessage(JText::_('COM_ES_AUDIO_DELETE_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $audio);
	}

	/**
	 * Unfeatures an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function unfeature()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the audio
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table->uid, $table->type, $table);

		// Ensure that the audio can be featured
		if (!$audio->canUnfeature()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_UNFEATURE'), 500);
		}

		// Feature the audio
		$audio->removeFeatured();

		$this->view->setMessage(JText::_('COM_ES_AUDIO_UNFEATURED_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $audio);
	}


	/**
	 * Features an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function feature()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the audio
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table->uid, $table->type, $table);

		// Get the callback url
		$callback = $this->input->get('return', '', 'default');

		// Ensure that the audio can be featured
		if (!$audio->canFeature()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_FEATURE'), 500);
		}

		// Feature the audio
		$audio->setFeatured();

		$this->view->setMessage(JText::_('COM_ES_AUDIO_FEATURED_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $audio, $callback);
	}

	/**
	 * Allow caller to import metadata
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function importMetadata()
	{
		if (!$this->config->get('audio.autoimportdata')) {
			return $this->ajax->reject();
		}

		$file = $this->input->files->get('audio', '');

		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			return $this->ajax->reject(JText::_('Invalid file'));
		}

		$metadata = ES::audio()->importMetadata($file);

		return $this->ajax->resolve($metadata);
	}

	/**
	 * Allow caller to temporary upload album art
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function uploadAlbumArt()
	{
		$file = $this->input->files->get('album_art', '');

		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			return $this->ajax->reject(JText::_('Invalid file'));
		}

		$options = array('name' => 'album_art');

		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		if ($file instanceof SocialResponse) {
			return $this->ajax->reject($file->message);
		}

		// Load the image library
		$image = ES::image();

		$name = $file['name'];

		// Load up the file
		$image->load($file['tmp_name'], $name);

		// Ensure the image is valid
		if (!$image->isValid()) {
			exit;
		}

		// Get the storage path
		$storage = ES::audio()->getTmpStoragePath();

		// Create new album art object
		$photos = ES::get('Photos', $image);

		$sizes = $photos->create($storage);

		// We want to format the output to get the full absolute url.
		$base = basename($storage);

		$result = array();

		foreach ($sizes as $size => $value) {
			$row = new stdClass();

			$row->title	= $file['name'];
			$row->file = $value;
			$row->path = '/media/com_easysocial/tmp/' . $base . '/' . $value;
			$row->uri = rtrim(JURI::root(), '/') . '/media/com_easysocial/tmp/' . $base . '/' . $value;

			$result[$size] = $row;
		}

		return $this->ajax->resolve($result);

	}

	/**
	 * Processes an audio creation
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function save()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the file data
		$file = $this->input->files->get('audio');

		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			$file = null;
		}

		// Get the posted data
		$post = $this->input->post->getArray();

		// This audio could be edited
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->post->get('type', SOCIAL_TYPE_USER, 'word');

		$table = ES::table('Audio');
		$table->load($id);

		if ($table->id && $table->type == SOCIAL_TYPE_USER) {
			// this is update.
			if ($table->user_id && $uid != $table->user_id) {
				// this could be super admin updating this audio.
				$uid = $table->user_id;
				$post['uid'] = $table->user_id;
			}
		}

		$audio = ES::audio($uid, $type, $table);

		// Determines if this is a new audio
		$isNew = $audio->isNew();

		// If this is a new audio, we should check against their permissions to create
		if (!$audio->allowCreation() && $audio->isNew()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_ADDING_AUDIOS'), 500);
		}

		// Ensure that the user can really edit this audio
		if (!$isNew && !$audio->canEdit()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_EDITING'), 500);
		}

		$options = array();

		// Audio upload will create stream once it is published.
		// We will only create a stream here when it is an external link.
		if ($post['source'] != SOCIAL_AUDIO_UPLOAD) {
			$options = array('createStream' => true);
		}

		// If the source is from external link, we need to format the url properly.
		if ($post['source'] == 'link') {
			$post['link'] = $audio->format($post['link']);
		}

		// Save the audio
		$state = $audio->save($post, $file, $options);

		// Load up the session
		$session = JFactory::getSession();

		if (!$state) {

			// Store the data in the session so that we can repopulate the values again
			$data = json_encode($audio->export());

			$session->set('audios.form', $data, SOCIAL_SESSION_NAMESPACE);

			$this->view->setMessage($audio->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $audio, $isNew, $file);
		}

		// Once an audio is created successfully, remove any data associated from the session
		$session->set('audios.form', null, SOCIAL_SESSION_NAMESPACE);

		return $this->view->call(__FUNCTION__, $audio, $isNew, $file);
	}

	/**
	 * Creates a new audio from the story
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function uploadStory()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the file data
		$file = $this->input->files->get('file');

		// Since user can't set the title of the audio when uploading via the story form, we need to generate a title for it
		// based on the name of the audio file.
		$data = array();

		// Format the title since the title is the file name
		$data['title'] = ucfirst(JFile::stripExt($file['name']));
		$data['source'] = 'upload';

		// Get a default genre to house the audio
		$model = ES::model('Audios');
		$genre = $model->getDefaultGenre();

		$data['genre_id'] = $genre->id;
		$data['albumart_source'] = 'audio';

		// Get the uid and type
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'word');

		$saveOptions = array('processMetadata' => true, 'isUploading' => true);

		if ($uid && $type && $type != SOCIAL_TYPE_USER) {
			$data['uid'] = $uid;
			$data['type'] = $type;
		}

		$audio = ES::audio($uid, $type);
		$state = $audio->save($data, $file, $saveOptions);

		if (!$state) {
			$this->view->setMessage($audio->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $audio);
		}

		// Determines if the audio should be processed immediately or it should be set under pending mode
		if ($this->config->get('audio.allowencode') && $this->config->get('audio.autoencode')) {
			// After creating the audio, process it
			$audio->process();
		}

		return $this->view->call(__FUNCTION__, $audio);
	}

	/**
	 * Allows caller to remove a tag from the audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function removeTag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the tag id
		$id = $this->input->get('id', 0, 'int');

		// Get the tag
		$tag = ES::table('Tag');
		$tag->load($id);

		// Check for permissions to delete this tag
		$table = ES::table('Audio');
		$table->load($tag->target_id);

		$audio = ES::audio($table->uid, $table->type, $table);

		if (!$audio->canRemoveTag($tag)) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_REMOVE_TAGS'), 500);
		}

		// Delete the tag
		$tag->delete();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to quickly tag people in this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function tag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the user id's.
		$ids = $this->input->get('ids', array(), 'array');

		// Get the audio
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table->uid, $table->type, $table);

		// Insert the user tags
		$tags = $audio->insertTags($ids);

		return $this->view->call(__FUNCTION__, $audio, $tags);
	}

	/**
	 * Checks the status
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function status()
	{
		$id = $this->input->get('id', 0, 'int');
		$file = $this->input->get('file', '', 'raw');
		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'word');

		// Load the audio
		$audio = ES::audio($uid, $type, $id);

		// Get the status of the audio
		$status = $audio->status();

		// If the audio is processed successfully, publish the audio now.
		if ($status === true) {

			// If needed, we need to delete the original video from the site
			if ($audio->isUpload() && $this->config->get('audio.delete')) {
				@JFile::delete($audio->table->original);
			}

			$shouldCreateStream = $audio->isNew() ? true : false;

			$createStream = $this->input->get('createStream', $shouldCreateStream, 'bool');

			$audio->publish(array('createStream' => $createStream));
		}

		return $this->view->call(__FUNCTION__, $audio, $status);
	}

	/**
	 * Initiates a process request to convert audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function process()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the audio that we are trying to convert here.
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table);

		// @TODO: Check if the user is really allowed to process this audio
		if (!$audio->canProcess()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_PROCESS_AUDIO'), 500);
		}

		// Only allow processing if the audio is in pending state
		if (!$audio->isPendingProcess()) {
			throw ES::exception(JText::_('Not pending state'), 500);
		}

		// Run the audio process
		$audio->process();

		return $this->view->call(__FUNCTION__, $audio);
	}

	/**
	 * Creates a new playlist.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function storePlaylist()
	{
		ES::requireLogin();
		ES::checkToken();

		if (! $this->config->get('audio.enabled')) {
			$this->view->setMessage('COM_ES_AUDIO_ERROR_AUDIO_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get post data.
		$data = $this->input->post->getArray();

		// Detect if this is an edited list or a new list
		$id = $this->input->get('id', 0, 'int');

		// Tell the library that this is sudio type list
		$data['type'] = SOCIAL_TYPE_AUDIOS;

		$list = ES::lists($id);
		$list->bind($data);

		if (!$list->canCreatePlaylist()) {
			return $this->view->exception('COM_ES_AUDIO_PLAYLISTS_ACCESS_NOT_ALLOWED');
		}

		$state = $list->savePlaylist();

		if ($state === false) {
			$this->view->setMessage($list->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_ES_AUDIO_PLAYLIST_CREATED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allow caller to add audio to playlist
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function addToPlaylist()
	{
		ES::requireLogin();
		ES::checkToken();

		$audioId = $this->input->get('audioId', 0, 'int');
		$playlistId = $this->input->get('playlistId', 0, 'int');

		// Load the audio
		$audio = ES::table('Audio');
		$audio->load($audioId);

		if (!$audio->id) {
			$this->ajax->reject(JText::_('COM_ES_AUDIO_INVALID_AUDIO_ID_PROVIDED'));
		}

		// Load the playlist
		$playlist = ES::lists($playlistId);

		if (!$playlist->id) {
			$this->ajax->reject(JText::_('COM_ES_AUDIO_INVALID_PLAYLIST_ID_PROVIDED'));
		}

		$state = $playlist->addAudio($audio->id);

		if (!$state) {
			$this->ajax->reject($playlist->getError());
		}

		$this->ajax->resolve();
	}

	/**
	 * Suggest a list of audio titles for a user.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function suggest()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load audios model.
		$model = ES::model('Audios');

		// Properties
		$search  = $this->input->get('search', '', 'default');
		$exclude = $this->input->get('exclude', '', 'default');

		$options = array();

		if ($exclude) {
			$options['exclude'] = $exclude;
		}

		// Currently only being used in playlist
		$options['playlist'] = true;

		// Determine if we should search all audio on the site
		$searchType = $this->input->get('type', '', 'cmd');

		// Try to get the search result.
		$result = $model->search($this->my->id, $search, $options);

		return $this->view->call(__FUNCTION__, $result);
	}

	/**
	 * Adds a list of audio into a playlist.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function assignItem()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get list of audio id's.
		$ids = $this->input->get('uid', array());
		$ids = ES::makeArray($ids);

		// Get the list
		$id = $this->input->get('listId', 0, 'int');
		$playlist = ES::lists($id);

		if (!$playlist->id || !$id) {
			return $this->view->exception('COM_ES_AUDIO_INVALID_PLAYLIST_ID_PROVIDED');
		}

		$model = ES::model('Lists');
		$total = $model->getCount($playlist->id, $playlist->type);

		// Add the audio to the list.
		$listMapIds = $playlist->addAudio($ids);

		if ($listMapIds === false) {
			return $this->view->exception($playlist->getError());
		}

		return $this->view->call(__FUNCTION__, $listMapIds, $total+1);
	}

	/**
	 * Deletes a playlist from the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deletePlaylist()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the list id.
		$id = $this->input->get('id', 0, 'int');

		// Try to load the list.
		$list = ES::table('List');
		$list->load($id);

		// Test if the id provided is valid.
		if (!$list->id || !$id) {
			return $this->view->exception('COM_EASYSOCIAL_LISTS_ERROR_LIST_INVALID');
		}

		// Test if the owner of the list matches.
		if (!$list->isOwner()) {
			return $this->view->exception('COM_EASYSOCIAL_LISTS_ERROR_LIST_IS_NOT_OWNED');
		}

		// Try to delete the list.
		$state = $list->delete();

		if (!$state) {
			return $this->view->exception($list->getError());
		}

		$this->view->setMessage('COM_ES_AUDIO_PLAYLIST_DELETE_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $list);
	}

	/**
	 * Removes an audio from the playlist.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function removeFromPlaylist()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the audio list map id that's being removed from the list.
		$listMapId = $this->input->get('listMapId', 0, 'int');

		// Get the current list id.
		$playlistId = $this->input->get('playlistId', 0, 'int');

		// Try to load the list now.
		$list = ES::table('List');
		$state = $list->load($playlistId);

		if (!$playlistId || !$state) {
			$this->view->setMessage('COM_EASYSOCIAL_LISTS_ERROR_LIST_INVALID', ES_ERROR);
			return $this->ajax->reject();
		}

		// Check if the list is owned by the current user.
		if (!$list->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_LISTS_ERROR_LIST_IS_NOT_OWNED', ES_ERROR);
			return $this->ajax->reject();
		}

		// Load the list map table
		$listMap = ES::table('ListMap');
		$listMap->load($listMapId);

		// Try to delete the item from the list.
		$state = $listMap->delete();

		if (!$state) {
			$this->view->setMessage($list->getError(), ES_ERROR);
			return $this->ajax->reject();
		}

		return $this->ajax->resolve();
	}

	/**
	 * Gets all the count of the audio playlists.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getListCounts()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Lists');
		$lists = $model->getLists(array('user_id' => $this->my->id, 'type' => SOCIAL_TYPE_AUDIOS));

		return $this->view->call(__FUNCTION__, $lists);
	}

	/**
	 * Update hit count for audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function hit()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Audio');
		$table->load($id);

		// Load up the audio
		$audio = ES::audio($table->uid, $table->type, $table);

		$audio->hit();
	}

	/**
	 * Process link from audio form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function processLink()
	{
		$link = $this->input->get('link', '', 'default');
		$link = trim($link);

		// We need to format the url properly.
		$audio = ES::audio();

		if ($audio->hasExceededLimit()) {
			return $this->ajax->reject(JText::_('COM_ES_AUDIO_EXCEEDED_LIMIT'));
		}

		if (!$audio->isValidUrl($link)) {
			return $this->ajax->reject(JText::_('COM_ES_AUDIO_LINK_EMBED_NOT_SUPPORTED'));
		}

		$link = $audio->format($link);

		$crawler = ES::crawler();
		$data = $crawler->scrape($link);

		$oembed = (array) $data->oembed;

		// Before we proceed, we need to ensure that $data->oembed is really exists.
		// If not exists, throw the appropriate error message to the user.
		if (!isset($data->oembed) || !$data->oembed || empty($oembed)) {
			return $this->ajax->reject(JText::_('COM_ES_AUDIO_LINK_EMBED_NOT_SUPPORTED'));
		}

		return $this->ajax->resolve($data);
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

		$size = $this->input->get('filesize', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('utype', SOCIAL_TYPE_USER, 'default');

		// Check for upload limit
		$audio = ES::audio($uid, $utype);
		$adapter = $audio->getAdapter();
		$uploadLimit = $adapter->getUploadLimit();
		$maxUploadSize = ES::math()->convertBytes($uploadLimit);

		if ($size > $maxUploadSize) {
			return $this->ajax->reject(JText::sprintf('COM_ES_AUDIO_EXCEEDED_ALLOWED_FILESIZE', $uploadLimit));
		}

		// Check for storage limit
		$storage = ES::storage();
		$isLimit = $storage->isLimit($this->my->id, $size);

		if ($isLimit) {
			$message = JText::_('COM_ES_STORAGE_INSUFFICIENT_STORAGE');
			return $this->ajax->reject($message);
		}

		return $this->ajax->resolve();
	}
}
