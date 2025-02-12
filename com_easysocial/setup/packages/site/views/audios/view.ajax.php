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
	 * Processes audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function process(SocialAudio $audio)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Returns the status of the processing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function status($audio, $progress)
	{
		// Once the progress is complete, we need to send the url to the audio
		$permalink = $audio->getPermalink(false);

		if ($progress === true) {
			return $this->ajax->resolve($permalink, 'done', $audio->export(), $audio->getAlbumArt());
		}

		return $this->ajax->resolve($permalink, $progress);
	}

	/**
	 * Displays confirmation to feature audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function confirmFeature()
	{
		// Get the audio id
		$id = $this->input->get('id', 0, 'int');

		// Determines if the user wants to specify a custom callback url
		$callback = $this->input->get('return', '', 'default');

		// Ensure that the user is really allowed to feature this audio
		$audioTable = ES::table('Audio');
		$audioTable->load($id);

		$audio = ES::audio($audioTable->uid, $audioTable->type, $audioTable);

		if (!$audio->canFeature()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_FEATURE'), 500);
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('callback', $callback);

		$output = $theme->output('site/audios/dialogs/feature');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation to unfeature audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function confirmUnfeature()
	{
		// Get the audio id
		$id = $this->input->get('id', 0, 'int');

		// Determines if the user wants to specify a custom callback url
		$callback = $this->input->get('return', '', 'default');

		// Ensure that the user is really allowed to delete this audio
		$audioTable = ES::table('Audio');
		$audioTable->load($id);

		$audio = ES::audio($audioTable->uid, $audioTable->type, $audioTable);

		if (!$audio->canUnfeature()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_UNFEATURE'), 500);
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('callback', $callback);

		$output = $theme->output('site/audios/dialogs/unfeature');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after a tag is deleted
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function removeTag()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays confirmation to delete audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Get the audio id
		$id = $this->input->get('id', 0, 'int');

		// Determines if the user wants to specify a custom callback url
		$callback = $this->input->get('return', '', 'default');

		$audioTable = ES::table('Audio');
		$audioTable->load($id);

		// Ensure that the user is really allowed to delete this audio
		$audio = ES::audio($audioTable);

		if (!$audio->canDelete()) {
			throw ES::exception(JText::_('COM_ES_AUDIO_NOT_ALLOWED_TO_DELETE'), 500);
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('callback', $callback);

		$output = $theme->output('site/audios/dialogs/delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Display confirmation to delete audio filter
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function confirmDeleteFilter()
	{
		// Get the filter id
		$id = $this->input->get('id', 0, 'int');
		$cid = $this->input->get('cid', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'string');

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('cid', $cid);
		$theme->set('clusterType', $clusterType);

		$output = $theme->output('site/audios/dialogs/deleteFilter');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after audio is tagged with people
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function tag(SocialAudio $audio, $tags = array())
	{
		$theme = ES::themes();
		$theme->set('audio', $audio);
		$theme->set('usersTags', $tags);

		$output = $theme->output('site/audios/item/tags.user');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays encoding message
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function showEncodingMessage()
	{
		$theme = ES::themes();

		$output = $theme->output('site/audios/dialogs/encoding');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays a dialog for users to tag
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function tagPeople()
	{
		$theme = ES::themes();

		// Get the audio id
		$id = $this->input->get('id', 0, 'int');
		$exclusion = $this->input->get('exclusion', array(), 'array');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'default');

		$audio = ES::audio($clusterId, $clusterType, $id);

		// Get a list of users that are already tagged with this audio
		$tags = $audio->getTags();

		$cluster = $audio->getCluster();

		$theme->set('cluster', $cluster);
		$theme->set('exclusion', $exclusion);

		$output = $theme->output('site/audios/dialogs/tag');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after retrieving audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAudios($audios = array(), $featuredAudios = array(), $pagination = null, $filter = null, $adapter = null, $rawUid = null, $uid = null, $type = null, $hashtags = null, $customFilter = null, $isSortingRequest = null, $from = false, $genreId = false, $activeGenre = false, $cluster = null)
	{
		$output = '';

		// Generate correct return urls for operations performed here
		$returnUrl = ESR::audios();

		if ($uid && $type) {
			$returnUrl = $adapter->getAllAudiosLink($filter);
		}

		$returnUrl = ES::formatCallback($returnUrl);
		$returnUrl = base64_encode($returnUrl);

		// Get the sorting URL
		$sortItems = new stdClass();
		$sortingTypes = array('latest', 'alphabetical', 'popular', 'commented', 'likes');

		$helper = ES::viewHelper('Audios', 'List');

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

			if ($genreId) {
				$urlOptions['genreId'] = $genreId;
			}

			if (!$genreId && !$customFilter->id) {
				$urlOptions['filter'] = $filter;
			}

			if ($customFilter->id) {
				$urlOptions['hashtagFilterId'] = $customFilter->getAlias();
			}

			$urlOptions['sort'] = $sortingType;

			$sortUrl = ESR::audios($urlOptions);

			$sortItems->{$sortingType}->attributes = $sortAttributes;
			$sortItems->{$sortingType}->url = $sortUrl;
		}

		// Get the list of playlist the user has.
		$listModel = ES::model('Lists');

		// Get the list items.
		$lists = $listModel->getLists(array('user_id' => $this->my->id, 'type' => SOCIAL_TYPE_AUDIOS));

		// We define this browse view same like $showsidebar. so it won't break when other custome that still using $showsidebar
		$browseView = !$uid;

		if ($customFilter && !$customFilter->id) {
			$customFilter = false;
		}

		$theme = ES::themes();
		$theme->set('browseView', $browseView);
		$theme->set('activeGenre', $activeGenre);
		$theme->set('returnUrl', $returnUrl);
		$theme->set('rawUid', $rawUid);
		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('hashtags', $hashtags);
		$theme->set('customFilter', $customFilter);
		$theme->set('sortItems', $sortItems);
		$theme->set('sort', $sortingType);
		$theme->set('from', $from);
		$theme->set('lists', $lists);
		$theme->set('cluster', $cluster);

		// if this is a sorting request.
		if ($isSortingRequest) {
			$contents = '';
			// Now retrieve the contents of the normal audios
			$theme->set('audios', $audios);
			$theme->set('showSidebar', true);
			$theme->set('pagination', $pagination);
			$contents .= $theme->output('site/audios/default/item.list');

			return $this->ajax->resolve($contents);
		}

		// below are the procesing when filter is click.
		$theme->set('featuredAudios', $featuredAudios);
		$featuredOutput = '';

		// If there is a list of featured audios, we need to output them as well
		if ($featuredAudios) {
			$theme->set('showSidebar', true);
			$theme->set('filter', 'featured');
			$theme->set('audios', $featuredAudios);
			$theme->set('pagination', '');
			$featuredOutput = $theme->output('site/audios/default/item.list');
		}

		$theme->set('featuredOutput', $featuredOutput);

		$theme->set('filter', $filter);
		$theme->set('isFeatured', false);

		// Since ajax calls should only happen when sidebar is available, we default it to true
		$showSidebar = !$uid;

		if ($filter == 'featured') {
			$theme->set('isFeatured', true);
		}

		$theme->set('showSidebar', $showSidebar);

		if ($pagination) {
			$theme->set('pagination', $pagination);
		}

		$theme->set('audios', $audios);

		$output .= $theme->output('site/audios/default/items');

		return $this->ajax->resolve($output);
	}

	/**
	 * Display Filter Form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getFilterForm()
	{
		$theme = ES::themes();

		// Get the filter id if the user is editing the filter
		$filterType = $this->input->get('type', '', 'word');
		$id = $this->input->get('id', 0, 'int');

		// Get cluster id
		$cid = $this->input->get('cid', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'string');

		// Try to load the filter
		$filter = ES::table('TagsFilter');

		if ($id) {
			$filter->load($id);
		}

		$theme->set('filter', $filter);
		$theme->set('filterType', $filterType);
		$theme->set('cid', $cid);
		$theme->set('clusterType', $clusterType);

		$output = $theme->output('site/audios/form/filter');

		return $this->ajax->resolve($output);
	}

	/**
	 * Display the playlist player
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function loadPlaylist()
	{
		$id = $this->input->get('id', 0, 'int');

		// Load the playlist
		$playlist = ES::table('list');
		$playlist->load($id);

		// Get the audios from the playlist
		$items = $playlist->getItems(false);

		$audios = array();
		foreach ($items as $item) {
			$audio = ES::table('Audio');
			$audio->load($item->target_id);

			// Assign listmap id into the audio
			$audioObj = ES::audio($audio);
			$audioObj->listMapId = $item->id;
			$audios[] = $audioObj;
		}

		$theme = ES::themes();
		$theme->set('activeList', $playlist);
		$theme->set('audios', $audios);

		$output = $theme->output('site/audios/player/playlist');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays a dialog to add audio to playlist
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function assignList()
	{
		// Only registered users allowed here
		ES::requireLogin();

		// Get the target id.
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$list = ES::table('List');
		$list->load($id);

		// Get a list of audios that are already in this list.
		$audios = $list->getItems();
		$audios = json_encode($audios);

		$theme = ES::themes();
		$theme->set('list', $list);
		$theme->set('audios', $audios);

		$contents = $theme->output('site/audios/dialogs/playlist.assign');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Responsible to output the JSON object of a result when searched.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function suggest($result)
	{
		// If there's nothing, just return the empty object.
		if (!$result) {
			return $this->ajax->resolve(array());
		}

		$items = array();
		$objects = array();

		// Determines if we should use a specific input name
		$inputName = $this->input->get('inputName', '', 'default');

		foreach ($result as $audio) {
			$theme = ES::themes();
			$theme->set('audio', $audio);
			$theme->set('inputName', $inputName);

			$items[] = $theme->output('site/audios/suggest/item');
		}

		return $this->ajax->resolve($items);
	}

	/**
	 * Assigns an audio into a playlist
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function assignItem($listMapIds = array(), $total = '')
	{
		$contents = array();

		foreach ($listMapIds as $key => $value) {

			$table = ES::table('audio');
			$table->load($value);
			$audio = ES::audio($table);

			// Assign list map id for this audio
			$audio->listMapId = $key;

			$theme = ES::themes();
			$theme->set('audio', $audio);
			$theme->set('count', $total);

			$contents[] = $theme->output('site/audios/player/playlist.item');

			$total++;
		}

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation to delete a playlist
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function confirmDeletePlaylist()
	{
		// Only registered users allowed here
		ES::requireLogin();

		// Get the target id.
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$list = ES::table('List');
		$list->load($id);

		$theme = ES::themes();
		$theme->set('list', $list);

		$contents = $theme->output('site/audios/dialogs/delete.playlist');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing for getplaylist count
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getListCounts($lists = array())
	{
		$result = array();

		if (!$lists) {
			return $this->ajax->resolve($result);
		}

		foreach ($lists as $list) {

			$data = new stdClass();
			$data->id = $list->id;
			$data->count = $list->getCount();

			$result[] = $data;
		}

		return $this->ajax->resolve($result);
	}

	/**
	 * Renders a custom filter form dialog
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function getFilterFormDialog()
	{
		$filterType = 'audios';
		$id = $this->input->get('id', 0, 'int');
		$cid = $this->input->get('cid', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'string');

		$filter = ES::table('TagsFilter');

		if ($id) {
			$filter->load($id);
		}

		$theme = ES::themes();
		$theme->set('filter', $filter);
		$theme->set('filterType', $filterType);
		$theme->set('cid', $cid);
		$theme->set('clusterType', $clusterType);

		$output = $theme->output('site/audios/dialogs/filter.form');

		return $this->ajax->resolve($output);
	}
}
