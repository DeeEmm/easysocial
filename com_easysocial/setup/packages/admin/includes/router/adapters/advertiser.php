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

class SocialRouterAdvertiser extends SocialRouterAdapter
{
	/**
	 * Constructs polls urls
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		$addExtraView = false;

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'advertiser') {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
			$addExtraView = false;
		}

		unset($query['view']);

		// Polls may have layout
		$layout = isset($query['layout']) ? $query['layout'] : null;

		if ($layout) {
			$segments[] = $this->translate('advertiser_layout_' . $layout);
			unset($query['layout']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		if ($total == 1) {
			$vars['view'] = 'advertiser';
			return $vars;
		}

		// layout=create
		if ($total == 2 && $segments[1] == $this->translate('advertiser_layout_create')) {
			$vars['view'] = 'advertiser';
			$vars['layout'] = 'create';

			return $vars;
		}


		return $vars;
	}
}
