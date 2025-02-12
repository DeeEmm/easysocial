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

class TasksViewProfile extends SocialAppsView
{
	public function display($userId = null, $docType = null)
	{
		$user = ES::user($userId);

		$params = $this->getParams();
		$hidePersonal = $params->get('hide_personal', true);

		if ($this->my->id == $userId) {
			$hidePersonal = false;
		}

		$helper = ES::viewHelper('tasks', 'list');
		$data = $helper->getData($user->id, $hidePersonal);

		$tasks = $data->tasks;
		$counters = $data->counters;

		$this->set('hidePersonal', $hidePersonal);
		$this->set('user', $user);
		$this->set('counters', $counters);
		$this->set('tasks', $tasks);

		echo parent::display('profile/default');
	}

	public function sidebar($moduleLib, $user)
	{
		$helper = ES::viewHelper('tasks', 'list');

		$params = $this->getParams();
		$hidePersonal = $params->get('hide_personal', true);

		if ($this->my->id == $user->id) {
			$hidePersonal = false;
		}

		$data = $helper->getData($user->id, $hidePersonal);

		$tasks = $data->tasks;
		$counters = $data->counters;

		$appFilters = array();
		$groupTaskAppEnabled = false;
		$eventTaskAppEnabled = false;

		$model = ES::model('Apps');

		// Determine whether those cluster task app enabled or not
		$isAppEnabled = $model->isAppEnabled('tasks', array(SOCIAL_APPS_GROUP_GROUP, SOCIAL_APPS_GROUP_EVENT));

		foreach ($isAppEnabled as $app) {

			if ($app->group == SOCIAL_APPS_GROUP_GROUP && $app->state) {
				$groupTaskAppEnabled = true;
			}

			if ($app->group == SOCIAL_APPS_GROUP_EVENT && $app->state) {
				$eventTaskAppEnabled = true;
			}
		}

		$this->set('groupTaskAppEnabled', $groupTaskAppEnabled);
		$this->set('eventTaskAppEnabled', $eventTaskAppEnabled);
		$this->set('counters', $counters);
		$this->set('hidePersonal', $hidePersonal);
		$this->set('moduleLib', $moduleLib);
		$this->set('user', $user);
		$this->set('cluster', false);

		echo parent::display('themes:/site/tasks/default/sidebar');
	}
}
