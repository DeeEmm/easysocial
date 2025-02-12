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

require_once(__DIR__ . '/abstract.php');

class SocialSidebarPolls extends SocialSidebarAbstract
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
		$helper = ES::viewHelper('Polls', 'List');
		$filter = $helper->getCurrentFilter();
		$filters = $helper->getFilterLinks();
		$createButtonLink = $helper->getCreateButtonLink();
		$showCreateButton = $helper->showCreateButton();
		$showStatistics = $helper->showStatistics();
		$cluster = $helper->getCluster();

		$theme = ES::themes();

		if ($showStatistics) {
			$total = $helper->getTotalMyPolls();
		}

		if (!$showStatistics) {
			$total = $helper->getTotalPolls();
			$myTotal = $helper->getTotalMyPolls();

			$theme->set('myTotal', $myTotal);
		}

		$theme->set('moduleLib', $this->lib);
		$theme->set('cluster', $cluster);
		$theme->set('createButtonLink', $createButtonLink);
		$theme->set('showCreateButton', $showCreateButton);
		$theme->set('showStatistics', $showStatistics);
		$theme->set('filter', $filter);
		$theme->set('filters', $filters);
		$theme->set('total', $total);

		echo $theme->output('site/polls/sidebar/default');
	}
}