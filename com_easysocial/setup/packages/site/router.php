<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php');

class EasySocialRouterBase extends EasySocial
{
	public static function build(&$query) 
	{
		// check if we want to this query allow to use sef caching or not.
		if (isset($query['view']) && $query['view'] && !ESR::isViewSefCacheAllow($query['view'])) {
			$segments = FRoute::build($query);
			return $segments;
		}

		// if there is no view, skip this url processing as this url most likely
		// from menu item. #4071
		if (!isset($query['view'])) {
			return array();
		}

		$debug = JFactory::getApplication()->input->get('debug', false, 'bool');

		$oriQuery = $query;
		$dbSegment = ESR::getDbSegments($oriQuery, $debug);

		if ($dbSegment === false) {
			$segments = FRoute::build($query);
			ESR::encode($segments);
			ESR::setDbSegments($oriQuery, $segments, $query, $debug);

			return $segments;

		}

		$segments = $dbSegment->segments;

		// now we need to remove the extra query that are already process.
		parse_str($dbSegment->rawurl, $rawQuery);
		foreach ($rawQuery as $key => $val) {
			unset($query[$key]);
		}

		return $segments;
	}

	public static function parse($segments)
	{
		$debug = JFactory::getApplication()->input->get('debug', false, 'bool');

		// If there is only 1 segment and the segment is index.php, it's just submitting
		if (count($segments) == 1 && $segments[0] == 'index.php') {
			return array();
		}

		// lets format the segment so that the 1st index will be the view.
		$test = ESR::format($segments);

		// check if we should retrieve from caching or not.
		if (!ESR::isViewSefCacheAllow($test[0])) {
			$query  = FRoute::parse($segments);
			return $query;
		}

		$tmp = $segments;
		$query = ESR::getDbVars($tmp, $debug);

		if (ES::isJoomla4() && $query !== false && $query) {

			// It seems like Joomla 4 try to merge the Itemid inito $vars / $query
			// in /libraries/src/Router/SiteRouter.php at line 312.
			// bcos of this, if there is a menu item that point to all layout, any other
			// sef that using this menu item will always point to all layout if the sef
			// link itself do not have a layout.
			// In this case, we override the menu item layout to empty string.
			// so far this happen on albums page only #5144

			if (isset($query['view']) && $query['view'] == 'albums' && isset($query['uid']) && isset($query['type']) && !isset($query['layout'])) {
				$query['layout'] = '';
			}
		}

		if ($query === false) {
			// if false, we try to fall back to normal parse rules.
			$query  = FRoute::parse($segments);
		}

		return $query;
	}

}

if (ES::isJoomla4()) {
	/**
	 * Routing class to support Joomla 4.0
	 *
	 * @since  3.3
	 */
	class EasySocialRouter extends Joomla\CMS\Component\Router\RouterBase
	{
		public function build(&$query)
		{
			$segments = EasySocialRouterBase::build($query);
			return $segments;
		}

		public function parse(&$segments)
		{
			$query = EasySocialRouterBase::parse($segments);

			// look like we have to manually reset the segments so that we will not hit this error:
			// Uncaught Joomla\CMS\Router\Exception\RouteNotFoundException: URL invalid in /libraries/src/Router/Router.php on line 152
			$segments = array();

			return $query;
		}
	}
}


/**
 * Responsible to build urls into SEF urls
 *
 * @since   1.0
 * @access  public
 */
function EasySocialBuildRoute(&$query)
{
	$segments = EasySocialRouterBase::build($query);
	return $segments;
}

/**
 * Responsible to rewrite urls from SEF into proper query strings.
 *
 * @since   1.0
 * @access  public
 */
function EasySocialParseRoute($segments)
{
	$query = EasySocialRouterBase::parse($segments);
	return $query;
}
