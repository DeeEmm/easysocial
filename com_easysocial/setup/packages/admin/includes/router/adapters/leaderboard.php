<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

/**
 * Component's router.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterLeaderBoard extends SocialRouterAdapter
{
	/**
	 * Constructs the profile urls
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'leaderboard') {
			$segments[]	= $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}
		unset($query['view']);

		return $segments;
	}

	/**
	 * Some desc
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		// Since there is only 1 view in the leaderboard view.
		if ($total == 1 && ($segments[0] == $this->translate('leaderboard') || $segments[0] == 'leaderboard')) {
			$vars['view'] = 'leaderboard';

			return $vars;
		}

		return $vars;
	}

}
