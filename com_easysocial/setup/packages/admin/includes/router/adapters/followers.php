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

/**
 * Component's router for followers view.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterFollowers extends SocialRouterAdapter
{
	/**
	 * Constructs the follower's urls
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function build(&$menu , &$query)
	{
		$segments 	= array();
		$addExtraView = false;

		$userId = isset($query['userid']) ? $query['userid'] : null;

		if (!is_null($userId)) {
			$segments[] = ESR::normalizePermalink($query['userid']);
			unset($query['userid']);

			$addExtraView = true;
		}


		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != $this->translate('followers') || $addExtraView) {
			$segments[] = $this->translate($query['view']);

			$addExtraView = false;
		}

		// If there's no menu, use the view provided
		if (!$menu || $addExtraView) {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}

		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[]	= $this->translate('followers_layout_' . $layout);
			unset($query['layout']);
		}

		$filter = isset($query['filter']) ? $query['filter'] : null;

		if (!is_null($filter)) {
			$segments[]	= $this->translate('followers_filter_' . $filter);
			unset($query['filter']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	An array of url segments
	 * @return	array 	The query string data
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

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

		// User is viewing their own followers
		// URL: http://site.com/menu/followers
		if ($total == 1 && $segments[0] == $this->translate('followers')) {
			$vars['view']	= 'followers';
			return $vars;
		}

		// When viewing a list of people that is following the current user.
		// URL: http://site.com/menu/followers/following
		if ($total == 2 && $segments[1] == $this->translate('followers_filter_following')) {
			$vars['view'] = 'followers';
			$vars['filter'] = 'following';

			return $vars;
		}

		// URL: http://site.com/menu/followers/following
		if ($total == 2 && $segments[1] == $this->translate('followers_filter_suggest')) {
			$vars['view'] = 'followers';
			$vars['filter'] = 'suggest';

			return $vars;
		}

		$filters = array($this->translate('followers_filter_following'), $this->translate('followers_filter_followers'), $this->translate('followers_filter_suggest'));

		// When user is viewing another person's follower list
		// URL: http://site.com/menu/followers/ID-username/following
		if ($total == 2 && !in_array($segments[1] , $filters)) {
			$vars['view'] = 'followers';
			$vars['userid'] = $this->getUserId($segments[1]);

			return $vars;
		}

		// When user is viewing another person's follower list
		// URL: http://site.com/menu/followers/ID-username/following
		if ($total == 3 && (in_array($segments[2], $filters) || $segments[2] == $this->translate('followers'))) {
			$vars['view'] = 'followers';
			$vars['userid'] = $this->getUserId($segments[1]);

			if ($segments[2] == $this->translate('followers_filter_following')) {
				$vars['filter']	= 'following';
			}

			if ($segments[2] == $this->translate('followers_filter_suggest')) {
				$vars['filter']	= 'suggest';
			}

			return $vars;
		}

		// URL: http://site.com/menu/followers/ID-username/followers/following
		if ($total == 4 && in_array($segments[3], $filters)) {
			$vars['view'] = 'followers';
			$vars['userid'] = $this->getUserId($segments[1]);

			if ($segments[3] == $this->translate('followers_filter_following')) {
				$vars['filter']	= 'following';
			}

			if ($segments[3] == $this->translate('followers_filter_suggest')) {
				$vars['filter']	= 'suggest';
			}

			return $vars;
		}

		return $vars;
	}
}
