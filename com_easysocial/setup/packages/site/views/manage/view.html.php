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

ES::import('site:/views/views');

class EasySocialViewManage extends EasySocialSiteView
{
	public function display($tpl = null)
	{
		return $this->clusters($tpl);
	}

	/**
	 * Renders the cluster moderation layout
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function clusters($tpl = null)
	{
		ES::requireLogin();
		ES::checkCompleteProfile();
		ES::setMeta();

		if (!$this->config->get('pages.enabled') && !$this->config->get('events.enabled') && !$this->config->get('groups.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Ensure that the user's acl is allowed to manage pending items
		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('pendings.manage')) {
			$this->setMessage(JText::_('COM_ES_MANAGE_NOT_ALLOWED_TO_VIEW'), ES_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirectToView('dashboard');
		}

		$filter = $this->input->get('filter', 'event', 'cmd');
		$options = ['filter' => $filter];

		$model = ES::model('Clusters');
		$clusters = $model->getPendingModeration($options);

		$pendingCounters = array();
		$pendingCounters['event'] = $model->getTotalPendingModeration(['filter' => 'event']);
		$pendingCounters['group'] = $model->getTotalPendingModeration(['filter' => 'group']);
		$pendingCounters['page'] = $model->getTotalPendingModeration(['filter' => 'page']);

		// Get pagination
		$pagination	= $model->getPagination();

		// Set additional params for the pagination links
		$pagination->setVar('view', 'manage');
		$pagination->setVar('layout', 'clusters');

		// Set page attributes
		$this->page->title('COM_ES_PAGE_TITLE_ITEMS_MODERATION');
		$this->page->breadcrumb('COM_ES_PAGE_TITLE_ITEMS_MODERATION');

		$this->set('clusters', $clusters);
		$this->set('filter', $filter);
		$this->set('pagination', $pagination);
		$this->set('pendingCounters', $pendingCounters);

		return parent::display('site/manage/clusters/default');
	}

	/**
	 * Process parent event
	 *
	 * @since   3.2.12
	 * @access  public
	 */
	public function approveEventHasRecurringData($event = null)
	{
		$redirectionURL = ESR::manage(array('layout' => 'clusters', 'filter' => 'event'));

		if (!$event || !isset($event->id)) {
			return $this->redirect($redirectionURL);
		}

		$params = $event->getParams();

		if (!$params->exists('recurringData')) {
			return $this->redirect($redirectionURL);
		}

		$eventDate = ES::date($event->getMeta('start'), false);

		// Get the recurring schedule
		$schedule = ES::model('Events')->getRecurringSchedule(array(
			'eventStart' => $eventDate,
			'end' => $params->get('recurringData')->end,
			'type' => $params->get('recurringData')->type,
			'daily' => $params->get('recurringData')->daily
		));

		if (empty($schedule)) {
			return $this->redirect($redirectionURL);
		}

		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'), ESR::events());
		$this->page->breadcrumb($event->getName(), $event->getPermalink());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_RECURRING_EVENT'));

		$this->page->title(JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_RECURRING_EVENT'));

		// count total of recurring events
		$totalRecurringEvents = count($schedule);

		// determine this process from which page
		$event->fromManagePage = true;

		$this->set('totalRecurringEvents', $totalRecurringEvents);
		$this->set('schedule', $schedule);
		$this->set('event', $event);

		echo parent::display('site/events/create/recurring');
	}
}
