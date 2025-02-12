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

class EasySocialControllerVideos extends EasySocialController
{
	/**
	 * Allows caller to render videos
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getVideos()
	{
		ES::checkToken();

		// Get a list of filters
		$filter = $this->input->get('filter', '', 'word');
		$category = $this->input->get('categoryId', '', 'int');
		$sort = $this->input->get('sort', '', 'word');
		$isSortingRequest = $this->input->get('isSort', false, 'default');

		$hashtags = $this->input->get('hashtags', '', 'word');
		$hashtagFilterId = $this->input->get('hashtagFilterId', 0, 'int');

		// used in link emebed
		$rawUid = $this->input->get('uid', '', 'default');
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'word');

		$from = 'listing'; // to determine if the page is coming from all videos listing page or not.

		if ($uid && $type) {
			$from = $type;
		}

		// Prepare the options
		$options = array();

		// Determines if the current viewer is allowed to create new video
		$adapter = ES::video($uid, $type);

		// Set the filter
		$options['filter'] = $filter;
		$options['category'] = $category;

		if ($sort) {
			$options['sort'] = $sort;
		}

		// Determines if we should retrieve featured videos
		$options['featured'] = false;

		if ($filter == 'featured') {
			$options['featured'] = true;
		}

		if ($filter == 'mine') {
			$options['featured'] = '';
		}

		$cluster = null;

		// Determines if this is to retrieve videos from groups or events
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

		$model = ES::model('Videos');

		// Get the total numbers of videos to show on the page.
		$options['limit'] = ES::getLimit('videos_limit', 20);

		// Get a list of videos from the site
		$videos = $model->getVideos($options);
		$pagination = $model->getPagination();

		$pagination->setVar('view' , 'videos');

		if ($filter && !$category) {
			$pagination->setVar('filter' , $filter);

			$user = ES::user($uid);
			$pagination->setVar('uid', $user->getAlias());
			$pagination->setVar('type', $type);
		}

		$activeCategory = false;

		if ($category) {
			$videoCategory = ES::table('VideoCategory');
			$videoCategory->load($category);

			$activeCategory = $videoCategory;

			$user = ES::user($uid);
			$pagination->setVar('uid', $user->getAlias());

			$pagination->setVar('type', $type);
			$pagination->setVar('categoryId' , $videoCategory->getAlias());
		}

		if ($sort) {
			$pagination->setVar('sort' , $sort);
		}

		// If the current filter is not a featured filter, we should also pick up the featured videos
		// With an exception for hashtag filter
		$featuredVideos = array();

		if (!($hashtagFilterId || $hashtags) && $filter == 'all' && !$isSortingRequest) {

			$totalFeatured = $this->config->get('video.layout.featured.total');

			$options['featured'] = true;
			$options['limit'] = $totalFeatured ? $totalFeatured : false ;
			$featuredVideos = $model->getVideos($options);
		}

		return $this->view->call(__FUNCTION__, $videos, $featuredVideos, $pagination, $filter, $adapter, $rawUid, $uid, $type, $hashtags, $tagsFilter, $isSortingRequest, $from, $category, $activeCategory, $cluster);
	}

	/**
	 * Deletes a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		// Ensure that the user is really allowed to delete the video
		if (!$video->canDelete()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_DELETE'), 500);
		}

		// Try to delete the video now
		$state = $video->delete();

		if (!$state) {
			throw ES::exception($video->getError(), 500);
		}

		// Set the success message
		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_DELETE_SUCCESS');

		return $this->view->call(__FUNCTION__, $video);
	}

	/**
	 * Unfeatures a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function unfeature()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the video
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table->uid, $table->type, $table);

		// Ensure that the video can be featured
		if (!$video->canUnfeature()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_UNFEATURE'), 500);
		}

		// Feature the video
		$video->removeFeatured();

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_UNFEATURED_SUCCESS');

		return $this->view->call(__FUNCTION__, $video);
	}


	/**
	 * Features a video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function feature()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the video
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table->uid, $table->type, $table);

		// Get the callback url
		$callback = $this->input->get('return', '', 'default');

		// Ensure that the video can be featured
		if (!$video->canFeature()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_FEATURE'), 500);
		}

		// Feature the video
		$video->setFeatured();

		$this->view->setMessage('COM_EASYSOCIAL_VIDEOS_FEATURED_SUCCESS');

		return $this->view->call(__FUNCTION__, $video, $callback);
	}

	/**
	 * Processes a video creation
	 *
	 * @since	3.2.16
	 * @access	public
	 */
	public function cancelSave()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$fileUploaded = $this->input->get('fileUploaded', false, 'bool');
		$video = '';

		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->post->get('type', SOCIAL_TYPE_USER, 'word');

		$options = array();

		if ($uid && $type && $type != SOCIAL_TYPE_USER) {
			$options['uid'] = $uid;
			$options['type'] = $type;
		}

		// generate the cancel link;
		$returnLink = ESR::videos($options, false);

		if ($id && $fileUploaded) {

			// let delete this video.
			$table = ES::table('Video');
			$table->load($id);

			if ($table->id && $table->isUpload() && ($table->user_id == $this->my->id)) {
				$video = ES::video($uid, $type, $table);

				if ($video->isNew() && $video->isUnpublished()) {

					// delete the uploaded video file.
					$video->deleteStorage();

					// now delete video record using jtable so that author will not get deduct their points.
					$table->delete();
				} else {

					// set the redirect link back to video page.
					$returnLink = $video->getPermalink();
				}
			}
		}

		// now let redirect user to videos page.
		return $this->view->setRedirection($returnLink);
	}

	/**
	 * Processes a video creation
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function save()
	{
		ES::requireLogin();
		ES::checkToken();

		// Check if there is new file uploaded
		$fileUploaded = $this->input->get('fileUploaded', false, 'bool');

		// Get the posted data
		$post = $this->input->post->getArray();

		// This video could be edited
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->post->get('type', SOCIAL_TYPE_USER, 'word');
		$desc = $this->input->get('description', '', 'raw');

		$table = ES::table('Video');
		$table->load($id);

		if ($table->id) {
			$uid = $table->uid;
			$type = $table->type;

			$post['uid'] = $uid;
			$post['type'] = $type;
		}

		$video = ES::video($uid, $type, $table);

		// Determines if this is a new video
		$isNew = $video->isNew();

		// If this is a new video, we should check against their permissions to create
		if (!$video->allowCreation() && $video->isNew()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_ADDING_VIDEOS'), 500);
		}

		// Ensure that the user can really edit this video
		if (!$isNew && !$video->canEdit()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING'), 500);
		}

		$options = [];

		// Video upload will create stream once it is published.
		// We will only create a stream here when it is an external link.
		if ($post['source'] != SOCIAL_VIDEO_UPLOAD) {
			$options = array('createStream' => true);
		}

		// If the source is from external link, we need to format the url properly.
		if ($post['source'] == 'link') {
			$post['link'] = $video->format($post['link']);
		}

		if ($post['source'] == SOCIAL_VIDEO_UPLOAD && $isNew && $fileUploaded) {
			// since we know this is a brand new video and there is a video being upload,
			// here we set the state back to pending. #4032
			$post['state'] = SOCIAL_VIDEO_PENDING;
		}

		// Retrieve video description data
		if ($desc) {
			$desc = ES::string()->filterHtml($desc);
			$post['description'] = $desc;
		}

		// We need to skip file validate since the file is already been validated
		$options['skipFileValidation'] = true;

		// Save the video
		$state = $video->save($post, [], $options);

		// Load up the session
		$session = JFactory::getSession();

		if (!$state) {

			// Store the data in the session so that we can repopulate the values again
			$data = json_encode($video->export());

			$session->set('videos.form', $data, SOCIAL_SESSION_NAMESPACE);

			$this->view->setMessage($video->getError(), ES_ERROR);

			return $this->view->call(__FUNCTION__, $video, $isNew, $fileUploaded);
		}

		// Once a video is created successfully, remove any data associated from the session
		$session->set('videos.form', null, SOCIAL_SESSION_NAMESPACE);

		return $this->view->call(__FUNCTION__, $video, $isNew, $fileUploaded);
	}

	/**
	 * Creates a new video from the story
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function uploadStory()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the file data
		$file = $this->input->files->get('file');

		// Since user can't set the title of the video when uploading via the story form, we need to generate a title for it
		// based on the name of the video file.
		$data = array();

		// Format the title since the title is the file name
		$data['title'] = ucfirst(JFile::stripExt($file['name']));
		$data['source'] = 'upload';

		// Get a default category to house the video
		$model = ES::model('Videos');
		$category = $model->getDefaultCategory();

		$data['category_id'] = $category->id;

		// Determine this process do not notify anyone yet since this is upload file only without save
		$saveOptions['isUploadFileProcess'] = true;

		// Get the uid and type
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'word');

		$video = ES::video($uid, $type);
		$state = $video->save($data, $file, $saveOptions);

		if (!$state) {
			$this->view->setMessage($video->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $video);
		}

		// Determines if the video should be processed immediately or it should be set under pending mode
		if ($this->config->get('video.autoencode')) {
			// After creating the video, process it
			$video->process();
		} else {
			// Just take a snapshot of the video
			$video->snapshot();
		}

		return $this->view->call(__FUNCTION__, $video);
	}

	/**
	 * Upload a new video from the form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function uploadFile()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the file data
		$file = $this->input->files->get('video', '');

		$data = array();

		// Format the title since the title is the file name
		$data['title'] = ucfirst(JFile::stripExt($file['name']));
		$data['source'] = 'upload';

		// Get a default category to house the video
		$model = ES::model('Videos');
		$category = $model->getDefaultCategory();

		$data['category_id'] = $category->id;

		// Get the uid and type
		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER);
		$isEditing = $this->input->get('isEditing', false);

		// Determine this process do not notify anyone yet since this is upload file only without save
		$saveOptions['isUploadFileProcess'] = true;

		// if this it is editing video, we load the video table
		if ($isEditing) {
			$table = ES::table('Video');
			$table->load($isEditing);

			$video = ES::video($uid, $type, $table);
		} else {
			$video = ES::video($uid, $type);
		}

		$state = $video->save($data, $file, $saveOptions);

		return $this->view->call(__FUNCTION__, $video);
	}

	/**
	 * Allows caller to remove a tag from the video
	 *
	 * @since	1.4
	 * @access	public
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
		$table = ES::table('Video');
		$table->load($tag->target_id);

		$video = ES::video($table->uid, $table->type, $table);

		if (!$video->canRemoveTag($tag)) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_REMOVE_TAGS'), 500);
		}

		// Delete the tag
		$tag->delete();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to quickly tag people in this video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function tag()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the user id's.
		$ids = $this->input->get('ids', array(), 'array');

		// Get the video
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table->uid, $table->type, $table);

		// Insert the user tags
		$tags = $video->insertTags($ids);

		return $this->view->call(__FUNCTION__, $video, $tags);
	}

	/**
	 * Checks the status
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function status()
	{
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$file = $this->input->get('file', '', 'raw');
		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'word');
		$unpublished = $this->input->get('unpublished', 0, 'int');

		// Load the video
		$video = ES::video($uid, $type, $id);

		// Get the status of the video
		$status = $video->status();

		// If the video is processed successfully, publish the video now.
		if ($status === true) {

			// If needed, we need to delete the original video from the site
			if ($video->isUpload() && $this->config->get('video.delete')) {
				@JFile::delete($video->table->original);
			}

			// If process via story form, do not directly publish the video. #819
			if ($unpublished) {
				$video->unpublish();
			} else {
				$createStream = $this->input->get('createStream', true, 'bool');

				if (!$video->isNew()) {
					$createStream = false;
				}

				$video->publish(['createStream' => $createStream]);
			}
		}

		return $this->view->call(__FUNCTION__, $video, $status);
	}

	/**
	 * Initiates a process request to convert video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function process()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the video that we are trying to convert here.
		$id = $this->input->get('id', 0, 'int');
		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		// @TODO: Check if the user is really allowed to process this video
		if (!$video->canProcess()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_PROCESS_VIDEO'), 500);
		}

		// Only allow processing if the video is in pending state
		if (!$video->isPendingProcess()) {
			throw ES::exception(JText::_('Not pending state'), 500);
		}

		// Run the video process
		$video->process();

		return $this->view->call(__FUNCTION__, $video);
	}

	/**
	 * Process video link from the video form
	 *
	 * @since	3.1.6
	 * @access	public
	 */
	public function processLink()
	{
		// Get the link from the video form
		$link = $this->input->get('link', '', 'default');

		// Remove any space on the left or right side of the string
		$link = trim($link);

		$video = ES::video();

		if ($video->hasExceededLimit()) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_VIDEOS_EXCEEDED_LIMIT'));
		}

		if (!$video->isValidUrl($link)) {
			return $this->ajax->reject(JText::_('COM_ES_VIDEO_LINK_EMBED_NOT_SUPPORTED'));
		}

		// Format the video link
		$link = $video->format($link);

		$crawler = ES::crawler();
		$data = $crawler->scrape($link);

		// Make there is oembed data if not do not proceed
		if (!$data || !isset($data->oembed) || !$data->oembed || empty($data->oembed) || is_null($data->oembed) || isset($data->oembedError) || (!isset($data->oembed->html) && !isset($data->opengraph->video))) {

			$message = JText::_('COM_ES_VIDEO_LINK_EMBED_NOT_SUPPORTED');

			if (isset($data->oembedError) && $data->oembedError) {
				$message = $data->oembedError;
			}

			return $this->ajax->reject($message);
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
		$video = ES::video($uid, $utype);
		$adapter = $video->getAdapter();
		$uploadLimit = $adapter->getUploadLimit();
		$maxUploadSize = ES::math()->convertBytes($uploadLimit);

		if ($size > $maxUploadSize) {
			return $this->ajax->reject(JText::sprintf('COM_EASYSOCIAL_VIDEOS_EXCEEDED_ALLOWED_FILESIZE', $uploadLimit));
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

	/**
	 * To revert the video thumbnail to use it's original thumbnail.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function revertThumbnail()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->view->exception('COM_ES_VIDEOS_INVALID_ID_PROVIDED');
		}

		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		// make sure the current user can edit video.
		if (!$video->canEdit()) {
			return $this->view->exception('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING');
		}

		$state = $video->revertThumbnail();
		$uri = $video->getThumbnail();

		$msg =  JText::_('COM_ES_VIDEOS_THUMB_REVERTED_SUCCESSFULLY');

		return $this->ajax->resolve($uri, $msg);
	}


	/**
	 * Allows caller to create an avatar by posted the $_FILE data
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function createThumbnailFromFile()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the unique item id
		$id = $this->input->get('id', 0, 'int');
		$file = $this->input->files->get('files', array(), 'raw');
		$isCapture = $this->input->get('isCapture', 0, 'int');

		if (!$id) {
			return $this->resolveThumbnailResponse(array('error' => true, 'message' => JText::_('COM_ES_VIDEOS_INVALID_ID_PROVIDED')));
		}

		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		if (!$video->canEdit()) {
			return $this->resolveThumbnailResponse(array('error' => true, 'message' => JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING')));
		}

		try {
			$state = $video->processCustomThumbnail($file);
		} catch (Exception $err) {
			return $this->resolveThumbnailResponse(array('error' => true, 'message' => $err->getMessage()));
		}

		$msg =  JText::_('COM_ES_VIDEOS_THUMB_SAVED_SUCCESSFULLY');

		return $this->resolveThumbnailResponse(array('url' => $state, 'message' => $msg));
	}

	/**
	 * a wrapper function to determine the ajax response type for thumbnail upload process
	 *
	 * @since   3.3
	 * @access  public
	 */
	private function resolveThumbnailResponse($data)
	{
		$url = isset($data['url']) ? $data['url'] : false;
		$msg = isset($data['message']) ? $data['message'] : '';
		$error = isset($data['error']) ? true : false;

		$isCapture = $this->input->get('isCapture', 0, 'int');

		if ($isCapture) {
			if ($error) {
				$result = array('error' => 1, 'message' => $msg);
				echo json_encode($result);
				exit;
			}

			$result = array('url' => $url, 'message' => $msg);
			echo json_encode($result);
			exit;
		}

		if ($error) {
			return $this->view->exception($msg);
		}

		return $this->ajax->resolve($url, $msg);
	}
}
