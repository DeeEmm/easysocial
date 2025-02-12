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

class ModEasySocialDropdownMenuHelper
{
	public static function getItems(&$params)
	{
		// Determine if we need to render menu items from specific menu.
		$menuType = $params->get('menu_type' , '');

		if (empty($menuType)) {
			return false;
		}

		$menu = JFactory::getApplication()->getMenu();
		$items = $menu->getItems('menutype' , $menuType);

		if (!$items) {
			return false;
		}

		foreach ($items as $i => &$item) {

			$menuParams = $item->getParams();

			// If the Menu item's 'Display in Menu' set to No, do not show it
			if ($menuParams && !is_null($menuParams->get('menu_show')) && !$menuParams->get('menu_show')) {
				unset($items[$i]);
				continue;
			}

			self::buildRoute($item);

			// Assign the padding value for each item
			$item->padding = ($item->level - 1) * 10;
		}

		return $items;
	}

	public static function buildRoute(&$item)
	{
		$item->flink = $item->link;
		$menuParams = $item->getParams();

		// Reverted back for CMS version 2.5.6
		switch ($item->type) {
			case 'separator':
			case 'heading':
				// No further action needed.
				break;

			case 'url':
				if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
					// If this is an internal Joomla link, ensure the Itemid is set.
					$item->flink = $item->link . '&Itemid=' . $item->id;
				}
				break;

			case 'alias':
				// If this is an alias use the item id stored in the parameters to make the link.
				$item->flink = 'index.php?Itemid=' . $menuParams->get('aliasoptions');
				break;

			default:

				if (ESRouter::getMode() == SOCIAL_ROUTER_MODE_SEF) {
					$item->flink = 'index.php?Itemid=' . $item->id;
				} else {
					$item->flink .= '&Itemid=' . $item->id;
				}
				break;
		}

		if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
			$item->flink = JRoute::_($item->flink, true, $item->getParams()->get('secure'));
		} else {
			$item->flink = JRoute::_($item->flink);
		}
	}
}