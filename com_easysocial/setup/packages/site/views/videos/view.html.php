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

ES::import('site:/views/views');

class EasySocialViewVideos extends EasySocialSiteView
{
	/**
	 * Renders the all videos page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::checkCompleteProfile();
		ES::setMeta();

		// Default page title 'All Videos'
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_ALL');

		// Determines the current filter being viewed
		$helper = $this->getHelper('List');

		$filter = $helper->getCurrentFilter();
		$activeCategory = $helper->getActiveCategory();
		$cluster = $helper->getCluster();
		$uid = $helper->getUid();
		$type = $helper->getType();

		// Ensure that the user is truly viewing a valid cluster
		if ($cluster && !$cluster->id) {
			return $this->redirectToView('dashboard');
		}

		// make sure the user is not blocked. #5069
		if ($type == SOCIAL_TYPE_USER) {
			$activeUser = ES::user($uid);
			if ($activeUser->isBlock() && !$this->my->isSiteAdmin()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
			}
		}

		$sort = $this->input->get('sort', 'latest', 'word');
		$from = $helper->getFrom();
		$browseView = $helper->isBrowseView();

		// Probably the user is filtering videos by hashtag
		$hashtags = $helper->getActiveHashtag();

		// this checking is to prevent user from entering the invalid valid which might cause php fatal error on later processing.
		if (!$sort) {
			$sort = 'latest';
		}

		// Prepare the options
		$options = [
			'filter' => $filter,
			'category' => $activeCategory ? $activeCategory->id : 0,
			'featured' => false,
			'sort' => $sort ? $sort : '',
			'uid' => $cluster ? $cluster->id : null,
			'type' => $cluster ? $cluster->getType() : null
		];

		$customFilter = $helper->getActiveCustomFilter();

		if ($customFilter) {
			$options['includeFeatured'] = true;
			$options['hashtags'] = $customFilter->getHashtag();
		}

		// If user is viewing my specific filters, we need to update the title accordingly.
		if ($filter && $filter != 'category' && $filter != 'customFilter' && !$customFilter) {
			$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_' . strtoupper($filter));
		}

		if ($activeCategory) {
			$this->page->description($helper->getPageCategoryDesc());
		}

		// Only for clusters
		if ($cluster && $cluster->id) {
			$cluster->renderPageTitle(null, 'videos');
			$cluster->hit();
		}

		$adapter = $helper->getAdapter();

		if (!$adapter) {
			return $this->redirectToView('dashboard');
		}

		$adapter->setBreadcrumbs($this->getLayout());

		// Determines if the user can access this videos section.
		// Instead of showing user 404 page, just show the restricted area.
		if (!$adapter->canAccessVideos()) {
			return $this->restricted($uid, $type);
		}

		if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {

			// If user is viewing their own videos, we should use filter = mine
			$options['filter'] = SOCIAL_TYPE_USER;

			if ($uid != $this->my->id) {
				$options['userid'] = $uid;
			}

			if ($uid == $this->my->id) {
				$options['filter'] = 'mine';
				$options['featured'] = false;
			}

			$options['uid'] = $uid;
			$options['type'] = $type;
		}

		// this checking used in normal videos to include the featured videos when 'featured' filter clicked.
		if ($filter == 'featured') {
			$options['featured'] = true;
		}

		if ($filter == 'mine') {
			$options['featured'] = false;
		}

		// For pending filters, we only want to retrieve videos uploaded by the current user
		if ($filter == 'pending') {
			$options['userid'] = $this->my->id;
		}

		$options['limit'] = ES::getLimit('videos_limit', 20);

		if ($hashtags) {
			$options['hashtags'] = $hashtags;
			$options['includeFeatured'] = true;
		}

		// Get a list of videos from the site
		$model = ES::model('Videos');
		$videos = $model->getVideos($options);
		$pagination = $model->getPagination();

		// Featured videos
		$featuredVideos = [];
		$totalFeatured = $this->config->get('video.layout.featured.total');

		if (!$customFilter && !$hashtags) {
			$options['featured'] = true;
			$options['maxlimit'] = $totalFeatured;
			$options['limit'] = null;

			$featuredVideos = $model->getVideos($options);
		}

		$pageTitle = $helper->getPageTitle();

		if ($pageTitle) {
			$this->page->title($pageTitle);
		}

		$canonicalUrl = $helper->getCanonicalUrl();
		$this->page->canonical($canonicalUrl);

		$sortItems = $helper->getSortables();

		// check if we need to display counters on the first page load.
		// most likely is for mobile view. # 4945
		$displayCounters = false;
		if (ES::themes()->isMobile()) {
			$displayCounters = $this->config->get('video.counters') ? true : false;
		}
		$categories = $helper->getCategories($displayCounters);

		$returnUrl = $helper->getReturnUrl();
		$canCreateFilter = $helper->canCreateFilter();
		$allowCreation = $helper->canCreateVideo();
		$createLink = $helper->getCreateLink();
		$filtersAcl = $helper->getFiltersAcl();
		$customFilters = $helper->getCustomFilters();
		$activeCustomFilter = $helper->getActiveCustomFilter();
		$titles = $helper->getPageTitles();
		$createCustomFilterLink = $helper->getCreateCustomFilterLink();

		$this->set('createCustomFilterLink', $createCustomFilterLink);
		$this->set('titles', $titles);
		$this->set('activeCustomFilter', $activeCustomFilter);
		$this->set('customFilters', $customFilters);
		$this->set('filtersAcl', $filtersAcl);
		$this->set('createLink', $createLink);
		$this->set('allowCreation', $allowCreation);
		$this->set('canCreateFilter', $canCreateFilter);
		$this->set('hashtags', $hashtags);
		$this->set('customFilter', $customFilter);
		$this->set('browseView', $browseView);
		$this->set('returnUrl', $returnUrl);
		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('adapter', $adapter);
		$this->set('cluster', $cluster);
		$this->set('featuredVideos', $featuredVideos);
		$this->set('activeCategory', $activeCategory);
		$this->set('filter', $filter);
		$this->set('videos', $videos);
		$this->set('categories', $categories);
		$this->set('sort', $sort);
		$this->set('pagination', $pagination);
		$this->set('sortItems', $sortItems);
		$this->set('from', $from);

		if ($featuredVideos && $filter != 'featured') {
			$theme = ES::themes();
			$theme->set('hashtags', false);
			$theme->set('customFilter', false);
			$theme->set('browseView', $browseView);
			$theme->set('type', $type);
			$theme->set('isFeatured', true);
			$theme->set('featuredVideos', $featuredVideos);
			$theme->set('videos', $featuredVideos);
			$theme->set('returnUrl', $returnUrl);
			$theme->set('sort', $sort);
			$theme->set('sortItems', $sortItems);
			$theme->set('pagination', '');
			$theme->set('from', $from);
			$theme->set('cluster', $cluster);
			$theme->set('uid', $uid);
			$theme->set('type', $type);

			$theme->set('featuredVideoLink', $adapter->getAllVideosLink('featured'));

			$featuredOutput = $theme->output('site/videos/default/item.list');
			$this->set('featuredOutput', $featuredOutput);
		}

		parent::display('site/videos/default/default');
	}

	/**
	 * Renders all videos from current logged in user.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function mine($tpl = null)
	{
		// check if the users is logged in or not.
		if ($this->my->guest) {
			ES::requireLogin();
		}

		$this->input->set('uid', $this->my->id);
		$this->input->set('type', SOCIAL_TYPE_USER);

		return $this->display($tpl);
	}

	/**
	 * Category Layout (Deprecated)
	 * We no longer use single category video layout as everything is handle in display function
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function category($tpl = null)
	{
		// Get category id
		$categoryId = $this->input->get('id', 0, 'int');

		// Set back the id to the request
		$this->input->set('categoryId', $categoryId);

		// Redirect to main view
		return $this->display();
	}

	/**
	 * Process the video author
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function processAuthor($videos, $cluster)
	{
		foreach ($videos as &$video) {
			$video->creator = $video->getVideoCreator($cluster);
		}

		return $videos;
	}

	/**
	 * Displays a restricted page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function restricted($uid = null, $type = SOCIAL_TYPE_USER)
	{
		$label = 'COM_EASYSOCIAL_VIDEOS_RESTRICTED';
		$text = 'COM_ES_RESTRICTED_AREA_DESC';

		// Cluster types
		$clusterTypes = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT);

		if ($type == SOCIAL_TYPE_USER) {
			$node = ES::user($uid);
		}

		if (in_array($type, $clusterTypes)) {
			$node = ES::cluster($type, $uid);

			if (!$node->id || ($node->isInviteOnly() && !$node->isMember())) {
				// We should not show the invite-only cluster if the current user is not a member of it
				return ES::raiseError(404, JText::_('COM_EASYSOCIAL_' . $type . '_NOT_FOUND'));
			}
		}

		$this->set('showProfileHeader', true);
		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('node', $node);
		$this->set('label', $label);
		$this->set('text', $text);

		echo parent::display('site/videos/restricted');
	}

	/**
	 * Displays the single video item
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function item()
	{
		ES::setMeta();

		// Get the video id
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Video');
		$table->load($id);

		if (!$table->id) {
			// video not found. redirect to error page.
			ES::raiseError(404, JText::_('COM_ES_VIDEOS_INVALID_VIDEO_ID_PROVIDED'));
		}

		// Make sure only video from published category is viewable.
		$videoCategory = ES::table('VideoCategory');
		$videoCategory->load($table->category_id);

		if ($table->isUnpublished() || !$videoCategory->isPublished()) {
			ES::raiseError(404, JText::_('COM_ES_VIDEOS_NO_FOUND'));
		}

		// Load up the video
		$video = ES::video($table->uid, $table->type, $table);

		// Ensure that the viewer can really view the video
		if (!$video->isViewable()) {
			return $this->restricted($table->uid, $table->type);
		}

		$from = $this->input->get('from', '', 'default');

		// Add canonical tags
		$this->page->canonical($video->getPermalink(true, null, null, false, true));

		// Set the page title
		$this->page->title($video->getTitle());

		// Add oembed tag
		$this->page->oembed($video->getExternalPermalink('oembed'));

		$video->setBreadcrumbs($this->getLayout());

		// Whenever a viewer visits a video, increment the hit counter
		$video->hit();

		// Retrieve the reports library
		$reports = $video->getReports();

		$streamId = $video->getStreamId('create');

		// Retrieve the comments library
		$comments = $video->getComments('create', $streamId);

		// Retrieve the likes library
		$likes = $video->getLikes('create', $streamId);

		// Retrieve the repost data
		$videoClusterId = ($video->type != SOCIAL_APPS_GROUP_USER) ? $video->uid : '0';
		$videoClusterType = ($video->type != SOCIAL_APPS_GROUP_USER) ? $video->type : '';

		$repost = $video->reposts($videoClusterId, $videoClusterType);

		// Retrieve the privacy library
		$privacyButton = $video->getPrivacyButton();

		// Retrieve the sharing library
		$sharing = $video->getSharing();

		// Retrieve users tagging
		$usersTags = $video->getEntityTags();
		$usersTagsList = '';

		if ($usersTags) {
			$usersTagsArray = array();

			foreach ($usersTags as $tag) {
				$usersTagsArray[] = $tag->item_id;
			}

			$usersTagsList = json_encode($usersTagsArray);
		}

		// Retrive tags
		$tags = $video->getTags();

		// Retrieve the cluster associated with the video
		$cluster = $video->getCluster();

		// Build user alias
		$creator = $video->getVideoCreator($cluster);

		// Render meta headers
		$video->renderHeaders();

		// Get random videos from the same category
		$otherVideos = array();

		// Get display other video type
		$otherVideoType = $this->config->get('video.layout.item.recent');

		// Do not skip this if set to any type
		if ($otherVideoType) {

			$options = array('exclusion' => $video->id, 'limit' => $this->config->get('video.layout.item.total'));

			if ($otherVideoType == SOCIAL_VIDEO_OTHER_CATEGORY) {
				$options['category'] = $video->category_id;
			}

			$model = ES::model('Videos');
			$otherVideos = $model->getVideos($options);
		}

		// Update the back link if there is an "uid" or "type" in the url
		$uid = $this->input->get('uid', '', 'int');
		$type = $this->input->get('type', '', 'string');
		$backLink = ESR::videos();

		if (!$uid && !$type) {
			// we will try to get from the current active menu item.
			$menu = $this->app->getMenu();
			if ($menu) {
				$activeMenu = $menu->getActive();

				$xQuery = $activeMenu->query;
				$xView = isset($xQuery['view']) ? $xQuery['view'] : '';
				$xLayout = isset($xQuery['layout']) ? $xQuery['layout'] : '';
				$xId = isset($xQuery['id']) ? (int) $xQuery['id'] : '';

				if ($xView == 'videos' && $xLayout == 'item' && $xId == $video->id) {
					if ($cluster) {
						$uid = $video->uid;
						$type = $video->type;
					}
				}
			}
		}

		if ($from == 'user') {
			$backLink = ESR::videos(array('uid' => $video->getAuthor()->getAlias(), 'type' => 'user'));

		} else if ($uid && $type && $from != 'listing') {
			$backLink = $video->getAllVideosLink();
		}

		$navigation = $video->getNavigation();

		// Generate a return url
		$returnUrl = base64_encode($video->getPermalink());

		$this->set('returnUrl', $returnUrl);
		$this->set('usersTagsList', $usersTagsList);
		$this->set('otherVideos', $otherVideos);
		$this->set('backLink', $backLink);
		$this->set('tags', $tags);
		$this->set('usersTags', $usersTags);
		$this->set('sharing', $sharing);
		$this->set('reports', $reports);
		$this->set('comments', $comments);
		$this->set('likes', $likes);
		$this->set('privacyButton', $privacyButton);
		$this->set('video', $video);
		$this->set('creator', $creator);
		$this->set('cluster', $cluster);
		$this->set('navigation', $navigation);

		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('repost', $repost);

		echo parent::display('site/videos/item/default');
	}

	/**
	 * Displays the form to create a video
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function form()
	{
		// Only logged in users should be allowed to create videos
		ES::requireLogin();
		ES::setMeta();

		// Determines if a video is being edited
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', null, 'int');
		$type = $this->input->get('type', null, 'word');

		// Load the video
		$video = ES::video($uid, $type, $id);

		if (in_array($type, array(SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_GROUP))) {
			$clusters = ES::cluster($type, $uid);
		}

		// Retrieve any previous data
		$session = JFactory::getSession();
		$data = $session->get('videos.form', null, SOCIAL_SESSION_NAMESPACE);

		if ($data) {
			$data = json_decode($data);

			// Ensure that it matches the id
			if (!$video->id || ($video->id && $video->id == $data->id)) {
				$video->bind($data);
			}
		}

		// Ensure that the user can really create new videos
		if (!$id && !$video->canUpload() && !$video->canEmbed()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_ADDING_VIDEOS'), 500);
		}

		// Ensure that the video is really editable by the user
		if ($id && !$video->isEditable()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING'), 500);
		}

		// When admin disables video upload and they try to edit a video that was previously uploaded
		if ($id && $video->isUpload() && !$video->canUpload()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING_FEATURE_DISABLED'), 500);
		}

		// When admin disables external videos and they try to edit a video that was previously added with external video link
		if ($id && $video->isLink() && !$video->canEmbed()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_EDITING_FEATURE_DISABLED'), 500);
		}

		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_CREATE_VIDEO');

		if ($id && !$video->isNew()) {
			$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_EDIT_VIDEO');
		}

		$model = ES::model('Videos');

		// Pre-selection of a category
		$defaultCategory = $model->getDefaultCategory();
		$defaultCategory = $defaultCategory ? $defaultCategory->id : 0;

		$defaultCategory = $this->input->get('categoryId', $defaultCategory, 'int');

		// Get a list of video categories
		$options = array();

		if (!$this->my->isSiteAdmin()) {
			$options = array('respectAccess' => true, 'profileId' => $this->my->getProfile()->id);
		}

		$options['ordering'] = 'ordering';

		$categories = $model->getCategories($options);

		$selectedCategory = $video->category_id ? $video->category_id : $defaultCategory;

		$privacy = ES::privacy();

		// Retrieve video tags
		$userTags = $video->getEntityTags();
		$userTagItemList = array();

		if ($userTags) {
			foreach($userTags as $userTag) {
				$userTagItemList[] = $userTag->item_id;
			}
		}

		$hashtags = $video->getTags(true);

		$isCluster = ($uid && $type && $type != SOCIAL_TYPE_USER) ? true : false;
		$type = $isCluster ? $type : SOCIAL_TYPE_USER;

		$isPrivateCluster = false;

		if ($isCluster) {
			$cluster = $video->getCluster();

			if (!$cluster || !$cluster->id) {
				return $this->redirectToView('dashboard');
			}

			$isPrivateCluster = $cluster->isOpen() ? false : true;
		}

		// Construct the cancel link
		$options = array();

		if ($uid && $type) {
			$options['uid'] = $uid;
			$options['type'] = $type;
		}

		$returnLink = ESR::videos($options);

		if ($video->id) {
			$returnLink = $video->getPermalink();
		}

		// Get the maximum file size allowed
		$uploadLimit = $video->getUploadLimit();

		$video->setBreadcrumbs($this->getLayout());

		// Retrieve the editor as what you set from the setting
		$defaultEditor = $this->config->get('video.layout.item.editor', 'none');
		$editor = ES::editor()->getEditor($defaultEditor);

		$this->set('returnLink', $returnLink);
		$this->set('uploadLimit', $uploadLimit);
		$this->set('defaultCategory', $defaultCategory);
		$this->set('selectedCategory', $selectedCategory);
		$this->set('userTags', $userTags);
		$this->set('userTagItemList', $userTagItemList);
		$this->set('hashtags', $hashtags);
		$this->set('video', $video);
		$this->set('privacy', $privacy);
		$this->set('categories', $categories);
		$this->set('isCluster', $isCluster);
		$this->set('isPrivateCluster', $isPrivateCluster);
		$this->set('type', $type);
		$this->set('editor', $editor);
		$this->set('defaultEditor', $defaultEditor);

		return parent::display('site/videos/form/default');
	}

	/**
	 * Displays the process to transcode the video
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function process()
	{
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'word');

		$video = ES::video($uid, $type, $id);

		// Ensure that the current user really owns this video
		if (!$video->canProcess()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_PROCESS'), 500);
		}

		$cluster = null;

		if ($uid && $type) {
			$cluster = ES::cluster($type, $uid);
		}

		$this->set('cluster', $cluster);
		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('video', $video);

		echo parent::display('site/videos/process/default');
	}

	/**
	 * Post process after a video is deleted from the site
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function delete($video)
	{
		$this->info->set($this->getMessage());

		$redirect = $video->getAllVideosLink('', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a filter is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteFilter($cid, $clusterType)
	{
		$video = ES::video($cid, $clusterType);

		$this->info->set($this->getMessage());

		$redirect = $video->getAllVideosLink('', false);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a video is unfeatured on the site
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function unfeature($video, $callback = null)
	{
		$this->info->set($this->getMessage());

		$redirect = $video->getAllVideosLink('featured', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a video is featured on the site
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function feature($video, $callback = null)
	{
		$this->info->set($this->getMessage());

		$redirect = $video->getAllVideosLink('featured', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a video is stored
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function save(SocialVideo $video, $isNew, $file)
	{
		// If there's an error, redirect them back to the form
		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());

			$options = array('layout' => 'form');

			if (!$video->isNew()) {
				$options['id'] = $video->id;
			}

			if ($video->isCreatedInCluster()) {
				$options['uid'] = $video->uid;
				$options['type'] = $video->type;
			}

			$url = FRoute::videos($options, false);

			return $this->redirect($url);
		}

		$message = 'COM_EASYSOCIAL_VIDEOS_ADDED_SUCCESS';

		if (!$isNew) {
			$message = 'COM_EASYSOCIAL_VIDEOS_UPDATED_SUCCESS';
		}

		// If this is a video link, we should just redirect to the video page.
		if ($video->isLink()) {

			$url = $video->getPermalink(false);

			$this->setMessage($message, SOCIAL_MSG_SUCCESS);
			$this->info->set($this->getMessage());

			return $this->redirect($url);
		}


		// Should we redirect the user to the progress page or redirect to the pending video page
		$options = array('id' => $video->getAlias());

		if ($isNew && $file || !$isNew && $file) {
			// If video will be processed by cronjob, do not redirect to the process page
			if (!$this->config->get('video.autoencode')) {
				$options = array('filter' => 'pending');

				if ($isNew) {
					$message = 'COM_EASYSOCIAL_VIDEOS_UPLOAD_SUCCESS_AWAIT_PROCESSING';
				}
			} else {
				$options['layout'] = 'process';
				$message = 'COM_EASYSOCIAL_VIDEOS_UPLOAD_SUCCESS_PROCESSING_VIDEO_NOW';
			}
		}

		if (!$isNew && !$file && $video->isPublished()) {
			$options['layout'] = 'item';
		}

		$this->setMessage($message, SOCIAL_MSG_SUCCESS);
		$this->info->set($this->getMessage());

		if ($video->isCreatedInCluster()) {
			$options['uid'] = $video->uid;
			$options['type'] = $video->type;
		}

		$url = ESR::videos($options, false);
		return $this->redirect($url);
	}

	/**
	 * Checks if this feature should be enabled or not.
	 *
	 * @since	1.4
	 * @access	private
	 */
	protected function isFeatureEnabled()
	{
		// Do not allow user to access groups if it's not enabled
		if (!$this->config->get('video.enabled')) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PAGE_NOT_FOUND'));
		}
	}

	/**
	 * Post processing after tag filters is saved
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveFilter($uid, $clusterType)
	{
		$video = ES::video($uid, $clusterType);

		$this->info->set($this->getMessage());

		$redirect = $video->getAllVideosLink();
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}
}
