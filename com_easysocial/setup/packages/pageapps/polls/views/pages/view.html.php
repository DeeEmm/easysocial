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

class PollsViewPages extends SocialAppsView
{
	/**
	 * Displays the application output in the canvas.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($pageId = null, $docType = null)
	{
		$page = ES::page($pageId);

		// Check if the viewer is allowed here.
		if (!$page->canViewItem()) {
			return $this->redirect($page->getPermalink(false));
		}

		$this->setTitle('APP_POLLS_APP_TITLE');

		// Get app params
		$params = $this->app->getParams();

		$options = [
			'cluster_id' => $pageId,
			'cluster_type' => $page->getType()
		];

		$filter = $this->input->get('filter', 'all', 'string');

		$title = 'COM_EASYSOCIAL_PAGE_TITLE_ALL_POLLS';

		if ($filter == 'mine' && !$this->my->id) {
			$filter = 'all';
		}

		if ($filter == 'mine') {
			$options['user_id'] = $this->my->id;
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_MY_POLLS';
		}

		$model = ES::model('Polls');
		$rows = $model->getPolls($options);
		$pagination = $model->getPagination();

		$polls = [];

		foreach ($rows as $row) {
			$table = ES::table('polls');
			$table->bind($row);

			$polls[] = $table;
		}

		$helper = ES::viewHelper('Polls', 'List');

		$filterLinks = $helper->getFilterLinks();
		$showCreateButton = $helper->showCreateButton();
		$createButtonLink = $helper->getCreateButtonLink();

		$theme = ES::themes();
		$theme->set('createButtonLink', $createButtonLink);
		$theme->set('filterLinks', $filterLinks);
		$theme->set('showCreateButton', $showCreateButton);
		$theme->set('polls', $polls);
		$theme->set('params', $params);
		$theme->set('pagination', $pagination);
		$theme->set('filter', $filter);
		$theme->set('snackbar', false);
		$theme->set('user', false);
		$theme->set('cluster', $page);

		echo $theme->output('site/polls/default/default');
	}

	public function sidebar($moduleLib, $cluster)
	{
		$helper = ES::viewHelper('Polls', 'List');

		$options = [
			'cluster_id' => $cluster->id,
			'cluster_type' => $cluster->getType()
		];

		$filter = $helper->getCurrentFilter();
		$filters = $helper->getFilterLinks();
		$createButtonLink = $helper->getCreateButtonLink();
		$showCreateButton = $helper->showCreateButton();
		$showStatistics = $helper->showStatistics();
		$total = $helper->getTotalPolls($options);
		$myTotal = $helper->getTotalMyPolls($options);

		$theme = ES::themes();

		$theme->set('moduleLib', $moduleLib);
		$theme->set('cluster', $cluster);
		$theme->set('createButtonLink', $createButtonLink);
		$theme->set('showCreateButton', $showCreateButton);
		$theme->set('showStatistics', $showStatistics);
		$theme->set('filter', $filter);
		$theme->set('filters', $filters);
		$theme->set('total', $total);
		$theme->set('myTotal', $myTotal);

		echo $theme->output('site/polls/sidebar/default');
	}
}
