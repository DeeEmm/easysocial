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

class SocialSidebarProfile extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$allowedLayouts = array('edit', 'editPrivacy', 'editNotifications', 'switchProfileEdit', 'about', 'manageBlockedUsers', 'mfa');
		$layout = $this->input->get('layout', '', 'cmd');
		$appId = $this->input->get('appId', 0, 'int');

		if ($layout && $this->inArrayCaseInsensitive($layout, $allowedLayouts)) {
			$method = 'render' . ucfirst($layout);
			return call_user_func_array(array($this, $method), array());
		}

		// Determine whether this is about page or not
		$isTimelinePage = $this->isTimelinePage(SOCIAL_TYPE_USERS);

		if (!$isTimelinePage && !$appId) {
			return call_user_func_array(array($this, 'renderAbout'), array());
		}

		// As we know, profile views must have an id,
		$id = $this->input->get('id', null, 'int');

		// Or the user is viewing their own profile
		if ($id === 0) {
			$id = null;
		}

		// Or the user is viewing their own profile
		$user = ES::user($id);

		if (!$user->id) {
			return;
		}

		// check if this is an app view or not.
		if ($appId) {
			$app = ES::table('App');
			$app->load($appId);

			return $this->app($app, $user, 'profile');
		}

		$path = $this->getTemplatePath('profile');

		require($path);
	}

	public function renderAbout()
	{
		$id = $this->input->get('id', null, 'int');

		// Or the user is viewing their own profile
		if ($id === 0) {
			$id = null;
		}

		$user = ES::user($id);

		if (!$user->id) {
			return;
		}

		$path = $this->getTemplatePath('profile_about');

		require($path);
	}

	public function renderSwitchProfileEdit()
	{
		$helper = ES::viewHelper('profile', 'switch');

		// process the require data for steps.
		$stepsData = $helper->getStepsData();

		$conditionalFields = $stepsData->conditionalFields;
		$steps = $stepsData->steps;

		$path = $this->getTemplatePath('profile_switch');
		require($path);
	}

	public function renderEdit()
	{
		$helper = ES::viewHelper('profile', 'edit');

		// initiate user required data
		$userData = $helper->initData();

		$user = $userData->user;
		$canEdit = $userData->canEdit;

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		// Get list of steps for this user's profile type.
		$profile = $user->getProfile();

		// process the require data for steps.
		$stepsData = $helper->getStepsData($user);

		$workflow = $stepsData->workflow;
		$conditionalFields = $stepsData->conditionalFields;
		$allSteps = $stepsData->allSteps;
		$steps = $stepsData->steps;
		$isLastStep = $stepsData->isLastStep;

		// Get oauth clients
		$oauthClients = $helper->getOauthClients($user);
		$profilesCount = $helper->getProfileCount($user);
		$showVerificationLink = $helper->showVerificationLink();

		$path = $this->getTemplatePath('profile_edit');
		require($path);
	}

	/**
	 * Renders the user privcy edit output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderEditPrivacy()
	{
		// Get helper proxy for events view
		$helper = ES::viewHelper('profile', 'privacy');

		$privacy = $helper->getPrivacy();
		$activeTab = $helper->getActiveTab();

		$path = $this->getTemplatePath('profile_privacy');
		require($path);
	}

	/**
	 * Renders the user notificaation edit output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderEditNotifications()
	{
		// Get helper proxy for events view
		$helper = ES::viewHelper('profile', 'notifications');

		$customAlerts = $helper->getCustomAlerts();
		$groups = $helper->getGroups();
		$alerts = $helper->getFilterAlerts();
		$activeTab = $helper->getActiveTab();

		$path = $this->getTemplatePath('profile_notifications');
		require($path);
	}

	/**
	 * Renders the user notificaation edit output from the sidebar
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderManageBlockedUsers()
	{
		// Get helper proxy for events view
		$helper = ES::viewHelper('profile', 'privacy');

		$path = $this->getTemplatePath('profile_blockusers');
		require($path);
	}

	/**
	 * Renders the user notificaation edit output from the sidebar
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderMfa()
	{
		// Get helper proxy for events view
		$helper = ES::viewHelper('profile', 'edit');

		$path = $this->getTemplatePath('profile_mfa');
		require($path);
	}
}
