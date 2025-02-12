<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterAudios extends SocialRouterAdapter
{
	/**
	 * Constructs the points urls
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function build(&$menu, &$query)
	{
		$segments = array();
		$isUserAudios = true;
		$isClusterAudios = false;
		$addExtraView = false;
		$config = ES::config();

		// Linkage to clusters
		if (isset($query['uid']) && isset($query['type']) && ($query['type'] == 'group' || $query['type'] == 'event' || $query['type'] == 'page')) {

			$isUserAudios = false;
			$addExtraSegments = true;
			$isClusterAudios = true;


			$xViews = array('group' => 'groups', 'page' => 'pages', 'event' => 'events');

			// we need to determine if we need to add below segments or not
			if (isset($query['Itemid'])) {
				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

				if ($xMenu) {
					$xquery = $xMenu->query;

					$utype = $query['type'];
					$xView = $xViews[$utype];

					if ($xquery['view'] == $xView && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id'])) {
						$xId = (int) $xquery['id'];
						$tId = (int) $query['uid'];
						if ($xId == $tId) {
							$addExtraSegments = false;
						}
					}
				}
			}

			$type = $query['type'];

			if ($addExtraSegments) {

				// we need to change Itemid to respect the culster type.
				$utype = $query['type'];
				$xView = $xViews[$utype];

				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);
				if ($xMenu) {
					$xquery = $xMenu->query;

					if ($xquery['view'] != $xView) {
						$query['Itemid'] = ESR::getItemId($xView, 'item', (int) $query['uid']);
						$addExtraView = true;

						$segments[] = $this->translate($xView);
					}
				}

				$segments[] = ESR::normalizePermalink($query['uid']);
			}

			unset($query['uid']);
			unset($query['type']);
		}

		// Audio id
		$isSingleAudio = false;
		if (isset($query['id'])) {
			$isSingleAudio = true;
		}


		$uid = isset($query['uid']) ? $query['uid'] : null;
		$type = isset($query['type']) ? $query['type'] : null;

		// for user profile audios, we need the uid segments
		if (isset($query['uid']) && isset($query['type']) && $isUserAudios && !$isSingleAudio) {
			$isUserAudios = true;
			$segments[] = ESR::normalizePermalink($query['uid']);
			$addExtraView = true;
		}

		// for user videos, we should standardize the url like cluster to include uid segment.
		// #3128
		if (isset($query['uid']) && isset($query['type']) && $isUserAudios && $isSingleAudio) {
			$isUserAudios = true;

			// only add the uid based on the config #3342
			if ($config->get('seo.mediasef') == SOCIAL_MEDIA_SEF_WITHUSER) {
				$segments[] = ESR::normalizePermalink($query['uid']);
				$addExtraView = true;
			}
		}

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'audios') {
			$segments[] = $this->translate($query['view']);
			$addExtraView = false;
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[] = $this->translate($query['view']);
			$addExtraView = false;
		}

		if ($addExtraView) {
			$segments[] = $this->translate($query['view']);
		}

		// Audio id
		if (isset($query['id'])) {
			$segments[] = ESR::normalizePermalink($query['id']);
			unset($query['id']);
		}

		// Filtering by genre
		if (isset($query['genreId'])) {
			$segments[] = $this->translate('audios_genres');
			$segments[] = ESR::normalizePermalink($query['genreId']);

			unset($query['genreId']);
		}

		unset($query['uid']);
		unset($query['type']);

		// layouts that we do not want to include into the sef
		$ignoreLayouts = array('item');

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout) && !in_array($layout, $ignoreLayouts)) {
			$segments[] = $this->translate('audios_layout_' . $layout);
		}
		unset($query['layout']);


		$listId = isset($query['listId']) ? $query['listId'] : '';

		if ($listId && $layout != 'playlistform') {
			$segments[]	= $this->translate('audios_layout_playlist');
			$segments[]	= $listId;
			unset($query['listId']);
		}

		// Filtering on audios listing
		if (!isset($query['genreId']) && isset($query['filter']) && $query['filter'] != 'all') {
			$segments[] = $this->translate('audios_filter_' . $query['filter']);
		}
		unset($query['filter']);

		// Custom filters
		if (isset($query['hashtagFilterId'])) {
			$segments[] = $this->translate('audios_hashtag_filter');
			$segments[] = $query['hashtagFilterId'];

			unset($query['hashtagFilterId']);
		}

		// hashtag filter
		if (isset($query['hashtag'])) {
			$segments[] = $this->translate('audios_hashtag');
			$segments[] = $query['hashtag'];

			unset($query['hashtag']);
		}

		unset($query['view']);

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function parse(&$segments)
	{
		$vars = array();

		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// Get active menu
		$activeMenu = $menu->getActive();

		// For audios on group pages, we need to parse it differently as it was composed differently with a menu id on the site
		// The activemenu MUST have the appropriate query data
		if ($activeMenu && isset($activeMenu->query['view']) && isset($activeMenu->query['layout']) && isset($activeMenu->query['id'])) {

			// Since there is parts of the group in the menu parameters, we can safely assume that the user is viewing a group item page.
			if (($activeMenu->query['view'] == 'groups' || $activeMenu->query['view'] == 'events' || $activeMenu->query['view'] == 'pages') && $activeMenu->query['layout'] == 'item' && $activeMenu->query['id']) {

				$uid = $activeMenu->query['id'];

				// we need to re-arrange the segments to simulate the groups audios.
				// we need to remove the 1st element 1st so that we can prepend whatever uid / type we need for the group.
				$firstSegment = array_shift($segments);

				// here we add the type and uid. do not re-arrange the sequence. do so will affect the segment index in parsing at later on.
				$clusterType = 'group';
				if ($activeMenu->query['view'] == 'events') {
					$clusterType = 'event';
				} else if ($activeMenu->query['view'] == 'pages') {
					$clusterType = 'page';
				}

				array_unshift($segments, $clusterType, $uid); // DO NOT REARRANGE!

				// now we add back the first element;
				array_unshift($segments, $firstSegment);
			}
		}

		$total = count($segments);

		if ($total >= 3 && ($segments[0] == $this->translate('audios') || $segments[0] == 'audios') && ($segments[2] == $this->translate('audios') || $segments[2] == 'audios')) {

			// we now, this is caused by audio menu items. lets re-arrange the segments
			$firstSegment = array_shift($segments);
			$secondSegment = array_shift($segments);
			array_shift($segments); // remove the 3rd elements, which is the 'audios'

			array_unshift($segments, $firstSegment, 'user', $secondSegment);

			// recalculate the total segments;
			$total = count($segments);
		}

		// By default this view is going to be audios
		$vars['view'] = 'audios';

		$filters = array($this->translate('audios_filter_all'), $this->translate('audios_filter_featured'), $this->translate('audios_filter_mine'), $this->translate('audios_filter_pending'), $this->translate('audios_filter_filterform'));
		$layouts = array($this->translate('audios_layout_form'), $this->translate('audios_layout_item'));

		// audios/form
		if ($total == 2 && $segments[1] == $this->translate('audios_layout_form')) {
			$vars['layout'] = 'form';

			return $vars;
		}

		// URL: http://site.com/menu/audios/listform
		if ($total == 2 && $segments[1] == $this->translate('audios_layout_playlistform')) {
			$vars['view'] = 'audios';
			$vars['layout'] = 'playlistform';

			return $vars;
		}

		// audios/filter
		if ($total == 2 && in_array($segments[1], $filters)) {
			$vars['filter'] = $this->getFilter($segments[1]);
			return $vars;
		}

		// audios/id-alias
		if ($total == 2){
			$vars['layout'] = 'item';
			$audioId = (int) $this->getIdFromPermalink($segments[1]);

			$audio = ES::table('Audio');
			$audio->load($audioId);

			$vars['id'] = $audioId;
			// $vars['uid'] = $audio->uid;
			// $vars['type'] = $audio->type;

			return $vars;
		}

		if ($total >= 3 && $segments[1] == $this->translate('audios_genres')) {
			$vars['genreId'] = $segments[2];

			if (isset($segments[3]) && $segments[3] == $this->translate('audios_layout_form')) {
				$vars['layout'] = 'form';
			}

			return $vars;
		}

		// audios/form/id-genre
		if ($total == 3 && $segments[1] == $this->translate('audios_layout_form')) {
			$vars['layout'] = 'form';
			$vars['genreId'] = $this->getIdFromPermalink($segments[2]);

			return $vars;
		}

		// audios/id/form
		if ($total == 3 && $segments[2] == $this->translate('audios_layout_form')) {
			$vars['id'] = $this->getIdFromPermalink($segments[1]);
			$vars['layout'] = 'form';

			return $vars;
		}

		// audios/id/process
		if ($total == 3 && $segments[2] == $this->translate('audios_layout_process')) {
			$vars['id'] = $this->getIdFromPermalink($segments[1]);
			$vars['layout'] = 'process';

			return $vars;
		}

		// URL: http://site.com/menu/audios/list/ID
		if ($total == 3 && $segments[1] == $this->translate('audios_layout_playlist')) {
			$vars['view'] = 'audios';
			$vars['listId'] = $segments[2];

			return $vars;
		}

		// audios/[hashtagFilter]/[hashtagFilterId]
		if ($total == 3 && $segments[1] == $this->translate('audios_hashtag_filter')) {
			$vars['hashtagFilterId'] = $this->getIdFromPermalink($segments[2]);

			return $vars;
		}

		// audios/[hashtagFilterId]/[filterForm]
		if ($total == 3 && $segments[2] == $this->translate('audios_filter_filterform')) {
			$vars['id'] = $segments[1];
			$vars['filter'] = $segments[2];

			return $vars;
		}

		// audios/[hashtag]/[hashtagKeyword]
		if ($total == 3 && $segments[1] == $this->translate('audios_hashtag')) {
			$vars['hashtag'] = $segments[2];

			return $vars;
		}

		// When user tries to download their audio
		// URL: http://site.com/menu/audios/ID-audio-alias/download
		if ($total == 3 && $segments[2] == $this->translate('audios_layout_download')) {
			$hasLayout = true;

			$vars['view'] = 'audios';
			$vars['layout'] = 'download';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			return $vars;
		}

		// most likely this is clusters or user audios
		// audios/[type]/[uid]
		$allowedTypes = array(SOCIAL_TYPE_USER, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT);

		if ($total == 3 && in_array($segments[1], $allowedTypes)) {
			$vars['type'] = $segments[1];

			if ($vars['type'] == SOCIAL_TYPE_USER) {
				$vars['uid'] = $this->getUserId($segments[2]);
			} else {
				$vars['uid'] = $this->getIdFromPermalink($segments[2]);
			}

			return $vars;
		}

		// audios/[type]/[uid]/form
		if ($total == 4 && $segments[3] == $this->translate('audios_layout_form')) {
			$vars['type'] = $segments[1];
			$vars['uid'] = $segments[2];
			$vars['layout'] = 'form';
			return $vars;
		}

		//audios/[type]/[uid]/genres
		//audios/[type]/[uid]/genres/id-genre
		if ($total >= 4 && $segments[3] == $this->translate('audios_genres')) {
			$vars['genreId'] = $segments[4];

			$vars['uid'] = $this->getIdFromPermalink($segments[2]);
			$vars['type'] = $segments[1];

			if (isset($segments[5]) && $segments[5] == $this->translate('audios_layout_form')) {
				$vars['layout'] = 'form';
			}

			return $vars;
		}

		// audios/[type]/[uid]/filter
		if ($total == 4 && in_array($segments[3], $filters)) {
			$vars['type'] = $segments[1];

			if ($vars['type'] == 'user') {
				$vars['uid'] = $this->getUserId($segments[2]);
			} else {
				$vars['uid'] = $this->getIdFromPermalink($segments[2]);
			}

			$vars['filter'] = $this->getFilter($segments[3]);
			return $vars;
		}

		// audios/[type]/[uid]/id-audio
		if ($total == 4 && !in_array($segments[3], $filters) && in_array($segments[1], $allowedTypes)) {

			$vars['layout'] = 'item';
			$vars['type'] = $segments[1];

			if ($vars['type'] == SOCIAL_TYPE_USER) {
				$vars['uid'] = $this->getUserId($segments[2]);
			} else {
				$vars['uid'] = $this->getIdFromPermalink($segments[2]);
			}

			$vars['id'] = $segments[3];
			return $vars;
		}

		// audios/[type]/[uid]/id/process
		if ($total == 5 && $segments[4] == $this->translate('audios_layout_process')) {
			$vars['type'] = $segments[1];
			$vars['uid'] = $segments[2];
			$vars['layout'] = 'process';
			$vars['id'] = $this->getIdFromPermalink($segments[3]);

			return $vars;
		}

		// audios/[type]/[uid]/[id]/form
		if ($total == 5 && $segments[4] == $this->translate('audios_layout_form')) {
			$vars['type'] = $segments[1];
			$vars['uid'] = $segments[2];
			$vars['id'] = $segments[3];
			$vars['layout'] = 'form';
			return $vars;
		}

		// audios/[type]/[uid]/[id]/[item]
		if ($total == 5 && $segments[4] == $this->translate('audios_layout_item')) {
			$vars['type'] = $segments[1];
			$vars['uid'] = $segments[2];
			$vars['id'] = $segments[3];
			$vars['layout'] = 'item';
			return $vars;
		}

		// audios/[type]/[uid]/[hashtagFilterId]/[filterForm]
		if ($total == 5 && in_array($segments[4], $filters)) {
			$vars['type'] = $segments[1];
			$vars['uid'] = $segments[2];
			$vars['id'] = $segments[3];
			$vars['filter'] = $segments[4];

			return $vars;
		}

		// audios/[type]/[uid]/[hashtag]/[hashtagKeyword]
		if ($total == 5 && $segments[3] == $this->translate('audios_hashtag')) {
			$vars['type'] = $segments[1];
			$vars['uid'] = $segments[2];
			$vars['hashtag'] = $segments[4];

			return $vars;
		}

		// audios/[type]/[uid]/[hashtagFilter]/[hashtagFilterId]
		if ($total == 5 && $segments[3] == $this->translate('audios_hashtag_filter')) {
			$vars['type'] = $segments[1];

			if ($vars['type'] == 'user') {
				$vars['uid'] = $this->getUserId($segments[2]);
			} else {
				$vars['uid'] = $this->getIdFromPermalink($segments[2]);
			}

			$vars['hashtagFilterId'] = $segments[4];
		}

		return $vars;
	}

	/**
	 * Retrieve the filter
	 *
	 * @since   2.1
	 * @access  public
	 */
	private function getFilter($translated)
	{
		if ($translated == $this->translate('audios_filter_mine')) {
			return 'mine';
		}

		if ($translated == $this->translate('audios_filter_pending')) {
			return 'pending';
		}

		if ($translated == $this->translate('groups_filter_featured')) {
			return 'featured';
		}

		if ($translated == $this->translate('audios_filter_filterform')) {
			return 'filterForm';
		}

		// Default to return all
		return 'all';
	}

}
