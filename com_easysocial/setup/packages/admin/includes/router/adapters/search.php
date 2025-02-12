<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterSearch extends SocialRouterAdapter
{
	/**
	 * Constructs the profile urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'search') {
			$segments[]	= $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}

		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;
		$fid = isset($query['fid']) ? $query['fid'] : null;

		// We do not want to display the layout
		if (!is_null($layout)) {
			$segments[]	= $this->translate('search_layout_' . $layout);
			unset($query['layout']);
		}

		if (!$fid && $layout && $layout == 'advanced' && isset($query['type'])) {
			$segments[]	= $query['type'];
			unset($query['type']);
		}

		if ($fid) {
			$filter = ES::table('SearchFilter');
			$filter->load($fid);

			if ($filter->alias) {
				$segments[]	= $filter->id . '-' . $filter->alias;
				unset($query['fid']);
			}
		}

		return $segments;
	}



	/**
	 * Parses search urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		// URL: http://site.com/menu/search/advanced/filter
		// URL: http://site.com/menu/search/advanced/type - user/page/group
		if ($total == 3 && ($segments[0] == $this->translate('search') || $segments[0] == 'search') && $segments[1] == $this->translate('search_layout_advanced')) {
			$vars['view'] = 'search';
			$vars['layout'] = 'advanced';

			$isFid = (int) $segments[2];

			if ($isFid > 0) {
				$vars['fid'] = $segments[2];
			} else {
				$vars['type'] = $segments[2];
			}

			return $vars;
		}

		// URL: http://site.com/menu/search/advanced
		if ($total == 2 && ($segments[0] == $this->translate('search') || $segments[0] == 'search') && $segments[1] == $this->translate('search_layout_advanced')) {
			$vars['view'] = 'search';
			$vars['layout']	= 'advanced';

			return $vars;
		}

		// URL: http://site.com/menu/search/
		if ($total == 1 && ($segments[0] == $this->translate('search') || $segments[0] == 'search')) {
			$vars['view'] = 'search';

			return $vars;
		}

		return $vars;
	}


}
