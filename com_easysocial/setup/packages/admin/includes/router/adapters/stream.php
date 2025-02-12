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
 * Component's router for stream view.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterStream extends SocialRouterAdapter
{
	/**
	 * Constructs the stream urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'stream') {
			$segments[] = $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[] = $this->translate($query['view']);
		}
		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		// We do not want to display the layout
		if (!is_null($layout)) {
			$segments[] = $this->translate('stream_layout_' . $layout);
			unset($query['layout']);
		}

		// Check if user id is supplied. If it does exist, use their alias as the first segment.
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

		if ($total == 3) {

			// URL: http://site.com/menu/stream/item/ID
			if (($segments[0] == $this->translate('stream') || $segments[0] == 'stream') && $segments[1] == $this->translate('stream_layout_item')) {
				$vars['view'] = 'stream';
				$vars['layout'] = 'item';
				$vars['id'] = $segments[2];

				return $vars;
			}
		}

		return $vars;
	}
}
