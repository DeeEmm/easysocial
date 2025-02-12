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

class EasySocialViewProfile extends EasySocialSiteView
{
	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isLockDown()
	{
		return true;
	}

	/**
	 * Displays a user profile to a 3rd person perspective.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::checkCompleteProfile();

		// Get the user's id.
		$id = $this->input->get('id', 0, 'int');

		// The current logged in user might be viewing their own profile.
		if ($id == 0) {
			$id = ES::user()->id;
		}

		// When the user tries to view his own profile but if he isn't logged in, throw a login page.
		if ($id == 0) {
			ES::requireLogin();
		}

		// Get the user's object.
		$user = ES::user($id);

		// If the user doesn't exist throw an error
		if (!$user->id) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// If the user is blocked or the user doesn't have community access
		if (($this->my->id != $user->id && $this->my->isBlockedBy($user->id, true)) || !$user->hasCommunityAccess()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// If the user is blocked, they should not be accessible
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		$userCustomRobots = $user->getUserRobots();

		// override the menu item robots data if the user has set the profile indexing from edit privacy page.
		ES::setMeta($userCustomRobots);

		// Determine if the current request is to load an app
		$appId = $this->input->get('appId', 0, 'int');

		// There is a possibility where the default profile page to render is the about section
		$defaultDisplay = $this->config->get('users.profile.display', 'timeline');
		$layout = $this->input->get('layout', '', 'cmd');

		if ($defaultDisplay == 'timeline' && !$layout && !$appId) {
			$layout = 'timeline';
		}

		if ($defaultDisplay == 'about' && $layout != 'timeline' && !$appId) {
			return $this->about();
		}

		// Set the page properties
		$this->page->title($this->string->escape($user->getName()));

		if ($defaultDisplay == 'about' && $layout == 'timeline') {
			$this->page->breadcrumb($this->string->escape($user->getName()), $user->getPermalink());
			$this->page->breadcrumb($this->string->escape('COM_ES_TIMELINE'));
		} else {
			$this->page->breadcrumb($this->string->escape($user->getName()));
		}

		// Let's test if the current viewer is allowed to view this profile.
		$canView = $user->isViewer() || $this->my->canView($user);

		$arguments = array(&$user, &$canView);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onDisplayProfile', $arguments);

		if (!$canView) {

			$facebook = ES::oauth('facebook');
			$return = base64_encode($user->getPermalink());

			$this->set('return', $return);
			$this->set('facebook', $facebook);
			$this->set('user', $user);

			return parent::display('site/profile/restricted');
		}

		// Apply metadata tags.
		$user->renderHeaders();

		// Assign badge only when other users are viewing the person's profile
		if (!$user->isViewer() && $this->my->id) {
			ES::badges()->log('com_easysocial', 'profile.view', $this->my->id, 'COM_EASYSOCIAL_PROFILE_VIEWED_A_PROFILE');
		}

		// Default contents
		$contents = '';

		// Load the app when necessary
		if ($appId) {
			$app = ES::table('App');
			$app->load($appId);

			return $this->app($user, $app);
		}

		// Should we filter stream items by specific app types
		$appType = $this->input->get('filterid', '', 'string');

		// Get the stream lib
		$stream = ES::stream();

		// If contents is still empty at this point, then we just get the stream items as the content
		if (!$contents) {

			// Retrieve user's stream
			$theme = ES::themes();

			// Get story
			$story = ES::story(SOCIAL_TYPE_USER);
			$story->target = $user->id;

			//lets get the sticky posts 1st
			$stickies = $stream->getStickies(array('userId' => $user->id, 'limit' => 0));

			if ($stickies) {
				$stream->stickies = $stickies;
			}

			$startLimit = $this->input->get('limitstart', 0, 'int');
			$streamOptions = array('userId' => $user->id, 'nosticky' => true, 'startlimit' => $startLimit);
			$stream->get($streamOptions);

			// Only registered users can access the story form
			if (!$this->my->guest) {
				$stream->story = $story;
			}

			// Set stream to theme
			$theme->set('stream', $stream);
			$contents = $theme->output('site/profile/stream');
		}

		$this->set('layout', $layout);
		$this->set('contents' , $contents);
		$this->set('user', $user);
		$this->set('stream', $stream);

		return parent::display('site/profile/default/default');
	}

	/**
	 * Renders the app section of a user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function app(&$user, $app)
	{
		// Check if the user has access to this app
		if (!$app->accessible($user->id)) {
			$this->info->set(false, JText::_('COM_ES_PROFILE_APP_IS_NOT_INSTALLED_BY_USER'), SOCIAL_MSG_ERROR);

			$redirect = ESR::profile(array('id' => $user->getAlias()), false);
			return $this->redirect($redirect);
		}

		// Set the page title
		$this->page->title(ES::string()->escape($user->getName()) . ' - ' . $app->get('title'));

		// to allow 3rd party app to support custom views,
		// we need to get from customView instead of hardcode the view to groups
		// when the site enable sef mode.
		$appView = $this->input->get('customView', 'profile', 'default');

		$lib = ES::apps();
		$contents = $lib->renderView(SOCIAL_APPS_VIEW_TYPE_EMBED, $appView, $app, array('userId' => $user->id));

		$this->set('active', $app->element);
		$this->set('user', $user);
		$this->set('contents', $contents);

		return parent::display('site/profile/app/default');
	}

	/**
	 * Displays the about section of a user
	 *
	 * @since	1.0
	 * @access	public
	 **/
	public function about($tpl = null)
	{
		// Get the user's id.
		$id = $this->input->get('id', 0, 'int');

		// The current logged in user might be viewing their own profile.
		if ($id == 0) {
			$id = ES::user()->id;
		}

		// When the user tries to view his own profile but if he isn't logged in, throw a login page.
		if ($id == 0) {
			ES::requireLogin();
		}

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Get the user's object.
		$user = ES::user($id);

		// If the user doesn't exist throw an error
		if (!$user->id) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// If the user is blocked or the user doesn't have community access
		if (($this->my->id != $user->id && $this->my->isBlockedBy($user->id)) || !$user->hasCommunityAccess()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// If the user is blocked, they should not be accessible
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		$userCustomRobots = $user->getUserRobots();

		// override the menu item robots data if the user has set the profile indexing from edit privacy page.
		ES::setMeta($userCustomRobots);

		$this->page->title($this->string->escape($user->getName()));

		$defaultDisplay = $this->config->get('users.profile.display', 'timeline');

		if ($defaultDisplay == 'timeline') {
			$this->page->breadcrumb($this->string->escape($user->getName()), $user->getPermalink());
			$this->page->breadcrumb($this->string->escape('COM_ES_ABOUT'));
		} else {
			$this->page->breadcrumb($this->string->escape($user->getName()));
		}

		// Let's test if the current viewer is allowed to view this profile.
		if (!$user->isViewer() && !$this->my->canView($user)) {

			$facebook = ES::oauth('facebook');
			$return = base64_encode($user->getPermalink());

			$this->set('return', $return);
			$this->set('facebook', $facebook);
			$this->set('user', $user);

			return parent::display('site/profile/restricted');
		}

		// Apply metadata tags.
		$user->renderHeaders();

		// We should generate a canonical link if user is viewing the about section and the default page is about
		if ($this->config->get('users.profile.display') == 'about') {
			$timelinePermalink = ESR::profile(array('id' => $user->getAlias(), 'layout' => 'timeline', 'external' => true), false);
			$this->page->canonical($timelinePermalink);
		}

		$usersModel = ES::model('Users');
		$steps = $usersModel->getAbout($user);

		// Determines if the viewer can edit the profile
		$canEdit = $user->id == $this->my->id;

		$arguments = array(&$user, &$canEdit);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onDisplayProfileAbout', $arguments);

		$this->set('canEdit', $canEdit);
		$this->set('steps', $steps);
		$this->set('user', $user);

		return parent::display('site/profile/about/default');
	}

	/**
	 * Responsible to output the edit profile layout
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function edit($errors = null)
	{
		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();
		ES::setMeta();

		// Set any messages here.
		$this->info->set($this->getMessage());

		// Load the language file from the back end.
		ES::language()->loadAdmin();

		$helper = $this->getHelper('edit');

		// initiate required user data
		$userData = $helper->initData();
		$user = $userData->user;
		$canEdit = $userData->canEdit;

		// If not allowed to edit, throw an error
		if (!$canEdit) {
			throw ES::exception('Not allowed', 500);
		}

		// Set page attributes
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_ACCOUNT_SETTINGS');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE', ESR::profile());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_ACCOUNT_SETTINGS');

		// Check if there are any errors in the session
		// If session contains error, means that this is from the ES::fields()->checkCompleteProfile();
		if (empty($errors)) {
			$session = JFactory::getSession();
			$errors = $session->get('easysocial.profile.errors', '', SOCIAL_SESSION_NAMESPACE);

			if (!empty($errors)) {
				ES::info()->set(false, JText::_('COM_EASYSOCIAL_PROFILE_PLEASE_COMPLETE_YOUR_PROFILE'), SOCIAL_MSG_ERROR);
			}
		}

		// Get user's installed apps
		$appsModel = ES::model('Apps');
		$userApps = $appsModel->getUserApps($user->id);

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		// Get list of steps for this user's profile type.
		$profile = $user->getProfile();

		// process the require data for steps.
		$stepsData = $helper->getStepsData($user, $errors);

		$workflow = $stepsData->workflow;
		$conditionalFields = $stepsData->conditionalFields;
		$allSteps = $stepsData->allSteps;
		$steps = $stepsData->steps;
		$isLastStep = $stepsData->isLastStep;

		// Get oauth clients
		$oauthClients = $helper->getOauthClients($user);
		$profilesCount = $helper->getProfileCount($user);
		$showVerificationLink = $helper->showVerificationLink();

		$this->set('user', $user);
		$this->set('activeStep', $activeStep);
		$this->set('conditionalFields', $conditionalFields);
		$this->set('oauthClients', $oauthClients);
		$this->set('profile', $profile);
		$this->set('workflow', $workflow);
		$this->set('steps', $steps);
		$this->set('apps', $userApps);
		$this->set('profilesCount', $profilesCount);
		$this->set('showVerificationLink', $showVerificationLink);
		$this->set('editLogic', $this->config->get('users.profile.editLogic'));
		$this->set('allSteps', $allSteps);
		$this->set('isLastStep', $isLastStep);

		return parent::display('site/profile/edit/default');
	}


	/**
	 * Responsible to output the mfa layout
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function mfa($errors = null)
	{
		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();
		ES::setMeta();

		if (!$this->my->canConfigureMFA()) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		$helper = $this->getHelper('edit');
		// $activeTab = $helper->getActiveTab();

		ES::setMeta();

		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_MANAGE_BLOCKED_USERS');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE', ESR::profile());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PRIVACY_SETTINGS');

		// $this->set('blockedUsers', $blockedUsers);
		// $this->set('activeTab', $activeTab);

		$mfaForms = $this->my->getMFAInterfaceUI();

		$this->set('mfaForms', $mfaForms);


		return parent::display('site/profile/mfa/default');
	}


	/**
	 * profile switch page.
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function switchProfile()
	{
		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();

		// Set any messages here.
		$this->info->set($this->getMessage());

		// Load the language file from the back end.
		ES::language()->loadAdmin();

		if (!$this->my->canSwitchProfile()) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_NOT_ALLOWED'), SOCIAL_MSG_ERROR);

			$redirect = ESR::profile(array('layout' => 'edit'), false);
			return $this->redirect($redirect);
		}

		// Get list of steps for this user's profile type.
		$profile = $this->my->getProfile();

		$profileModel = ES::model('Profiles');
		$profilesCount = $profileModel->getTotalProfiles(['switchable_in' => '1']);

		if ($profilesCount <= 1) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_NO_PROFILE_TO_SWITCH'), SOCIAL_MSG_ERROR);

			$redirect = ESR::profile(array('layout' => 'edit'), false);
			return $this->redirect($redirect);
		}

		$options = array('state'	=> SOCIAL_STATE_PUBLISHED,
						'ordering' => 'ordering',
						'limit' => SOCIAL_PAGINATION_NO_LIMIT,
						'excludeProfileIds' => array($profile->id),
						'switchable_in' => true
					);

		$profiles = $profileModel->getProfiles($options);

		// Set the page title
		$this->page->title(JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_SELECT_PROFILE_TYPE_TITLE'));
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE', ESR::profile());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE_SWITCH');

		// The first profile selection page is always the first in the progress bar.
		$this->set('currentStep', SOCIAL_REGISTER_SELECTPROFILE_STEP);
		// $this->set('profileId', $profileId);
		$this->set('profiles', $profiles);

		return parent::display('site/profile/switch/default');

		return;
	}

	/**
	 * profile switch edit page.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function switchProfileEdit()
	{
		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();

		// Set any messages here.
		$this->info->set($this->getMessage());

		// Load the language file from the back end.
		ES::language()->loadAdmin();

		if (!$this->my->canSwitchProfile()) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_NOT_ALLOWED'), SOCIAL_MSG_ERROR);

			$redirect = ESR::profile(array('layout' => 'edit'), false);
			return $this->redirect($redirect);
		}

		$helper = $this->getHelper('switch');

		$profile = $helper->getNewProfileType();

		if (!$profile->id) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_INVALID_PROFILE_TYPE'), SOCIAL_MSG_ERROR);

			$redirect = ESR::profile(array('layout' => 'edit'), false);
			return $this->redirect($redirect);
		}

		// Check if there are any errors in the session
		// If session contains error, means that this is from the ES::fields()->checkCompleteProfile();
		if (empty($errors)) {
			$session = JFactory::getSession();
			$errors = $session->get('easysocial.profile.errors', '', SOCIAL_SESSION_NAMESPACE);

			if (!empty($errors)) {
				ES::info()->set(false, JText::_('COM_EASYSOCIAL_PROFILE_PLEASE_COMPLETE_YOUR_PROFILE'), SOCIAL_MSG_ERROR);
			}
		}

		// Set page attributes
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_ACCOUNT_SETTINGS');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE', ESR::profile());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_ACCOUNT_SETTINGS');

		// Get list of steps for this user's profile type.
		$currentProfile = $this->my->getProfile();

		// process the require data for steps.
		$stepsData = $helper->getStepsData($errors);

		$conditionalFields = $stepsData->conditionalFields;
		$steps = $stepsData->steps;

		$this->set('profile', $profile);
		$this->set('steps', $steps);
		$this->set('currentProfile', $currentProfile);
		$this->set('conditionalFields', $conditionalFields);

		return parent::display('site/profile/switch/edit');

	}

	/**
	 * Renders the edit alerts form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function editNotifications()
	{
		ES::requireLogin();
		ES::checkCompleteProfile();
		ES::setMeta();

		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_NOTIFICATION_SETTINGS');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE', ESR::profile());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_NOTIFICATION_SETTINGS');

		// Render custom alert settings from the app
		$helper = $this->getHelper('notifications');

		$customAlerts = $helper->getCustomAlerts();

		// Hardcode the groups
		$groups = $helper->getGroups();

		// filter the alerts to remove the alerts for those disabled features. #717
		$filteredAlerts = $helper->getFilterAlerts();

		$activeTab = $helper->getActiveTab();

		$this->set('activeTab', $activeTab);
		$this->set('customAlerts', $customAlerts);
		$this->set('groups', $groups);
		$this->set('alerts', $filteredAlerts);

		parent::display('site/profile/editnotifications/default');
	}

	/**
	 * Allows caller to request to be submitted for verification
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function submitVerification()
	{
		ES::setMeta();

		$verification = ES::verification();

		if (!$verification->canRequest($this->my->id, SOCIAL_TYPE_USER)) {

			$verify = $verification->hasRequested();

			// Display proper message if user has already request for verification previously.
			if ($verify->state) {
				$this->setMessage($verify->message, 'error');
				$this->info->set($this->getMessage());
				return $this->redirect(ESR::dashboard());
			}

			throw ES::exception('This feature is not available', 500);
		}

		parent::display('site/profile/submitverification/default');
	}

	/**
	 * GDPR download
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function download()
	{
		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();
		ES::setMeta();

		if (!$this->config->get('users.download.enabled')) {
			$this->setMessage('COM_ES_RESTRICTED_AREA_DESC', 'error');
			$this->info->set($this->getMessage());

			$redirect = ESR::profile(['layout' => 'edit'], false);
			return $this->redirect($redirect);
		}

		// Set page properties
		$this->page->title('COM_ES_PAGE_TITLE_GDPR_DOWNLOAD');
		$this->page->breadcrumb('COM_ES_PAGE_TITLE_GDPR_DOWNLOAD');

		// Check if this user has any download request or not
		$download = ES::table('Download');
		$download->load(array('userid' => $this->my->id));

		$this->set('download', $download);

		return parent::display('site/profile/download/default');
	}

	/**
	 * Post processing after saving verification
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function saveVerification()
	{
		$this->info->set($this->getMessage());

		$redirect = ESR::profile(array(), false);

		return $this->app->redirect($redirect);
	}

	/**
	 * Renders the edit privacy form
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function editPrivacy()
	{
		ES::requireLogin();
		ES::checkCompleteProfile();

		if (!$this->config->get('privacy.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		ES::setMeta();

		// Set page attributes
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_PRIVACY_SETTINGS');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE', ESR::profile());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PRIVACY_SETTINGS');

		$helper = $this->getHelper('privacy');

		// Get user's privacy
		$privacy = $helper->getPrivacy();

		$activeTab = $helper->getActiveTab();

		// Retrieve the current user profile robots indexing value
		$robots = $this->my->getUserRobots();

		$this->set('activeTab', $activeTab);
		$this->set('privacy', $privacy);
		$this->set('robots', $robots);

		return parent::display('site/profile/editprivacy/default');
	}

	/**
	 * Renders the block users page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function manageBlockedUsers()
	{
		ES::requireLogin();
		ES::checkCompleteProfile();

		if (!$this->config->get('users.blocking.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		$helper = $this->getHelper('privacy');
		$activeTab = $helper->getActiveTab();

		// Get a list of blocked users for this user
		$blockedUsers = $helper->getBlockedUsers();

		ES::setMeta();

		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_MANAGE_BLOCKED_USERS');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PROFILE', ESR::profile());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_PRIVACY_SETTINGS');

		$this->set('blockedUsers', $blockedUsers);
		$this->set('activeTab', $activeTab);

		return parent::display('site/profile/manageblockedusers/default');
	}

	/**
	 * Handle save profiles.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save()
	{
		$this->info->set($this->getMessage());

		$task = $this->input->getString('task');
		$userId = $this->input->get('userId', '', 'int');
		$nextStepId = $this->input->get('nextStepId', 0, 'int');
		$isLastStep = $this->input->get('isLastStep', 0, 'int');

		$options = array();

		$saveLogic = $this->config->get('users.profile.editLogic', 'default');
		$user = ES::user($userId);

		if (($saveLogic == 'steps' && ($isLastStep || $task == 'saveclose')) || ($saveLogic != 'steps' && $userId && $task != 'save')) {
			return $this->redirect($user->getPermalink(false));
		}

		if ($task == 'save') {
			$options['layout'] = 'edit';

			if ($userId) {
				$options['id'] = $user->getAlias();
			}

			if ($nextStepId) {
				$options['activeStep'] = $nextStepId;
			}
		}

		$this->redirect(ESR::profile($options, false));
	}

	/**
	 * Handle save notification.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function saveNotification()
	{
		$this->info->set($this->getMessage());

		// Remember active tab
		$active = $this->input->get('activeTab', '', 'cmd');

		$return = ESR::profile(array('layout' => 'editNotifications', 'activeTab' => $active), false);
		$this->redirect($return);
	}


	/**
	 * Handle save privacy.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function savePrivacy()
	{
		$this->info->set($this->getMessage());

		// Remember active tab
		$active = $this->input->get('activeTab', '', 'cmd');

		$return = ESR::profile(array('layout' => 'editPrivacy', 'activeTab' => $active), false);

		$this->redirect($return);
	}


	/**
	 * Allows viewer to download a file from the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function downloadFile()
	{
		// Load the file object
		$id = $this->input->get('fileid', 0, 'int');
		$file = ES::table('File');
		$file->load($id);

		if (!$file->id || !$id) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Add points for the user when they upload a file.
		ES::points()->assign('files.download', 'com_easysocial', $this->my->id);

		// @TODO: Check for the privacy.

		$file->download();
		exit;
	}

	/**
	 * Post process after removing an avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeAvatar($user)
	{
		$this->info->set($this->getMessage());

		$return = $user->getPermalink();

		$this->redirect($return);
	}


	/**
	 * Post processing after the user wants to delete their account
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		$this->info->set($this->getMessage());

		$return = ESR::dashboard(array(), false);

		$this->redirect($return);
	}

	/**
	 * Post processing after the user switch profile type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function completeSwitch()
	{
		$this->info->set($this->getMessage());

		$return = ESR::profile(array('layout' => 'edit'), false);
		$this->redirect($return);
	}

	/**
	 * Allows viewer to preview the attached file
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function preview()
	{
		// Get the file id from the request
		$fileId = $this->input->get('fileid', null, 'int');

		$file = ES::table('File');
		$file->load($fileId);

		if (!$file->id || !$fileId) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		$file->preview();
		exit;
	}

	/**
	 * Retrieves the user's timeline
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getStream($stream, $story)
	{
		// If there's an error throw it back to the caller.
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$theme = ES::themes();
		$theme->set('stream', $stream);
		$theme->set('story', $story);

		$contents = $theme->output('site/profile/stream');

		$data = new stdClass();
		$data->contents = $contents;

		echo json_encode($data);exit;
	}
}
