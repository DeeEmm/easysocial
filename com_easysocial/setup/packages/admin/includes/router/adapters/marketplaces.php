<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterMarketplaces extends SocialRouterAdapter
{
	public function build(&$menu, &$query)
	{
		$segments = array();
		$config = ES::config();
		$isClusterListing = false;

		$ignoreLayouts = array('item');
		$frontLayouts = array('category');
		$addExtraView = false; // used for user clusters

		// Linkage to clusters
		if (isset($query['uid']) && isset($query['type']) && ($query['type'] == 'group' || $query['type'] == 'page')) {
			$isClusterListing = true;
			$addExtraSegments = true;
			$type = $query['type'];

			// we need to determine if we need to add below segments or not
			if (isset($query['Itemid'])) {
				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

				if ($xMenu) {
					$xquery = $xMenu->query;

					$xView = 'groups';
					if ($type == 'page') {
						$xView = 'pages';
					}

					if ($xquery['view'] == $xView && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id'])) {
						$xId = (int) $xquery['id'];
						$tId = (int) $query['uid'];
						if ($xId == $tId) {
							$addExtraSegments = false;
						}
					}
				}
			}

			if ($addExtraSegments) {

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

		$isSingleListing = false;
		if (isset($query['id'])) {
			$isSingleListing = true;
		}

		$uid = isset($query['uid']) ? $query['uid'] : null;
		$type = isset($query['type']) ? $query['type'] : null;

		// for user profile videos, we need the uid segments
		if (!is_null($uid) && !is_null($type) && !$isClusterListing && !$isSingleListing) {
			$isUserVideos = true;
			$segments[] = ESR::normalizePermalink($query['uid']);
			$addExtraView = true;
		}

		if (!is_null($uid) && (isset($query['type']) && $query['type'] == 'user') && $isSingleListing) {

			// only add the uid based on the config #3342
			if ($config->get('seo.mediasef') == SOCIAL_MEDIA_SEF_WITHUSER) {
				$segments[] = ESR::normalizePermalink($query['uid']);
				$addExtraView = true;
			}
		}


		if ($menu && $menu->query['view'] !== 'marketplaces' || $addExtraView) {
			$segments[] = $this->translate($query['view']);
			$addExtraView = false;
		}

		if (!$menu || $addExtraView) {
			$segments[] = $this->translate($query['view']);
		}
		unset($query['view']);

		if (isset($query['filter'])) {

			// If filter is all, then we do not want this segment
			if ($query['filter'] !== 'all') {
				$segments[] = $this->translate('marketplaces_filter_' . $query['filter']);
			}

			unset($query['filter']);
		}

		if (isset($query['categoryid']) && $query['categoryid']) {
			$segments[] = $this->translate('marketplaces_categories');
			$segments[] = ESR::normalizePermalink($query['categoryid']);
			unset($query['categoryid']);
		}

		unset($query['uid']);
		unset($query['type']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		// front layouts
		if (!is_null($layout) && in_array($layout, $frontLayouts)) {
			$segments[] = $this->translate('marketplaces_layout_' . $layout);
		}

		// marketplace id.
		if (isset($query['id'])) {

			$addExtraSegments = true;

			if (isset($query['Itemid'])) {

				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

				if ($xMenu) {
					$xquery = $xMenu->query;

					$xView = 'marketplaces';
					$allowedType = array('info');

					if ($xquery['view'] == $xView && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id']) && in_array(isset($query['page']), $allowedType)) {
						$xV = (int) $xquery['view'];
						$tV = (int) $query['page'];

						if ($xV == $tV) {
							$addExtraSegments = false;
						}
					}
				}
			}

			if ($addExtraSegments) {
				$segments[] = ESR::normalizePermalink($query['id']);
			}

			unset($query['id']);
		}

		// behind layout
		if (!is_null($layout) && !in_array($layout, $ignoreLayouts) && !in_array($layout, $frontLayouts)) {
			$segments[] = $this->translate('marketplaces_layout_' . $layout);
		}

		unset($query['layout']);

		if (isset($query['step'])) {
			$segments[] = $query['step'];
			unset($query['step']);
		}

		// Special handling for timeline and about

		if (isset($query['page'])) {


			if ($query['page'] === 'filterForm') {
				$segments[] = $this->translate('marketplaces_type_filterform');

				if (isset($query['filterId'])) {
					$segments[] = $query['filterId'];
					unset($query['filterId']);
				}
			}

			unset($query['page']);
		}

		//
		if (isset($query['ordering'])) {
			$segments[] = $this->translate('marketplaces_ordering_' . $query['ordering']);
			unset($query['ordering']);
		}

		return $segments;
	}

	public function parse(&$segments)
	{
		$vars = array();

		$ordering = array(
			$this->translate('marketplaces_ordering_start'),
			$this->translate('marketplaces_ordering_recent')
		);

		$total = count($segments);

		$menu = JFactory::getApplication()->getMenu();
		$xquery = $menu->getActive()->query;

		// we need to check further if this current active menu item is a cluster or not. e.g. group or page.
		if (($xquery['view'] == 'groups' || $xquery['view'] == 'pages') && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id']) && $xquery['id']) {
			$cluster = 'group';
			if ($xquery['view'] == 'pages') {
				$cluster = 'page';
			}

			$firstSegment = array_shift($segments);

			// now join back the remaining segments.
			array_unshift($segments, $firstSegment, $cluster, $xquery['id']);

			// recalculate the total segments
			$total = count($segments);
		}

		// If the total segments is 2 or more, this could means this event uses menu item if the menu item layout equal to 'item'
		// So, we'll need to append back the id in order to display it properly.
		if ($total >= 2 && ($segments[0] == $this->translate('marketplaces') || $segments[0] == 'marketplaces') && $xquery['view'] == 'marketplaces' && (isset($xquery['layout']) && $xquery['layout'] == 'item')) {
			$firstSegment = array_shift($segments);
			array_unshift($segments, $firstSegment, $xquery['id']);
			$total = count($segments);
		}

		if ($total >= 3 && ($segments[0] == $this->translate('marketplaces') || $segments[0] == 'marketplaces') && ($segments[2] == $this->translate('marketplaces') || $segments[2] == 'marketplaces')) {

			// we now, this is caused by groups menu items. lets re-arrange the segments
			$firstSegment = array_shift($segments);
			$secondSegment = array_shift($segments);
			array_shift($segments); // remove the 3rd elements, which is the 'groups'

			array_unshift($segments, $firstSegment, 'user', $secondSegment);

			// recalcute the total segments;
			$total = count($segments);
		}

		// users event
		if ($total == 3 && $segments[1] == 'user') {
			// this is viewing user's event.
			$vars['view'] = 'marketplaces';
			$vars['uid'] = $segments[2];

			return $vars;
		}

		// users event with filter
		if ($total >= 4 && $segments[1] == 'user') {
			// this is viewing user's event.
			$vars['view'] = 'marketplaces';
			$vars['userid'] = $segments[2];

			switch ($segments[3]) {
				case $this->translate('marketplaces_filter_created'):
					$vars['filter'] = 'created';
				break;
			}

			if (isset($segments[4]) && $segments[4] == 'created') {
				$vars['ordering'] = 'created';
			}

			return $vars;
		}

		// clusters
		$uTypes = array('group', 'page');
		if ($total >= 3 && in_array($segments[1], $uTypes)) {

			// this is viewing cluster's event.
			$vars['type'] = $segments[1];
			$vars['uid'] = $segments[2];

			// now, lets re-arrange the segments.

			array_shift($segments); // remove the 'marketplaces'
			array_shift($segments); // remove the 'type'
			array_shift($segments); // remove the 'uid'

			array_unshift($segments, 'marketplaces');

			// reset the segment count.
			$total = count($segments);
		}

		$vars['view'] = 'marketplaces';

		// translating ordering.
		// since we know ordering always at the last segment, we can
		// check for ordering segment using the latest index.

		if (in_array($segments[$total - 1], $ordering)) {
			$vars['ordering'] = $segments[$total - 1];

			// lets remove the last segment so that it wont affect the below processing.
			unset($segments[$total - 1]);
			$total = count($segments);
		}

		if ($total === 2) {
			switch ($segments[1]) {
				// site.com/menu/marketplaces/all
				case $this->translate('marketplaces_filter_all'):
					$vars['filter'] = 'all';
				break;

				// site.com/menu/marketplaces/featured
				case $this->translate('marketplaces_filter_featured'):
					$vars['filter'] = 'featured';
				break;

				// site.com/menu/marketplaces/mine
				case $this->translate('marketplaces_filter_mine'):
					$vars['filter'] = 'mine';
				break;

				// site.com/menu/marketplaces/create
				case $this->translate('marketplaces_layout_create'):
					$vars['layout'] = 'create';
				break;

				// site.com/menu/marketplaces/ID-title
				default:
					$listingId = (int) $this->getIdFromPermalink($segments[1]);

					if ($listingId) {
						$vars['layout'] = 'item';
						$vars['id'] = $listingId;
					} else {
						$vars['filter'] = $segments[1];
					}
				break;
			}
		}


		// site.com/menu/marketplaces/ID-event/edit
		if ($total == 3 && $segments[2] == $this->translate('marketplaces_layout_edit')) {
			$vars['layout'] = 'edit';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			return $vars;
		}


		if ($total === 3) {
			switch ($segments[1]) {

				// site.com/menu/marketplaces/category/ID-category
				case $this->translate('marketplaces_layout_category'):
					$vars['layout'] = 'category';
					$vars['id'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				// site.com/menu/marketplaces/steps/ID-event
				case $this->translate('marketplaces_layout_steps'):
					$vars['layout'] = 'steps';
					$vars['step'] = $segments[2];
					return $vars;
				break;

				// site.com/menu/marketplaces/featured/ID-category
				case $this->translate('marketplaces_filter_featured'):
					$vars['filter'] = 'featured';
					$vars['categoryid'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				// site.com/menu/marketplaces/mine/ID-category
				case $this->translate('marketplaces_filter_mine'):
					$vars['filter'] = 'mine';
					$vars['categoryid'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				// site.com/menu/marketplaces/all/ID-category
				case $this->translate('marketplaces_filter_all'):
					$vars['filter'] = 'all';
					$vars['categoryid'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				default:
					break;
			}
		}

		$typeException = array($this->translate('marketplaces_type_info'),
			$this->translate('marketplaces_type_filterform'));

		if (($total >= 3)) {

			if ($segments[1] === $this->translate('marketplaces_categories')) {
				$vars['categoryid'] = $segments[2];
				return $vars;
			}

			if ($segments[2] === $this->translate('marketplaces_type_info')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);

				if ($segments[2] == $this->translate('marketplaces_type_info')) {
					$vars['page'] = 'info';
				}

				if (isset($segments[3])) {
					$vars['step'] = $segments[3];
				}

				return $vars;
			}

			if ($segments[2] === $this->translate('marketplaces_type_filterform')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);
				$vars['page'] = 'filterForm';
				if (isset($segments[3])) {
					$vars['filterId'] = $segments[3];
				}
				return $vars;
			}
		}

		return $vars;
	}

	public function getUrl($query, $url)
	{
		static $cache = array();

		// Get a list of menus for the current view.
		$itemMenus = FRoute::getMenus($this->name, 'item');

		// For single group item
		// index.php?option=com_easysocial&view=marketplaces&layout=item&id=xxxx
		$items = array('item', 'info', 'edit');

		if (isset($query['layout']) && in_array($query['layout'], $items) && isset($query['id']) && !empty($itemMenus)) {

			foreach($itemMenus as $menu) {
				$id = (int) $menu->segments->id;
				$queryId = (int) $query['id'];

				if ($queryId == $id) {
					$url .= '&Itemid=' . $menu->id;
					return $url;
				}
			}
		}

		// For group categories
		$menus = FRoute::getMenus($this->name, 'category');
		$items = array('category');

		if (isset($query['layout']) && in_array($query['layout'], $items) && isset($query['id']) && !empty($itemMenus)) {

			foreach ( $menus as $menu) {
				$id = (int) $menu->segments->id;
				$queryId = (int) $query['id'];

				if ($queryId == $id) {
					if ($query['layout'] == 'category') {
						$url = 'index.php?Itemid=' . $menu->id;

						return $url;
					}

					$url .= '&Itemid=' . $menu->id;

					return $url;
				}

			}
		}

		return false;
	}
}
