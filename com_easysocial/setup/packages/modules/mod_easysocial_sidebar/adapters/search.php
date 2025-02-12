<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSidebarSearch extends SocialSidebarAbstract
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
		if ($layout == 'advanced') {
			return $this->renderListing();
		}
	}

	public function renderListing()
	{
		$helper = ES::viewHelper('Search', 'Advanced');
		$type = $helper->getActiveType();
		$newSearchLink = $helper->getCreateLink();
		$adapters = $helper->getAdapters();
		$filters = $helper->getFilters();
		$activeFilter = $helper->getActiveFilter();

		// For now , we only refresh the page for user
		$refreshPageOnFilter = $type === 'user' ? true : false;

		$path = $this->getTemplatePath('search');

		require($path);
	}
}
