<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSidebarAudios extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$layout = $this->input->get('layout', '', 'cmd');

		// We do not want to render anything on the item layout
		if ($layout == 'item') {
			return;
		}

		return $this->renderListing();
	}

	public function renderListing()
	{
		$helper = ES::viewHelper('Audios', 'List');

		$filter = $helper->getCurrentFilter();

		$uid = $helper->getUid();
		$type = $helper->getType();
		$adapter = $helper->getAdapter();

		$cluster = $helper->getCluster();
		$isCluster = $helper->isCluster();
		$titles = $helper->getPageTitles();
		$isUserProfileView = $helper->isUserProfileView();

		// Custom filters
		$customFilters = $helper->getCustomFilters();
		$canCreateFilter = $helper->canCreateFilter();
		$createCustomFilterLink = $helper->getCreateCustomFilterLink();
		$activeCustomFilter = $helper->getActiveCustomFilter();

		// Acl for creation
		$allowCreation = $helper->canCreateAudio();
		$canCreatePlaylist = $helper->canCreatePlaylist();
		$createLink = $helper->getCreateLink();

		$total = false;
		$browseView = $helper->isBrowseView();

		// Additional filters
		$showMyAudios = $helper->showMyAudios();
		$showPendingAudios = $helper->showPendingAudios();

		// Filter acl
		$filtersAcl = $helper->getFiltersAcl();

		// Get a list of audio genres
		$genres = $helper->getGenres();
		$currentGenre = $helper->getActiveGenre();
		$playlists = $helper->getPlayLists();
		$activePlaylist = $helper->getActivePlaylist();

		$path = $this->getTemplatePath('audios');

		require($path);
	}
}
