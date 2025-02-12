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

require_once(__DIR__ . '/abstract.php');

class SocialSidebarUsers extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$layout = $this->input->get('layout', '', 'cmd');

		// We do not want to render anything on the item layout
		if ($layout == 'item') {
			return;
		}

		return $this->renderListing();
	}

	public function renderListing()
	{
		$helper = ES::viewHelper('Users', 'List');
		$filter = $helper->getActiveFilter();
		$searchFilters = $helper->getSearchFilters();
		$profiles = $helper->getProfileTypes();
		$fid = $helper->getActiveId();
		$activeProfile = $helper->getActiveProfile();

		$path = $this->getTemplatePath('users');

		require($path);
	}
}
