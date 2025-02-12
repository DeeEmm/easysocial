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

class EasySocialViewVideos extends EasySocialSiteView
{
	/**
	 * Renders the embed video dialog
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function embed()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Video');
		$table->load($id);

		$video = ES::video($table);

		$theme = ES::themes();
		$theme->set('video', $video);
		$output = $theme->output('site/videos/dialogs/embed');

		return $this->ajax->resolve($output);
	}

	/**
	 * Processes videos
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function process(SocialVideo $video)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Returns the video after upload
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function uploadFile($video)
	{
		if ($video->getError()) {
			return $this->ajax->reject($video->getError());
		}

		$result = new stdClass();
		$result->id = $video->table->id;
		$result->title = $video->table->title;

		return $this->ajax->resolve($result);
	}

	/**
	 * Returns the status of the processing
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function status($video, $progress)
	{
		$permalink = '';

		// this method is being called from both backend and frontend. we need to redirect to proper page. #597
		$permalink = $video->getPermalink(false);

		if (ES::isFromAdmin()) {
			$permalink = 'index.php?option=com_easysocial&view=videos&layout=form&id=' . $video->id;
		}

		// Once the progress is complete, we need to send the url to the video
		if ($progress === true) {
			if (ES::isFromAdmin()) {
				$this->setMessage('COM_EASYSOCIAL_VIDEOS_UPDATED_SUCCESS', SOCIAL_MSG_SUCCESS);
				$this->info->set($this->getMessage());
			}

			$export = $video->export();

			$export->title = ES::string()->escape($export->title);

			return $this->ajax->resolve($permalink, 'done', $export, $video->getThumbnail());
		}

		return $this->ajax->resolve($permalink, $progress);
	}

	/**
	 * Displays confirmation to feature videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmFeature()
	{
		// Get the video id
		$id = $this->input->get('id', 0, 'int');

		// Determines if the user wants to specify a custom callback url
		$callback = $this->input->get('return', '', 'default');

		// Ensure that the user is really allowed to feature this video
		$videoTable = ES::table('Video');
		$videoTable->load($id);

		$video = ES::video($videoTable->uid, $videoTable->type, $videoTable);

		if (!$video->canFeature()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_FEATURE'), 500);
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('callback', $callback);

		$output = $theme->output('site/videos/dialogs/feature');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation to unfeature videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmUnfeature()
	{
		// Get the video id
		$id = $this->input->get('id', 0, 'int');

		// Determines if the user wants to specify a custom callback url
		$callback = $this->input->get('return', '', 'default');

		// Ensure that the user is really allowed to delete this video
		$videoTable = ES::table('Video');
		$videoTable->load($id);

		$video = ES::video($videoTable->uid, $videoTable->type, $videoTable);

		if (!$video->canUnfeature()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_UNFEATURE'), 500);
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('callback', $callback);

		$output = $theme->output('site/videos/dialogs/unfeature');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after a tag is deleted
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function removeTag()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays confirmation to delete videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Get the video id
		$id = $this->input->get('id', 0, 'int');

		// Determines if the user wants to specify a custom callback url
		$callback = $this->input->get('return', '', 'default');

		$videoTable = ES::table('Video');
		$videoTable->load($id);

		// Ensure that the user is really allowed to delete this video
		$video = ES::video($videoTable);

		if (!$video->canDelete()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_VIDEOS_NOT_ALLOWED_TO_DELETE'), 500);
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('callback', $callback);

		$output = $theme->output('site/videos/dialogs/delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after video is tagged with people
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function tag(SocialVideo $video, $tags = array())
	{
		$theme = ES::themes();
		$theme->set('video', $video);
		$theme->set('usersTags', $tags);

		$output = $theme->output('site/videos/item/tags.user');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays encoding message
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function showEncodingMessage()
	{
		$theme = ES::themes();

		$output = $theme->output('site/videos/dialogs/encoding');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays a dialog for users to tag
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function tagPeople()
	{
		$theme = ES::themes();

		// Get the video id
		$id = $this->input->get('id', 0, 'int');
		$exclusion = $this->input->get('exclusion', array(), 'array');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'default');

		$video = ES::video($clusterId, $clusterType, $id);

		// Get a list of users that are already tagged with this video
		$tags = $video->getTags();

		// Get video cluster
		$cluster = $video->getCluster();

		$theme->set('cluster', $cluster);
		$theme->set('exclusion', $exclusion);

		$output = $theme->output('site/videos/dialogs/tag');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after retrieving videos
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getVideos($videos = array(), $featuredVideos = array(), $pagination = null, $filter = null, $adapter = null, $rawUid = null, $uid = null, $type = null, $hashtags = null, $customFilter = null, $isSortingRequest = null, $from = false, $categoryId = false, $activeCategory = null, $cluster = null)
	{
		$output = '';

		// Generate correct return urls for operations performed here
		$returnUrl = ESR::videos();

		if ($uid && $type) {
			$returnUrl = $adapter->getAllVideosLink($filter);
		}

		$returnUrl = ES::formatCallback($returnUrl);
		$returnUrl = base64_encode($returnUrl);

		// Get the sorting URL
		$sortItems = new stdClass();
		$sortingTypes = array('latest', 'alphabetical', 'popular', 'commented', 'likes');

		$helper = ES::viewHelper('Videos', 'List');

		foreach ($sortingTypes as $sortingType) {

			$sortItems->{$sortingType} = new stdClass();

			// display the proper sorting name for the page title.
			$displaySortingName = $helper->getPageTitle(true);

			$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sortingType));
			$displaySortingName = $displaySortingName . ' - ' . $sortType;

			// attributes
			$sortAttributes = array('data-sorting', 'data-type="' . $sortingType . '"', 'title="' . $displaySortingName . '"');
			if ($customFilter->id) {
				$attributes[] = 'data-tag-id="' . $customFilter->id . '"';
			} else {
				$attributes[] = 'data-filter="' . $filter . '"';
			}

			//url
			$urlOptions = array();

			if ($categoryId) {
				$urlOptions['categoryId'] = $categoryId;
			}

			if (!$categoryId && !$customFilter->id) {
				$urlOptions['filter'] = $filter;
			}

			if ($customFilter->id) {
				$urlOptions['hashtagFilterId'] = $customFilter->getAlias();
			}

			$urlOptions['sort'] = $sortingType;

			$sortUrl = ESR::videos($urlOptions);

			$sortItems->{$sortingType}->attributes = $sortAttributes;
			$sortItems->{$sortingType}->url = $sortUrl;
		}

		// We define this browse view same like $showsidebar.
		// so it won't break when other customer that still using $showsidebar
		$browseView = !$uid;

		if ($customFilter && !$customFilter->id) {
			$customFilter = false;
		}

		$theme = ES::themes();
		$theme->set('filter', $filter);
		$theme->set('browseView', $browseView);
		$theme->set('activeCategory', $activeCategory);
		$theme->set('returnUrl', $returnUrl);
		$theme->set('rawUid', $rawUid);
		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('hashtags', $hashtags);
		$theme->set('customFilter', $customFilter);
		$theme->set('sortItems', $sortItems);
		$theme->set('sort', $sortingType);
		$theme->set('from', $from);
		$theme->set('cluster', $cluster);

		// if this is a sorting request.
		if ($isSortingRequest) {
			$contents = '';
			// Now retrieve the contents of the normal videos
			$theme->set('videos', $videos);
			$theme->set('showSidebar', true);
			$theme->set('pagination', $pagination);
			$contents .= $theme->output('site/videos/default/item.list');

			return $this->ajax->resolve($contents);
		}

		// below are the procesing when filter is click.
		$theme->set('featuredVideos', $featuredVideos);
		$featuredOutput = '';

		// If there is a list of featured videos, we need to output them as well
		if ($featuredVideos) {
			$theme->set('showSidebar', true);
			$theme->set('filter', 'featured');
			$theme->set('videos', $featuredVideos);
			$theme->set('pagination', '');
			$theme->set('isFeatured', true);
			$theme->set('featuredVideoLink', $adapter->getAllVideosLink('featured'));

			$featuredOutput = $theme->output('site/videos/default/item.list');
		}

		$theme->set('featuredOutput', $featuredOutput);

		$theme->set('filter', $filter);
		$theme->set('isFeatured', false);

		// Since ajax calls should only happen when sidebar is available, we default it to true
		$showSidebar = true;

		if ($filter == 'featured') {
			$theme->set('isFeatured', true);
			// $showSidebar = false;
		}

		$theme->set('showSidebar', $showSidebar);

		if ($pagination) {
			$theme->set('pagination', $pagination);
		}

		$theme->set('videos', $videos);

		$output .= $theme->output('site/videos/default/items');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders a custom filter form dialog
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterFormDialog()
	{
		// Get the filter id if the user is editing the filter
		$filterType = 'videos';
		$id = $this->input->get('id', 0, 'int');

		// Get cluster id
		$cid = $this->input->get('cid', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'string');

		// Try to load the filter
		$filter = ES::table('TagsFilter');

		if ($id) {
			$filter->load($id);
		}

		$theme = ES::themes();
		$theme->set('filter', $filter);
		$theme->set('filterType', $filterType);
		$theme->set('cid', $cid);
		$theme->set('clusterType', $clusterType);

		$output = $theme->output('site/videos/dialogs/filter.form');

		return $this->ajax->resolve($output);
	}

	/**
	 * Retrieve embed code of the video
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getEmbedCodes()
	{
		$id = $this->input->get('id', 0, 'int');

		// Load the video
		$video = ES::video();
		$video->load($id);

		$output = $video->getEmbedCodes(true);

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the dialog for users to upload picture for their video cover
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function uploadThumb()
	{
		// Only allow logged in users
		ES::requireLogin();

		// Get the unique item id
		$id = $this->input->get('id', 0, 'int');
		$return = $this->getReturnUrl();
		$isMobile = ES::responsive()->isMobile() ? '_MOBILE' : '';

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('isMobile', $isMobile);
		$theme->set('return', $return);

		$output = $theme->output('site/videos/dialogs/upload');

		return $this->ajax->resolve($output);
	}
}
