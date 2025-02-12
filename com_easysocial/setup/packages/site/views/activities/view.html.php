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

class EasySocialViewActivities extends EasySocialSiteView
{
	public function display($tpl = null)
	{
		ES::requireLogin();
		ES::checkCompleteProfile();
		ES::setMeta();

		if (!$this->config->get('activity.logs.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		$helper = $this->getHelper('logs');

		// Get the necessary attributes from the request
		$filterType = $helper->getActiveFilter();
		$active = $filterType;

		// Default title
		$title = JText::sprintf('COM_EASYSOCIAL_ACTIVITY_ITEM_TITLE', ucfirst($filterType));
		switch ($filterType) {
			case 'hiddenapp':
				$title = 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS';
				break;

			case 'hidden':
				$title = 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTIVITIES';
				break;

			case 'hiddenactor':
				$title = 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS';
				break;

			case 'all':
				$title = 'COM_EASYSOCIAL_ACTIVITY_ALL_ACTIVITIES';
				break;

			default:
				break;
		}

		// Set the page title
		$this->page->title($title);

		// Set the page breadcrumb
		$this->page->breadcrumb($title);


		$data = $helper->getActivities($filterType);
		$filterType = $data->filterType;
		$activities = $data->activities;
		$nextLimit = $data->nextLimit;

		$apps = $helper->getApps();

		$this->set('active', $active);
		$this->set('title', JText::_($title));
		$this->set('apps', $apps);
		$this->set('activities', $activities);
		$this->set('nextlimit', $nextLimit);
		$this->set('filtertype', $filterType);
		$this->set('user', $this->my);

		echo parent::display('site/activities/default/default');
	}

}
