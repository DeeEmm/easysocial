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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelSidebar extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('sidebar');
	}

	/**
	 * Returns a list of menus for the admin sidebar.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItems($activeView)
	{
		// @TODO: Configurable theme path for the back end.

		// Get the sidebar ordering from defaults first
		$defaults = SOCIAL_ADMIN_DEFAULTS . '/sidebar.json';

		$sidebarOrdering = ES::makeObject( $defaults );

		if (!$sidebarOrdering) {
			return false;
		}

		$items = array();

		jimport( 'joomla.filesystem.folder' );

		$path = SOCIAL_ADMIN_DEFAULTS . '/sidebar';

		$files = JFolder::files( $path, '.json' );

		foreach ($sidebarOrdering as $sidebarItem) {
			// Remove the reference from the $files array if it is defined in the ordering
			$index = array_search( $sidebarItem . '.json', $files );

			if ($index !== false) {
				unset($files[$index]);
			}

			// Get the sidebar items in the defined order
			$content = ES::makeObject( $path . '/' . $sidebarItem . '.json' );
			if( $content !== false )
			{
				$items[] = $content;
			}
		}

		// Add any remaining files into items
		foreach ($files as $file) {
			$content = ES::makeObject( $path . '/' . $file );

			if ($content !== false) {
				$items[] = $content;
			}
		}

		// If there are no items there, it should throw an error.
		if (!$items) {
			return false;
		}

		// Initialize default result.
		$result = array();

		foreach ($items as $item) {

			// Generate a unique id.
			$uid = uniqid();

			// Generate a new group object for the sidebar.
			$obj = clone($item);

			// Assign the unique id.
			$obj->uid = $uid;

			// Initialize the counter
			$obj->count	= 0;

			// Determine the type to check for to determine if the child is active
			$obj->activeChildType = $this->input->get($obj->active, '', 'string');

			// Parent would always get the counter from its child
			$obj->count = 0;

			if (isset($obj->counter) && !$obj->childs) {
				$obj->count = $this->getCount($obj->counter);
			}

			$obj->views = $this->getViews($obj);
			$obj->isActive = in_array($activeView, $obj->views) ? true : false;

			// Ensure that each menu item has a child property
			if (!isset($obj->childs)) {
				$obj->childs = array();
			}

			if (!empty($obj->childs)) {
				$childItems = array();

				foreach ($obj->childs as $child) {

					// Clone the child object.
					$childObj = clone($child);

					// Let's get the URL.
					$url = array('index.php?option=com_easysocial');
					$query = ES::makeArray($child->url);

					// Set the url into the child item so that we can determine the active submenu.
					$childObj->url = $child->url;

					if ($query) {

						foreach ($query as $queryKey => $queryValue) {

							if ($queryValue) {
								$url[]	= $queryKey . '=' . $queryValue;
							}

							// If this is a call to the controller, it must have a valid token id.
							if ($queryKey == 'controller') {
								$url[] = ES::token() . '=1';
							}
						}
					}

					// Set the item link.
					$childObj->link = implode('&amp;', $url);

					// Initialize the counter
					$childObj->count = 0;

					// Check if there's any sql queries to execute.
					if (isset($childObj->counter)) {
						$childObj->count = $this->getCount($childObj->counter);

						$obj->count += $childObj->count;
					}

					// Add a unique id for the side bar for accordion purposes.
					$childObj->uid = $uid;

					// Determine if the current child menu should be active
					$childObj->isActive = false;

					if (
						($obj->activeChildType == $childObj->url->{$obj->active} || (isset($childObj->activeLayouts) && in_array($obj->activeChildType, $childObj->activeLayouts))) &&
						($activeView == $childObj->url->view)
					) {
						$childObj->isActive = true;
					}

					// Add the menu item to the child items.
					$childItems[] = $childObj;
				}

				$obj->childs = $childItems;

				// Sort child items
				if (isset($obj->childSorting) && $obj->childSorting) {
					usort($obj->childs, function($a, $b) {
						$al = strtolower(JText::_($a->title));
						$bl = strtolower(JText::_($b->title));

						if ($al == $bl) {
							return 0;
						}

						return ($al > $bl) ? +1 : -1;
					});
				}
			}

			$result[] = $obj;
		}

		return $result;
	}

	/**
	 * Given a list of sidebar structure, determine all the views
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getViews($menuItem)
	{
		$views = array($menuItem->view);

		if (isset($menuItem->childs) && $menuItem->childs) {
			foreach ($menuItem->childs as $childMenu) {
				$views[] = $childMenu->url->view;
			}
		}

		$views = array_unique($views);

		return $views;
	}

	/**
	 * Retrieves a specific count item based on the namespace
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getCount($namespace)
	{
		list($modelName, $method) = explode('/', $namespace);

		$model = ES::model($modelName);
		$count = $model->$method();

		return $count;
	}

	public static function sortItems($a, $b)
	{

	}
}
