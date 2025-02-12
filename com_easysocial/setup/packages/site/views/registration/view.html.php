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

class EasySocialViewRegistration extends EasySocialSiteView
{
	/**
	 * Determines if the registration page should be visible
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isLockDown()
	{
		if ($this->config->get('general.site.lockdown.registration')) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the registration page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Clear cache as soon as the user registers on the site.
		$cache = JFactory::getCache();
		$cache->clean('page');
		$cache->clean('_system');

		// Do not allow users to proceed if registrations are disabled
		if (!$this->config->get('registrations.enabled')) {
			$this->info->set(JText::_('COM_EASYSOCIAL_ERROR_REGISTRATION_DISABLED'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::login(array(), false));
		}

		ES::setMeta();

		// Do not allow registered users to create account.
		$user = ES::user();

		// Checks if the user is already registered.
		if ($user->isRegistered()) {

			$id = $this->input->get('invite', '', 'int');

			// if there is invite via email link from the cluster, it should redirect to the cluster instead of the dashboard
			if ($id) {
				$table = ES::table('friendinvite');
				$table->load($id);

				if ($table->id && $table->isValidInvitation($user->email)) {
					$cluster = ES::cluster($table->utype, $table->uid);

					// if the link is 1st time to be accessed
					if (!$table->registered_id) {

						$table->registered_id = $user->id;
						$table->store();

						// Make them both friends
						$table->makeFriends();

						// If this invitation to cluster, we add them as member
						if ($table->isCluster()) {

							$profile = $user->getProfile();

							// Add the user as a member
							$cluster->createMember($user->id, true, $profile->getRegistrationType());
						}
					}

					return $this->redirect($cluster->getPermalink());
				}
			}

			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Retrieve profile id from the query string
		$profileId = $this->input->get('profile_id', 0, 'int');

		// If there's a profile id, we need to redirect them to the appropriate step
		if ($profileId) {
			$redirectOptions = array('controller' => 'registration', 'task' => 'selectType', 'profile_id' => $profileId);
			$redirection = ESR::registration($redirectOptions, false);

			return $this->redirect($redirection);
		}

		// Detect for an existing registration session.
		$session = JFactory::getSession();

		// Check if user was invited by another user via link sharing
		$referralId = $this->input->get('rid', 0, 'default');

		if ($referralId) {
			$table = ES::table('Users');
			$exists = $table->load(['affiliation_id' => $referralId]);

			if ($exists) {
				$session->set('referral', (int) $table->user_id, SOCIAL_SESSION_NAMESPACE);
			}
		}

		// Check if this user was invited by someone else
		$inviteId = $this->input->get('invite', 0, 'int');

		if ($inviteId) {
			$session->set('invite', $inviteId, SOCIAL_SESSION_NAMESPACE);
		}

		// Check if there is a quick parameter or not and set it into the session
		$session->set('quick', $this->input->get('quick', false, 'bool'), SOCIAL_SESSION_NAMESPACE);

		// Load up necessary model and tables.
		$registration	= ES::table('Registration');

		// Purge expired session data for registrations.
		$model = ES::model('Registration');
		$model->purgeExpired();

		// If user doesn't have a record in registration yet, we need to create this.
		if (!$registration->load($session->getId())) {
			$registration->set('session_id', $session->getId());
			$registration->set('created', ES::get('Date')->toMySQL());
			$registration->set('profile_id', $profileId);

			if (!$registration->store()) {
				$this->setError($registration->getError());
				return false;
			}
		}

		// If there is only 1 profile type, we don't really need to show the profile type selection
		$profileModel = ES::model('Profiles');
		$options = array('state'	=> SOCIAL_STATE_PUBLISHED,
							'ordering' => 'ordering',
							'limit' => SOCIAL_PAGINATION_NO_LIMIT,
							'totalUsers' => $this->config->get('registrations.profiles.usersCount'),
							'validUser' => false,
							'registration' => true,

					);

		$profiles = $profileModel->getProfiles($options);

		// Add the "users" to the profiles
		foreach ($profiles as $profile) {
			$includeAdmin = $this->config->get('users.listings.admin');
			$profile->users = $profileModel->getMembers($profile->id, array('limit' => 10, 'randomize' => true, 'includeAdmin' => $includeAdmin));
		}

		// If there's only 1 profile type, we should just ignore this step and load the steps page.
		if (count($profiles) == 1) {

			$profile = $profiles[0];

			// Store the profile type id into the session.
			$session->set('profile_id', $profile->id, SOCIAL_SESSION_NAMESPACE);

			// Set the current profile type id.
			$registration->profile_id 	= $profile->id;

			// When user accesses this page, the following will be the first page
			$registration->step 	= 1;

			// Add the first step into the accessible list.
			$registration->addStepAccess(1);

			// Let's save this into a temporary table to avoid missing data.
			$registration->store();

			$this->steps();
			return;
		}

		// Set the page title
		$this->page->title(JText::_('COM_EASYSOCIAL_PAGE_TITLE_REGISTRATION_SELECT_PROFILE'));

		// Try to retrieve the profile id from the session.
		$profileId  = $session->get('profile_id', $profileId, SOCIAL_SESSION_NAMESPACE);

		$useDropdownList = $this->config->get('registrations.profiles.selection.layout') == 'dropdown' ? true : false;

		// The first profile selection page is always the first in the progress bar.
		$this->set('currentStep', SOCIAL_REGISTER_SELECTPROFILE_STEP);
		$this->set('profileId', $profileId);
		$this->set('profiles', $profiles);
		$this->set('useDropdownList', $useDropdownList);

		return parent::display('site/registration/default/default');
	}

	/**
	 * Allows caller to direct users to this page to request for a request token
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function oauthRequestToken()
	{

		// Get allowed clients
		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Get the current client.
		$client = $this->input->get('client', '', 'word');

		if (!in_array($client, $allowedClients)) {
			return $this->exception('COM_EASYSOCIAL_OAUTH_INVALID_CLIENT');
		}

		// Get the consumer
		$consumer = ES::oauth($client);

		// Get the callback url
		$callback = $this->input->get('callback', '', 'default');
		$callback = base64_decode($callback);

		// Retrieve the callback URL directly on frontend for debugging
		$debug = $this->input->get('debug', false, 'bool');

		if ($debug) {
			// For Twitter callback URL do not need extra URL query string e.g. ?return=xxx
			echo $callback; die;
		}

		// Get the authorization url of the respective oauth client
		$redirect = $consumer->getAuthorizationURL($callback);

		return $this->redirect($redirect);
	}

	/**
	 * This is the first entry point when the social site redirects back to this callback.
	 * It is responsible to close the popup and redirect to the appropriate url.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function oauthDialog($isSharer = false)
	{
		// Get allowed clients
		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Get the current client.
		$oauthClient = $this->input->get('client', '', 'word');

		if (!in_array($oauthClient, $allowedClients)) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_OAUTH_INVALID_OAUTH_CLIENT_PROVIDED', $oauthClient));
		}

		// Joomla 3 - using pop up dialog method except Facebook
		// Joomla 4 - no more pop up dialog method
		// Behavior: using pop up dialog method only need to close the window automatically
		$isJoomla3 = ES::isJoomla3();
		$isFacebookOauthClient = $oauthClient === 'facebook' ? true : false;

		$this->set('isJoomla3', $isJoomla3);
		$this->set('isFacebookOauthClient', $isFacebookOauthClient);

		// If the user cancels the oauth sign up, we need to handle this properly
		$denied = $this->input->get('denied', '');
		$code = $this->input->get('code', '', 'default');

		if ($denied || ($oauthClient == 'linkedin' && !$code)) {
			$url = ESR::dashboard(array(), false);
			$this->set('redirect', $url);

			return parent::display('site/oauth/popup');
		}

		// Get the oauth client object.
		$client = ES::oauth($oauthClient);

		// Get the access tokens first
		$verifier = $this->input->get('oauth_verifier', '', 'default');

		// since apple only return user object on the first request, we need to store it somewhere first
		if ($oauthClient == 'apple') {
			$userObj = json_decode($this->input->get('user', '', 'default'));
			$userObject = new stdClass;

			if ($userObj && isset($userObj->name)) {
				$userObject->name = $userObj->name;
				$userObject->fullname = $userObj->name->firstName . ' ' . $userObj->name->lastName;
				$userObject->email = $userObj->email;
			}

			$client->setUserObj($userObject);
		}

		// There is a possibility that the user is already logged in
		try {
			$access = $client->getAccess($verifier, $code);
		} catch (Exception $e) {
			$this->info->set($e->getMessage(), SOCIAL_MSG_ERROR);

			$url = ESR::dashboard(array(), false);
			$this->set('redirect', $url);

			return parent::display('site/oauth/popup');
		}

		// Set the access token on the session
		$session = JFactory::getSession();
		$session->set($oauthClient . '.access', $access, SOCIAL_SESSION_NAMESPACE);
		$session->set($oauthClient . '.token', $access->token, SOCIAL_SESSION_NAMESPACE);
		$session->set($oauthClient . '.secret', $access->secret, SOCIAL_SESSION_NAMESPACE);

		// Some clients requires us to set the access token
		$client->setAccess($access->token, $access->secret);

		// Determines if the user was already registered on the site previously
		$isRegistered = $client->isRegistered();

		if (!$isRegistered) {

			// Try get return value from the url
			$returnUrl = $this->input->get('return', '', 'base64');

			// if there doesn't set any redirect URL then refer back to the profile type
			if (!$returnUrl) {

				// Retrieve the default social profile type
				$profileType = ES::oauth()->getDefaultProfile($oauthClient);

				// Load the profile object.
				$profile = ES::table('Profile');
				$profile->load($profileType->id);

				$params = $profile->getParams();
				$registerRedirection = $params->get('registration_success');

				// If the redirection set it to default behaviour, it will respect the global Login Redirection setting
				// If the global login redirection setting also set to stay on the same page.
				if (!$registerRedirection || $registerRedirection == 'null') {

					// Try get return value from the session
					// Currently only store the Facebook login redirection on session
					$returnUrl = $session->get('oauth.login.redirection', '', SOCIAL_SESSION_NAMESPACE);

					// Clear off the session once it's been picked up.
					$session->clear('oauth.login.redirection', SOCIAL_SESSION_NAMESPACE);

				} else {

					// Retrieve the redirection menu link based on the setting
					$link = ESR::getMenuLink($registerRedirection, true);

					// Parse to SEF link
					$link = ESR::_($link);

					// Ensure that the return url is always encoded correctly.
					$returnUrl = base64_encode($link);
				}
			}

			$redirect = ESR::registration(array('layout' => 'oauth', 'client' => $oauthClient, 'returnUrl' => $returnUrl), false);

			$this->set('redirect', $redirect);

			return parent::display('site/oauth/popup');
		}

		// Here onwards, we know the user was already registered previously
		// We need to update the token
		$client->updateToken();

		// Log the user in
		$loginState = $client->login();

		// login failed. lets show proper message.
		if ($loginState === false) {

			$session = JFactory::getSession();

			$msgObj = new stdClass();
			$msgObj->message = JText::_('JGLOBAL_AUTH_NO_USER');
			$msgObj->type = 'error';

			//save messsage into session
			$session->set('social.message.oauth', $msgObj, 'SOCIAL.MESSAGE.OAUTH');
		}


		// let get user data again.
		$user = ES::user();
		$url = false;

		// @TODO:: here we will redirect user to our password reset page. awesome possum.
		if ($loginState && $user->require_reset) {
			$url = ESR::account(array('layout' => 'requirePasswordReset'), false);
		} else {

			$menuId = $this->config->get('general.site.login');

			// Determine which URL to redirect the user to based on the settings
			if ($menuId != 'null' && !empty($menuId)) {
				$url = ESR::getMenuLink($menuId);
				$url = ESR::_($url);
			}

			if ($url === false) {
				// Default URL redirection
				$url = ESR::dashboard(array(), false);

				// Determine if there a referer URL
				$callback = ESR::referer();

				// We do not want it to redirect to facebook page
				if (strpos($callback, 'https://www.facebook.com/') !== false) {
					$callback = false;
				}

				// We do not want it to redirect to mobile facebook page
				if (strpos($callback, 'https://m.facebook.com/') !== false) {
					$callback = false;
				}

				// We do not want it to redirect to twitter page
				if (strpos($callback, 'https://api.twitter.com/') !== false) {
					$callback = false;
				}

				// We do not want it to redirect to the Google or youtube page, for some reason some of the customer site will redirect to these page
				if (strpos($callback, 'https://accounts.google.com') !== false || strpos($callback, 'https://accounts.youtube.com') !== false) {
					$callback = false;
				}

				// We check if there is a return query in the callback
				if ($callback) {
					$parts = parse_url($callback);

					if (isset($parts['query']) && $parts['query']) {
						parse_str($parts['query'], $query);

						if (isset($query['return']) && $query['return']) {
							$callback = base64_decode($query['return']);
						}
					}
				}

				// Try get return value from the url
				$return = $this->input->get('return', '', 'default');

				// Try get return value from the session
				// Currently only store the Facebook login redirection on session
				if (!$return) {
					$return = $session->get('oauth.login.redirection', '', SOCIAL_SESSION_NAMESPACE);

					// Clear off the session once it's been picked up.
					$session->clear('oauth.login.redirection', SOCIAL_SESSION_NAMESPACE);
				}

				if ($return) {
					$callback = base64_decode($return);
				}

				if ($callback) {
					$url = $callback;
				}
			}
		}

		if ($isSharer) {
			$return = $session->get('oauth.login.redirection', '', SOCIAL_SESSION_NAMESPACE);

			// Clear off the session once it's been picked up.
			$session->clear('oauth.login.redirection', SOCIAL_SESSION_NAMESPACE);
			$url = base64_decode($return);
		}

		// we need to make sure the url is translated to sef if site's sef is enabled. #5361
		if (strpos($url, 'index.php?') !== false) {
			$url = ESR::_($url);
		}

		$this->set('redirect', $url);

		return parent::display('site/oauth/popup');
	}

	/**
	 * To handle oauth popup login error
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function oauthError()
	{
		$session = JFactory::getSession();
		$oauthMessage = $session->get('social.message.oauth', '', 'SOCIAL.MESSAGE.OAUTH');

		$message = $oauthMessage->message;
		$type = $oauthMessage->type;

		// now clear the message
		$session->set('social.message.oauth', null, 'SOCIAL.MESSAGE.OAUTH');

		// show proper message
		$this->set('message', $message);

		return parent::display('site/oauth/oauth.error');
	}


	/**
	 * This is only used when autologin is activated
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function oauthLogin()
	{
		// Get allowed clients
		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Get the current client.
		$oauthClient = $this->input->get('client', '', 'word');

		if (!in_array($oauthClient, $allowedClients)) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_OAUTH_INVALID_OAUTH_CLIENT_PROVIDED', $oauthClient));
		}

		// If the user cancels the oauth sign up, we need to handle this properly
		$denied = $this->input->get('denied', '');

		if ($denied) {
			die('Facebook denied access');
		}

		// Get the oauth client object.
		$client = ES::oauth($oauthClient);

		// Get the access tokens first
		$verifier = $this->input->get('oauth_verifier', '', 'default');
		$access = $client->getAccess($verifier);

		// Set the access token on the session
		$session = JFactory::getSession();
		$session->set($oauthClient . '.access', $access, SOCIAL_SESSION_NAMESPACE);
		$session->set($oauthClient . '.token', $access->token, SOCIAL_SESSION_NAMESPACE);
		$session->set($oauthClient . '.secret', $access->secret, SOCIAL_SESSION_NAMESPACE);

		// Some clients requires us to set the access token
		$client->setAccess($access->token, $access->secret);

		// Determines if the user was already registered on the site previously
		$isRegistered = $client->isRegistered();

		if (!$isRegistered) {
			die('Not allowed here');
		}

		// Here onwards, we know the user was already registered previously
		// We need to update the token
		$client->updateToken();

		// Log the user in
		$client->login();

		// let get user data again.
		$user = ES::user();

		$url = false;

		// Here we will redirect user to our password reset page. awesome possum.
		if ($user->require_reset) {
			return $this->redirect(ESR::account(array('layout' => 'requirePasswordReset'), false));
		}

		// Try get redirection URL from the session
		// Currently only store the Facebook login redirection on session
		$returnURL = $session->get('oauth.autologin.redirection', '', SOCIAL_SESSION_NAMESPACE);

		// Clear off the session once it's been picked up.
		$session->clear('oauth.autologin.redirection', SOCIAL_SESSION_NAMESPACE);

		// if there got redirection URL from the session then use it
		if ($returnURL) {
			$url = base64_decode($returnURL);

			if ($url) {
				return $this->redirect($url);
			}
		}

		// If there is a referer link and there do not have any redirection URL from the session
		$referer = ESR::referer();

		if (!$returnURL && $referer) {
			return $this->redirect($referer);
		}

		// If until here don't have any redirection URL, then manually retrieve the URL again
		$menuId = $this->config->get('general.site.login');

		// Determine which URL to redirect the user to based on the settings
		if ($menuId != 'null') {
			$url = ESR::getMenuLink($menuId);
			$url = ESR::_($url);
		}

		if ($url === false) {
			// Default URL redirection
			$url = ESR::dashboard(array(), false);
		}

		return $this->redirect($url);
	}

	/**
	 * Displays the first step of user signing up with oauth
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function oauth()
	{
		// If user is already logged in here, they shouldn't be allowed on this page.
		if (!$this->my->guest) {
			return $this->exception('COM_EASYSOCIAL_OAUTH_YOU_ARE_ALREADY_LOGGED_IN');
		}

		// Get allowed clients
		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Get the current client.
		$oauthClient = $this->input->get('client', '', 'word');

		if (!in_array($oauthClient, $allowedClients)) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_OAUTH_INVALID_OAUTH_CLIENT_PROVIDED', $oauthClient));
		}

		// Get return url
		$returnUrl = $this->input->get('returnUrl', '', 'base64');

		// Get the oauth client object.
		$client = ES::oauth($oauthClient);

		// Add page title
		$title = JText::sprintf('COM_EASYSOCIAL_OAUTH_PAGE_TITLE', ucfirst($oauthClient));
		$this->page->title($title);
		$this->page->breadcrumb($title);

		// Check configuration if the registration mode is set to simplified or normal.
		$registrationType = $this->config->get('oauth.' . $oauthClient . '.registration.type');

		$urlOptions = array('layout' => 'oauthSelectProfile', 'client' => $oauthClient);

		if ($returnUrl) {
			$urlOptions['returnUrl'] = $returnUrl;
		}

		// Determines the creation url
		$createUrl = ESR::registration($urlOptions);

		if ($registrationType == 'simplified') {
			$createUrlString = 'index.php?option=com_easysocial&controller=registration&task=oauthSignup&client=' . $oauthClient;

			if ($returnUrl) {
				$createUrlString .= '&returnUrl=' . $returnUrl;
			}

			$createUrl = ESR::raw($createUrlString);
		}

		// Check if import avatar option is enabled
		$importAvatar = $this->config->get('oauth.' . $oauthClient . '.registration.avatar');
		$importCover = $this->config->get('oauth.' . $oauthClient . '.registration.cover');

		// Linkedin does not have the API to retrieve user profile cover yet. #1467
		if ($oauthClient == 'linkedin' || $oauthClient == 'facebook') {
			$importCover = false;
		}

		// Try to get the tokens from the session
		$session = JFactory::getSession();
		$token = $session->get($oauthClient . '.token', '', SOCIAL_SESSION_NAMESPACE);
		$secret = $session->get($oauthClient . '.secret', '', SOCIAL_SESSION_NAMESPACE);

		$client->setAccess($token, $secret);

		// Get user's meta
		try {
			$meta = $client->getUserMeta();
		} catch (Exception $e) {
			$app = JFactory::getApplication();

			// Use dashboard here instead of login because api error calls might come from after user have successfully logged in
			$url = ESR::dashboard(array(), false);

			$this->setMessage(JText::sprintf('COM_EASYSOCIAL_OAUTH_FACEBOOK_ERROR_MESSAGE', $e->getMessage()), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect($url);
		}

		// Auto linking to existing joomla account
		if ($oauthClient == 'facebook' && $this->config->get('oauth.facebook.autolink')) {
			$url = JRoute::_('index.php?option=com_easysocial&controller=registration&task=oauthLinkAccount&client=facebook&' . ES::token() . '=1', false);
			return $this->redirect($url);
		}

		$this->set('meta', $meta);
		$this->set('createUrl', $createUrl);
		$this->set('clientType', $oauthClient);
		$this->set('importAvatar', $importAvatar);
		$this->set('importCover', $importCover);
		$this->set('returnUrl', $returnUrl);

		parent::display('site/oauth/default/default');
	}

	/**
	 * Displays the list of profile types for the user to choose
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function oauthSelectProfile()
	{
		// Get allowed clients
		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Get the current client.
		$oauthClient = $this->input->get('client', '', 'word');

		if (!in_array($oauthClient, $allowedClients)) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_OAUTH_INVALID_OAUTH_CLIENT_PROVIDED', $oauthClient));
		}

		// Get the oauth client object.
		$client = ES::oauth($oauthClient);

		// If there is only 1 profile type, we don't really need to show the profile type selection
		$model = ES::model('Profiles');
		$options = array('state' => SOCIAL_STATE_PUBLISHED,
						'ordering' => 'ordering',
						'limit' => SOCIAL_PAGINATION_NO_LIMIT,
						'totalUsers' => $this->config->get('registrations.profiles.usersCount'),
						'validUser' => false,
						'registration' => SOCIAL_STATE_PUBLISHED
					);

		$profiles = $model->getProfiles($options);

		// If there's only 1 profile type, we should just ignore this step and load the steps page.
		if (count($profiles) == 1) {
			$profile = $profiles[0];

			return $this->oauthPreferences($profile->id, '', '', $oauthClient);
		}

		// Get return url
		$returnUrl = $this->input->get('returnUrl', '', 'base64');

		$this->set('profiles', $profiles);
		$this->set('clientType'	, $oauthClient);
		$this->set('returnUrl', $returnUrl);

		parent::display('site/oauth/profile/default');
	}

	/**
	 * Post processing after linking an account
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function oauthLinkAccount($clientType)
	{
		$redirect = ESR::dashboard(array(), false);

		$returnUrl = $this->input->get('returnUrl', '', 'base64');

		if ($returnUrl) {
			$redirect = base64_decode($returnUrl);
		}

		$this->info->set($this->getMessage());

		// If it was successfully, we need to redirect to the dashboard area.
		return $this->redirect($redirect);
	}

	/**
	 * Displays the first step of user signing up with oauth
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function oauthPreferences($profileId = '', $username = '', $email = '', $oauthClient = '')
	{
		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());
		}

		// Get allowed clients
		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Get the profile id.
		$profileId = $this->input->get('profile', $profileId);

		// Get the current client.
		$oauthClient = $this->input->get('client', $oauthClient);

		if (!in_array($oauthClient, $allowedClients)) {
			return $this->exception('COM_EASYSOCIAL_OAUTH_INVALID_OAUTH_CLIENT_PROVIDED');
		}

		// Add page title
		$title = JText::sprintf('COM_EASYSOCIAL_OAUTH_PAGE_TITLE_INFO', ucfirst($oauthClient));
		$url = ESR::registration(array('view' => 'registration', 'layout' => 'oauth', 'client' => $oauthClient));

		$this->page->title($title);
		$this->page->breadcrumb(JText::sprintf('COM_EASYSOCIAL_OAUTH_PAGE_TITLE', ucfirst($oauthClient)), $url);
		$this->page->breadcrumb($title);

		// Try to get the tokens from the session
		$session = JFactory::getSession();
		$token = $session->get($oauthClient . '.token', '', SOCIAL_SESSION_NAMESPACE);
		$secret = $session->get($oauthClient . '.secret', '', SOCIAL_SESSION_NAMESPACE);

		$client = ES::oauth($oauthClient);
		$client->setAccess($token, $secret);

		try {
			$meta = $client->getUserMeta();
		} catch (Exception $e) {
			// Use dashboard here instead of login because api error calls might come from after user have successfully logged in
			$redirect = ESR::dashboard(array(), false);

			$this->setMessage(JText::sprintf('COM_EASYSOCIAL_OAUTH_FACEBOOK_ERROR_MESSAGE', $e->getMessage()), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect($redirect);
		}

		$nameExists = true;
		if ($oauthClient == 'apple') {
			// In apple sign in, user has the option to hide email. for now we just treat them as no email.
			if (strpos($meta['email'], '@privaterelay.appleid.com') !== false) {
				$meta['email'] = '';
				$meta['username'] = '';
			}

			if (empty($meta['name'])) {
				$nameExists = false;
			}
		}

		// We might reach here from oauth.profile from normal registration, and in that case, username and email might be empty
		if (!$username || !$email) {
			if (empty($username)) {
				$username = $meta['username'];
			}

			if (empty($email)) {
				$email = $meta['email'];
			}
		}

		// Check if import avatar option is enabled
		$importAvatar = $this->config->get('oauth.' . $oauthClient . '.registration.avatar');
		$importCover = $this->config->get('oauth.' . $oauthClient . '.registration.cover');

		// Linkedin does not have the API to retrieve user profile cover yet. #1467
		if ($oauthClient == 'linkedin') {
			$importCover = false;
		}

		// Check if the username has been used, if it does, generate a username for him.
		$model = ES::model('Registration');
		$usernameExists	= $model->isUsernameExists($username);

		// Generate username
		if ($usernameExists && !$this->config->get('registrations.emailasusername')) {
			$username = $model->generateUsername($username);
		}

		$emailExists = $model->isEmailExists($email);

		jimport('joomla.mail.helper');
		$validEmail = JMailHelper::isEmailAddress($meta['email']);

		$returnUrl = $this->input->get('returnUrl', '', 'base64');

		$this->set('validEmail', $validEmail);
		$this->set('meta', $meta);
		$this->set('emailExists', $emailExists);
		$this->set('nameExists', $nameExists);
		$this->set('usernameExists', $usernameExists);
		$this->set('username', $username);
		$this->set('email', $email);
		$this->set('profileId', $profileId);
		$this->set('clientType', $oauthClient);
		$this->set('importAvatar', $importAvatar);
		$this->set('importCover', $importCover);
		$this->set('returnUrl', $returnUrl);

		parent::display('site/oauth/preferences/default');
	}

	/**
	 * Post process by redirecting user to the login page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function oauthSignup($redirect = '')
	{
		$this->info->set($this->getMessage());

		if (empty($redirect)) {
			$redirect = ESR::getLoginRedirectionLink();
		}

		return $this->redirect($redirect);
	}

	/**
	 * Renders the output of the form for each steps
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function steps()
	{
		// Clear cache as soon as the user registers on the site.
		ES::clearCache('page', '_system');

		// Ensure that registrations is enabled.
		if (!$this->config->get('registrations.enabled')) {
			return $this->exception('COM_EASYSOCIAL_ERROR_REGISTRATION_DISABLED');
		}

		// Checks if the user is already registered.
		if ($this->my->id) {
			$this->info->set(JText::_('COM_EASYSOCIAL_ERROR_REGISTRATION_ALREADY_A_REGISTERED_MEMBER'), SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Check if this user was invited by someone else
		$inviteId = $this->input->get('invite', 0, 'int');

		$session = JFactory::getSession();

		if ($inviteId) {
			$session->set('invite', $inviteId, SOCIAL_SESSION_NAMESPACE);
		}

		// Retrieve the user's session.
		$registration = ES::table('Registration');
		$registration->load($session->getId());

		// If there's no registration info stored, the user must be a lost user.
		if (is_null($registration->step)) {
			$this->info->set(JText::_('COM_EASYSOCIAL_REGISTRATION_UNABLE_TO_DETECT_ACTIVE_SESSION'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::registration(array(), false));
		}

		// Let's try to load the profile type that the user has already selected.
		$profile = ES::table('Profile');
		$profile->load($registration->profile_id);

		$workflow = $profile->getWorkflow();

		$errors = $registration->getErrors();
		$data = $registration->getValues();

		// Get the current step index
		$stepIndex = $this->input->get('step', 1, 'int');

		$sequence = $profile->getSequenceFromIndex($stepIndex, SOCIAL_PROFILES_VIEW_REGISTRATION);

		// If no sequence found, means don't have any page to show
		if (!$sequence) {
			return $this->exception('COM_EASYSOCIAL_REGISTRATION_NO_VALID_REGISTRATION_STEPS');
		}

		// Users should not be allowed to proceed to a future step if they didn't traverse their sibling steps.
		if (empty($registration->session_id) || ($stepIndex != 1 && !$registration->hasStepAccess($stepIndex))) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_ERROR_REGISTRATION_COMPLETE_PREVIOUS_STEP_FIRST', $stepIndex));
		}

		// Check if this is a valid step in the profile
		if (!$profile->isValidStep($sequence, SOCIAL_PROFILES_VIEW_REGISTRATION)) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_ERROR_REGISTRATION_NO_ACCESS_TO_STEP', $sequence));
		}

		// Remember current state of registration step
		$registration->set('step', $stepIndex);
		$registration->store();

		// Load the current workflow / step.
		$step = ES::table('FieldStep');
		$step->loadBySequence($workflow->id, SOCIAL_TYPE_PROFILES, $sequence);

		$totalSteps	= $profile->getTotalSteps();
		$registrationModel = ES::model('Registration');

		// Since they are bound to the respective groups, assign the fields into the appropriate groups.
		$args = array(&$data, &$registration);

		// Get fields library as we need to format them.
		$fields = ES::getInstance('Fields');

		// Retrieve custom fields for the current step
		$fieldsModel = ES::model('Fields');
		$customFields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'visible' => SOCIAL_PROFILES_VIEW_REGISTRATION));

		// Set the page attributes
		$this->page->title($step->_('title'));
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_REGISTRATION_SELECT_PROFILE', FRoute::registration(array('profile_id' => '0')));
		$this->page->breadcrumb($step->_('title'));

		// Set the callback for the triggered custom fields
		$callback = array($fields->getHandler(), 'getOutput');

		// Trigger onRegister for custom fields.
		if (!empty($customFields)) {
			$fields->trigger('onRegister', SOCIAL_FIELDS_GROUP_USER, $customFields, $args, $callback);
		}

		$conditionalFields = array();

		foreach ($customFields as $field) {
			if ($field->isConditional()) {
				$conditionalFields[$field->id] = false;
			}
		}

		if ($conditionalFields) {
			$conditionalFields = json_encode($conditionalFields);
		} else {
			$conditionalFields = false;
		}

		// We don't want to show the profile types if there's only 1 profile in the system.
		$profilesModel = ES::model('Profiles');
		$totalProfiles = $profilesModel->getTotalProfiles(array('registration' => true));

		// Pass in the steps for this profile type.
		$steps = $profile->getSteps(SOCIAL_PROFILES_VIEW_REGISTRATION);

		// Get the total steps
		$totalSteps = $profile->getTotalSteps(SOCIAL_PROFILES_VIEW_REGISTRATION);

		// Format the steps
		if ($steps) {
			$currentStep = $sequence;
			$counter = 1;

			foreach ($steps as &$step) {
				$stepClass = $step->sequence == $currentStep || $currentStep > $step->sequence || $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? ' active' : '';
				$stepClass .= $step->sequence < $currentStep || $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? $stepClass . ' past' : '';

				$step->css = $stepClass;
				$step->permalink = 'javascript:void(0);';

				if ($registration->hasStepAccess($step->sequence)) {
					$step->permalink = $step->sequence == $currentStep ? 'javascript:void(0);' : ESR::registration(array('layout' => 'steps', 'step' => $counter));
				}

				$counter++;
			}
		}

		// We use step index to determine the previous step link. #442
		$previousLink = ESR::registration(array('layout' => 'steps', 'step' => ($stepIndex - 1)), false);

		$showProfileTypesLink = array('link' => 'hidden', 'tooltip' => '');

		// Determine whether need to show a link for select profile type
		if ($totalProfiles > 1) {
			$showProfileTypesLink = array('link' => ESR::registration(array('profile_id' => '0')), 'tooltip' => 'COM_EASYSOCIAL_REGISTRATIONS_SELECT_A_PROFILE');
		}

		$this->set('conditionalFields', $conditionalFields);
		$this->set('previousLink', $previousLink);
		$this->set('registration', $registration);
		$this->set('steps', $steps);
		$this->set('totalProfiles', $totalProfiles);
		$this->set('currentStep', $currentStep);
		$this->set('currentIndex', $stepIndex);
		$this->set('totalSteps', $totalSteps);
		$this->set('step', $step);
		$this->set('fields', $customFields);
		$this->set('errors', $errors);
		$this->set('profile', $profile);
		$this->set('workflow', $workflow);
		$this->set('showProfileTypesLink', $showProfileTypesLink);

		return parent::display('site/registration/steps/default');
	}

	/**
	 * Method is invoked each time a step is saved. Responsible to redirect or show necessary info about the current step.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveStep($registration, $currentIndex, $completed = false)
	{
		$info = ES::info();
		$config = ES::config();

		// Registrations must be enabled.
		if (!$config->get('registrations.enabled')) {
			$info->setMessage(false, JText::_('COM_EASYSOCIAL_REGISTRATIONS_DISABLED'), SOCIAL_MSG_ERROR);
			return $this->redirect(FRoute::login(array(), false));
		}

		// Set any message that was passed from the controller.
		$info->set($this->getMessage());

		// If there's an error, redirect back user to the correct step and show the error.
		if ($this->hasErrors()) {
			return $this->redirect(FRoute::registration(array('layout' => 'steps', 'step' => $currentIndex), false));
		}

		// Registration is completed. Redirect user to the complete page.
		if ($completed) {
			return $this->redirect(FRoute::registration(array('layout' => 'completed'), false));
		}

		// Registration is not completed yet, redirect user to the appropriate step.
		return $this->redirect(FRoute::registration(array('layout' => 'steps', 'step' => $currentIndex + 1), false));
	}

	/**
	 * Post process after the user selects the type.
	 *
	 * @access	public
	 * @param	null
	 */
	public function selectType()
	{
		// Set message data.
		ES::info()->set($this->getMessage());

		// @task: Check for errors.
		if ($this->hasErrors()) {
			return $this->redirect(FRoute::registration(array(), false));
		}

		// @task: We always know that after selecting the profile type, the next step would always be the first step.
		$url 	= FRoute::registration(array('layout' => 'steps', 'step' => 1), false);

		return $this->redirect(FRoute::registration(array('layout' => 'steps', 'step' => 1), false));
	}

	/**
	 * Displays some information once the user registration is completed.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function complete(&$user, &$profile)
	{
		// Check if there is a menu assigned for completed registration.
		$params = $profile->getParams();

		// Redirect to expected page
		if ($profile->getRegistrationType() == 'verify' ||
			$profile->getRegistrationType() == 'approvals' ||
			$profile->getRegistrationType() == 'login' ||
			$profile->getRegistrationType() == 'confirmation_approval')
		{
			$url = ESR::registration(array('layout' => 'completed', 'id' => $profile->id, 'userid' => $user->id), false);

			return $this->redirect($url);
		}

		// Here we respect the settings that is configured for the registration success settings.
		if ($params->get('registration_success') === 'previous') {
			$session = JFactory::getSession();
			$previousLink = $session->get('easysocial.before_registration', '', SOCIAL_SESSION_NAMESPACE);

			if ($previousLink) {
				$previousLink = base64_decode($previousLink);

				return $this->redirect($previousLink);
			}
		}

		$menuRegistrationSuccess = $params->get('registration_success');

		if ($menuRegistrationSuccess !== 'null') {
			$link = ESR::getMenuLink($menuRegistrationSuccess, true);

			if ($link) {
				$link = ESR::_($link);
				return $this->redirect($link);
			}
		}

		// If profile is configured to be automatically logged in, redirect them to the dashboard page.
		if ($profile->getRegistrationType() == 'auto') {

			// Check if session has a return value or not
			$session = JFactory::getSession();
			$registration = ES::table('Registration');
			$registration->load($session->getId());
			$registry = ES::registry($registration->values);
			$return = $registry->get('return');

			if (!empty($return)) {
				return $this->redirect(base64_decode($return));
			}

			$url = ESR::getLoginRedirectionLink();
			return $this->redirect($url);
		}

		$url = ESR::registration(array('layout' => 'completed', 'id' => $profile->id, 'userid' => $user->id), false);

		return $this->redirect($url);
	}

	/**
	 * Displays some information once the user registration is completed.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function completed()
	{
		// If user is already logged in, redirect them to their dashboard automatically.
		$user = ES::user();

		// Override return url if there any
		$returnUrl = $this->input->get('returnUrl', '', 'base64');

		if ($user->id) {
			$loginMenu = $this->config->get('general.site.login', 'null');

			if ($loginMenu == 'null') {
				$url = ESR::dashboard(array(), false);
			} else {
				$url = ESR::getMenuLink($loginMenu);
				$url = ESR::_($url);
			}

			// Override the return url if there any
			if ($returnUrl) {
				$url = base64_decode($returnUrl);
			}

			return $this->redirect($url);
		}

		$userId = $this->input->get('userid', 0, 'int');
		$user = ES::user($userId);

		// Get the profile type.
		$id = $this->input->get('id', 0, 'int');
		$profile = ES::table('Profile');
		$profile->load($id);

		$oauth = $this->input->get('oauth', 0, 'int');

		// Get the registration type
		$type = $profile->getRegistrationType(false, $oauth);

		$this->set('user', $user);
		$this->set('profile', $profile);
		$this->set('returnUrl', $returnUrl);

		$namespace = 'site/registration/completed/' . $type;

		echo parent::display($namespace);
	}

	/**
	 * Responsible to display the activation form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function activation()
	{
		$id = $this->input->get('userid', 0, 'int');

		$user = ES::user($id);

		$this->set('user', $user);

		echo parent::display('site/registration/completed/verify');
	}

	/**
	 * Responsible for post processing after a user signs up with their oauth account
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function oauthCreateAccount($user = null)
	{
		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());
			return $this->redirect(ESR::dashboard(array(), false));
		}

		$returnUrl = $this->input->get('returnUrl', '', 'base64');

		$options = [
				'layout' => 'completed',
				'userid' => $user->id,
				'id' => $user->profile_id,
				'oauth' => 1,
				'returnUrl' => $returnUrl
		];

		$redirect = ESR::registration($options, false);

		return $this->redirect($redirect);
	}

	/**
	 * Responsible for post-processing of activation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function activate($user = null)
	{
		ES::info()->set($this->getMessage());

		if ($this->hasErrors()) {
			$url = ESR::registration(array('layout' => 'activation', 'userid' => $user->id), false);

			return $this->redirect($url);
		}

		return $this->redirect(FRoute::login(array(), false));
	}

	/**
	 * Post process after a user has been approved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function approveUser()
	{
		ES::info()->set($this->getMessage());

		echo parent::display('site/registration/moderation.approved');
	}


	/**
	 * Post process after a user has been rejected
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function rejectUser()
	{
		ES::info()->set($this->getMessage());

		echo parent::display('site/registration/moderation.rejected');
	}

	/**
	 * Post processing after trying to create user
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function quickRegister($profileId = null)
	{
		$this->info->set($this->getMessage());

		$redirect = ESR::registration(array(), false);

		// Redirect user to a valid registration form based on the profile type
		if ($profileId) {
			$redirectOptions = array('controller' => 'registration', 'task' => 'selectType', 'profile_id' => $profileId);
			$redirect = ESR::registration($redirectOptions, false);
		}

		return $this->redirect($redirect);
	}

	/**
	 * Proxy function to route the mini registration process to full registration page
	 *
	 * @since  1.2
	 * @access public
	 */
	public function miniRegister()
	{
		$this->info->set($this->getMessage());

		$redirect = ESR::registration(array(), false);
		return $this->redirect($redirect);
	}

	/**
	 * Proxy function to route the mini registration process to full registration page
	 *
	 * @since  1.2
	 * @access public
	 */
	public function fullRegister($profileId = 0)
	{
		$this->info->set($this->getMessage());

		$redirect = ESR::registration(array(), false);

		// If no profile id, then we route user to select a profile
		if (!empty($profileId)) {
			return $this->redirect(ESR::registration(array('layout' => 'steps', 'step' => 1), false));
		}

		return $this->redirect($redirect);
	}

	/**
	 * Shorthand for view to pass in quick as parameter to the registration page
	 *
	 * @since  1.2
	 * @access public
	 */
	public function selectProfile()
	{
		$this->redirect(ESR::registration(array('quick' => true), false));
	}

	/**
	 * Responsible for redirect on the page once user confirmation their email account.
	 *
	 * @since  2.2.3
	 * @access public
	 */
	public function confirmationUserEmail()
	{
		ES::info()->set($this->getMessage());

		$redirect = ESR::login(array(), false);

		return $this->redirect($redirect);
	}
}
