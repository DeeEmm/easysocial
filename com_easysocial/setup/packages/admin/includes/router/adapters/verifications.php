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

class SocialRouterVerifications extends SocialRouterAdapter
{
	/**
	 * Construct urls for account verifications
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		$addExtraView = false;

		if ($menu && $menu->query['view'] != 'verifications') {
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
			$segments[] = $this->translate('verifications_layout_' . $layout);
			unset($query['layout']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		if ($total == 1) {
			$vars['view'] = 'verifications';
			return $vars;
		}

		// layout=request || layout=form&id=xxx
		if (($total == 3 || $total == 2) && $segments[1] == $this->translate('verifications_layout_request')) {
			$vars['view'] = 'verifications';
			$vars['layout'] = 'request';

			return $vars;
		}


		return $vars;
	}
}
