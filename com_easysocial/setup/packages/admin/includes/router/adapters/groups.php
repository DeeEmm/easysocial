<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterGroups extends SocialRouterAdapter
{
	/**
	 * Constructs the points urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu, &$query)
	{
		$segments = array();

		$ignoreLayouts = array('item');
		$frontLayouts = array('category');

		$addExtraView = false; // used for user clusters

		$addAlias = true; // determine if we need to add group alias or not.

		if (isset($query['Itemid']) && isset($query['id'])) {
			$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

			if ($xMenu) {
				$xquery = $xMenu->query;
				if ($xquery['view'] == 'groups' && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id'])) {
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
		if ($menu && $menu->query['view'] != 'groups' || $addExtraView) {
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

		if (!is_null($category) && $category) {
			$segments[] = $this->translate('groups_categories');
			$segments[] = ESR::normalizePermalink($query['categoryid']);
		}
		unset($query['categoryid']);


		$layout = isset($query['layout']) ? $query['layout'] : null;

		// front layouts
		if (!is_null($layout) && in_array($layout, $frontLayouts)) {
			$segments[] = $this->translate('groups_layout_' . $layout);
		}

		// Translate id
		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id) && $addAlias) {
			$segments[] = ESR::normalizePermalink($id);
		}
		unset($query['id']);

		// behind layout
		if (!is_null($layout) && !in_array($layout, $ignoreLayouts) && !in_array($layout, $frontLayouts)) {
			$segments[] = $this->translate('groups_layout_' . $layout);
		}

		unset($query['layout']);

		//translate step
		$step = isset($query['step']) ? $query['step'] : null;

		if (!is_null($step)) {
			$segments[] = $step;
			unset($query['step']);
		}

		// Translate app id
		$appId = isset($query['appId']) ? $query['appId'] : null;

		if (!is_null($appId)) {
			$segments[] = ESR::normalizePermalink($appId);
			unset($query['appId']);
		}

		// If there is no type defined but there is a "app" defined and default display is NOT timeline, then we have to punch in timeline manually
		if (isset($query['app']) && !isset($query['type']) && ES::config()->get('events.item.display', 'timeline') !== 'timeline') {
			$segments[] = $this->translate('groups_type_timeline');
		}

		// If there is no type defined but there is a "filterId" defined and default display is NOT timeline, then we have to punch in timeline manually
		if (isset($query['filterId']) && !isset($query['type']) && ES::config()->get('events.item.display', 'timeline') !== 'timeline') {
			$segments[] = $this->translate('groups_type_timeline');
		}

		// Special handling for timeline and about
		if(isset($query['type'])) {
			$defaultDisplay = ES::config()->get('groups.item.display', 'timeline');

			// If type is info and there is a step provided, then info has to be added regardless of settings
			if ($query['type'] === 'info' && ($defaultDisplay !== $query['type'] || isset($query['infostep']))) {
				$segments[] = $this->translate('groups_type_info');

				if (isset($query['infostep'])) {
					$segments[] = $query['infostep'];
					unset($query['infostep']);
				}
			}

			// Depending settings, if default is set to timeline and type is timeline, we don't need to add this into the segments
			if ($query['type'] === 'timeline' && $defaultDisplay !== $query['type']) {
				$segments[] = $this->translate('groups_type_timeline');
			}

			if ($query['type'] === 'filterForm') {
				$segments[] = $this->translate('groups_type_filterform');

				if (isset($query['filterId'])) {
					$segments[] = $query['filterId'];
					unset($query['filterId']);
				}
			}

			unset($query['type']);
		}

		if (isset($query['tag'])) {
			$segments[] = $this->translate('groups_hashtag');
			$segments[] = $query['tag'];

			unset($query['tag']);
		}

		// Translate filter urls
		$filter = isset($query['filter']) ? $query['filter'] : null;
		$menuFilter = ($menu && $menu->query['view'] == 'groups' && isset($menu->query['filter'])) ? $menu->query['filter'] : null;
		$addFilter = false;

		if (is_null($menuFilter) && !is_null($filter)) {
			$addFilter = true;
		}

		if (!is_null($filter) && $filter != $menuFilter) {
			$addFilter = true;
		}

		if ($addFilter) {
			$segments[] = $this->translate('groups_filter_' . $query['filter']);
		}

		unset($query['filter']);


		if (isset($query['ordering'])) {
			$segments[] = $this->translate('groups_ordering_' . $query['ordering']);
			unset($query['ordering']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		$filters = array(
			$this->translate('groups_filter_all'),
			$this->translate('groups_filter_recent'),
			$this->translate('groups_filter_featured'),
			$this->translate('groups_filter_mine'),
			$this->translate('groups_filter_invited'),
			$this->translate('groups_filter_pending'),
			$this->translate('groups_filter_nearby'),
			$this->translate('groups_filter_participated'),
			$this->translate('groups_filter_created')
		);

		$ordering = array(
			$this->translate('groups_ordering_latest'),
			$this->translate('groups_ordering_name'),
			$this->translate('groups_ordering_popular')
		);

		$typeException = array(
			$this->translate('groups_type_info'),
			$this->translate('groups_type_timeline'),
			$this->translate('groups_type_filterform')
		);

		// we need to check here if these segments actually belong to apps / videos / albums / photos links
		if ($total >= 3) {
			// lets do some testing here before we proceed further.

			// apps
			if (($segments[0] == $this->translate('groups') || $segments[0] == 'groups')
				&& $segments[2] == $this->translate('apps')) {

				$uid = $segments[1];

				require_once(SOCIAL_LIB . '/router/adapters/apps.php');
				$appsRouter = new SocialRouterApps('apps');

				array_shift($segments); // remove the 'groups'
				array_shift($segments); // remove the 'id-group'
				array_shift($segments); // remove the 'apps'

				array_unshift($segments, 'apps', 'group', $uid);

				$vars = $appsRouter->parse($segments);
				return $vars;
			}

			// events
			if (($segments[0] == $this->translate('groups') || $segments[0] == 'groups')
				&& ($segments[2] == $this->translate('events') || $segments[2] == 'events')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_GROUP);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'groups'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'events'

				// now add back the required segments.
				array_unshift($segments, 'events', 'group', $uid);

				// Parse the segments
				$router = ES::router('events');
				$vars = $router->parse($segments);

				return $vars;
			}


			// videos
			if (($segments[0] == $this->translate('groups') || $segments[0] == 'groups')
				&& $segments[2] == $this->translate('videos')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_GROUP);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'groups'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'videos'

				// now add back the required segments.
				array_unshift($segments, 'videos', 'group', $uid);

				// Parse the segments
				$router = ES::router('videos');
				$vars = $router->parse($segments);

				return $vars;
			}

			// audios
			if (($segments[0] == $this->translate('groups') || $segments[0] == 'groups')
				&& $segments[2] == $this->translate('audios')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_GROUP);

				// we need to re-arrange the segments.
				array_shift($segments); // remove the 'groups'
				array_shift($segments); // remove the 'uid'
				array_shift($segments); // remove the 'audios'

				// now add back the required segments.
				array_unshift($segments, 'audios', 'group', $uid);

				// Parse the segments
				$router = ES::router('audios');
				$vars = $router->parse($segments);

				return $vars;
			}

			//albums
			if (($segments[0] == $this->translate('groups') || $segments[0] == 'groups')
				&& $segments[2] == $this->translate('albums')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_GROUP);

				array_shift($segments); // remove the 'groups'
				array_shift($segments); // remove the 'uid'

				// check if last segments is form or not. if yes we will need to re-arrange the segment
				$lastSegment = $segments[count($segments) - 1];
				if ($lastSegment == $this->translate('albums_layout_form') || $lastSegment == 'form') {
					array_pop($segments); // remove the 'form'
				}

				$segments[] = 'group';
				$segments[] = $uid;

				if ($lastSegment == $this->translate('albums_layout_form') || $lastSegment == 'form') {
					$segments[] = $lastSegment;
				}

				$albumRouter = ES::router('albums');
				$vars = $albumRouter->parse($segments);
				return $vars;
			}

			// //photos
			if (($segments[0] == $this->translate('groups') || $segments[0] == 'groups')
				&& $segments[2] == $this->translate('photos')) {

				$uid = (int) $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_GROUP);

				require_once(SOCIAL_LIB . '/router/adapters/photos.php');
				$photoRouter = new SocialRouterPhotos('photos');

				array_shift($segments); // remove the 'groups'
				array_shift($segments); // remove the 'uid'

				// check if last segments is form or not. if yes we will need to re-arrange the segment
				$lastSegment = $segments[count($segments) - 1];
				if ($lastSegment == $this->translate('photos_layout_form') || $lastSegment == 'form') {
					array_pop($segments); // remove the 'form'
				}

				$segments[] = 'group';
				$segments[] = $uid;

				if ($lastSegment == $this->translate('photos_layout_form') || $lastSegment == 'form') {
					$segments[] = $lastSegment;
				}

				$vars = $photoRouter->parse($segments);
				return $vars;
			}
		}

		// $total = count($segments);
		if ($total >= 3 && ($segments[0] == $this->translate('groups') || $segments[0] == 'groups') && ($segments[2] == $this->translate('groups') || $segments[2] == 'groups')) {

			// we now, this is caused by groups menu items. lets re-arrange the segments
			$firstSegment = array_shift($segments);
			$secondSegment = array_shift($segments);
			array_shift($segments); // remove the 3rd elements, which is the 'groups'

			array_unshift($segments, $firstSegment, 'user', $secondSegment);


			// recalcute the total segments;
			$total = count($segments);
		}

		$vars['view'] = "groups";

		if ($total == 1) {
			return $vars;
		}

		if ($total == 2 && in_array($segments[1], $filters)) {
			$vars['filter'] = $this->getFilter($segments[1]);
			return $vars;
		}

		if ($total == 4 && $segments[1] == 'user' && in_array($segments[3], $filters)) {
			$vars['filter'] = $this->getFilter($segments[3]);
			$vars['userid'] = $segments[2];

			return $vars;
		}

		// When viewer tries to sort items on groups listings
		if ($total == 2 && in_array($segments[1], $ordering)) {
			$vars['ordering'] = $this->getOrdering($segments[1]);
			return $vars;
		}

		if ($total == 2 && $segments[1] == $this->translate('groups_layout_create')) {
			$vars['layout'] = 'create';
			return $vars;
		}

		if ($total == 2) {
			$id = (int) $this->getIdFromPermalink($segments[1]);
			$vars['layout'] = 'item';
			$vars['id'] = $id;

			return $vars;
		}

	   // http://site.com/menu/groups/all/latest
		if ($total == 3 && in_array($segments[1], $filters)) {
			$vars['filter'] = $this->getFilter($segments[1]);
			$vars['ordering'] = $this->getOrdering($segments[2]);

			return $vars;
		}

		// http://site.com/menu/groups/category/ID-category
		if ($total == 3 && $segments[1] == $this->translate('groups_layout_category')) {
			$vars['layout'] = 'category';
			$vars['id'] = $this->getIdFromPermalink($segments[2]);
			return $vars;
		}

		// http://site.com/menu/groups/ID-alias/edit
		if ($total == 3 && $segments[2] == $this->translate('groups_layout_edit')) {
			$vars['layout'] = 'edit';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);

			return $vars;
		}

		// http://site.com/menu/groups/steps/x
		if ($total == 3 && $segments[1] == $this->translate('groups_layout_steps')) {
			$vars['layout'] = 'steps';
			$vars['step'] = $segments[2];

			return $vars;
		}


		// http://site.com/menu/groups/user/ID-alias
		if ($total == 3 && ($segments[1] == $this->translate('groups_user')) || $segments[1] == 'user') {
			$vars['userid'] = $segments[2];
			return $vars;
		}

		// Specifically check for both info and timeline. If 4th segment is not info nor timeline, then we assume it is app
		if ($total == 3 && $segments[1] != $this->translate('groups_categories') && !in_array($segments[2], $typeException)) {
			$vars['layout'] = 'item';
			$vars['id'] = $this->getIdFromPermalink($segments[1]);
			$appId = $this->getIdFromPermalink($segments[2]);

			// $vars['type'] = $appId;
			$vars[(int) $appId ? 'appId' : 'app'] = $appId;
		}


		if ($total >= 3) {

			// http://site.com/menu/groups/categories/ID-category
			if ($segments[1] == $this->translate('groups_categories')) {
				$catId = $this->getIdFromPermalink($segments[2]);
				$vars['categoryid'] = $catId;
			}

			// http://site.com/menu/groups/categories/ID-category/latest
			if (isset($segments[3]) && in_array($segments[3], $ordering)) {
				$vars['ordering'] = $this->getOrdering($segments[3]);
			}

			// http://site.com/menu/groups/categories/ID-category/all
			if (isset($segments[3]) && in_array($segments[3], $filters)) {
				$vars['filter'] = $this->getFilter($segments[3]);
			}

			// http://site.com/menu/groups/categories/ID-category/all/latest
			if (isset($segments[4]) && in_array($segments[4], $ordering)) {
				$vars['ordering'] = $this->getOrdering($segments[4]);
			}

			// http://site.com/menu/groups/ID-alias/info
			// http://site.com/menu/groups/ID-alias/info/step
			if ($segments[2] == $this->translate('groups_type_info') || $segments[2] == $this->translate('groups_type_timeline')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);

				if ($segments[2] == $this->translate('groups_type_info')) {
					$vars['type'] = 'info';
				}

				if ($segments[2] == $this->translate('groups_type_timeline')) {
					$vars['type'] = 'timeline';
				}

				if (isset($segments[3]) && $segments[3]) {
					$vars['step'] = $segments[3];
				}

				return $vars;
			}

			// http://site.com/menu/groups/ID-alias/filterForm
			// http://site.com/menu/groups/ID-alias/filterForm/ID-filter
			if ($segments[2] == $this->translate('groups_type_filterform')) {

				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1]);
				$vars['type'] = 'filterForm';

				if (isset($segments[3]) && $segments[3]) {
					$vars['filterId'] = $segments[3];
				}

				return $vars;
			}

			// http://site.com/menu/groups/ID-alias/ID-app/filter
			if ($total == 4 && $segments[1] != $this->translate('groups_categories')) {
				$vars['layout'] = 'item';
				$vars['id'] = $this->getIdFromPermalink($segments[1], SOCIAL_TYPE_GROUP);

				if ($segments[2] == $this->translate('groups_custom_filter')) {
					$vars['filterId'] = $segments[3];
				} else if ($segments[2] == $this->translate('groups_hashtag')) {
					$vars['tag'] = $segments[3];
				} else {
					$vars['filter'] = $segments[3];

					$appId = $this->getIdFromPermalink($segments[2]);
					$vars[(int) $appId ? 'appId' : 'app'] = $appId;
				}
			}
		}

		// dump($vars);

		return $vars;
	}

	/**
	 * Retrieves the correct url that the current request should use.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getUrl($query, $url)
	{
		static $cache	= array();

		// Get a list of menus for the current view.
		$itemMenus	= FRoute::getMenus($this->name, 'item');

		// For single group item
		// index.php?option=com_easysocial&view=groups&layout=item&id=xxxx
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

		// For group categories
		$menus 	= FRoute::getMenus($this->name, 'category');
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
	 * @since	1.0
	 * @access	public
	 */
	private function getFilter($translated)
	{
		if ($translated == $this->translate('groups_filter_featured')) {
			return 'featured';
		}

		if ($translated == $this->translate('groups_filter_recent')) {
			return 'recent';
		}

		if ($translated == $this->translate('groups_filter_mine')) {
			return 'mine';
		}

		if ($translated == $this->translate('groups_filter_invited')) {
			return 'invited';
		}

		if ($translated == $this->translate('groups_filter_pending')) {
			return 'pending';
		}

		if ($translated == $this->translate('groups_filter_nearby')) {
			return 'nearby';
		}

		if ($translated == $this->translate('groups_filter_participated')) {
			return 'participated';
		}

		if ($translated == $this->translate('groups_filter_created')) {
			return 'created';
		}

		// Default to return all
		return 'all';
	}

	/**
	 * Retrieve the ordering
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string	The translated string
	 * @return	string	The actual ordering title
	 */
	private function getOrdering($translated)
	{
		if ($translated == $this->translate('groups_ordering_latest')) {
			return 'latest';
		}

		if ($translated == $this->translate('groups_ordering_name')) {
			return 'name';
		}

		if ($translated == $this->translate('groups_ordering_popular')) {
			return 'popular';
		}

		// Default to return latest
		return 'latest';
	}
}
