<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterPolls extends SocialRouterAdapter
{
	/**
	 * Constructs polls urls
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		$userId = isset($query['userid']) ? $query['userid'] : null;
		$clusterId = isset($query['clusterId']) ? $query['clusterId'] : null;
		$clusterType = isset($query['clusterType']) ? $query['clusterType'] : null;

		$addExtraView = false;

		// Linkage to clusters
		if ($clusterId && $clusterType && ($clusterType == 'group' || $clusterType == 'page' || $clusterType == 'event')) {

			$addExtraSegments = true;

			// we need to determine if we need to add below segments or not
			if (isset($query['Itemid'])) {
				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

				if ($xMenu) {
					$xquery = $xMenu->query;

					$xView = 'groups';
					if ($clusterType == 'page') {
						$xView = 'pages';
					}
					if ($clusterType == 'event') {
						$xView = 'events';
					}

					if ($xquery['view'] == $xView && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id'])) {
						$xId = (int) $xquery['id'];
						$tId = (int) $query['clusterId'];
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
						$query['Itemid'] = ESR::getItemId($xView, 'item', (int) $query['clusterId']);
						$addExtraView = true;
						$segments[] = $this->translate($xView);

					}
				}

				$cluster = ES::cluster($clusterType, $clusterId);
				$segments[] = ESR::normalizePermalink($cluster->getAlias());
			}

			unset($query['clusterId']);
			unset($query['clusterType']);
		}


		if (!is_null($userId)) {
			$segments[] = ESR::normalizePermalink($query['userid']);
			$addExtraView = true;
			unset($query['userid']);
		}

		// if (!is_null($clusterId)) {
		// 	$cluster = ES::cluster($clusterType, $clusterId);

		// 	$segments[] = $this->translate($clusterType . 's');
		// 	$segments[] = ESR::normalizePermalink($cluster->getAlias());

		// 	$addExtraView = true;
		// 	unset($query['clusterId']);
		// 	unset($query['clusterType']);
		// }

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'polls') {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}

		// we need to check if we should add the view here #2441
		if ($addExtraView) {
			$segments[]	= $this->translate($query['view']);
		}

		unset($query['view']);

		// Polls may have layout
		$layout = isset($query['layout']) ? $query['layout'] : null;

		if ($layout) {
			$segments[] = $this->translate('polls_layout_' . $layout);
			unset($query['layout']);
		}

		$filter = isset($query['filter']) ? $query['filter'] : null;
		$menuFilter = isset($menu->query['filter']) ? $menu->query['filter'] : null;
		$addFilter = false;

		if (is_null($menuFilter)) {
			if (!is_null($filter)) {
				$addFilter = true;
			}
		} else {
			if(!is_null($filter) && $filter != $menuFilter) {
				$addFilter = true;
			}
		}

		if ($addFilter) {
			$segments[]	= $this->translate('polls_filter_' . $query['filter']);
		}
		unset($query['filter']);

		// dump($segments);

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

		if ($total == 1) {
			$vars['view'] = 'polls';
			return $vars;
		}

		// layout=create
		if ($total == 2 && ($segments[1] == $this->translate('polls_layout_create') || $segments[1] == 'create')) {
			$vars['view'] = 'polls';
			$vars['layout'] = 'create';

			return $vars;
		}

		if ($total == 2 && ($segments[1] == $this->translate('polls_filter_all') || $segments[1] == 'all')) {
			$vars['view'] = 'polls';
			$vars['filter'] = 'all';

			return $vars;
		}

		if ($total == 2 && ($segments[1] == $this->translate('polls_filter_mine') || $segments[1] == 'mine')) {
			$vars['view'] = 'polls';
			$vars['filter'] = 'mine';

			return $vars;
		}

		// if the segment[1] is non of the aboves, mean, it could be coming from profile page.
		// Coming from profile page
		if ($total > 1) {
			$vars['view'] = 'polls';

			if ($segments[1] == 'user' && isset($segments[2]) && $segments[2]) {
				$vars['userid'] = $this->getUserId($segments[2]);
			}

			if ($segments[1] != 'user') {
				$vars['userid'] = $this->getUserId($segments[1]);
			}

			return $vars;
		}

		return $vars;
	}
}
