<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterPoints extends SocialRouterAdapter
{
	/**
	 * Constructs the points urls
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function build(&$menu, &$query)
	{
		$segments 	= array();

		$isUserPoints = false;

		$userid = isset($query['userid']) ? $query['userid'] : null;
		if (!is_null($userid)) {
			$segments[]	= ESR::normalizePermalink($query['userid']);
			unset($query['userid']);

			$isUserPoints = true;
		}

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && ($menu->query['view'] != 'points' || $isUserPoints)) {
			$segments[] = $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[] = $this->translate($query['view']);
		}
		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[]	= $this->translate('points_layout_' . $layout);
			unset($query['layout']);
		}

		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id)) {
			$segments[]	= $id;
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

		// When site has a points menu created
		if ($total == 4 && $segments[1] != 'user') {
			$userid = $segments[1];

			// Remove the first two segments
			array_shift($segments);
			array_shift($segments);

			// add back the user id.
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

		// URL: http://site.com/menus/points
		if ($total == 1 && ($segments[0] == $this->translate('points') || $segments[0] == 'points')) {

			$vars['view'] = 'points';
			return $vars;
		}

		if ($total == 2 && ($segments[0] == $this->translate('points') || $segments[0] == 'points') && $segments[1] == $this->translate('points_layout_history')) {
			$vars['view'] = 'points';
			$vars['layout'] = 'history';
		}

		// URL: http://site.com/menus/points/item/ID-point-alias
		if ($total == 3 && ($segments[0] == $this->translate('points') || $segments[0] == 'points') && $segments[1] == $this->translate('points_layout_item')) {
			$vars['view'] = 'points';
			$vars['layout'] = 'item';
			$vars['id'] = $segments[2];

			return $vars;
		}

		// URL: http://site.com/menus/points/history/ID-point-alias
		if ($total == 3 && ($segments[0] == $this->translate('points') || $segments[0] == 'points') && $segments[1] == $this->translate('points_layout_history')) {
			$vars['view'] = 'points';
			$vars['layout'] = 'history';
			$vars['userid'] = $this->getUserId($segments[2]);

			return $vars;
		}

		return $vars;
	}
}
