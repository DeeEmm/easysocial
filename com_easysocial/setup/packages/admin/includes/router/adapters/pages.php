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

class SocialRouterPages extends SocialRouterAdapter
{
	/**
	 * Constructs the points urls
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function build(&$menu, &$query)
	{
		$segments = array();

		$ignoreLayouts = array('item', 'search');
		$frontLayouts = array('category');

		$addExtraView = false; // used for user clusters
		$categoryFilter = false;

		$addAlias = true; // determine if we need to add page alias or not.

		if (isset($query['Itemid']) && isset($query['id'])) {
			$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

			if ($xMenu) {
				$xquery = $xMenu->query;
				if ($xquery['view'] == 'pages' && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id'])) {
					$xId = (int) $xquery['id'];
					$cId = (int) $query['id'];
					if ($xId == $cId) {
						$addAlias = false;
					}
				}
			}
		}

		$userId = isset($query['userid']) ? $query['userid'] : null;
		if (!is_null($userId)) {
			$segments[] = ESR::normalizePermalink($query['userid']);
			unset($query['userid']);

			$addExtraView = true;

		}

		// If there is a menu but not pointing to the profile view, we need to set a view
		if($menu && $menu->query['view'] != 'pages' || $addExtraView) {
			$segments[]	= $this->translate($query['view']);

			$addExtraView = false;

		}

		// If there's no menu, use the view provided
		if (!$menu || $addExtraView) {
			$segments[]	= $this->translate($query['view']);
		}

		unset($query['view']);

		// Translate category urls
		$category = isset($query['categoryid']) ? $query['categoryid'] : null;

		if (!is_null($category)) {
			$segments[] = $this->translate('pages_categories');
			$segments[]	= ESR::normalizePermalink($query['categoryid']);

			// Add filter for category layout
			$categoryFilter = true;
			unset($query['categoryid']);
		}

		// Translate layout
		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout) && in_array($layout, $frontLayouts)) {
			$segments[]	= $this->translate('pages_layout_' . $layout);
		}

		// Translate id
		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id) && $addAlias) {
			$segments[]	= ESR::normalizePermalink($id);
		}
		unset($query['id']);

		// behind layout
		if (!is_null($layout) && !in_array($layout, $ignoreLayouts) && !in_array($layout, $frontLayouts)) {
			$segments[] = $this->translate('pages_layout_' . $layout);
		}

		unset($query['layout']);


		// Translate step
		$step = isset($query['step']) ? $query['step'] : null;

		if (!is_null($step)) {
			$segments[]	= $step;
			unset($query['step']);
		}


		// Translate app id
		$appId = isset($query['appId']) ? $query['appId'] : null;

		if (!is_null($appId)) {
			$segments[]	= ESR::normalizePermalink($appId);

			unset($query['appId']);
		}

		// If there is no type defined but there is a "app" defined and default display is NOT timeline, then we have to punch in timeline manually
		if (isset($query['app']) && !isset($query['type']) && ES::config()->get('events.item.display', 'timeline') !== 'timeline') {
			$segments[] = $this->translate('pages_type_timeline');
		}

		// If there is no type defined but there is a "filterId" defined and default display is NOT timeline, then we have to punch in timeline manually
		if (isset($query['filterId']) && !isset($query['type']) && ES::config()->get('events.item.display', 'timeline') !== 'timeline') {
			$segments[] = $this->translate('pages_type_timeline');
		}

		// Special handling for timeline and about
		if (isset($query['type'])) {
			$defaultDisplay = ES::config()->get('pages.item.display', 'timeline');

			// If type is info and there is a step provided, then info has to be added regardless of settings
			if ($query['type'] === 'info' && ($defaultDisplay !== $query['type'] || isset($query['infostep']))) {
				$segments[] = $this->translate('pages_type_info');

				if (isset($query['infostep'])) {
					$segments[] = $query['infostep'];
					unset($query['infostep']);
				}
			}

			// Depending settings, if default is set to timeline and type is timeline, we don't need to add this into the segments
			if ($query['type'] === 'timeline' && $defaultDisplay !== $query['type']) {
				$segments[] = $this->translate('pages_type_timeline');
			}

			if ($query['type'] === 'filterForm') {
				$segments[] = $this->translate('pages_type_filterform');

				if (isset($query['filterId'])) {
					$segments[] = $query['filterId'];
					unset($query['filterId']);
				}
			}

			unset($query['type']);
		}

		if (isset($query['tag'])) {
			$segments[] = $this->translate('pages_hashtag');
			$segments[] = $query['tag'];

			unset($query['tag']);
		}

		// Translate filter urls
		$filter = isset($query['filter']) ? $query['filter'] : null;
		$menuFilter = ($menu && $menu->query['view'] == 'pages' && isset($menu->query['filter'])) ? $menu->query['filter'] : null;
		$addFilter = false;

		if (is_null($menuFilter) && !is_null($filter)) {
		   $addFilter = true;
		}

		if (!is_null($filter) && $filter != $menuFilter) {
			$addFilter = true;
		}

		if ($addFilter || ($categoryFilter && $filter)) {
			$segments[]	= $this->translate('pages_filter_' . $query['filter']);
		}

		unset($query['filter']);

		if (isset($query['ordering'])) {
			$segments[] = $this->translate('pages_ordering_' . $query['ordering']);
			unset($query['ordering']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);
		$app = JFactory::getApplication();

		$filters = array(
			$this->translate('pages_filter_all'),
			$this->translate('pages_filter_recent'),
			$this->translate('pages_filter_featured'),
			$this->translate('pages_filter_mine'),
			$this->translate('pages_filter_invited'),
			$this->translate('pages_filter_liked'),
			$this->translate('pages_filter_created'),
			$this->translate('pages_filter_participated'),
			$this->translate('pages_filter_search')
		);

		$ordering = array(
			$this->translate('pages_ordering_latest'),
			$this->translate('pages_ordering_name'),
			$this->translate('pages_ordering_popular')
		);

		$typeException = array(
			$this->translate('pages_type_info'),
			$this->translate('pages_type_timeline'),
			$this->translate('pages_type_filterform')
		);

		// var_dump('pages', $segments);
		// exit;

		// apps / videos / albums / photos links.
		if ($total >= 3) {
			// lets do some testing here before we proceed further.

			// apps
			if (($segments[0] == $this->translate('pages') || $segments[0] == 'pages')
				&& $segments[2] == $this->translate('apps')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_PAGE);

				require_once(SOCIAL_LIB . '/router/adapters/apps.php');
				$appsRouter = new SocialRouterApps('apps');

				array_shift($segments); // remove the 'pages'
				array_shift($segments); // remove the 'id-page'
				array_shift($segments); // remove the 'apps'

				array_unshift($segments, 'apps', 'page', $uid);

				$vars = $appsRouter->parse($segments);
				return $vars;
			}

			// events
			if (($segments[0] == $this->translate('pages') || $segments[0] == 'pages')
				&& $segments[2] == $this->translate('events')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_PAGE);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'pages'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'events'

				// now add back the required segments.
				array_unshift($segments, 'events', 'page', $uid);


				// Parse the segments
				$router = ES::router('events');
				$vars = $router->parse($segments);

				return $vars;
			}

			// videos
			if (($segments[0] == $this->translate('pages') || $segments[0] == 'pages')
				&& $segments[2] == $this->translate('videos')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_PAGE);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'pages'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'videos'

				// now add back the required segments.
				array_unshift($segments, 'videos', 'page', $uid);


				// Parse the segments
				$router = ES::router('videos');
				$vars = $router->parse($segments);

				return $vars;
			}

			// Audio
			if (($segments[0] == $this->translate('pages') || $segments[0] == 'pages')
				&& $segments[2] == $this->translate('audios')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_PAGE);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'pages'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'audios'

				// now add back the required segments.
				array_unshift($segments, 'audios', 'page', $uid);


				// Parse the segments
				$router = ES::router('audios');
				$vars = $router->parse($segments);

				return $vars;
			}


			//albums
			if (($segments[0] == $this->translate('pages') || $segments[0] == 'pages')
				&& $segments[2] == $this->translate('albums')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_PAGE);

				array_shift($segments); // remove the 'pages'
				array_shift($segments); // remove the 'uid'

				// check if last segments is form or not. if yes we will need to re-arrange the segment
				$lastSegment = $segments[count($segments) - 1];
				if ($lastSegment == $this->translate('albums_layout_form') || $lastSegment == 'form') {
					array_pop($segments); // remove the 'form'
				}

				$segments[] = 'page';
				$segments[] = $uid;

				if ($lastSegment == $this->translate('albums_layout_form') || $lastSegment == 'form') {
					$segments[] = $lastSegment;
				}

				$albumRouter = ES::router('albums');
				$vars = $albumRouter->parse($segments);
				return $vars;
			}

			//photos
			if (($segments[0] == $this->translate('pages') || $segments[0] == 'pages')
				&& $segments[2] == $this->translate('photos')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_PAGE);

				require_once(SOCIAL_LIB . '/router/adapters/photos.php');
				$photoRouter = new SocialRouterPhotos('photos');

				array_shift($segments); // remove the 'pages'
				array_shift($segments); // remove the 'uid'

				// check if last segments is form or not. if yes we will need to re-arrange the segment
				$lastSegment = $segments[count($segments) - 1];
				if ($lastSegment == $this->translate('photos_layout_form') || $lastSegment == 'form') {
					array_pop($segments); // remove the 'form'
				}

				$segments[] = 'page';
				$segments[] = $uid;

				if ($lastSegment == $this->translate('photos_layout_form') || $lastSegment == 'form') {
					$segments[] = $lastSegment;
				}

				$vars = $photoRouter->parse($segments);
				return $vars;
			}

		}

		if ($total >= 3 && ($segments[0] == $this->translate('pages') || $segments[0] == 'pages') && ($segments[2] == $this->translate('pages') || $segments[2] == 'pages')) {

			// we now, this is caused by pages menu items. lets re-arrange the segments
			$firstSegment = array_shift($segments);
			$secondSegment = array_shift($segments);
			array_shift($segments); // remove the 3rd elements, which is the 'pages'

			array_unshift($segments, $firstSegment, 'user', $secondSegment);

			// recalcute the total segments;
			$total = count($segments);
		}

		// Default pages view
		$vars['view'] = 'pages';

		// URL: http://site.com/menus/pages
		if ($total == 1) {
			return $vars;
		}

		// page search: http://site.com/menus/pages/filter/name
		if ($total >= 2 && in_array($segments[1], $filters) && ($segments[1] == $this->translate('pages_filter_search') || $segments[1] == 'filter')) {

			$vars['layout'] = 'search';

			if (isset($segments[2]) && $segments[2]) {
				$vars['ordering'] = $this->getOrdering($segments[2]);
			}

			return $vars;
		}


		if ($total == 2 && in_array($segments[1], $filters)) {

			$filter = $this->getFilter($segments[1]);

			if ($filter == 'search') {
				$vars['layout'] = 'search';
				return $vars;
			}

			$vars['filter'] = $this->getFilter($segments[1]);
			return $vars;
		}

		// To fix ordering issue if there are active menu item for this page. #651
		if ($total == 2 && in_array($segments[1], $ordering)) {

			$menu = $app->getMenu();
			$active = $menu->getActive();

			if ($active->query['view'] == 'pages') {
				$filter = $active->query['filter'];
			} else {
				$filter = 'all';
			}

			// Set filter based on menu item
			$vars['filter'] = $filter;
			$vars['ordering'] = $this->getOrdering($segments[1]);
			return $vars;
		}


		// http://site.com/menu/pages/create
		if ($total == 2 && $segments[1] == $this->translate('pages_layout_create')) {
			$vars['layout']	= 'create';
			return $vars;
		}

		// http://site.com/menu/pages/
		// http://site.com/menu/pages/ID-page-alias
		if ($total == 2) {
			$id = (int) $this->getIdFromPermalink($segments[1]);
			$vars['layout'] = 'item';
			$vars['id'] = $id;

			return $vars;
		}

	   // http://site.com/menu/pages/all/latest
		if ($total == 3 && in_array($segments[1], $filters)) {
			$vars['filter'] = $this->getFilter($segments[1]);
			$vars['ordering'] = $this->getOrdering($segments[2]);

			return $vars;
		}

		if ($total == 4 && $segments[1] == 'user' && in_array($segments[3], $filters)) {
			$vars['filter'] = $this->getFilter($segments[3]);
			$vars['userid'] = $segments[2];

			return $vars;
		}


		// http://site.com/menu/pages/category/ID-category
		if ($total == 3 && $segments[1] == $this->translate('pages_layout_category')) {
			$vars['layout']	= 'category';
			$vars['id'] = $this->getIdFromPermalink($segments[2]);
			return $vars;
		}

		// // http://site.com/menu/pages/info/ID-category
		// if ($total == 3 && $segments[1] == $this->translate('pages_layout_info')) {
		// 	$vars['layout']	= 'info';
		// 	$vars['id']		= $this->getIdFromPermalink($segments[2]);
		// 	return $vars;
		// }

		// // http://site.com/menu/pages/item/ID-alias
		// if ($total == 3 && $segments[1] == $this->translate('pages_layout_item')) {
		// 	$vars['layout']	= 'item';
		// 	$vars['id'] = $this->getIdFromPermalink($segments[2]);

		// 	return $vars;
		// }

		// http://site.com/menu/pages/ID-alias/edit
		if ($total == 3 && $segments[2] == $this->translate('pages_layout_edit')) {
			$vars['layout']	= 'edit';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			return $vars;
		}

		// http://site.com/menu/pages/steps/xx
		if ($total == 3 && $segments[1] == $this->translate('pages_layout_steps')) {
			$vars['layout']	= 'steps';
			$vars['step'] = $segments[2];
			return $vars;
		}

		// http://site.com/menu/pages/user/ID-alias
		if ($total == 3 && $segments[1] == $this->translate('pages_user') || $segments[1] == 'user') {
			$vars['userid'] = $segments[2];
			return $vars;
		}

		// Specifically check for both info and timeline. If 4th segment is not info nor timeline, then we assume it is app
		if ($total == 3 && $segments[1] != $this->translate('pages_categories') && !in_array($segments[2], $typeException)) {
			$vars['layout'] = 'item';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);
			$appId = $this->getIdFromPermalink($segments[2]);

			// $vars['type'] = $appId;
			$vars[(int) $appId ? 'appId' : 'app'] = $appId;
		}

		if ($total >= 3) {

			// http://site.com/menu/pages/categories/ID-category
			if ($segments[1] == $this->translate('pages_categories')) {
				$catId = $this->getIdFromPermalink($segments[2]);
				$vars['categoryid'] = $catId;
			}

			// http://site.com/menu/pages/categories/ID-category/all
			if (isset($segments[3]) && in_array($segments[3], $filters)) {
				$vars['filter'] = $this->getFilter($segments[3]);
			}

			// http://site.com/menu/pages/categories/ID-category/all/latest
			if (isset($segments[4]) && in_array($segments[4], $ordering)) {
				$vars['ordering'] = $this->getOrdering($segments[4]);
			}

			// http://site.com/menu/groups/ID-alias/info
			// http://site.com/menu/groups/ID-alias/info/step
			if ($segments[2] == $this->translate('pages_type_info') || $segments[2] == $this->translate('pages_type_timeline')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);

				if ($segments[2] == $this->translate('pages_type_info')) {
					$vars['type'] = 'info';
				}

				if ($segments[2] == $this->translate('pages_type_timeline')) {
					$vars['type'] = 'timeline';
				}

				if (isset($segments[3]) && $segments[3]) {
					$vars['step'] = $segments[3];
				}

				return $vars;
			}

			// http://site.com/menu/pages/ID-alias/filterForm
			// http://site.com/menu/pages/ID-alias/filterForm/ID-filter
			if ($segments[2] == $this->translate('pages_type_filterform')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);
				$vars['type'] = 'filterForm';

				if (isset($segments[3]) && $segments[3]) {
					$vars['filterId'] = $segments[3];
				}

				return $vars;
			}

			// http://site.com/menu/pages/ID-alias/ID-app/filter
			if ($total == 4 && $segments[1] != $this->translate('pages_categories')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_PAGE);

				if ($segments[2] == $this->translate('pages_custom_filter')) {
					$vars['filterId'] = $segments[3];
				} else if ($segments[2] == $this->translate('pages_hashtag')) {
					$vars['tag'] = $segments[3];
				} else {
					$vars['filter'] = $segments[3];

					$appId = $this->getIdFromPermalink($segments[2]);
					$vars[(int) $appId ? 'appId' : 'app'] = $appId;
				}
			}
		}

		return $vars;
	}

	/**
	 * Retrieves the correct url that the current request should use.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getUrl($query, $url)
	{
		static $cache	= array();

		// Get a list of menus for the current view.
		$itemMenus	= ESR::getMenus($this->name, 'item');

		// For single page item
		// index.php?option=com_easysocial&view=pages&layout=item&id=xxxx
		$items 	= array('item', 'info', 'edit');

		if (isset($query['layout']) && in_array($query['layout'], $items) && isset($query['id']) && !empty($itemMenus)) {

			foreach($itemMenus as $menu) {
				$id 		= (int) $menu->segments->id;
				$queryId	= (int) $query['id'];

				if ($queryId == $id) {

					// The query cannot contain appId
					if ($query['layout'] == 'item' && !isset($query['appId'])) {
						$url 	= 'index.php?Itemid=' . $menu->id;
						return $url;
					}


					$url 	.= '&Itemid=' . $menu->id;
					return $url;
				}
			}
		}

		// For page categories
		$menus 	= ESR::getMenus($this->name, 'category');
		$items 	= array('category');

		if (isset($query['layout']) && in_array($query['layout'], $items) && isset($query['id']) && !empty($itemMenus)) {

			foreach ($menus as $menu) {
				$id 		= (int) $menu->segments->id;
				$queryId	= (int) $query['id'];

				if ($queryId == $id) {
					if ($query['layout'] == 'category') {
						$url 	= 'index.php?Itemid=' . $menu->id;

						return $url;
					}

					$url 	.= '&Itemid=' . $menu->id;

					return $url;
				}

			}
		}

		return false;
	}

	/**
	 * Retrieve the filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getFilter($translated)
	{
		if ($translated == $this->translate('pages_filter_featured')) {
			return 'featured';
		}

		if ($translated == $this->translate('pages_filter_recent')) {
			return 'recent';
		}

		if ($translated == $this->translate('pages_filter_mine')) {
			return 'mine';
		}

		if ($translated == $this->translate('pages_filter_invited')) {
			return 'invited';
		}

		if ($translated == $this->translate('pages_filter_liked')) {
			return 'liked';
		}

		if ($translated == $this->translate('pages_filter_participated')) {
			return 'participated';
		}

		if ($translated == $this->translate('pages_filter_created')) {
			return 'created';
		}

		if ($translated == $this->translate('pages_filter_search')) {
			return 'search';
		}

		// Default to return all
		return 'all';
	}


	/**
	 * Retrieve the ordering
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getOrdering($translated)
	{
		if ($translated == $this->translate('pages_ordering_latest')) {
			return 'latest';
		}

		if ($translated == $this->translate('pages_ordering_name')) {
			return 'name';
		}

		if ($translated == $this->translate('pages_ordering_popular')) {
			return 'popular';
		}

		// Default to return latest
		return 'latest';
	}

}
