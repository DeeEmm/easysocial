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

class SocialRouterManage extends SocialRouterAdapter
{
	/**
	 * Constructs the manage urls
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function build(&$menu, &$query)
	{

		$segments = array();

		// If there is a menu but not pointing to the manage view, we need to set a view
		if ($menu && $menu->query['view'] != 'manage') {
			$segments[]	= $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}

		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[]	= $this->translate('manage_layout_' . $layout);
			unset($query['layout']);
		}

		$filter = isset($query['filter']) ? $query['filter'] : '';
		
		if ($filter && $filter != 'all') {
			$segments[]	= $this->translate('manage_cluster_filter_' . $filter);
			unset($query['filter']);
		}

		return $segments;
	}
	
	/**
	 * Parse the URL for manage view
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		// URL: http://site.com/menu/manage/clusters
		if ($total == 2 && ($segments[0] == $this->translate('manage') || $segments[0] == 'manage') && $segments[1] == $this->translate('manage_layout_clusters')) {
			$vars['view'] = 'manage';
			$vars['layout'] = 'clusters';

			return $vars;
		}

		// URL: http://site.com/menu/manage/clusters/group
		if ($total == 3 && ($segments[0] == $this->translate('manage') || $segments[0] == 'manage') && $segments[1] == $this->translate('manage_layout_clusters')) {
			$vars['view'] = 'manage';
			$vars['layout'] = 'clusters';
			$vars['filter'] = $segments[2];

			return $vars;
		}

		// URL: http://site.com/menu/manage/
		if ($total == 1 && ($segments[0] == $this->translate('manage') || $segments[0] == 'manage')) {
			$vars['view'] = 'manage';
			return $vars;
		}

		return $vars;
	}


}
