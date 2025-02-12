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
 * Component's router for discussion app customView.
 *
 * @since	2.0
 */
class SocialRouterAppFeeds extends SocialRouterAdapter
{
	/**
	 * Constructs the app customView urls
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function build(&$menu , &$query, &$segments)
	{
		//customView
		$customView = isset($query['customView']) ? $query['customView'] : null;

		if (!is_null($customView)) {
			$segments[]	= $this->translate('feeds_customview_' . $customView );
			unset($query['customView']);
		}

		// Check if user id is supplied. If it does exist, use their alias as the first segment.
		$id = isset($query['rssid']) ? $query['rssid'] : null;

		if(!is_null($id)) {
			$segments[]	= $id;
			unset($query['rssid']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	2.0
	 * @access	public
	 * @param	array 	An array of url segments
	 * @return	array 	The query string data
	 */
	public function parse(&$segments, &$vars)
	{
		$total = count($segments);

		$customViews = array(
			$this->translate('feeds_customview_item')
		);

		if ($total >= 5 && in_array($segments[4], $customViews)) {
			$vars['customView'] = $this->getCustomView($segments[4]);

			if (isset($segments[5]) && $segments[5]) {
				$vars['rssid'] = $segments[5];
			}
		}

		return $vars;
	}


	/**
	 * Retrieve the custom view
	 *
	 * @since	2.0
	 * @access	private
	 */
	private function getCustomView($translated)
	{
		// Default to return item
		return 'item';
	}
}
