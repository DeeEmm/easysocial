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

class EasySocialViewAlbums extends EasySocialSiteView
{
	/**
	 * Determines if the photos is enabled.
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function checkFeature()
	{
		// Do not allow user to access photos if it's not enabled
		if (!$this->config->get('photos.enabled')) {
			$this->setMessage('COM_EASYSOCIAL_ALBUMS_PHOTOS_DISABLED', SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());
			$this->redirect(ESR::dashboard(array(), false));
		}
	}

	/**
	 * Generates the list of albums a user has created
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Check if photos is enabled
		$this->checkFeature();

		$helper = $this->getHelper('List');

		// Check if the current request is made for the current logged in user or another user.
		if ($helper->isViewAll()) {
			return $this->all($tpl);
		}

		return $this->showAlbums();
	}

	protected function showAlbums()
	{
		$helper = $this->getHelper('List');

		// Check if the current request is made for the current logged in user or another user.
		$uid = $this->input->get('uid', null, 'int');
		$type = $this->input->get('type', null, 'cmd');

		// make sure the user is not blocked. #5069
		if ($type == SOCIAL_TYPE_USER) {
			$activeUser = ES::user($uid);
			if ($activeUser->isBlock() && !$this->my->isSiteAdmin()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
			}
		}

		$lib = ES::albums($uid, $type);

		// Check if this current viewer blocked by the album onwer or not.
		if ($lib->isBlocked()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_USER_PROVIDED'));
		}

		// There are times where the album is already deleted but user tries to access it
		$valid = $lib->isValidNode();

		// Determines if the viewer is trying to view albums for a valid node.
		if (!$lib->isValidNode()) {
			$this->setMessage($lib->getError(), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			$redirect = ESR::dashboard(array(), false);
			return $this->redirect($redirect);
		}

		// Check if the album is viewable
		if (!$lib->viewable()) {
			return $this->restricted($lib->uid, $lib->type);
		}

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Set page attributes
		$layout = $this->getLayout();
		$title = $lib->getPageTitle($layout);
		$this->page->title($title);
		$lib->setBreadcrumbs($layout);

		// Only for clusters page title
		if ($uid && $type && $type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($type, $uid);
			$cluster->renderPageTitle(null, 'albums');

			// Increment the hit counter
			$lib->hit();
		}

		// Get albums model
		$model = ES::model("Albums");
		$model->initStates();

		// Get the start limit from the request
		$startlimit = $this->input->get('limitstart', 0, 'int');

		if (!$startlimit) {
			$model->setState('limitstart', 0);
		}

		// Get a list of normal albums
		$options = array();
		$options['pagination'] = true;
		$options['order'] = 'assigned_date';
		$options['direction'] = 'DESC';
		$options['privacy'] = true;
		$options['core'] = false;

		if ($lib->isClusterAlbum()) {
			$options['privacy'] = false;
		}

		// Get the albums
		$albums = $model->getAlbums($lib->uid, $lib->type, $options);

		$pagination = $model->getPagination();

		if ($albums) {
			for ($i = 0; $i < count($albums); $i++) {
				$album =& $albums[$i];

				// tagging
				$album->tags = $album->getTags(true, 3);

				// photos
				$result = $album->getPhotos(array('limit' => 3));

				$album->photos = $result['photos'];
			}
		}

		// Format albums by date
		$data = $lib->groupAlbumsByDate($albums);

		// Get a list of core albums
		$model = ES::model("Albums");
		$coreAlbums	= $model->getAlbums($lib->uid, $lib->type, array('coreAlbumsOnly' => true));
		$coreAlbum = false;

		// If there is non-core album, we try to display core album
		if (!$data && count($coreAlbums) > 0) {
			// Load up 1 core album lib
			$coreAlbum = ES::albums($coreAlbums[0]->uid, $coreAlbums[0]->type, $coreAlbums[0]->id);
		}

		$emptyText = 'COM_EASYSOCIAL_NO_ALBUM_AVAILABLE';

		if ($type != SOCIAL_TYPE_USER) {
			$emptyText = 'COM_EASYSOCIAL_NO_ALBUM_AVAILABLE_' . strtoupper($type);
		}

		$theme = ES::themes();
		$theme->set('lib', $lib);
		$theme->set('data', $data);
		$theme->set('pagination', $pagination);
		$theme->set('emptyText', $emptyText);
		$theme->set('coreAlbum', $coreAlbum);

		// add canonical link
		$canonicalUrl = $helper->getCanonicalUrl();
		$this->page->canonical($canonicalUrl);

		// Get the contents
		$output = $theme->output('site/albums/default/default');

		// Wrap it with the albums wrapper.
		return $this->output($lib->uid, $lib->type, $output);
	}


	/**
	 * Renders all albums from current logged in user.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function mine($tpl = null)
	{
		// check if the users is logged in or not.
		if ($this->my->guest) {
			ES::requireLogin();
		}

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Check if photos is enabled
		$this->checkFeature();

		ES::setMeta();

		$this->input->set('uid', $this->my->id);
		$this->input->set('type', SOCIAL_TYPE_USER);

		return $this->showAlbums();
	}

	/**
	 * Renders all albums from the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function all($tpl = null)
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();
		ES::setMeta();

		// Check if photos is enabled
		$this->checkFeature();
		$helper = $this->getHelper('List');

		// Get the albums
		$lib = $helper->getAlbumsLibrary();

		$mainPageTitle = JText::_('COM_EASYSOCIAL_ALBUMS_ALL_ALBUMS');
		$pageTitle = $mainPageTitle;

		// By default albums should be sorted by creation date.
		$sorting = $this->input->get('sort', '');

		if ($sorting) {
			$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sorting));
			$pageTitle = $mainPageTitle . ' - ' . $sortType;
		}

		// Set the page title
		$this->page->title($pageTitle);
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_ALBUMS_ALL_ALBUMS'));

		// Get albums model
		$model = ES::model('Albums');
		$model->initStates();

		// Get the start limit from the request
		$startlimit = $this->input->get('limitstart', 0, 'int');

		if (!$startlimit) {
			$model->setState('limitstart', 0);
		}

		// Get a list of normal albums
		$options = [
			'pagination' => true,
			'order' => 'assigned_date',
			'direction' => 'DESC',
			'core' => false
		];

		if ($sorting == 'alphabetical') {
			$options['order'] = 'title';
			$options['direction'] = 'ASC';
		}

		if ($sorting == 'popular') {
			$options['order'] = 'hits';
			$options['direction'] = 'DESC';
		}

		if ($sorting == 'likes') {
			$options['order'] = 'likes';
			$options['direction'] = 'DESC';
		}

		if ($sorting == 'comments') {
			$options['order'] = 'comments';
			$options['direction'] = 'DESC';
		}

		$options['privacy'] = true;
		// $options['excludedisabled'] = true;
		$options['withCovers'] = true;
		$options['excludeblocked'] = true;

		// Get the albums
		$albums = $model->getAlbums('', SOCIAL_TYPE_USER, $options);

		$photos = array();

		// we will get the photos here
		if ($albums) {

			$ids = array();
			for ($i = 0; $i < count($albums); $i++) {
				$album =& $albums[$i];

				// tagging
				$album->tags = $album->getTags(true, 3);
				$ids[] = $album->id;
			}

			if ($ids) {
				$pModel = ES::model('Photos');
				$photos = $pModel->getAlbumPhotos($ids, 3);
			}
		}

		// Get the album pagination
		$pagination = $model->getPagination();

		$filter = 'all';
		$sortItems = new stdClass();
		$sortingTypes = [
			'latest',
			'alphabetical',
			'popular',
			'likes',
			'comments'
		];

		foreach ($sortingTypes as $sortingType) {

			$sortItems->{$sortingType} = new stdClass();

			// Display the proper sorting name for the page title.
			$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sortingType));
			$displaySortingName = $mainPageTitle . ' - ' . $sortType;

			// attributes
			$sortAttributes = array('data-sorting', 'data-filter="' . $filter . '"', 'data-type="' . $sortingType . '"', 'title="' . $displaySortingName . '"');

			/// url
			$urlOptions = array();
			$urlOptions['sort'] = $sortingType;

			$sortUrl = ESR::albums($urlOptions);

			$sortItems->{$sortingType}->attributes = $sortAttributes;
			$sortItems->{$sortingType}->url = $sortUrl;
		}

		// add canonical link
		$canonicalUrl = $helper->getCanonicalUrl();
		$this->page->canonical($canonicalUrl);

		$this->set('sortItems', $sortItems);
		$this->set('sorting', $sorting);
		$this->set('lib', $lib);
		$this->set('albums', $albums);
		$this->set('photos', $photos);
		$this->set('pagination', $pagination);
		$this->set('filter', 'all');

		// Wrap it with the albums wrapper.
		return parent::display('site/albums/all/default');
	}


	/**
	 * Renders all favourite albums by logged in user from the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function favourite($tpl = null)
	{
		if ($this->my->guest) {
			ES::requireLogin();
		}

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Check if photos is enabled
		$this->checkFeature();

		ES::setMeta();

		$helper = $this->getHelper('List');

		// Get the albums
		$lib = $helper->getAlbumsLibrary();

		$mainPageTitle = JText::_('COM_EASYSOCIAL_ALBUMS_FAVOURITE_ALBUMS');
		$pageTitle = $mainPageTitle;

		// By default albums should be sorted by creation date.
		$sorting = $this->input->get('sort', '');

		if ($sorting) {
			$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sorting));
			$pageTitle = $mainPageTitle . ' - ' . $sortType;
		}

		// Set the page title
		$this->page->title(JText::_($pageTitle));
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_ALBUMS_FAVOURITE_ALBUMS'));

		// Get albums model
		$model = ES::model('Albums');
		$model->initStates();

		// Get the start limit from the request
		$startlimit = $this->input->get('limitstart', 0, 'int');

		// By default albums should be sorted by creation date.
		$sorting = $this->input->get('sort', 'latest');

		if (!$startlimit) {
			$model->setState('limitstart', 0);
		}

		// Get a list of favourite albums
		$options = [
			'pagination' => true,
			'order' => 'assigned_date',
			'direction' => 'DESC',
			'core' => false,
			'favourite' => true,
			'userFavourite' => $this->my->id
		];

		if ($sorting == 'alphabetical') {
			$options['order'] = 'title';
			$options['direction'] = 'ASC';
		}

		if ($sorting == 'popular') {
			$options['order'] = 'hits';
			$options['direction'] = 'DESC';
		}

		if ($sorting == 'likes') {
			$options['order'] = 'likes';
			$options['direction'] = 'DESC';
		}

		// We need to also check for privacy here
		$options['privacy'] = true;

		// Get the albums
		$albums = $model->getAlbums('', SOCIAL_TYPE_USER, $options);
		$photos = [];

		// we will get the photos here
		if ($albums) {

			$ids = [];
			for ($i = 0; $i < count($albums); $i++) {
				$album =& $albums[$i];

				// tagging
				$album->tags = $album->getTags(true, 3);
				$ids[] = $album->id;
			}

			if ($ids) {
				$pModel = ES::model('Photos');
				$photos = $pModel->getAlbumPhotos($ids, 3);
			}
		}

		// Get the album pagination
		$pagination = $model->getPagination();

		$filter = 'favourite';
		$sortItems = new stdClass();
		$sortingTypes = array('latest', 'alphabetical', 'popular', 'likes', 'comments');

		foreach ($sortingTypes as $sortingType) {

			$sortItems->{$sortingType} = new stdClass();

			// Display the proper sorting name for the page title.
			$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sortingType));
			$displaySortingName = $mainPageTitle . ' - ' . $sortType;

			// attributes
			$sortAttributes = array('data-sorting', 'data-filter="' . $filter . '"', 'data-type="' . $sortingType . '"', 'title="' . $displaySortingName . '"');

			//url
			$urlOptions = array();
			$urlOptions['sort'] = $sortingType;
			$urlOptions['layout'] = 'favourite';

			$sortUrl = ESR::albums($urlOptions);

			$sortItems->{$sortingType}->attributes = $sortAttributes;
			$sortItems->{$sortingType}->url = $sortUrl;
		}

		$this->set('sortItems', $sortItems);
		$this->set('sorting', $sorting);
		$this->set('lib', $lib);
		$this->set('albums', $albums);
		$this->set('photos', $photos);
		$this->set('pagination', $pagination);
		$this->set('filter', $filter);

		// add canonical link
		$canonicalUrl = $helper->getCanonicalUrl();
		$this->page->canonical($canonicalUrl);

		// Wrap it with the albums wrapper.
		return parent::display('site/albums/all/default');
	}

	/**
	 * Displays a restricted page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function restricted($uid = null, $type = SOCIAL_TYPE_USER)
	{
		$label = 'COM_EASYSOCIAL_ALBUMS_RESTRICTED';
		$text = 'COM_EASYSOCIAL_ALBUMS_RESTRICTED_DESC';

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

		echo parent::display('site/albums/restricted');
	}

	/**
	 * Displays the album item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function item()
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Check if photos is enabled
		$this->checkFeature();

		ES::setMeta();

		// Retrieve the album from request
		$id = $this->input->get('id', 0, 'int');

		// Get the unique id and type
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'string');

		// If id is provided but UID is not provided, probably they created a menu that links to a single album
		if ($id && !$uid) {
			$album = ES::table('Album');
			$album->load($id);

			if (!$album->id) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED'));
			}

			$uid = $album->uid;
			$type = $album->type;
		}

		// Load up the albums library
		$lib = ES::albums($uid, $type, $id);

		if (!$lib->canViewAlbum()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_ALBUMS_RESTRICTED_DESC'));
		}

		// Determines if the viewer is trying to view albums for a valid node.
		if (!$lib->isValidNode()) {
			$this->setMessage($lib->getError(), SOCIAL_MSG_ERROR);

			$this->info->set($this->getMessage());
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Empty id or invalid id is not allowed.
		if (!$id || !$lib->data->id) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_ALBUM_ID_PROVIDED'));
		}

		// Check if the album is viewable
		$viewable = $lib->viewable();

		if (!$viewable) {
			return $this->restricted($lib->data->uid, $lib->data->type);
		}

		// NOTE: Add opengraph data for each photos now moved to album libs

		// Increment the hit counter
		$lib->hit();

		// Set page attributes
		$title = $lib->getPageTitle($this->getLayout());
		$this->page->title($title);
		$lib->setBreadcrumbs($this->getLayout());

		// Since we already have the album alias, then we do not need to load it again.
		$alias = $this->input->get('id', 0, 'string');

		// Add canonical tag for this album.
		$canonicalOptions = array(
			'external' => true,
			'id' => $alias,
			'uid' => $uid,
			'type' => $type,
			'layout' => 'item'
			);

		switch ($type) {
			case SOCIAL_TYPE_GROUP:
				$canonicalOptions['uid'] = ES::group($uid)->getAlias();
				break;
			case SOCIAL_TYPE_PAGE:
				$canonicalOptions['uid'] = ES::page($uid)->getAlias();
				break;
			case SOCIAL_TYPE_EVENT:
				$canonicalOptions['uid'] = ES::event($uid)->getAlias();
				break;
			case SOCIAL_TYPE_USER:
			default:
				$canonicalOptions['uid'] = ES::user($uid)->getAlias();
				break;
		}

		$this->page->canonical(ESR::albums($canonicalOptions));

		// Render options
		// Not entirely sure why we need to have this privacy only when the owner is viewing their own album
		// but it caused the loadmore to not be rendered correctly. #2843
		// $requiredPrivacy = ($this->my->id == $lib->data->user_id) ? true : false;
		$requiredPrivacy = true;
		$options = array('viewer' => $this->my->id, 'privacy'=> $requiredPrivacy, 'ordering' => $this->config->get('photos.layout.ordering'));

		// Render item
		$output = $lib->renderItem($options);

		$protected = $this->isProtected($lib->data, $lib, $uid, $type, $output);

		if ($protected !== false) {
			return;
		}

		return $this->output($uid, $type, $output, $lib->data);
	}

	/**
	 * Renders the album's form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form()
	{
		ES::checkCompleteProfile();
		ES::requireLogin();

		$this->checkFeature();

		ES::setMeta();

		// Get album properties
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'cmd');

		if ($type == 'everyone') {
			$type = SOCIAL_TYPE_USER;
		}

		if ($type == SOCIAL_TYPE_USER && !$uid) {
			$uid = $this->my->id;
		}

		// Load up the albums lib
		$lib = ES::albums($uid, $type, $id);

		// If we are creating an album
		if (!$lib->data->id) {
			$lib->data->uid = $lib->uid;
			$lib->data->type = $lib->type;

			// Check if we have exceeded album creation limit.
			if ($lib->exceededLimits()) {
				return $this->output($lib->getExceededHTML(), $lib->data);
			}
		}

		// Set the page title
		$title = $lib->getPageTitle($this->getLayout());
		$this->page->title($title);

		// Set the breadcrumbs
		$lib->setBreadcrumbs($this->getLayout());

		// Determines if the current user can edit this album
		if ($lib->data->id && !$lib->editable($lib->data)) {
			return $this->restricted($lib->data->uid, $lib->data->type);
		}

		// Render options
		$options = array(
			'viewer' => $this->my->id,
			'layout' => 'form',
			'showStats' => false,
			'showResponse' => $lib->data->id ? true : false,
			'showTags' => $lib->data->id ? true : false,
			'photoItem' => array(
				'openInPopup' => false
			));

		// Render item
		$output	= $lib->renderItem($options);

		return $this->output($lib->uid, $lib->type, $output, $lib->data);
	}

	/**
	 * Since the albums layout has a standard wrapper throughout each layouts, this method
	 * is a centralized way to wrap all the other layout's content.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function output($uid, $type, $content = '', $album = false)
	{
		// Load up the albums library
		$lib = ES::albums($uid, $type, $album ? $album->id : null);

		// If no layout was given, load recent layout
		$layout	= $this->input->get('layout', 'recent', 'cmd');

		// Browser menu
		$id = $this->input->get('id', '', 'int');

		// Get a list of core albums
		$model = ES::model("Albums");
		$coreAlbums	= $model->getAlbums($lib->uid, $lib->type, array('coreAlbumsOnly' => true));

		// Get a list of normal albums
		$options = array();
		$options['core'] = false;
		$options['order'] = 'assigned_date';
		$options['direction'] = 'DESC';
		$options['privacy'] = true;

		if ($lib->isClusterAlbum()) {
			$options['privacy'] = false;
		}

		$myAlbums = false;
		$myTotalClusterAlbums = 0;
		$currentLoggedInUserId = $this->my->id;

		// Only if this is view for albums in group/event
		if ($lib->showMyAlbums()) {

			// Get a list of current logged in user's album in group/event
			$options['userId'] = $currentLoggedInUserId;
			$myAlbums = $model->getAlbums($lib->uid, $lib->type, $options);

			// Retrieve album that is not belong to the current logged-in user
			$options['othersAlbum'] = true;
			$options['userId'] = false;

			// Retrieve the user total cluster album
			$totalUserClusterAlbumOptions = array('uid' => $uid, 'excludeCore' => true, 'type' => $lib->type);

			// Retrieve the user cluster album total count
			$totalUserClusterAlbumOptions['userId'] = $currentLoggedInUserId;
			$myTotalClusterAlbums = $model->getTotalAlbums($totalUserClusterAlbumOptions);
		}

		// include the album limit from the user album sidebar area
		$options['limit'] = $this->config->get('photos.layout.albumlimit', 10);
		$startlimit = $this->config->get('photos.layout.albumlimit', 10);

		$albums = $model->getAlbums($lib->uid, $lib->type, $options);

		$totalOptions = array('uid' => $uid, 'excludeCore' => true, 'type' => SOCIAL_TYPE_USER);

		if ($lib->isClusterAlbum()) {
			$totalOptions['type'] = $lib->type;

			// Retrieve album that is not belong to the current logged-in user
			$totalOptions['othersAlbum'] = true;
		}

		$totalAlbums = $model->getTotalAlbums($totalOptions);

		$cluster = null;

		if ($type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($type, $uid);
		}

		$mobileFilter = '';

		if ($album) {
			if ($album->isCore()) {
				$mobileFilter = 'core';
			} else {
				if ($lib->showMyAlbums() && $album->user_id == $currentLoggedInUserId) {
					$mobileFilter = 'mine';
				} else {
					$mobileFilter = 'regular';
				}
			}
		}

		$helper = $this->getHelper('Item');
		$uuid = $helper->getUuid($id);

		$this->set('lib', $lib);
		$this->set('id', $id);
		$this->set('cluster', $cluster);
		$this->set('coreAlbums', $coreAlbums);
		$this->set('myAlbums', $myAlbums);
		$this->set('albums', $albums);
		$this->set('content', $content);
		$this->set('uuid', $uuid);
		$this->set('layout', $layout);
		$this->set('totalAlbums', $totalAlbums);
		$this->set('startlimit', $startlimit);
		$this->set('mobileFilter', $mobileFilter);
		$this->set('myTotalClusterAlbums', $myTotalClusterAlbums);
		$this->set('currentLoggedInUserId', $currentLoggedInUserId);

		echo parent::display('site/albums/browser/default');
	}

	/**
	 * Post processing when creating a new album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($album = null)
	{
		// Require user to be logged in
		ES::requireLogin();

		ES::info()->set($this->getMessage());

		if($this->hasErrors())
		{
			return $this->form();
		}

		return $this->redirect(FRoute::albums(array('id' => $album->getAlias(), 'layout' => 'item')));
	}

	/**
	 * Post processing when deleting an album
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($link)
	{
		// Require user to be logged in
		ES::requireLogin();

		ES::info()->set($this->getMessage());

		$this->redirect($link);
	}

	/**
	 * Determine if the current album is protected
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function isProtected($data, $lib, $uid, $type, $output)
	{
		// todo: setting Password protection is disabled

		//Site admin should not be restricted
		if (ES::isSiteAdmin()) {
			return false;
		}

		// maybe buat satu function dalam album library $lib->isPasswordProtected()
		$password = trim($data->password);

		// Album do not have password protect
		if (empty($password)) {
			return false;
		}

		// User already entered password
		if ($lib->verifyPassword()) {
			return false;
		}

		return $this->output($uid, $type, $output, $data);

	}

}
