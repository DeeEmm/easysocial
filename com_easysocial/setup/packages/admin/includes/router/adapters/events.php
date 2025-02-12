<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterEvents extends SocialRouterAdapter
{
	public function build(&$menu, &$query)
	{
		$segments = array();

		$ignoreLayouts = array('item');

		$frontLayouts = array('category');

		$addExtraView = false; // used for user clusters

		// Linkage to clusters
		if (isset($query['uid']) && isset($query['type']) && ($query['type'] == 'group' || $query['type'] == 'page')) {

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

		$userId = isset($query['userid']) ? $query['userid'] : null;
		if (!is_null($userId)) {
			$segments[] = ESR::normalizePermalink($query['userid']);
			unset($query['userid']);
			$addExtraView = true;
		}

		if ($menu && $menu->query['view'] !== 'events' || $addExtraView) {
			$segments[] = $this->translate($query['view']);
			$addExtraView = false;
		}

		if (!$menu || $addExtraView) {
			$segments[] = $this->translate($query['view']);
		}
		unset($query['view']);

		if (isset($query['filter'])) {

			// If filter is all, then we do not want this segment
			if ($query['filter'] !== 'all' && !isset($query['appId'])) {
				$segments[] = $this->translate('events_filter_' . $query['filter']);

				if (isset($query['date'])) {
					$segments[] = $query['date'];
					unset($query['date']);
				}

				if (isset($query['distance'])) {
					$segments[] = $query['distance'];
					unset($query['distance']);
				}
			}

			if (isset($query['appId']) && $query['appId']) {
				$segments[] = $query['filter'];
			}

			unset($query['filter']);
		}

		if (isset($query['categoryid']) && $query['categoryid']) {
			$segments[] = $this->translate('events_categories');
			$segments[] = ESR::normalizePermalink($query['categoryid']);
			unset($query['categoryid']);
		}

		$layout = isset($query['layout']) ? $query['layout'] : null;

		// front layouts
		if (!is_null($layout) && in_array($layout, $frontLayouts)) {
			$segments[] = $this->translate('events_layout_' . $layout);
		}

		// event id.
		if (isset($query['id'])) {

			$addExtraSegments = true;

			if (isset($query['Itemid'])) {

				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

				if ($xMenu) {
					$xquery = $xMenu->query;

					$xView = 'events';
					$allowedType = array('info', 'timeline');

					if ($xquery['view'] == $xView && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id']) && in_array(isset($query['page']), $allowedType)) {
						$xV = (int) $xquery['view'];
						$tV = (int) $query['page'];

						if ($xV == $tV) {
							$addExtraSegments = false;
						}
					}

					// If the event menu is provided, then we remove the event id from the url.
					if (isset($query['appId']) && !isset($query['page']) && $xquery['view'] == $xView && isset($xquery['layout']) && $xquery['layout'] == 'item') {
						$addExtraSegments = false;
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
			$segments[] = $this->translate('events_layout_' . $layout);
		}

		unset($query['layout']);

		if (isset($query['step'])) {
			$segments[] = $query['step'];
			unset($query['step']);
		}


		if (isset($query['appId'])) {
			$segments[] = ESR::normalizePermalink($query['appId']);

			// Check additional app filter. #2627
			if (isset($query['appFilter']) && $query['appFilter']) {
				$segments[] = $query['appFilter'];
				unset($query['appFilter']);
			}

			unset($query['appId']);
		}

		if (isset($query['tag'])) {
			$segments[] = $this->translate('events_hashtag');
			$segments[] = $query['tag'];

			unset($query['tag']);
		}

		// If there is no type defined but there is a "app" defined and default display is NOT timeline, then we have to punch in timeline manually
		if (isset($query['app']) && !isset($query['page']) && ES::config()->get('events.item.display', 'timeline') !== 'timeline') {
			$segments[] = $this->translate('events_type_timeline');

		}

		// If there is no type defined but there is a "filterId" defined and default display is NOT timeline, then we have to punch in timeline manually
		if (isset($query['filterId']) && !isset($query['page']) && ES::config()->get('events.item.display', 'timeline') !== 'timeline') {
			$segments[] = $this->translate('events_type_timeline');
		}

		// Special handling for timeline and about

		if (isset($query['page'])) {
			$defaultDisplay = $this->config->get('events.item.display', 'timeline');

			// If type is info and there is a step provided, then info has to be added regardless of settings
			if ($query['page'] === 'info' && ($defaultDisplay !== $query['page'] || isset($query['infostep']))) {
				$segments[] = $this->translate('events_type_info');

				if (isset($query['infostep'])) {
					$segments[] = $query['infostep'];
					unset($query['infostep']);
				}
			}

			// Depending settings, if default is set to timeline and type is timeline, we don't need to add this into the segments
			if ($query['page'] === 'timeline' && $defaultDisplay !== $query['page']) {
				$segments[] = $this->translate('events_type_timeline');
			}

			if ($query['page'] === 'filterForm') {
				$segments[] = $this->translate('events_type_filterform');

				if (isset($query['filterId'])) {
					$segments[] = $query['filterId'];
					unset($query['filterId']);
				}
			}

			unset($query['page']);
		}

		// // Translate custom filters
		// if (isset($query['filterId'])) {
		// 	$segments[] = $this->translate('events_custom_filter');
		// 	$segments[] = $query['filterId'];

		// 	unset($query['filterId']);
		// }

		//
		if (isset($query['ordering'])) {
			$segments[] = $this->translate('events_ordering_' . $query['ordering']);
			unset($query['ordering']);
		}

		return $segments;
	}

	public function parse(&$segments)
	{
		$vars = array();

		$ordering = array(
			$this->translate('events_ordering_start'),
			$this->translate('events_ordering_recent')
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
		if ($total >= 2 && ($segments[0] == $this->translate('events') || $segments[0] == 'events') && $xquery['view'] == 'events' && (isset($xquery['layout']) && $xquery['layout'] == 'item')) {
			$firstSegment = array_shift($segments);
			array_unshift($segments, $firstSegment, $xquery['id']);
			$total = count($segments);
		}

		if ($total >= 3 && ($segments[0] == $this->translate('events') || $segments[0] == 'events') && ($segments[2] == $this->translate('events') || $segments[2] == 'events')) {

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
			$vars['view'] = 'events';
			$vars['userid'] = $segments[2];

			return $vars;
		}

		// users event with filter
		if ($total >= 4 && $segments[1] == 'user') {
			// this is viewing user's event.
			$vars['view'] = 'events';
			$vars['userid'] = $segments[2];

			switch ($segments[3]) {
				case $this->translate('events_filter_created'):
					$vars['filter'] = 'created';
				break;

				case $this->translate('events_filter_participated'):
					$vars['filter'] = 'participated';
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

			array_shift($segments); // remove the 'events'
			array_shift($segments); // remove the 'type'
			array_shift($segments); // remove the 'uid'

			array_unshift($segments, 'events');

			// reset the segment count.
			$total = count($segments);
		}

		// var_dump('events', $segments);

		// videos / albums / photos links.
		if ($total >= 3) {
			// lets do some testing here before we proceed further.
			// apps

			if (($segments[0] == $this->translate('events') || $segments[0] == 'events')
				&& $segments[2] == $this->translate('apps')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_EVENT);

				require_once(SOCIAL_LIB . '/router/adapters/apps.php');
				$appsRouter = new SocialRouterApps('apps');

				array_shift($segments); // remove the 'event'
				array_shift($segments); // remove the 'id-group'
				array_shift($segments); // remove the 'apps'

				array_unshift($segments, 'apps', 'event', $uid);

				$vars = $appsRouter->parse($segments);
				return $vars;
			}

			// videos
			if (($segments[0] == $this->translate('events') || $segments[0] == 'events')
				&& $segments[2] == $this->translate('videos')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_EVENT);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'event'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'videos'

				// now add back the required segments.
				array_unshift($segments, 'videos', 'event', $uid);


				// Parse the segments
				$router = ES::router('videos');
				$vars = $router->parse($segments);

				return $vars;
			}

			if (($segments[0] == $this->translate('events') || $segments[0] == 'events')
				&& $segments[2] == $this->translate('audios')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_EVENT);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'event'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'audios'

				// now add back the required segments.
				array_unshift($segments, 'audios', 'event', $uid);

				// Parse the segments
				$router = ES::router('audios');
				$vars = $router->parse($segments);

				return $vars;
			}

			//albums
			if (($segments[0] == $this->translate('events') || $segments[0] == 'events')
				&& $segments[2] == $this->translate('albums')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_EVENT);

				array_shift($segments); // remove the 'event'
				array_shift($segments); // remove the 'uid'

				// check if last segments is form or not. if yes we will need to re-arrange the segment
				$lastSegment = $segments[count($segments) - 1];
				if ($lastSegment == $this->translate('albums_layout_form') || $lastSegment == 'form') {
					array_pop($segments); // remove the 'form'
				}

				$segments[] = 'event';
				$segments[] = $uid;

				if ($lastSegment == $this->translate('albums_layout_form') || $lastSegment == 'form') {
					$segments[] = $lastSegment;
				}

				$albumRouter = ES::router('albums');
				$vars = $albumRouter->parse($segments);
				return $vars;
			}

			// //photos
			if (($segments[0] == $this->translate('events') || $segments[0] == 'events')
				&& $segments[2] == $this->translate('photos')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_EVENT);

				require_once(SOCIAL_LIB . '/router/adapters/photos.php');
				$photoRouter = new SocialRouterPhotos('photos');

				array_shift($segments); // remove the 'event'
				array_shift($segments); // remove the 'uid'

				// check if last segments is form or not. if yes we will need to re-arrange the segment
				$lastSegment = $segments[count($segments) - 1];
				if ($lastSegment == $this->translate('photos_layout_form') || $lastSegment == 'form') {
					array_pop($segments); // remove the 'form'
				}

				$segments[] = 'event';
				$segments[] = $uid;

				if ($lastSegment == $this->translate('photos_layout_form') || $lastSegment == 'form') {
					$segments[] = $lastSegment;
				}

				$vars = $photoRouter->parse($segments);
				return $vars;
			}

		}

		$vars['view'] = 'events';

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
				// site.com/menu/events/all
				case $this->translate('events_filter_all'):
					$vars['filter'] = 'all';
				break;

				// site.com/menu/events/nearby
				case $this->translate('events_filter_nearby'):
					$vars['filter'] = 'nearby';
				break;

				// site.com/menu/events/featured
				case $this->translate('events_filter_featured'):
					$vars['filter'] = 'featured';
				break;

				// site.com/menu/events/mine
				case $this->translate('events_filter_mine'):
					$vars['filter'] = 'mine';
				break;

				// site.com/menu/events/invited
				case $this->translate('events_filter_invited'):
					$vars['filter'] = 'invited';
				break;

				// site.com/menu/events/create
				case $this->translate('events_layout_create'):
					$vars['layout'] = 'create';
				break;

				// site.com/menu/events/calendar/
				case $this->translate('events_layout_calendar');
					$vars['layout'] = 'calendar';
				break;

				// site.com/menu/events/week1
				case $this->translate('events_filter_week1'):
					$vars['filter'] = 'week1';
				break;

				// site.com/menu/events/week2
				case $this->translate('events_filter_week2'):
					$vars['filter'] = 'week2';
				break;

				// site.com/menu/events/past
				case $this->translate('events_filter_past'):
					$vars['filter'] = 'past';
				break;

				// site.com/menu/events/date/
				case $this->translate('events_filter_date'):
					$vars['filter'] = 'date';
				break;

				// site.com/menu/events/today/
				case $this->translate('events_filter_today');
					$vars['filter'] = 'date';
				break;

				// site.com/menu/events/nearby/
				case $this->translate('events_filter_nearby');
					$vars['filter'] = 'nearby';
				break;

				// site.com/menu/events/ID-title
				default:
					$eventId = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_EVENT);

					if ($eventId) {
						$vars['layout'] = 'item';
						$vars['id'] = $eventId;
					} else {
						$vars['filter'] = $segments[1];
					}
				break;
			}
		}


		// site.com/menu/events/ID-event/edit
		if ($total == 3 && $segments[2] == $this->translate('events_layout_edit')) {
			$vars['layout'] = 'edit';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			return $vars;
		}


		if ($total === 3) {
			switch ($segments[1]) {
				// site.com/menu/events/date/[date]
				case $this->translate('events_filter_date'):
					$vars['filter'] = 'date';
					$vars['date'] = $segments[2];
					return $vars;
				break;

				// site.com/menu/events/nearby/[distance]
				case $this->translate('events_filter_nearby');
					$vars['filter'] = 'nearby';
					$vars['distance'] = $segments[2];
					return $vars;
				break;

				// site.com/menu/events/category/ID-category
				case $this->translate('events_layout_category'):
					$vars['layout'] = 'category';
					$vars['id'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				// site.com/menu/events/export/ID-event
				case $this->translate('events_layout_export'):
					$vars['layout'] = 'export';
					$vars['id'] = (int) $segments[2];
					return $vars;
				break;

				// site.com/menu/events/steps/ID-event
				case $this->translate('events_layout_steps'):
					$vars['layout'] = 'steps';
					$vars['step'] = $segments[2];
					return $vars;
				break;

				// site.com/menu/events/featured/ID-category
				case $this->translate('events_filter_featured'):
					$vars['filter'] = 'featured';
					$vars['categoryid'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				// site.com/menu/events/mine/ID-category
				case $this->translate('events_filter_mine'):
					$vars['filter'] = 'mine';
					$vars['categoryid'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				// site.com/menu/events/recent/ID-category
				case $this->translate('events_filter_invited'):
					$vars['filter'] = 'invited';
					$vars['categoryid'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				// site.com/menu/events/all/ID-category
				case $this->translate('events_filter_all'):
					$vars['filter'] = 'all';
					$vars['categoryid'] = $this->getIdFromPermalink($segments[2]);
					return $vars;
				break;

				default:
					break;
			}
		}

		$typeException = array($this->translate('events_type_info'),
			$this->translate('events_type_timeline'),
			$this->translate('events_type_filterform'),
			$this->translate('events_custom_filter'),
			$this->translate('events_hashtag'));

		// Specifically check for both info and timeline. If 4th segment is not info nor timeline, then we assume it is app
		if (($total >= 3) && !in_array($segments[2], $typeException) && $segments[1] != $this->translate('events_categories')) {
			$vars['layout'] = 'item';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);
			$appId = $this->getIdFromPermalink($segments[2]);

			// There might some sort of additional filter going on here
			if ($total === 4) {
				if ($segments[3]) {
					$vars['appFilter'] = $segments[3];
				}
			}

			$vars[(int) $appId ? 'appId' : 'app'] = $appId;

			return $vars;
		}

		if (($total >= 3)) {

			if ($segments[1] === $this->translate('events_categories')) {
				$vars['categoryid'] = $segments[2];
				return $vars;
			}

			if ($segments[1] === $this->translate('events_filter_nearby')) {
				$vars['filter'] = 'nearby';

				if (isset($segments[3]) && $segments[3] == $this->translate('events_ordering_distance')) {
					$vars['distance'] = $segments[2];
				}

				return $vars;
			}

			if ($segments[2] === $this->translate('events_type_info') || $segments[2] == $this->translate('events_type_timeline')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);

				if ($segments[2] == $this->translate('events_type_info')) {
					$vars['page'] = 'info';
				}

				if ($segments[2] == $this->translate('events_type_timeline')) {
					$vars['page'] = 'timeline';
				}

				if (isset($segments[3])) {
					$vars['step'] = $segments[3];
				}

				return $vars;
			}

			if ($segments[2] === $this->translate('events_type_timeline')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);
				$vars['page'] = 'timeline';
				return $vars;
			}

			if ($segments[2] === $this->translate('events_type_filterform')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);
				$vars['page'] = 'filterForm';
				if (isset($segments[3])) {
					$vars['filterId'] = $segments[3];
				}
				return $vars;
			}

			if ($segments[2] === $this->translate('events_custom_filter')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);
				$vars['filterId'] = $this->getIdFromPermalink($segments[3]);
				return $vars;
			}

			if ($segments[2] === $this->translate('events_hashtag')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_EVENT);
				$vars['tag'] = $segments[3];
				return $vars;
			}

			// Event Atendees filter pagination
			if ($total == 4) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[2]);
				$appId = $this->getIdFromPermalink($segments[3]);

				$vars['appId'] = $appId;
				$vars['page'] = $segments[1];

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
		// index.php?option=com_easysocial&view=events&layout=item&id=xxxx
		$items = array('item', 'info', 'edit');

		if (isset($query['layout']) && in_array($query['layout'], $items) && isset($query['id']) && !empty($itemMenus)) {

			foreach($itemMenus as $menu) {
				$id = (int) $menu->segments->id;
				$queryId = (int) $query['id'];

				if ($queryId == $id) {

					// The query cannot contain appId
					if ($query['layout'] == 'item' && !isset($query['appId'])) {
						$url = 'index.php?Itemid=' . $menu->id;
						return $url;
					}


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
