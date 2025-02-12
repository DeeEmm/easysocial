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

class EasySocialViewAudios extends EasySocialSiteView
{
	/**
	 * Renders the all audios page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::checkCompleteProfile();
		ES::setMeta();

		$helper = $this->getHelper('List');

		$uid = $helper->getUid();
		$type = $helper->getType();

		$filter = $helper->getCurrentFilter();
		$sort = $this->input->get('sort', 'latest', 'word');

		$customFilter = $helper->getActiveCustomFilter();
		$hashtags = $helper->getActiveHashtag();

		$activePlaylist = $helper->getActivePlaylist();
		$activeGenre = $helper->getActiveGenre();
		$cluster = $helper->getCluster();
		$browseView = $helper->isBrowseView();
		$from = $helper->getFrom();

		$adapter = $helper->getAdapter();

		// User trying to spoof something
		if (!$adapter) {
			return $this->redirectToView('dashboard');
		}

		// make sure the user is not blocked. #5069
		if ($type == SOCIAL_TYPE_USER) {
			$activeUser = ES::user($uid);
			if ($activeUser->isBlock() && !$this->my->isSiteAdmin()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
			}
		}

		// Determines if the user can access this audios section.
		// Instead of showing user 404 page, just show the restricted area.
		if (!$adapter->canAccessAudios()) {
			return $this->restricted($uid, $type);
		}

		// Prepare the options
		$options = [
			'list_id' => $activePlaylist ? $activePlaylist->id : null,
			'filter' => $filter,
			'genre' => $activeGenre ? $activeGenre->id : null,
			'featured' => false,
			'sort' => $sort,
			'uid' => $uid ? $uid : null,
			'type' => $type ? $type : null
		];

		if ($customFilter) {
			$options['includeFeatured'] = true;
			$hashtags = $customFilter->getHashtag();
		}

		// Viewing a user's audios
		if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {
			$options['filter'] = SOCIAL_TYPE_USER;

			if ($uid == $this->my->id) {
				$options['filter'] = 'mine';
				$options['featured'] = false;
			}

			if ($uid != $this->my->id) {
				$options['userid'] = $uid;
			}
		}

		// this checking used in normal audios to include the featured audios when 'featured' filter clicked.
		if ($filter == 'featured') {
			$options['featured'] = true;
		}

		if ($filter == 'mine') {
			$options['featured'] = false;
		}

		// For pending filters, we only want to retrieve audios uploaded by the current user
		if ($filter == 'pending') {
			$options['userid'] = $this->my->id;
		}

		$options['limit'] = ES::getLimit('audios_limit', 20);

		if ($hashtags) {
			$options['hashtags'] = $hashtags;
			$options['includeFeatured'] = true;
		}

		// Get the output from the playlist
		$playlistOutput = $helper->getPlayListHtml();

		$model = ES::model('Audios');
		$audios = $model->getAudios($options);
		$pagination = $model->getPagination();

		// Get featured audios
		$featuredAudios = array();

		if (!$customFilter && !$hashtags) {
			$options['featured'] = true;
			$options['limit'] = false;
			$featuredAudios = $model->getAudios($options);
		}

		$total = $helper->getTotal();
		$allowCreation = $helper->canCreateAudio();

		// Increase the hit
		$adapter->hit();

		$layout = $this->getLayout();
		$adapter->setBreadcrumbs($layout);

		// Generate the page title
		$title = $helper->getPageTitle();
		$this->page->title($title);

		if ($activeGenre) {
			$this->page->description($helper->getPageCategoryDesc());
		}

		$displayCounters = false;

		if (ES::themes()->isMobile()) {
			$displayCounters = $this->config->get('audio.counters') ? true : false;
		}

		$genres = $helper->getGenres($displayCounters);
		$returnUrl = $helper->getReturnUrl();
		$sortItems = $helper->getSortables();
		$lists = $helper->getPlayLists();

		$canonicalUrl = $helper->getCanonicalUrl();
		$this->page->canonical($canonicalUrl);

		$canCreatePlaylist = $helper->canCreatePlayList();
		$showPendingAudios = $helper->showPendingAudios();
		$showMyAudios = $helper->showMyAudios();
		$createLink = $helper->getCreateLink();
		$canCreateFilter = $helper->canCreateFilter();
		$customFilters = $helper->getCustomFilters();
		$activeCustomFilter = $helper->getActiveCustomFilter();

		$this->set('createLink', $createLink);
		$this->set('showPendingAudios', $showPendingAudios);
		$this->set('canCreatePlaylist', $canCreatePlaylist);
		$this->set('browseView', $browseView);
		$this->set('returnUrl', $returnUrl);
		$this->set('showMyAudios', $showMyAudios);
		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('adapter', $adapter);
		$this->set('allowCreation', $allowCreation);
		$this->set('cluster', $cluster);
		$this->set('featuredAudios', $featuredAudios);
		$this->set('activeGenre', $activeGenre);
		$this->set('filter', $filter);
		$this->set('audios', $audios);
		$this->set('genres', $genres);
		$this->set('sort', $sort);
		$this->set('hashtags', $hashtags);
		$this->set('customFilter', $customFilter);
		$this->set('pagination', $pagination);
		$this->set('sortItems', $sortItems);
		$this->set('featuredAudios', $featuredAudios);
		$this->set('from', $from);
		$this->set('lists', $lists);
		$this->set('activePlaylist', $activePlaylist);
		$this->set('playlistOutput', $playlistOutput);
		$this->set('canCreateFilter', $canCreateFilter);
		$this->set('customFilters', $customFilters);
		$this->set('activeCustomFilter', $activeCustomFilter);
		$this->set('displayCounters', $displayCounters);

		if ($featuredAudios && $filter != 'featured' && $filter != 'list') {
			$theme = ES::themes();
			$theme->set('browseView', $browseView);
			$theme->set('uid', $uid);
			$theme->set('type', $type);
			$theme->set('isFeatured', true);
			$theme->set('audios', $featuredAudios);
			$theme->set('returnUrl', $returnUrl);
			$theme->set('sort', $sort);
			$theme->set('sortItems', $sortItems);
			$theme->set('pagination', '');
			$theme->set('from', $from);
			$theme->set('cluster', $cluster);
			$theme->set('lists', $lists);

			$featuredOutput = $theme->output('site/audios/default/item.list');
			$this->set('featuredOutput', $featuredOutput);
		}

		echo parent::display('site/audios/default/default');
	}

	/**
	 * Renders all audios from current logged in user.
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
	 * New Playlist form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function playlistform()
	{
		// Ensure that user is logged in.
		ES::requireLogin();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		if (! $this->config->get('audio.enabled')) {
			return $this->exception('COM_ES_AUDIO_ERROR_AUDIO_DISABLED');
		}

		$this->info->set($this->getMessage());

		// Get the list id.
		$id = $this->input->get('listId', 0, 'int');

		$list = ES::table('List');
		$list->load($id);

		if (!ES::lists()->canCreatePlaylist()) {
			return $this->exception('COM_ES_AUDIO_PLAYLISTS_ACCESS_NOT_ALLOWED');
		}

		// Check if this list is being edited.
		if ($id && !$list->id) {
			$this->setMessage('COM_ES_AUDIO_INVALID_PLAYLIST_ID_PROVIDED', SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::audios(array(), false));
		}

		// Set the page title
		$title = 'COM_ES_PAGE_TITLE_AUDIO_CREATE_PLAYLIST_FORM';

		if ($list->id) {
			$title = 'COM_ES_PAGE_TITLE_AUDIO_EDIT_PLAYLIST_FORM';
		}

		$this->set('list', $list);
		$this->set('id', $id);

		// Load theme files.
		echo parent::display('site/audios/playlistform/default');
	}

	/**
	 * Process the audio author
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function processAuthor($audios, $cluster)
	{
		$processedAudios = array();

		foreach ($audios as $audio) {
			$audio->creator = $audio->getAudioCreator($cluster);

			$processedAudios[] = $audio;
		}

		return $processedAudios;
	}

	/**
	 * Displays a restricted page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function restricted($uid = null, $type = SOCIAL_TYPE_USER)
	{
		$label = 'COM_ES_AUDIO_RESTRICTED';
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

		echo parent::display('site/audios/restricted');
	}

	/**
	 * Displays the single audio item
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function item()
	{
		ES::setMeta();

		// Get the audio id
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Audio');
		$table->load($id);

		if (!$table->id) {
			ES::raiseError(404, JText::_('COM_ES_AUDIO_INVALID_ID_PROVIDED'));
		}

		if ($table->isUnpublished()) {
			ES::raiseError(404, JText::_('COM_ES_AUDIO_NOT_FOUND'));
		}

		// Load up the audio
		$audio = ES::audio($table->uid, $table->type, $table);

		// Ensure that the viewer can really view the audio
		if (!$audio->isViewable()) {
			return $this->restricted($table->uid, $table->type);
		}

		$from = $this->input->get('from', '', 'default');

		// Add canonical tags
		$this->page->canonical($audio->getPermalink());

		// Set the page title
		$this->page->title($audio->getTitle());

		// Whenever a viewer visits an audio, increment the hit counter
		// Only for audio link
		if (!$audio->isUpload()) {
			$audio->hit();
		}

		// Retrieve the reports library
		$reports = $audio->getReports();

		$streamId = $audio->getStreamId('create');

		// Retrieve the comments library
		$comments = $audio->getComments('create', $streamId);

		// Retrieve the likes library
		$likes = $audio->getLikes('create', $streamId);

		// Retrieve the repost data
		$audioClusterId = ($audio->type != SOCIAL_APPS_GROUP_USER) ? $audio->uid : '0';
		$audioClusterType = ($audio->type != SOCIAL_APPS_GROUP_USER) ? $audio->type : '';

		$repost = $audio->reposts($audioClusterId, $audioClusterType);

		// Retrieve the privacy library
		$privacyButton = $audio->getPrivacyButton();

		// Retrieve the sharing library
		$sharing = $audio->getSharing();

		// Retrieve users tagging
		$usersTags = $audio->getEntityTags();
		$usersTagsList = '';

		if ($usersTags) {
			$usersTagsArray = array();

			foreach ($usersTags as $tag) {
				$usersTagsArray[] = $tag->item_id;
			}

			$usersTagsList = json_encode($usersTagsArray);
		}

		// Retrive tags
		$tags = $audio->getTags();

		// Retrieve the cluster associated with the audio
		$cluster = $audio->getCluster();

		// Build user alias
		$creator = $audio->getAudioCreator($cluster);

		// Render meta headers
		$audio->renderHeaders();

		// Get random audios from the same genre
		$otherAudios = array();

		if ($this->config->get('audio.layout.item.recent')) {
			$options = array('genre_id' => $audio->genre_id, 'exclusion' => $audio->id, 'limit' => $this->config->get('audio.layout.item.total'));
			$model = ES::model('Audios');
			$otherAudios = $model->getAudios($options);
		}

		// Update the back link if there is an "uid" or "type" in the url
		$uid = $this->input->get('uid', '', 'int');
		$type = $this->input->get('type', '');
		$backLink = ESR::audios();

		if (!$uid && !$type) {
			// we will try to get from the current active menu item.
			$menu = $this->app->getMenu();
			if ($menu) {
				$activeMenu = $menu->getActive();

				$xQuery = $activeMenu->query;
				$xView = isset($xQuery['view']) ? $xQuery['view'] : '';
				$xLayout = isset($xQuery['layout']) ? $xQuery['layout'] : '';
				$xId = isset($xQuery['id']) ? (int) $xQuery['id'] : '';

				if ($xView == 'audios' && $xLayout == 'item' && $xId == $audio->id) {
					if ($cluster) {
						$uid = $audio->uid;
						$type = $audio->type;
					}
				}
			}
		}

		if ($from == 'user') {
			$backLink = ESR::audios(array('uid' => $audio->getAuthor()->getAlias(), 'type' => 'user'));
		} else if ($uid && $type && $from != 'listing') {
			$backLink = $audio->getAllAudiosLink();
		}

		// Generate a return url
		$returnUrl = base64_encode($audio->getPermalink());

		// Get the list of playlist the user has.
		$listModel = ES::model('Lists');

		// Get the list items.
		$lists = $listModel->getLists(array('user_id' => $this->my->id, 'type' => SOCIAL_TYPE_AUDIOS));

		$audio->setBreadcrumbs($this->getLayout());

		$navigation = $audio->getNavigation();

		$this->set('navigation', $navigation);
		$this->set('lists', $lists);
		$this->set('returnUrl', $returnUrl);
		$this->set('usersTagsList', $usersTagsList);
		$this->set('otherAudios', $otherAudios);
		$this->set('backLink', $backLink);
		$this->set('tags', $tags);
		$this->set('usersTags', $usersTags);
		$this->set('sharing', $sharing);
		$this->set('reports', $reports);
		$this->set('comments', $comments);
		$this->set('likes', $likes);
		$this->set('privacyButton', $privacyButton);
		$this->set('audio', $audio);
		$this->set('creator', $creator);
		$this->set('cluster', $cluster);

		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('repost', $repost);

		echo parent::display('site/audios/item/default');
	}

	/**
	 * Displays the edit form for an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function form()
	{
		// Only logged in users should be allowed to create audios
		ES::requireLogin();

		ES::setMeta();

		// Determines if an audio is being edited
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', null, 'int');
		$type = $this->input->get('type', null, 'word');

		// Load the audio
		$audio = ES::audio($uid, $type, $id);

		// Increment the hit counter
		if (in_array($type, array(SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_GROUP))) {
			$clusters = ES::cluster($type, $uid);
		}

		// Retrieve any previous data
		$session = JFactory::getSession();
		$data = $session->get('audios.form', null, SOCIAL_SESSION_NAMESPACE);

		if ($data) {
			$data = json_decode($data);

			// Ensure that it matches the id
			if (!$audio->id || ($audio->id && $audio->id == $data->id)) {
				$audio->bind($data);
			}
		}

		// Ensure that the current user can create this audio
		if (!$id && !$audio->canUpload() && !$audio->canEmbed()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_ADDING_AUDIOS'), 500);
		}

		// Ensure that the current user can really edit this audio
		if ($id && !$audio->isEditable()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_EDITING'), 500);
		}

		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_CREATE_AUDIO');

		if ($id && !$audio->isNew()) {
			$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_EDIT_AUDIO');
		}

		$model = ES::model('Audios');

		// Pre-selection of a genre
		$defaultGenre = $model->getDefaultGenre();
		$defaultGenre = $defaultGenre ? $defaultGenre->id : 0;

		$defaultGenre = $this->input->get('genreId', $defaultGenre, 'int');

		// Get a list of audio genres
		$options = array();

		if (!$this->my->isSiteAdmin()) {
			$options = array('respectAccess' => true, 'profileId' => $this->my->getProfile()->id);
		}

		$genres = $model->getGenres($options);

		$selectedGenre = $audio->genre_id ? $audio->genre_id : $defaultGenre;

		$privacy = ES::privacy();

		// Retrieve audio tags
		$userTags = $audio->getEntityTags();
		$userTagItemList = array();

		if ($userTags) {
			foreach($userTags as $userTag) {
				$userTagItemList[] = $userTag->item_id;
			}
		}

		$hashtags = $audio->getTags(true);

		$isCluster = ($uid && $type && $type != SOCIAL_TYPE_USER) ? true : false;
		$isPrivateCluster = false;

		if ($isCluster) {
			$cluster = $audio->getCluster();

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

		$returnLink = ESR::audios($options);

		if ($audio->id) {
			$returnLink = $audio->getPermalink();
		}

		// Get the maximum file size allowed
		$uploadLimit = $audio->getUploadLimit();

		$defaultAlbumart = $audio->getDefaultAlbumart();

		$supportedProviders = $audio->getSupportedProviders();
		$supportedProviders = implode(', ', $supportedProviders);

		$audio->setBreadcrumbs($this->getLayout());

		$this->set('returnLink', $returnLink);
		$this->set('uploadLimit', $uploadLimit);
		$this->set('selectedGenre', $selectedGenre);
		$this->set('userTags', $userTags);
		$this->set('userTagItemList', $userTagItemList);
		$this->set('hashtags', $hashtags);
		$this->set('audio', $audio);
		$this->set('privacy', $privacy);
		$this->set('genres', $genres);
		$this->set('isCluster', $isCluster);
		$this->set('defaultAlbumart', $defaultAlbumart);
		$this->set('supportedProviders', $supportedProviders);
		$this->set('isPrivateCluster', $isPrivateCluster);

		return parent::display('site/audios/form/default');
	}

	/**
	 * Displays the process to transcode the audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function process()
	{
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'word');

		$audio = ES::audio($uid, $type, $id);

		// Ensure that the current user really owns this audio
		if (!$audio->canProcess()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_PROCESS'), 500);
		}

		$cluster = null;

		if ($uid && $type) {
			$cluster = ES::cluster($type, $uid);
		}

		$this->set('cluster', $cluster);
		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('audio', $audio);

		echo parent::display('site/audios/process/default');
	}

	/**
	 * Post process after an audio is deleted from the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete($audio)
	{
		$this->info->set($this->getMessage());

		$redirect = $audio->getAllAudiosLink('', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a filter is deleted
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deleteFilter($cid, $clusterType)
	{
		$audio = ES::audio($cid, $clusterType);

		$this->info->set($this->getMessage());

		$redirect = $audio->getAllAudiosLink('', false);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after an audio is unfeatured on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function unfeature($audio, $callback = null)
	{
		$this->info->set($this->getMessage());

		$redirect = $audio->getAllAudiosLink('featured', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after an audio is featured on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function feature($audio, $callback = null)
	{
		$this->info->set($this->getMessage());

		$redirect = $audio->getAllAudiosLink('featured', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after an audio is stored
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save(SocialAudio $audio, $isNew, $file)
	{
		// If there's an error, redirect them back to the form
		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());

			$options = array('layout' => 'form');

			if (!$audio->isNew()) {
				$options['id'] = $audio->id;
			}

			if ($audio->isCreatedInCluster()) {
				$options['uid'] = $audio->uid;
				$options['type'] = $audio->type;
			}

			$url = FRoute::audios($options, false);

			return $this->redirect($url);
		}

		$message = 'COM_ES_AUDIO_ADDED_SUCCESS';

		if (!$isNew) {
			$message = 'COM_ES_AUDIO_UPDATED_SUCCESS';
		}

		// If this is an audio link, we should just redirect to the audio page.
		if ($audio->isLink()) {

			$url = $audio->getPermalink(false);

			$this->setMessage($message, SOCIAL_MSG_SUCCESS);
			$this->info->set($this->getMessage());

			return $this->redirect($url);
		}


		// Should we redirect the user to the progress page or redirect to the pending audio page
		$options = array('id' => $audio->getAlias());

		if ($isNew && $file || !$isNew && $file) {
			// If audio will be processed by cronjob, do not redirect to the process page
			if (!$this->config->get('audio.autoencode')) {
				$options = array('filter' => 'pending');

				if ($isNew) {
					$message = 'COM_ES_AUDIO_UPLOAD_SUCCESS_AWAIT_PROCESSING';
				}
			} else if ($this->config->get('audio.allowencode')){
				$options['layout'] = 'process';

				if ($isNew) {
					$message = 'COM_ES_AUDIO_UPLOAD_SUCCESS_PROCESSING_AUDIO_NOW';
				}
			} else {
				if ($isNew) {
					$message = 'COM_ES_AUDIO_UPLOAD_SUCCESS';
				}
			}
		}

		if (!$isNew && $audio->isPublished()) {
			$options['layout'] = 'item';
		}

		$this->setMessage($message, SOCIAL_MSG_SUCCESS);
		$this->info->set($this->getMessage());

		if ($audio->isCreatedInCluster()) {
			$options['uid'] = $audio->uid;
			$options['type'] = $audio->type;
		}

		$url = ESR::audios($options, false);
		return $this->redirect($url);
	}

	/**
	 * Checks if this feature should be enabled or not.
	 *
	 * @since	2.1
	 * @access	private
	 */
	protected function isFeatureEnabled()
	{
		// Do not allow user to access groups if it's not enabled
		if (!$this->config->get('audio.enabled')) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PAGE_NOT_FOUND'));
		}
	}

	/**
	 * Post processing after tag filters is saved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveFilter($uid, $clusterType)
	{
		$audio = ES::audio($uid, $clusterType);

		$this->info->set($this->getMessage());

		$redirect = $audio->getAllAudiosLink();
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Perform redirection after the playlist is created.
	 *
	 * @since	2.1
	 * @access	public
	 **/
	public function storePlaylist()
	{
		if (!$this->config->get('audio.enabled')) {
			return $this->exception('COM_ES_AUDIO_ERROR_AUDIO_DISABLED');
		}

		$this->info->set($this->getMessage());

		$this->redirect(ESR::audios(array(), false));
	}

	/**
	 * Post processing of delete playlist
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deletePlaylist()
	{

		$this->info->set($this->getMessage());

		$redirect = ESR::audios(array(), false);

		return $this->redirect($redirect);
	}

	/**
	 * Allows use to download an audio from the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function download()
	{
		// Get the id of the audio
		$id = $this->input->get('id', null, 'int');

		$table = ES::table('Audio');
		$table->load($id);

		// Id provided must be valid
		if (!$id || !$table->id) {
			$this->setMessage(JText::_('COM_ES_AUDIO_INVALID_AUDIO_ID_PROVIDED'), ES_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::audios(array(), false));
		}

		// Load up the audio
		$audio = ES::audio($table->uid, $table->type, $table);

		// Let's try to download the file now
		$audio->download();
	}
}
