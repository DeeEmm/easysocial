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

class EasySocialViewAudiosListHelper extends EasySocial
{
	public function canCreateFilter()
	{
		static $allowed = null;

		if (is_null($allowed)) {
			$allowed = false;

			// Get available hashtag filter
			if ($this->my->id) {
				$cluster = $this->getCluster();

				$allowed = true;
			}
		}

		return $allowed;
	}

	public function canCreateAudio()
	{
		static $allowed = null;

		if (is_null($allowed)) {
			$uid = $this->getUid();
			$type = $this->getType();

			// If the current type is user, we shouldn't display the creation if they are viewing another person's list of audios
			if ($type == SOCIAL_TYPE_USER && $uid != $this->my->id) {
				return false;
			}

			$adapter = $this->getAdapter($uid, $type);
			$allowed = $adapter->allowCreation();
		}

		return $allowed;
	}

	public function canCreatePlayList()
	{
		static $allowed = null;

		if (is_null($allowed)) {
			$user = $this->getActiveUser();

			if ($user) {
				$allowed = ES::lists()->canCreatePlaylist() && $user->id == $this->my->id;

				return $allowed;
			}

			$allowed = (bool) ES::lists()->canCreatePlaylist();
		}

		return $allowed;
	}

	public function getActiveCategoryId()
	{
		$id = $this->input->get('categoryId', '', 'int');

		return $id;
	}

	public function getActiveHashtag()
	{
		static $hashtag = null;

		if (is_null($hashtag)) {
			$hashtag = $this->input->get('hashtag', '', 'string');
		}

		return $hashtag;
	}

	public function getActiveCustomFilterId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('hashtagFilterId', 0, 'int');
		}

		return $id;
	}

	public function getActiveCustomFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$id = $this->getActiveCustomFilterId();

			if (!$id) {
				$filter = false;
				return $filter;
			}

			$filter = ES::Table('TagsFilter');
			$filter->load((int) $id);
		}

		return $filter;
	}

	public function getActiveGenreId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('genreId', '', 'int');
		}
		return $id;
	}

	public function getActiveGenre()
	{
		static $genre = null;

		if (is_null($genre)) {
			$id = $this->getActiveGenreId();

			if (!$id) {
				$genre = false;
				return $genre;
			}

			$genre = ES::table('AudioGenre');
			$genre->load($id);

			// Make sure the active category is published.
			if (!$genre->isPublished()) {
				$genre = false;
			}
		}

		return $genre;
	}

	public function getActivePlaylistId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('listId', 0, 'int');
		}

		return $id;
	}

	public function getActivePlaylist()
	{
		static $playlist = null;

		if (is_null($playlist)) {
			$id = $this->getActivePlaylistId();

			if (!$id) {
				$playlist = false;
				return $playlist;
			}

			$playlist = ES::table('List');
			$playlist->load($id);
		}

		return $playlist;
	}

	public function getActiveUserId()
	{
		static $id = null;

		if (is_null($id)) {
			$uid = $this->getUid();
			$type = $this->getType();

			if ($type == SOCIAL_TYPE_USER && $uid) {
				$id = $uid;
				return $id;
			}

			$id = false;
		}

		return $id;
	}

	public function getActiveUser()
	{
		$id = $this->getActiveUserId();

		if ($id === false) {
			return false;
		}

		$user = ES::user($id);
		return $user;
	}

	public function getActiveCategory()
	{
		static $category = null;

		if (is_null($category)) {
			$id = $this->getActiveCategoryId();

			if (!$id) {
				$category = false;
				return $category;
			}

			$category = ES::table('AudioGenre');
			$category->load($id);
		}

		return $category;
	}

	public function getAdapter()
	{
		static $adapter = null;

		if (is_null($adapter)) {
			$uid = $this->getUid();
			$type = $this->getType();

			$validAdapters = ['', SOCIAL_TYPE_USER, SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE];

			if (!in_array($type, $validAdapters)) {
				return false;
			}

			$adapter = ES::audio($uid, $type);
		}

		return $adapter;
	}

	public function getCustomFilters()
	{
		static $filters = null;

		if (is_null($filters)) {
			$cluster = $this->getCluster();

			$tags = ES::tag();
			$filters = $tags->getFilters($this->my->id, 'audios', $cluster);
		}

		return $filters;
	}

	public function getCreateCustomFilterLink()
	{
		static $link = null;

		if (is_null($link)) {
			// Custom filter creation link
			$options = array('filter' => 'filterForm');

			if ($this->isCluster()) {
				$options['uid'] = $this->getUid();
				$options['type'] = $this->getType();
			}

			$link = ESR::audios($options);
		}

		return $link;
	}

	public function getCanonicalOptions()
	{
		static $options = null;

		if (is_null($options)) {
			$options = array('external' => true);

			$customFilter = $this->getActiveCustomFilter();

			if ($customFilter) {
				$options['hashtagFilterId'] = $customFilter->getAlias();
			}

			$cluster = $this->getCluster();

			if ($cluster) {
				$options['uid'] = $cluster->getAlias();
				$options['type'] = $cluster->getType();
			}

			$type = $this->getType();
			$filter = $this->getCurrentFilter();

			if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {
				$user = $this->getActiveUser();

				if ($user) {
					$options['uid'] = $user->getAlias();
				}

				$options['type'] = SOCIAL_TYPE_USER;
			}

			// this checking used in normal audios to include the featured audios when 'featured' filter clicked.
			if ($filter == 'featured') {
				$options['filter'] = 'featured';
			}

			if ($filter == 'mine') {
				$options['filter'] = 'mine';
			}

			$genre = $this->getActiveGenre();

			if ($genre) {
				$options['genreId'] = $genre->getAlias();
			}

			$hashtags = $this->getActiveHashtag();

			if ($hashtags && !$customFilter) {
				$options['hashtag'] = $hashtags;
			}

			$sort = $this->getSort();

			if ($sort) {
				$options['filter'] = $filter;
				$options['sort'] = $sort;

				// Exclude these ordering and sorting page shouldn't get index by search engine advised by Google
				$this->doc->setMetadata('robots', 'noindex,follow');
			}

			$limitstart = $this->getLimitstart();

			if ($limitstart) {
				$options['limitstart'] = $limitstart;
			}
		}

		return $options;
	}

	public function getCanonicalUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$options = $this->getCanonicalOptions();

			$url = ESR::audios($options);
		}

		return $url;
	}

	public function getGenres($counters = false)
	{
		static $genres = null;

		if (is_null($genres)) {
			$adapter = $this->getAdapter();
			$model = ES::model('Audios');
			$genres = $model->getGenres(array('pagination' => false, 'ordering' => 'ordering', 'direction' => 'asc'));

			$uid = $this->getUid();
			$type = $this->getType();
			$cluster = $this->getCluster();

			if ($genres) {
				foreach ($genres as &$genre) {

					$genre->pageTitle = $genre->title;
					if ($counters) {
						$genre->total = $genre->getTotalAudios($cluster, $uid, $type);
					}

					if ($uid && $type) {
						$genre->pageTitle = $adapter->getGenrePageTitle($genre);
					}
				}
			}
		}

		return $genres;
	}

	public function getCluster()
	{
		static $cluster = null;

		if (is_null($cluster)) {
			$uid = $this->getUid();
			$type = $this->getType();

			if (!$uid || $type == SOCIAL_TYPE_USER) {
				$cluster = false;
				return $cluster;
			}

			$cluster = ES::cluster($type, $uid);
		}

		return $cluster;
	}

	public function getCreateLink()
	{
		static $link = null;

		if (is_null($link)) {
			// Construct the audio creation link
			$options = array('layout' => 'form');

			$genre = $this->getActiveGenre();

			if ($genre) {
				$options['genreId'] = $genre->id;
			}

			$cluster = $this->getCluster();

			if ($cluster) {
				$options['uid'] = $cluster->getAlias();
				$options['type'] = $cluster->getType();
			}

			$link = ESR::audios($options);
		}

		return $link;
	}

	public function getCurrentFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', 'all', 'word');

			$genre = $this->getActiveGenre();

			if ($genre) {
				$filter = 'genre';
			}

			$customFilter = $this->input->get('hashtagFilterId', 0, 'int');

			if ($customFilter) {
				$filter = 'customFilter';
			}

			$playlist = $this->getActivePlaylist();

			if ($playlist) {
				$filter = 'list';
			}
		}

		return $filter;
	}

	public function getFrom()
	{
		static $from = null;

		if (is_null($from)) {
			$from = 'listing';

			$cluster = $this->getCluster();

			if ($cluster) {
				$from = $cluster->getType();
			}

			$type = $this->getType();
			$filter = $this->getCurrentFilter();

			if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {
				$from = SOCIAL_TYPE_USER;
			}
		}

		return $from;
	}

	/**
	 * Determines the current category filter description
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function getPageCategoryDesc($reload = false)
	{
		static $description = null;

		if (is_null($description) || $reload) {

			$description = '';
			$genre = $this->getActiveGenre();

			if ($genre) {
				$description = $genre->getDescription();
			}
		}

		return $description;
	}

	public function getPageTitle($reload = false)
	{
		static $title = null;

		if (is_null($title) || $reload) {

			$title = 'COM_EASYSOCIAL_PAGE_TITLE_AUDIO_FILTER_ALL';

			// If exists, means user is viewing the playlist
			$playlist = $this->getActivePlaylist();

			if ($playlist) {
				$title = $playlist->get('title');
			}

			// If user is viewing my specific filters, we need to update the title accordingly.
			$filter = $this->getCurrentFilter();

			if ($filter && $filter != 'genre' && $filter != 'list') {
				$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO_FILTER_' . strtoupper($filter));
			}

			// Default audio title
			$uid = $this->getUid();
			$type = $this->getType();
			$adapter = $this->getAdapter();
			$sort = $this->getSort();

			if (($uid && $type) || $filter == 'all') {
				$title = $adapter->getListingPageTitle();
			}

			// Featured audios title
			if ($filter == 'featured') {
				$title = $adapter->getFeaturedPageTitle();
			}

			// If this is filter by genre, we need to set the genre title as the page title
			$genre = $this->getActiveGenre();

			if ($filter == 'genre' && $genre) {
				$title = $genre->title;

				if ($uid && $type) {
					$title = $adapter->getGenrePageTitle($genre);
				}
			}

			if ($filter == 'customFilter') {
				$active = $this->getActiveCustomFilter();

				$title = $active->title;
			}

			// Not handle for the ajax call for this sorting
			if ($sort && !$reload) {
				$sort = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sort));
				$title = $title . ' - ' . $sort;
			}
		}

		return $title;
	}

	public function getPageTitles()
	{
		static $titles = null;

		if (is_null($titles)) {
			$titles = new stdClass();
			$titles->all = JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO_FILTER_ALL');
			$titles->featured = JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO_FILTER_FEATURED');

			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$adapter = $this->getAdapter();

				$titles->all = $adapter->getListingPageTitle();
				$titles->featured = $adapter->getFeaturedPageTitle();
			}
		}

		return $titles;
	}

	public function getPlayLists()
	{
		static $lists = null;

		if (is_null($lists)) {
			$model = ES::model('Lists');

			$user = $this->getActiveUser();

			// Get the playlist for the currently viewed user
			if ($user) {
				$lists = $model->getLists(array('user_id' => $user->id, 'type' => SOCIAL_TYPE_AUDIOS));
			}

			// Browse mode, get lists from current logged in user
			if (!$user) {
				$lists = $model->getLists(array('user_id' => $this->my->id, 'type' => SOCIAL_TYPE_AUDIOS));
			}
		}

		return $lists;
	}

	public function getPlayListHtml()
	{
		static $html = null;

		if (is_null($html)) {
			$html = '';
			$filter = $this->getCurrentFilter();

			if ($filter != 'list') {
				return $html;
			}

			$playlist = $this->getActivePlaylist();
			$items = $playlist->getItems(false);

			$audios = array();

			if ($items) {
				foreach ($items as $item) {
					$audio = ES::table('Audio');
					$audio->load($item->target_id);

					// Assign listmap id into the audio
					$audioObj = ES::audio($audio);
					$audioObj->listMapId = $item->id;
					$audios[] = $audioObj;
				}
			}

			$theme = ES::themes();
			$theme->set('activeList', $playlist);
			$theme->set('audios', $audios);

			$html = $theme->output('site/audios/player/playlist');
		}

		return $html;
	}

	public function getReturnUrl()
	{
		static $url = null;

		// Generate correct return urls for operations performed here
		if (is_null($url)) {

			// Retrieve current page URL before perform any action on the video listing page.
			$url = ES::getURI(true);

			if (!$url) {
				$url = ESR::audios();
			}

			$uid = $this->getUid();
			$type = $this->getType();

			// temporary comment out this is because this getreturnUrl method only handle for the action #3472
			// if ($uid && $type) {
			// 	$adapter = $this->getAdapter();
			// 	$filter = $this->getCurrentFilter();

			// 	$url = $adapter->getAllAudiosLink($filter);
			// }

			$url = base64_encode($url);
		}

		return $url;
	}

	public function getSortables()
	{
		static $items = null;

		if (is_null($items)) {

			$items = new stdClass();
			$types = array('latest', 'alphabetical', 'popular', 'commented', 'likes');
			$filter = $this->getCurrentFilter();
			$genre = $this->getActiveGenre();
			$customFilter = $this->getActiveCustomFilter();

			foreach ($types as $type) {

				$items->{$type} = new stdClass();

				// display the proper sorting name for the page title.
				$displaySortingName = JText::_($this->getPageTitle(true));
				$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($type));

				if ($filter || $genre) {
					$displaySortingName = $displaySortingName . ' - ' . $sortType;
				}

				$attributes = array('data-sorting', 'data-type="' . $type . '"', 'title="' . $displaySortingName . '"');

				if ($customFilter) {
					$attributes[] = 'data-tag-id="' . $customFilter->id . '"';
				} else {
					$attributes[] = 'data-filter="' . $filter . '"';
				}

				$urlOptions = array();
				$urlOptions['sort'] = $type;

				if ($genre) {
					$attributes[] = 'data-id="' . $genre->id . '"';
					$urlOptions['genreId'] = $genre->getAlias();
				}

				if (!$genre && !$customFilter) {
					$urlOptions['filter'] = $filter;
				}

				if ($customFilter) {
					$urlOptions['hashtagFilterId'] = $customFilter->getAlias();
				}

				$items->{$type}->attributes = $attributes;
				$items->{$type}->url = ESR::audios($urlOptions);;
			}
		}

		return $items;
	}

	public function getTotal()
	{
		static $total = null;

		if (is_null($total)) {
			$total = new stdClass();

			$total->audios = $this->getTotalAudios();
			$total->user = $this->getTotalUserAudios();
			$total->featured = $this->getTotalFeaturedAudios();
			$total->pending = $this->getTotalPendingAudios();
		}

		return $total;
	}

	public function getTotalAudios()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Audios');

			$user = $this->getActiveUser();

			// Get the total audio for the currently viewed user
			if ($user) {
				$options = array(
							'uid' => $user->id,
							'type' => SOCIAL_TYPE_USER
				);

				$total = $model->getTotalAudios($options);
				return $total;
			}

			$options = array();
			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$options['uid'] = $cluster->id;
				$options['type'] = $cluster->getType();
			}

			$total = $model->getTotalAudios($options);
		}

		return $total;
	}

	public function getTotalUserAudios()
	{
		$model = ES::model('Audios');
		$total = $model->getTotalUserAudios($this->my->id);

		return $total;
	}

	public function getTotalFeaturedAudios()
	{
		static $total = null;

		if (is_null($total)) {

			$options = array();
			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$options['uid'] = $cluster->id;
				$options['type'] = $cluster->getType();
			}


			if ($cluster === false) {
				$uid = $this->getUid();
				$type = $this->getType();

				if ($uid && $type) {
					// user profile featured audio
					$options['uid'] = $uid;
					$options['type'] = $type;
				}
			}

			$model = ES::model('Audios');
			$total = $model->getTotalFeaturedAudios($options);
		}

		return $total;
	}

	public function getTotalPendingAudios()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Audios');
			$total = $model->getTotalPendingAudios($this->my->id);
		}

		return $total;
	}

	public function getType()
	{
		static $type = null;

		if (is_null($type)) {
			$type = $this->input->get('type', '', 'word');
		}

		return $type;
	}

	/**
	 * Determines the current sorting type from the listing page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getSort()
	{
		static $sort = null;

		if (is_null($sort)) {
			$sort = $this->input->get('sort', '', 'word');
		}

		return $sort;
	}

	/**
	 * Determines the current limitstart from the listing page
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getLimitstart()
	{
		static $limitstart = null;

		if (is_null($limitstart)) {
			$limitstart = $this->input->get('limitstart', '', 'int');
		}

		return $limitstart;
	}

	public function getUid()
	{
		static $uid = null;

		if (is_null($uid)) {
			$uid = $this->input->get('uid', 0, 'int');
		}

		return $uid;
	}

	public function isCluster()
	{
		$type = $this->getType();
		$uid = $this->getUid();

		if ($type && $uid && $type != SOCIAL_TYPE_USER) {
			return true;
		}

		return false;
	}

	public function isBrowseView()
	{
		$uid = $this->getUid();

		// differentiate between browse all audio or viewing profile audio
		// If no uid, means user is viewing the browsing all audio view
		$browseView = !$uid;

		return $browseView;
	}

	/**
	 * Determines if the current viewer is viewing audios from the user profile audio page
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isUserProfileView()
	{
		static $isUserProfileView = null;

		if (is_null($isUserProfileView)) {
			$uid = $this->getUid();
			$type = $this->getType();

			if ($uid && $type == SOCIAL_TYPE_USER) {
				$isUserProfileView = true;
			}
		}

		return $isUserProfileView;
	}

	public function showMyAudios()
	{
		static $show = null;

		if (is_null($show)) {
			$show = true;

			// We gonna show the 'My audios' if the user is viewing browse all audio page
			$cluster = $this->getCluster();

			if (!$this->my->id || ($cluster !== false) || !$this->isBrowseView()) {
				$show = false;
			}
		}

		return $show;
	}

	/**
	 * Determines which filters are viewable by the user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFiltersAcl()
	{
		static $acl = null;

		if (is_null($acl)) {
			$acl = new stdClass();
			$acl->mine = $this->showMyAudios();
			$acl->pending = $this->showPendingAudios();
		}

		return $acl;
	}

	public function showPendingAudios()
	{
		static $show = null;

		if (is_null($show)) {
			$totalPending = $this->getTotalPendingAudios();
			$show = ($totalPending > 0);

			// When viewing another person's audios
			$uid = $this->getUid();
			$type = $this->getType();

			// Do not show pending audios when viewing another person's audio list
			if ($uid && $type == SOCIAL_TYPE_USER && $uid != $this->my->id) {
				$show = false;
			}
		}

		return $show;
	}
}
