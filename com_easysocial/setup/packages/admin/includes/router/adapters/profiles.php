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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

/**
 * Component's router for users view.
 *
 * @since	1.3
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterProfiles extends SocialRouterAdapter
{
	/**
	 * Constructs users urls
	 *
	 * @since	1.0
	 * @access	public
	 * @param	JMenu 	The active menu object.
	 * @param	array 	An array of query strings
	 * @return	array 	The url structure
	 */
	public function build( &$menu , &$query )
	{
		$segments 	= array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query[ 'view' ] != 'profiles') {
			$segments[]	= $this->translate( $query[ 'view' ] );
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}
		unset($query['view']);

		// Process layout
		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[]	= $this->translate('profiles_layout_' . $layout);
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
	 * @param	array 	An array of url segments
	 * @return	array 	The query string data
	 */
	public function parse(&$segments)
	{
		$vars 		= array();
		$total 		= count($segments);

		$vars['view']	= 'profiles';

		// URL: http://site.com/menu/profiles/item/ID-xxx
		if ($segments[1] == $this->translate('profiles_layout_item')) {

			$vars['layout']	= 'item';
			$vars['id'] = $segments[2];

			return $vars;
		}


		return $vars;
	}
}
