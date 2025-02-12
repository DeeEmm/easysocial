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

class SocialRouterAlbums extends SocialRouterAdapter
{
	/**
	 * Constructs the album's urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$ignoreLayouts = array('item');
		$segments 	= array();

		$isUserAlbums = false;
		$isClusterAlbums = false;
		$addExtraView = false;
		$config = ES::config();

		// Linkage to clusters
		if (isset($query['uid']) && isset($query['type']) && ($query['type'] == 'group' || $query['type'] == 'event' || $query['type'] == 'page')) {

			$isUserAlbums = false;
			$isClusterAlbums = true;

			$xViews = array('group' => 'groups', 'page' => 'pages', 'event' => 'events');

			$addExtraSegments = true;
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

		$isSingleAlbum = false;
		$albumId = isset($query['id']) ? $query['id'] : null;

		if (!is_null($albumId)) {
			$isSingleAlbum = true;
		}

		// this code is needed here for user albums
		// New url structure uses uid=x&type=y
		$uid = isset($query['uid']) ? $query['uid'] : null;
		$type = isset($query['type']) ? $query['type'] : null;

		// for user profile albums, we need the uid segments
		if (!is_null($uid) && !is_null($type) && !$isClusterAlbums && !$isSingleAlbum) {
			$isUserAlbums = true;
			$segments[] = ESR::normalizePermalink($uid);
			$addExtraView = true;
		}

		if (!is_null($uid) && !is_null($type) && !$isClusterAlbums && $isSingleAlbum) {
			$isUserAlbums = true;

			if ($config->get('seo.mediasef') == SOCIAL_MEDIA_SEF_WITHUSER) {

				$segments[] = ESR::normalizePermalink($uid);
				$addExtraView = true;
			}

			if ($config->get('seo.mediasef') == SOCIAL_MEDIA_SEF_DEFAULT && !$config->get('seo.useid') && $albumId) {
				// if admin set to use default media style AND, if admin decide not to include object id. This might give
				// issue for user's avatar / story albums. Thus, we enforce to have the user-alias segment.

				// lets check if this is core albums or not.
				$album = ES::table('Album');
				$album->load($albumId);

				if ($album->id && $album->isCore()) {
					$segments[] = ESR::normalizePermalink($uid);
					$addExtraView = true;
				}
			}
		}

		unset($query['uid']);
		unset($query['type']);

		// Determines if userid is present in query string
		$userId = isset($query['userid']) ? $query['userid'] : null;

		if (!is_null($userId)) {
			$segments[] = ESR::normalizePermalink($query['userid']);

			unset($query['userid']);
		}

		// if ($menu && ($menu->query['view'] != 'albums' || $isUserAlbums || $isClusterAlbums)) {
		// if ($menu && $menu->query['view'] != 'albums' && (is_null($albumId) || $isUserAlbums || ($uid && $type == 'user' && $albumId) ) ) {

		if ($menu && ($menu->query['view'] != 'albums')) {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}

		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}


		if ($addExtraView) {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}

		unset($query['view']);

		// if there is an album id attached, we will add it here.
		if (!is_null($albumId)) {
			$segments[]	= ESR::normalizePermalink($albumId);
		}
		unset($query['id']);


		$layout = isset($query['layout']) ? $query['layout'] : null;
		$menuLayout = isset($menu->query['layout']) ? $menu->query['layout'] : null;
		$addLayout = false;

		if (is_null($menuLayout)) {
			if (!is_null($layout)) {
				$addLayout = true;
			}
		} else {
			if (!is_null($layout) && $layout != $menuLayout) {
				$addLayout = true;
			}

			// Exception for form layout
			if (!is_null($layout) && $layout == 'form') {
				$addLayout = true;
			}
		}

		if ($addLayout && !in_array($layout, $ignoreLayouts)) {
			$segments[] = $this->translate('albums_layout_' . $layout);
		}
		unset($query['layout']);

		return $segments;
	}


	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	An array of url segments
	 * @return	array 	The query string data
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// Get active menu
		$activeMenu = $menu->getActive();

		// For albums on group pages, we need to parse it differently as it was composed differently with a menu id on the site
		// The activemenu MUST have the appropriate query data
		if ($activeMenu && isset($activeMenu->query['view']) && isset($activeMenu->query['layout']) && isset($activeMenu->query['id'])) {

			// Since there is parts of the group in the menu parameters, we can safely assume that the user is viewing a group item page.
			if (($activeMenu->query['view'] == 'groups' || $activeMenu->query['view'] == 'events' || $activeMenu->query['view'] == 'pages') && $activeMenu->query['layout'] == 'item' && $activeMenu->query['id']) {
				$uid = $activeMenu->query['id'];

				if ($total > 1) {
					// we need to re-arrange the segments to simulate the groups albums.

					$addItemLayout = true;
					if (($segments[1] == $this->translate('albums_layout_form') || $segments[1] == 'form') ||
						($segments[1] == $this->translate('albums_layout_all') || $segments[1] == 'all') ||
						($segments[1] == $this->translate('albums_layout_favourite') || $segments[1] == 'favourite')) {
						$addItemLayout = false;
					}

					$firstSegment = array_shift($segments);
					if ($addItemLayout) {
						// array_unshift($segments, 'item'); // we need this layout 'item'
					}

					// now we add back the first element;
					array_unshift($segments, $firstSegment);
				}

				$clusterType = 'group';
				if ($activeMenu->query['view'] == 'events') {
					$clusterType = 'event';
				} else if ($activeMenu->query['view'] == 'pages') {
					$clusterType = 'page';
				}

				// to fulfill the parser, we will need to add the below segments
				$segments[] = $clusterType;
				$segments[] = $uid;
			}
		}

		// reset the total count.
		$total = count($segments);

		if ($total >= 3 && ($segments[0] == $this->translate('albums') || $segments[0] == 'albums') && ($segments[2] == $this->translate('albums') || $segments[2] == 'albums')) {

			// we now, this is caused by album menu items. lets re-arrange the segments
			$firstSegment = array_shift($segments);
			$secondSegment = array_shift($segments);
			array_shift($segments); // remove the 3rd elements, which is the 'albums'

			array_unshift($segments, $firstSegment, 'user', $secondSegment);

			// recalcute the total segments;
			$total = count($segments);
		}

		// var_dump('albums', $segments);

		// User is viewing their own albums list
		// URL: http://site.com/menu/albums
		if ($total == 1 && ($segments[0] == $this->translate('albums') || $segments[0] == 'albums')) {
			$vars['view'] = 'albums';
			return $vars;
		}

		// User is trying to create a new album
		// URL: http://site.com/menu/albums/form
		if ($total == 2 && ($segments[1] == $this->translate('albums_layout_form') || $segments[1] == 'form')) {
			$vars['view'] = 'albums';
			$vars['layout'] = 'form';

			return $vars;
		}


		// URL: http://site.com/menu/albums/all
		if ($total == 2 && ($segments[1] == $this->translate('albums_layout_all') || $segments[1] == 'all')) {
			$vars['view'] = 'albums';
			$vars['layout'] = 'all';

			return $vars;
		}

		// URL: http://site.com/menu/albums/favourite
		if ($total == 2 && ($segments[1] == $this->translate('albums_layout_favourite') || $segments[1] == 'favourite')) {
			$vars['view'] = 'albums';
			$vars['layout'] = 'favourite';

			return $vars;
		}

		// URL: http://site.com/menu/albums/mine
		if ($total == 2 && ($segments[1] == $this->translate('albums_layout_mine') || $segments[1] == 'mine')) {
			$vars['view'] = 'albums';
			$vars['layout'] = 'mine';

			return $vars;
		}


		// URL: http://site.com/menu/albums/id-album-title
		if ($total == 2) {
			$vars['view'] = 'albums';
			$vars['layout'] = 'item';

			$albumId = $this->getIdFromPermalink($segments[1]);
			$vars['id'] = $albumId;


			$album = ES::table('Album');
			$album->load($albumId);

			$vars['uid'] = $album->uid;
			$vars['type'] = $album->type;

			return $vars;
		}

		// URL: http://site.com/menu/albums/id-album-title/form
		if ($total == 3 && ($segments[2] == $this->translate('albums_layout_form') || $segments[2] == 'form')) {
			$vars['view'] = 'albums';
			$vars['layout'] = 'form';

			$albumId = $this->getIdFromPermalink($segments[1]);
			$vars['id'] = $albumId;


			$album = ES::table('Album');
			$album->load($albumId);

			$vars['uid'] = $album->uid;
			$vars['type'] = $album->type;

			return $vars;
		}


		// User is viewing another person's albums list
		// URL: http://site.com/menus/albums/TYPE/ID-alias/
		if ($total == 3 && ($segments[0] == $this->translate('albums') || $segments[0] == 'albums')) {
			$vars['view'] = 'albums';
			$vars['type'] = $segments[1];

			// Get the id from the permalink
			$vars['uid'] = $this->getIdFromPermalink($segments[2] , $vars['type']);

			return $vars;
		}

		$uTypes = array('group', 'event', 'page', 'user');

		// URL: http://site.com/menu/albums/ID-ALIAS/TYPE/ID-TYPEALIAS
		// URL: http://site.com/menu/albums/ID-ALIAS/TYPE/ID-TYPEALIAS/form
		if ($total > 3 && in_array($segments[2], $uTypes)) {
			$vars['view'] = 'albums';
			$vars['type'] = $segments[2];
			$vars['uid'] = $this->getIdFromPermalink($segments[3], $segments[2]);

			$vars['layout'] = 'item';

			if ($segments[1] !== $this->translate('albums_layout_form')) {
				$vars['id'] =  $this->getIdFromPermalink($segments[1]);
			} else {
				$vars['layout'] = 'form';
			}

			if (isset($segments[4]) && ($segments[4] == $this->translate('albums_layout_form') || $segments[4] == 'form')) {
				$vars['layout'] = 'form';
			}
		}

		// URL:: http://site.com/menu/albums/album-id-alias/form/type/type-alias
		if ($total > 3 && in_array($segments[3], $uTypes)) {
			$vars['view'] = 'albums';
			$vars['type'] = $segments[3];
			$vars['uid'] = $this->getIdFromPermalink($segments[4], $segments[3]);

			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			if ($segments[2] == $this->translate('albums_layout_form') || $segments[2] == 'form') {
				$vars['layout'] = 'form';
			}
		}

		// cluster create album
		// URL: http://site.com/menu/albums/TYPE/ID-TYPEALIAS/form
		if ($total > 3 && in_array($segments[1], $uTypes) && ($segments[3] == $this->translate('albums_layout_form') || $segments[3] == 'form')) {
			$vars['view'] = 'albums';
			$vars['type'] = $segments[1];
			$vars['uid'] = $this->getIdFromPermalink($segments[2], $segments[1]);
			$vars['layout'] = 'form';
		}

		// // Creating a new album
		// if ($total == 4 && ($segments[1] == $this->translate('albums_layout_form') || $segments[1] == 'form')) {
		// 	$vars['view'] = 'albums';
		// 	$vars['layout']	= 'form';
		// 	$vars['type'] = $segments[2];
		// 	$vars['uid'] = $this->getIdFromPermalink($segments[3] , SOCIAL_TYPE_USER);

		// 	return $vars;
		// }

		// // Editing an album
		// // URL: http://site.com/menu/albums/form/ID-ALIAS/TYPE/ID-TYPEALIAS
		// if ($total == 5 && ($segments[1] == $this->translate('albums_layout_form') || $segments[1] == 'form')) {
		// 	$vars['view'] = 'albums';
		// 	$vars['layout']	= 'form';
		// 	$vars['id'] = $this->getIdFromPermalink($segments[2]);
		// 	$vars['type'] = $segments[3];
		// 	$vars['uid'] = $this->getIdFromPermalink($segments[4] , $segments[3]);

		// 	return $vars;
		// }

		// User is viewing another object's album
		// if ($total == 5 && ($segments[1] == $this->translate('albums_layout_item') || $segments[1] == 'item')) {

		// 	$vars['view'] = 'albums';
		// 	$vars['layout'] = 'item';
		// 	$vars['id'] = $this->getIdFromPermalink($segments[2]);
		// 	$vars['type'] = $segments[3];

		// 	if ($vars['type'] == 'user') {
		// 		$vars['uid'] = $this->getUserId($segments[4]);
		// 	} else {
		// 		$vars['uid'] = $this->getIdFromPermalink($segments[4]);
		// 	}

		// 	return $vars;
		// }

		return $vars;
	}
}
