<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterBadges extends SocialRouterAdapter
{
	/**
	 * Construct's the badges url
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If user id is set
		$userId = isset($query['userid']) ? $query['userid'] : null;

		$isUserBadges = false;

		if (!is_null($userId)) {
			$segments[]	= ESR::normalizePermalink($query['userid']);
			unset($query['userid']);

			$isUserBadges = true;
		}

		// If there is no active menu for friends, we need to add the view.
		if ($menu && ($menu->query['view'] != 'badges' || $isUserBadges)) {
			$segments[] = $this->translate($query['view']);
		}

		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}

		unset($query['view']);

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
		}

		if ($addLayout) {
			$segments[]	= $this->translate('badges_layout_' . $layout);
		}
		unset($query['layout']);

		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id)) {
			$segments[]	= ESR::normalizePermalink($id);
			unset($query['id']);
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

		// When there is a badges menu, the segments could be different for the user's badge achievements
		if (($total == 4 || $total == 3) && $segments[$total - 1] == $this->translate('badges_layout_achievements')) {
			$userid = $segments[1];

			// Remove the first two segments
			array_shift($segments);
			array_shift($segments);

			// Add back the user id.
			$segments[] = $userid;

			// re-calculate the totals
			$total = count($segments);
		}

		if ($total > 1 && $segments[1] == 'user') {

			$userid = $segments[2];

			// we knwo this is coming from profile page. lets unset the 'user' from segment.
			// lets get the 1st segment for later use.
			$firstSegment = array_shift($segments);

			array_shift($segments); // remove 'user' segment
			array_shift($segments); // remove 'id-user' segment

			// now we add back the 1st segments.
			array_unshift($segments, $firstSegment);

			// add back the user id.
			$segments[] = $userid;

			// re-calculate the totals
			$total = count($segments);
		}

		// var_dump('badges', $segments);

		// URL: http://site.com/menu/badges
		if ($total == 1) {
			$vars['view'] = 'badges';
			return $vars;
		}

		// URL: http://site.com/menu/badges/achievements
		if ($total == 2 && $segments[1] == $this->translate('badges_layout_achievements')) {
			$vars['view'] = 'badges';
			$vars['layout'] = 'achievements';

			return $vars;
		}

		// URL: http://site.com/menu-badges/achievements
		if ($total == 2 && $segments[0] == $this->translate('badges_layout_achievements')) {
			$vars['view'] = 'badges';
			$vars['layout'] = 'achievements';
			$vars['userid'] = $this->getUserId($segments[1]);

			return $vars;
		}

		// URL: http://site.com/menu/badges/item/ID-badge-alias
		if ($total == 3 && $segments[1] == $this->translate('badges_layout_item')) {
			$vars['view'] = 'badges';
			$vars['layout'] = 'item';
			$vars['id'] = $this->getIdFromPermalink($segments[2]);

			return $vars;
		}

		// URL: http://site.com/menu-badges/ID-user-alias
		if ($total == 2 && $segments[0] == 'badges') {
			$vars['view'] = 'badges';
			$vars['layout'] = 'achievements';
			$vars['userid'] = $this->getUserId($segments[1]);

			return $vars;
		}

		// URL: http://site.com/menu/badges/achievements/ID-user-alias
		if ($total == 3 && $segments[1] == $this->translate('badges_layout_achievements') && $segments[2] == 'user') {
			$vars['view'] = 'badges';
			$vars['layout'] = 'achievements';
			$vars['userid'] = $this->getUserId($segments[0]);
		}

		// URL: http://site.com/menu/badges/achievements/ID-user-alias
		if ($total == 3 && $segments[1] == $this->translate('badges_layout_achievements') && $segments[2] != 'user') {
			$vars['view'] = 'badges';
			$vars['layout'] = 'achievements';
			$vars['userid'] = $this->getUserId($segments[2]);
		}

		// URL: http://easysocial.com/[MENU]/798-admin/badges
		if ($total == 3 && $segments[2] == $this->translate('badges')) {
			$vars['view'] = 'badges';
			$vars['layout'] = 'achievements';
			$vars['userid'] = $this->getUserId($segments[1]);
		}


		return $vars;
	}
}
