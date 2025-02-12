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

class SocialRouterFriends extends SocialRouterAdapter
{
	/**
	 * Constructs friends urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		$addExtraView = false;

		// Check if user id is supplied. If it does exist, use their alias as the first segment.
		$userId = isset($query['userid']) ? $query['userid'] : null;

		$filter = isset($query['filter']) ? $query['filter'] : '';

		$ignoreFilters = array('pending', 'suggest', 'request', 'invites', 'list');

		if (!is_null($userId)) {

			if ($filter && in_array($filter, $ignoreFilters)) {
				$addExtraView = false;
			} else {
				$segments[] = ESR::normalizePermalink($query['userid']);
				$addExtraView = true;
			}

			unset($query['userid']);
		}

		// If there is no active menu for friends, we need to add the view.
		if ($menu && $menu->query['view'] != 'friends' || $addExtraView) {
			$segments[] = $this->translate($query['view']);
			$addExtraView = false;
		}

		// If there's no menu, use the view provided
		if (!$menu || $addExtraView) {
			$segments[] = $this->translate($query['view']);
			$addExtraView = false;
		}

		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[]	= $this->translate('friends_layout_' . $layout);
			unset($query['layout']);
		}

		$listId = isset($query['listId']) ? $query['listId'] : '';

		if ($listId) {
			$segments[]	= $this->translate('friends_layout_list');
			$segments[]	= $listId;
			unset($query['listId']);
		}

		if ($filter == 'list') {
			$segments[]	= $this->translate('friends_layout_list');

			$id = isset($query['id']) ? $query['id'] : '';
			$segments[]	= $id;

			unset($query['id']);
			unset($query['filter']);
		}

		if ($filter && $filter != 'list') {
			$segments[]	= $this->translate('friends_filter_' . $filter);
			unset($query['filter']);
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

		if ($total >= 3 && ($segments[0] == $this->translate('friends') || $segments[0] == 'friends') && ($segments[2] == $this->translate('friends') || $segments[2] == 'friends')) {

			// we now, this is caused by friends menu items. lets re-arrange the segments
			$firstSegment = array_shift($segments);
			$secondSegment = array_shift($segments);
			array_shift($segments); // remove the 3rd elements, which is the 'friends'

			array_unshift($segments, $firstSegment, 'user', $secondSegment);

			// recalcute the total segments;
			$total = count($segments);
		}

		if ($total > 1 && $segments[1] == 'user') {

			// we knwo this is coming from profile page. lets unset the 'user' from segment.
			// lets get the 1st segment for later use.
			$firstSegment = array_shift($segments);

			array_shift($segments); // remove 'user' segment

			// now we add back the 1st segments.
			array_unshift($segments, $firstSegment);

			// re-calculate the totals
			$total = count($segments);
		}

		// URL: http://site.com/menu/friends/invite
		if ($total == 2 && $segments[1] == $this->translate('friends_layout_invite')) {
			$vars['view'] = 'friends';
			$vars['layout'] = 'invite';

			return $vars;
		}

		// URL: http://site.com/menu/friends/listform
		if ($total == 2 && $segments[1] == $this->translate('friends_layout_listform')) {
			$vars['view'] = 'friends';
			$vars['layout'] = 'listform';

			return $vars;
		}

		// Viewing a list of my own friends
		//
		// URL: http://site.com/menu/friends
		if ($total == 1 && $segments[0] == $this->translate('friends')) {
			$vars['view'] = 'friends';

			return $vars;
		}

		// We need to test for "filters" first
		//
		// URL: http://site.com/menu/friends/pending
		// URL: http://site.com/menu/friends/request
		// URL: http://site.com/menu/friends/suggest
		$filters 	= array($this->translate('friends_filter_invites'), $this->translate('friends_filter_pending'), $this->translate('friends_filter_request'), $this->translate('friends_filter_suggest'));

		if ($total == 2 && ($segments[0] == $this->translate('friends') || $segments[0] == 'friends') && in_array($segments[1], $filters)) {
			$vars['view'] = 'friends';

			if ($segments[1] == $this->translate('friends_filter_pending')) {
				$vars['filter'] = 'pending';
			}

			if ($segments[1] == $this->translate('friends_filter_request')) {
				$vars['filter'] = 'request';
			}

			if ($segments[1] == $this->translate('friends_filter_suggest')) {
				$vars['filter']	= 'suggest';
			}

			if ($segments[1] == $this->translate('friends_filter_invites')) {
				$vars['filter'] = 'invites';
			}

			return $vars;
		}

		// URL: http://site.com/menu/friends/ID-username
		if ($total == 2) {

			$vars['view'] = 'friends';
			$vars['userid'] = $this->getUserId($segments[1]);

			return $vars;
		}

		// URL: http://site.com/menu/friends/ID-username/mutual
		if ($total == 3 && $segments[0] == $this->translate('friends') && $segments[2] == $this->translate('friends_filter_mutual')) {
			$vars['view'] = 'friends';
			$vars['userid'] = $this->getUserId($segments[1]);
			$vars['filter'] = 'mutual';

			return $vars;
		}

		// URL: http://site.com/menu/friends/ID-username/friends
		if ($total == 3 && $segments[0] == $this->translate('friends') && $segments[2] == $this->translate('friends')) {
			$vars['view'] = 'friends';
			$vars['userid'] = $this->getUserId($segments[1]);
			// $vars['filter'] = 'friends';

			return $vars;
		}

		// If there are 3 segments and the second segment is 'list'
		// URL: http://site.com/menu/friends/list/ID
		if ($total == 3 && $segments[1] == $this->translate('friends_layout_list')) {
			$vars['view'] = 'friends';
			// $vars['layout'] = 'list';
			// $vars['filter'] = 'list';
			$vars['listId'] = $segments[2];

			return $vars;
		}

		return $vars;
	}
}
