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

class ThemesHelperHeader extends ThemesHelperAbstract
{
	/**
	 * Renders group category header
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function profilesType(SocialTableProfile $profile)
	{
		$helper = ES::viewHelper('Profiles', 'Item');

		// Validate for the current profile type id
		$profile = $helper->getActiveProfiles();
		
		// Retrieve total of member count
		$totalUsers = $helper->getMembersCount();

		$theme = ES::themes();

		$theme->set('profile', $profile);
		$theme->set('totalUsers', $totalUsers);

		$output = $theme->output('site/helpers/header/profilestype');

		return $output;
	}

	/**
	 * Renders group category header
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function groupCategory(SocialTableGroupCategory $category)
	{
		$helper = ES::viewHelper('Groups', 'Category');

		// Retrieve immediately child categories from the current view
		$childs = $helper->getImmediateChildCategories();

		// Get total groups within a category
		$totalGroups = $helper->getTotalGroups();

		// Get total albums within a category
		$totalAlbums = $helper->getTotalAlbums();

		$theme = ES::themes();

		$theme->set('category', $category);
		$theme->set('childs', $childs);
		$theme->set('totalGroups', $totalGroups);
		$theme->set('totalAlbums', $totalAlbums);

		$output = $theme->output('site/helpers/header/groupcategory');

		return $output;
	}

	/**
	 * Renders event category header
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function eventCategory(SocialTableEventCategory $category)
	{
		$helper = ES::viewHelper('Events', 'Category');

		// Retrieve immediately child categories from the current view
		$childs = $helper->getImmediateChildCategories();

		// Get total event within a category
		$totalEvents = $helper->getTotalEvents();

		// Get total albums within a category
		$totalAlbums = $helper->getTotalAlbums();

		$theme = ES::themes();

		$theme->set('category', $category);
		$theme->set('childs', $childs);
		$theme->set('totalEvents', $totalEvents);
		$theme->set('totalAlbums', $totalAlbums);

		$output = $theme->output('site/helpers/header/eventcategory');

		return $output;
	}

	/**
	 * Renders page category header
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function pageCategory(SocialTablePageCategory $category)
	{
		$helper = ES::viewHelper('Pages', 'Category');

		// Retrieve immediately child categories from the current view
		$childs = $helper->getImmediateChildCategories();

		// Get total page within a category
		$totalPages = $helper->getTotalPages();

		// Get total albums within a category
		$totalAlbums = $helper->getTotalAlbums();

		$theme = ES::themes();

		$theme->set('category', $category);
		$theme->set('childs', $childs);
		$theme->set('totalPages', $totalPages);
		$theme->set('totalAlbums', $totalAlbums);

		$output = $theme->output('site/helpers/header/pagecategory');

		return $output;
	}
}
