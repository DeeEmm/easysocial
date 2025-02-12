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

class SocialRouterPhotos extends SocialRouterAdapter
{
	/**
	 * Constructs the photo's urls
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function build(&$menu , &$query)
	{
		$segments   = array();

		// Linkage to clusters
		if (isset($query['uid']) && isset($query['type']) && ($query['type'] == 'group' || $query['type'] == 'event' || $query['type'] == 'page')) {

			$addExtraSegments = true;

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

		// If there is no active menu for friends, we need to add the view.
		if ($menu && $menu->query['view'] != 'photos') {
			$segments[] = $this->translate($query['view']);
		}

		if (!$menu) {
			$segments[] = $this->translate($query['view']);
		}
		unset($query['view']);

		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id)) {
			$segments[] = $id;
			unset($query['id']);
		}

		// layouts that we do not want to include into the sef
		$ignoreLayouts = array('item');

		$layout     = isset($query['layout']) ? $query['layout'] : null;
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
		}

		if ($addLayout && !in_array($layout, $ignoreLayouts)) {
			$segments[] = $this->translate('photos_layout_' . $layout);
		}

		unset($query['layout']);

		// this code is needed here for user photos
		// New url structure uses uid=x&type=y
		$uid = isset( $query[ 'uid' ] ) ? $query[ 'uid' ] : null;
		$type = isset( $query[ 'type' ] ) ? $query[ 'type' ] : null;

		if (!is_null($uid) && !is_null($type)) {
			// $segments[] = $type;
			// $segments[] = $uid;

			unset( $query[ 'uid' ] );
			unset( $query[ 'type' ] );
		}


		// Determines if we should encode the userid
		$userId = isset($query['userid']) ? $query['userid'] : null;

		if (!is_null($userId)) {
			$segments[] = ESR::normalizePermalink($query['userid']);

			unset($query['userid']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since   1.0
	 * @access  public
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
			if (($activeMenu->query['view'] == 'groups' || $activeMenu->query['view'] == 'events') && $activeMenu->query['layout'] == 'item' && $activeMenu->query['id']) {
				$uid = $activeMenu->query['id'];

				if ($total > 1) {
					// we need to re-arrange the segments to simulate the groups albums.
					//
					$firstSegment = array_shift($segments);
					// array_unshift($segments, 'item'); // we need this layout 'item'

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


		// var_dump('photos', $segments);

		// reset the total count.
		$total = count($segments);
		$hasLayout  = false;

		// When user is viewing their own photo
		// URL: http://site.com/menu/photos/ID-photo-alias
		if ($total == 2) {
			$hasLayout = true;

			$vars['view'] = 'photos';
			$vars['layout'] = 'item';
			$photoId = $this->getIdFromPermalink($segments[1]);

			$vars['id'] = $photoId;

			$photo = ES::table('Photo');
			$photo->load($photoId);

			$vars['uid'] = $photo->uid;
			$vars['type'] = $photo->type;

			return $vars;
		}

		// When user tries to download their own photo
		// URL: http://site.com/menu/photos/ID-photo-alias/download
		if ($total == 3 && $segments[2] == $this->translate('photos_layout_download')) {
			$hasLayout = true;

			$vars['view'] = 'photos';
			$vars['layout'] = 'download';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			return $vars;
		}

		// When user tries to edit their own photo
		// URL: http://site.com/menu/photos/ID-photo-alias/form
		if ($total == 3 && $segments[2] == $this->translate('photos_layout_form')) {
			$hasLayout = true;

			$vars['view'] = 'photos';
			$vars['layout'] = 'form';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			return $vars;
		}


		$uTypes = array('group', 'event', 'page');

		// URL: http://site.com/menu/photos/ID-ALIAS/TYPE/ID-TYPEALIAS
		// URL: http://site.com/menu/photos/ID-ALIAS/TYPE/ID-TYPEALIAS/form
		if ($total > 3 && in_array($segments[2], $uTypes)) {
			$vars['view'] = 'photos';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);
			$vars['type'] = $segments[2];
			$vars['uid'] = $this->getIdFromPermalink($segments[3], $segments[2]);

			$vars['layout'] = 'item';
			if (isset($segments[4]) && ($segments[4] == $this->translate('photos_layout_form') || $segments[4] == 'form')) {
				$vars['layout'] = 'form';
			}

			if (isset($segments[4]) && ($segments[4] == $this->translate('photos_layout_download') || $segments[4] == 'download')) {
				$vars['layout'] = 'download';
			}

			// var_dump($vars);
		}




		// // When viewer tries to view another person's photo
		// // URL: http://site.com/menu/photos/item/ID-photo-alias/ID-user-alias
		// if ($total == 5 && $segments[1] == $this->translate('photos_layout_item')) {
		//  $vars['view'] = 'photos';
		//  $vars['layout'] = 'item';
		//  $vars['id'] = $segments[2];
		//  $vars['type'] = $segments[3];

		//  if ($vars['type'] == 'user') {
		//      $vars['uid'] = $this->getUserId($segments[4]);
		//  } else {
		//      $vars['uid'] = $this->getIdFromPermalink($segments[4]);
		//  }

		//  return $vars;
		// }


		// // When viewer tries to view another person's photo
		// // URL: http://site.com/menu/photos/item/ID-photo-alias/ID-user-alias
		// if ($total == 4 && $segments[1] == $this->translate('photos_layout_item')) {
		//  $hasLayout = true;

		//  $vars['view'] = 'photos';
		//  $vars['layout'] = 'item';
		//  $vars['id'] = $segments[2];
		//  $vars['userid'] = $this->getUserId($segments[3]);

		//  return $vars;
		// }

		// // When viewer is editing a photo
		// // URL: http://site.com/menu/photos/form/ID-photo-alias/ID-user-alias
		// if ($total == 4 && $segments[1] == $this->translate('photos_layout_form')) {
		//  $vars['view'] = 'photos';
		//  $vars['layout'] = 'form';
		//  $vars['id'] = $segments[2];
		//  $vars['userid'] = $this->getUserId($segments[3]);

		//  return $vars;
		// }

		// // URL: http://site.com/menu/photos/ID-photo-alias/ID-user-alias
		// if ($total == 3 && !$hasLayout) {

		//  $vars['view'] = 'photos';
		//  $vars['layout'] = 'item';
		//  $vars['id'] = $this->getIdFromPermalink($segments[1]);
		//  $vars['userid'] = $this->getUserId($segments[2]);

		//  return $vars;
		// }

		return $vars;
	}
}
