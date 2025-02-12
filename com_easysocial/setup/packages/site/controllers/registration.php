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

class EasySocialControllerRegistration extends EasySocialController
{
	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	1.0
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
	 * Allows user to activate their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function activate()
	{
		// Get the id from the request
		$id = $this->input->get('userid', 0, 'int');
		$currentUser = ES::user($id);

		// If user is already logged in, redirect to the dashboard.
		if ($this->my->isLoggedIn()) {
			return $this->view->call(__FUNCTION__, $currentUser);
		}

		// Get the token
		$token = $this->input->get('token', '', 'default');

		// If token is empty, warn the user.
		if (empty($token) || strlen($token) !== 32) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGISTRATION_ACTIVATION_TOKEN_INVALID'), ES_ERROR);
			return $this->view->call(__FUNCTION__ , $currentUser);
		}

		// Try to activate the user based on the token.
		$model = ES::model('Registration');
		$user = $model->activate($token);

		if ($user === false) {
			$this->view->setMessage($model->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $currentUser);
		}

		// once the user activated, process the pending referral
		$referral = $user->getEsParams()->get('referral');

		if ($referral) {
			$invites = ES::invites();
			$invites->registered($referral, $user->id);
		}

		$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGISTRATION_ACTIVATION_COMPLETED_SUCCESS'), SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $user);
	}

	/**
	 * This adds information about the current profile that the user selected during registration.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function selectType()
	{
		// Ensure that registrations is enabled.
		if (!$this->config->get('registrations.enabled')) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_ERROR_REGISTRATION_DISABLED', ES_ERROR));
			return $this->view->call(__FUNCTION__);
		}

		// Get the profile id
		$id = $this->input->get('profile_id', 0, 'int');

		// If there's no profile id selected, throw an error.
		if (!$id) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_ERROR_REGISTRATION_EMPTY_PROFILE_ID'), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// We need to ensure that the user can really select such profile type during registrations
		$profile = ES::table('Profile');
		$profile->load($id);

		if (!$profile->allowsRegistration()) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_ERROR_REGISTRATION_EMPTY_PROFILE_ID'), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// @task: Let's set some info about the profile into the session.
		$session = JFactory::getSession();
		$session->set('profile_id', $id, SOCIAL_SESSION_NAMESPACE);

		// @task: Try to load more information about the current registration procedure.
		$registration = ES::table('Registration');
		$registration->load($session->getId());
		$registration->profile_id = $id;

		// When user accesses this page, the following will be the first page
		$registration->set('step', 1);

		// Add the first step into the accessible list.
		$registration->addStepAccess( 1 );
		$registration->store();

		// After a profile type is selected, ensure that the cache are cleared.
		$cache	= JFactory::getCache();
		$cache->clean();

		// Check in the session if quick is flagged as true
		if ($session->get('quick', false, SOCIAL_SESSION_NAMESPACE)) {
			return $this->quickRegister();
		}

		return $this->view->call( __FUNCTION__ );
	}

	/**
	 * Each time the user clicks on the next button, this method is invoked.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveStep()
	{
		ES::checkToken();

		// Registrations must be enabled.
		if (!$this->config->get('registrations.enabled')) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGISTRATIONS_DISABLED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Retrieve all file objects if needed
		$files = $this->input->files->getArray();
		$post = $this->input->post->getArray();

		// Get current user's info
		$session = JFactory::getSession();

		// Get necessary info about the current registration process.
		$registration = ES::table('Registration');
		$state = $registration->load($session->getId());

		// There are cases where the registration page is not loaded through display function in view.html.php due to cache, then the session is not created in registration table
		if (!$state) {
			$registration->set('session_id', $session->getId());
			$registration->set('created', ES::get('Date')->toMySQL());
			$registration->set('profile_id', $post['profileId']);
			$registration->set('step', 1);
			$registration->addStepAccess(1);

			$registration->store();
		}

		// Load the profile object.
		$profile = ES::table('Profile');
		$profile->load($registration->get('profile_id'));

		// Get the sequence
		$sequence = $profile->getSequenceFromIndex($registration->get('step'), SOCIAL_PROFILES_VIEW_REGISTRATION);

		// Load the current step.
		$step = ES::table('FieldStep');
		$step->loadBySequence($profile->getWorkflow()->id, SOCIAL_TYPE_PROFILES, $sequence);

		// Merge the post values
		$registry = ES::registry($registration->values);

		// Load registration model
		$registrationModel = ES::model('Registration');

		// Get all published fields apps that are available in the current form to perform validations
		$fieldsModel = ES::model('Fields');
		$options = array('step_id' => $step->id, 'visible' => SOCIAL_PROFILES_VIEW_REGISTRATION);
		$fields = $fieldsModel->getCustomFields($options);

		// Process all $_POST variables and normalize the data
		$token = ES::token();

		foreach ($post as $key => $value) {

			if ($key != $token) {

				if (is_array($value)) {
					$value = json_encode($value);
				}

				$registry->set($key, $value);
			}
		}

		// Honeypot checks
		if ($this->config->get('registrations.honeypot')) {
			$honeypot = ES::honeypot();
			$trapped = $honeypot->isTrapped('registration');

			if ($trapped) {
				$this->view->setMessage(JText::_('Registration completed'));

				return $this->view->selectType();
			}
		}

		// Convert the values into an array.
		$data = $registry->toArray();

		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$registration);

		// Load up our fields library
		$fieldsLib = ES::fields();

		// Get the trigger handler
		$handler = $fieldsLib->getHandler();

		// Get the trigger handler
		$handler = $fieldsLib->getHandler();

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler));

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$registration);

		// Allow custom fields to perform their on validation
		// @trigger onRegisterValidate
		$errors = $fieldsLib->trigger('onRegisterValidate', SOCIAL_FIELDS_GROUP_USER, $fields , $args, array($handler, 'validate'));

		// The values needs to be stored in a JSON notation.
		$registration->values = json_encode($data);
		$registration->store();

		// Get the current step (before saving)
		// Add the current step into the accessible list
		$currentStep = $registration->step;
		$registration->addStepAccess($currentStep);

		// Bind any errors into the registration object
		$registration->setErrors($errors);

		// Saving was intercepted by one of the field applications.
		if (is_array($errors) && count($errors) > 0) {

			// If there are any errors on the current step, remove access to future steps to avoid any bypass
			$registration->removeAccess($currentStep);

			// Reset steps to the current step
			$registration->step = $currentStep;
			$registration->store();

			$this->view->setMessage('COM_EASYSOCIAL_REGISTRATION_SOME_ERRORS_IN_THE_REGISTRATION_FORM', ES_ERROR);

			return $this->view->call('saveStep', $registration, $currentStep);
		}

		// Determine whether the next step is completed. It has to be before updating the registration table's step
		// Otherwise, the step doesn't exist in the site.
		$completed = $step->isFinalStep(SOCIAL_PROFILES_VIEW_REGISTRATION);

		// Update creation date
		$registration->created = JFactory::getDate()->toSql();

		// Get the next step the user should go through
		$nextSequence = $step->getNextSequence(SOCIAL_PROFILES_VIEW_REGISTRATION);

		if ($nextSequence !== false) {
			$nextIndex = $profile->getIndexFromSequence($nextSequence, SOCIAL_PROFILES_VIEW_REGISTRATION);
			$registration->addStepAccess($nextIndex);
			$registration->step = $nextIndex;
		}

		// Save the temporary data.
		$registration->store();

		// If this is the last step, we try to save all user's data and create the necessary values.
		if ($completed) {

			$referral = $session->get('referral', false, SOCIAL_SESSION_NAMESPACE);
			$inviteId = $session->get('invite', false, SOCIAL_SESSION_NAMESPACE);

			// Create user object.
			$user = $registrationModel->createUser($registration, false, $inviteId, $referral);

			// If there's no id, we know that there's some errors.
			if (empty($user->id)) {
				$errors = $registrationModel->getError();

				$this->view->setMessage($errors, ES_ERROR);

				return $this->view->call('saveStep', $registration, $currentStep);
			}

			// Get the registration data
			$registrationData = ES::registry($registration->values);

			// Clear existing registration objects once the creation is completed.
			$registration->delete();

			// Clear cache as soon as the user registers on the site.
			$cache = JFactory::getCache();
			$cache->clean('page');
			$cache->clean('_system');

			// Force unset on the user first to reload the user object
			$user->removeFromCache();

			// Get the current registered user data.
			$my = ES::user($user->id);

			// We need to send the user an email with their password
			$my->password_clear	= $user->password_clear;

			// Convert the data into an array of result.
			$mailerData = ES::registry($registration->values)->toArray();

			// Send notification to admin if necessary.
			if ($profile->getParams()->get('email.moderators', true)) {
				$registrationModel->notifyAdmins($mailerData, $my, $profile, false);
			}

			// Send notification to admin if necessary.
			if ($profile->getParams()->get('custom.emails')) {
				$registrationModel->notifyCustom($mailerData, $my, $profile, false);
			}

			// If everything goes through fine, we need to send notification emails out now.
			if ($profile->getParams()->get('email.users', true)) {
				$registrationModel->notify($mailerData, $my, $profile);
			}

			// we need to reset site language back to default due to the posibility of site language changed during the email notification.
			// #432
			$siteLang = JFactory::getLanguage()->getTag();
			$myLang = $my->getParam('language', $siteLang);

			// Load site and admin languages
			if ($siteLang != $myLang) {
				$lang = ES::language();
				$lang->load('joomla', JPATH_ROOT, $siteLang, true, true);
				$lang->loadSite($siteLang, true, true);
				$lang->loadAdmin($siteLang, true, true);
			}

			// We need to log the user in after they have successfully registered.
			if ($profile->getRegistrationType() == 'auto') {

				// Try to log the user into the site
				$credentials = array('username' => $my->username, 'password' => $my->password_clear);
				$this->app->login($credentials);
			}

			// Synchronize with Joomla's finder
			$my->syncIndex();

			// Store the user's custom fields data now.
			return $this->view->complete($user, $profile);
		}

		return $this->view->saveStep($registration, $currentStep, $completed);
	}

	/**
	 * Normal oauth registration or if the user has an invalid email or username in simplified process.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function oauthCreateAccount()
	{
		ES::checkToken();

		// Get allowed clients
		$allowedClients	= array_keys((array) $this->config->get('oauth'));
		$clientType = $this->input->get('client', '', 'word');

		if (!$clientType || !in_array($clientType, $allowedClients)) {
			return $this->view->exception('COM_EASYSOCIAL_OAUTH_INVALID_CLIENT');
		}

		// Get the profile
		$profileId = $this->input->get('profile', 0, 'int');
		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Check if the profile id is provided.
		if (!$profileId || !$profile->id) {
			return $this->view->exception('COM_EASYSOCIAL_OAUTH_INVALID_PROFILEID');
		}

		// Get the access token from session
		$client = ES::oauth($clientType);
		$session = JFactory::getSession();
		$accessToken = $session->get($clientType . '.access', '', SOCIAL_SESSION_NAMESPACE);

		// Check if the profile id is provided.
		if (!$accessToken) {
			return $htis->view->exception('COM_EASYSOCIAL_OAUTH_ACCESS_TOKEN_NOT_FOUND');
		}

		// Set the token
		$client->setAccess($accessToken->token, $accessToken->secret, $accessToken);

		// Determines if the oauth id is already registered on the site.
		$isRegistered = $client->isRegistered();

		// If user has already registered previously, just log them in.
		// Throw an error message here because they shouldn't be coming through this page.
		if ($isRegistered) {
			return $this->view->exception('COM_EASYSOCIAL_REGISTRATION_ALREADY_REGISTERED');
		}

		// Get the user's meta
		try {
			$meta = $client->getUserMeta();
		} catch (Exception $e) {
			return $this->view->exception(JText::sprintf('COM_EASYSOCIAL_OAUTH_FACEBOOK_ERROR_MESSAGE', $e->getMessage()));
		}

		// Get user properties
		$import = $this->input->get('import', false, 'bool');
		$sync = $this->input->get('stream', false, 'bool');
		$username = $this->input->get('oauth-username', '', 'string');
		$email = $this->input->get('oauth-email', '', 'email');
		$firstname = $this->input->get('oauth-firstname', '', 'string');
		$lastname = $this->input->get('oauth-lastname', '', 'string');

		// If emailasusername is on, then we manually assign email into username
		if ($this->config->get('registrations.emailasusername')) {
			$username = $email;
		}

		$meta['password'] = $this->input->get('oauth-password', $meta['password'], 'default');
		$meta['profileId'] = $profile->id;
		$meta['username'] = $username;
		$meta['email'] = $email;

		if ($clientType == 'apple' && empty($meta['name'])) {
			$meta['fullname'] = $firstname . ' ' . $lastname;
			$meta['name'] = $firstname . ' ' . $lastname;
		}

		// Retrieve the model.
		$model = ES::model('Registration');

		// Double check to see if the email and username still exists.
		if ($model->isUsernameExists($meta['username'])) {
			$this->view->setMessage('COM_EASYSOCIAL_OAUTH_USERNAME_ALREADY_USED', ES_ERROR);
			return $this->view->call('oauthPreferences', $profile->id, $meta['username'], $meta['email'], $client);
		}

		// Double check to see if the email and username still exists.
		if ($model->isEmailExists($meta['email'])) {
			$this->view->setMessage('COM_EASYSOCIAL_OAUTH_EMAIL_ALREADY_USED', ES_ERROR);
			return $this->view->call('oauthPreferences', $profile->id, $meta['username'], $meta['email'], $client);
		}

		// Create the user account in Joomla
		$user = $model->createOauthUser($accessToken, $meta, $client, $import, $sync);

		// If there's a problem creating user, throw message.
		if (!$user) {
			$this->view->setMessage($model->getError(), ES_ERROR);
			return $this->view->call('oauthPreferences', $profile->id, $meta['username'], $meta['email'], $client);
		}

		// Check if the profile type requires activation. Only log the user in when user is supposed to automatically login.
		$type = $profile->getRegistrationType(false, true);

		// Send notification to admin if necessary.
		if ($profile->getParams()->get('email.moderators', true)) {
			$model->notifyAdmins($meta, $user, $profile, true);
		}

		// Send notification to admin if necessary.
		if ($profile->getParams()->get('custom.emails')) {
			$model->notifyCustom($meta, $user, $profile, true);
		}


		// Send registration confirmation email to user.
		if ($profile->getParams()->get('email.users', true)) {
			$model->notify($meta, $user, $profile, true);
		}

		// Only log the user in if the profile allows this.
		if ($type == 'auto') {
			$client->login();
		}

		return $this->view->call(__FUNCTION__, $user);
	}

	/**
	 * Allows caller to link an oauth account with a registered account
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function oauthLinkAccount()
	{
		ES::checkToken();

		$clientType = $this->input->get('client', '', 'word');
		$client = ES::oauth($clientType);

		$session = JFactory::getSession();
		$accessToken = $session->get($clientType . '.access', '', SOCIAL_SESSION_NAMESPACE);

		// Check if the profile id is provided.
		if (!$accessToken) {
			return $htis->view->exception('COM_EASYSOCIAL_OAUTH_ACCESS_TOKEN_NOT_FOUND');
		}

		// Set the token
		$client->setAccess($accessToken->token, $accessToken->secret, $accessToken);

		if ($this->config->get('oauth.facebook.autolink') && $clientType == 'facebook') {

			// Try to associate the user account
			$meta = $client->getUserMeta();
			$oauthId = $client->getUser();

			if (!isset($meta['email'])) {
				$this->view->setMessage('COM_EASYSOCIAL_OAUTH_FACEBOOK_ERROR_EMAIL_NOT_FOUND_MESSAGE', ES_ERROR);

				return $this->view->call(__FUNCTION__, $clientType);
			}

			$email = $meta['email'];

			$model = ES::model('Users');
			$userId = $model->getUserId('email', $email);

			// Since this is auto linking, create the account immediately
			if (!$userId) {

				$registrationModel = ES::model('Registration');

				// If the username or email exists
				$emailExists = $registrationModel->isEmailExists($meta['email']);
				$usernameExists = $registrationModel->isUsernameExists($meta['username']);

				// Check if the email address is valid
				jimport('joomla.mail.helper');
				$validEmail = JMailHelper::isEmailAddress($meta['email']);

				// If this is a twitter client, we need to always retrieve it's email
				if ($emailExists || $usernameExists || !$validEmail) {
					return $this->view->call('oauthPreferences', $meta['profileId'], $meta['username'], $meta['email'], $clientType);
				}

				// Get the access token from the session
				$session = JFactory::getSession();
				$accessToken = $session->get($clientType . '.access', '', SOCIAL_SESSION_NAMESPACE);

				// Create user account
				$user = $registrationModel->createOauthUser($accessToken, $meta, $client);

				if (!$user) {
					$this->view->setMessage('COM_EASYSOCIAL_OAUTH_USERNAME_PASSWORD_ERROR', ES_ERROR);

					$redirect = ESR::registration(array('layout' => 'oauth'), false);

					return ES::redirect($redirect);
				}

				// If the profile type is auto login, we need to log the user in
				$profile = ES::table('Profile');
				$profile->load($meta['profileId']);

				// Check if the profile type requires activation. Only log the user in when user is supposed to automatically login.
				$type = $profile->getRegistrationType(false, true);

				// Send notification to admin if necessary.
				if ($profile->getParams()->get('email.moderators', true)) {
					$registrationModel->notifyAdmins($meta, $user, $profile, true);
				}

				if ($profile->getParams()->get('custom.emails')) {
					$registrationModel->notifyCustom($meta, $user, $profile, true);
				}

				// Send registration confirmation email to user.
				if ($profile->getParams()->get('email.users', true)) {
					$registrationModel->notify($meta, $user, $profile, true);
				}

				// @points: user.register
				// Assign points when user registers on the site.
				ES::points()->assign('user.registration', 'com_easysocial', $user->id);
				ES::badges()->log('com_easysocial', 'registration.create', $user->id, 'COM_EASYSOCIAL_REGISTRATION_BADGE_REGISTERED');

				// check for the oauth registration type and do different action
				if ($type == 'verify') {
					return $this->view->call('oauthCreateAccount', $user);
				}

				if ($type == 'approvals') {
					$this->view->setMessage('COM_EASYSOCIAL_REGISTRATION_COMPLETED_WAITING_APPROVAL_DESCRIPTION', SOCIAL_MSG_SUCCESS);
					return $this->view->call(__FUNCTION__, $clientType);
				}

			} else {
				$user = ES::user($userId);

				$model = ES::model('Registration');
				$state = $model->linkOAuthUser($client, $user);

				if (!$state) {
					$this->view->setMessage($model->getError(), ES_ERROR);

					return $this->view->call(__FUNCTION__, $clientType);
				}
			}

			$credentials = array('username' => $user->username, 'password' => rand());

			// Try to log the user in
			$state = $this->app->login($credentials);

		} else {
			// Get the user's username and password
			$username = $this->input->get('username', '', 'default');
			$password = $this->input->get('password', '', 'default');

			$credentials = array('username' => $username, 'password' => $password);

			// Try to log the user in
			$state = $this->app->login($credentials);

			if (!$state) {

				$options = array('layout' => 'oauth', 'client' => $clientType);
				$returnUrl = $this->input->get('returnUrl', '', 'base64');

				if ($returnUrl) {
					$options = array_merge($options, array('returnUrl' => $returnUrl));
				}

				$this->info->set(false, 'COM_EASYSOCIAL_OAUTH_USERNAME_PASSWORD_ERROR', ES_ERROR);
				$redirect = ESR::registration($options, false);

				return ES::redirect($redirect);
			}

			// Get the logged in user
			$user = JFactory::getUser();

			// re-load the social user
			$my = ES::user($user->id);

			// further check if this is a guest or not.
			// #481
			if (!$my->id || $my->guest) {
				$this->view->setMessage('COM_EASYSOCIAL_OAUTH_USERNAME_PASSWORD_ERROR', ES_ERROR);
				return $this->view->call(__FUNCTION__, $clientType);
			}

			// Check if the user already linked to oauth account
			if ($my->hasOAuth($client->getType())) {
				$this->view->setMessage('COM_ES_OAUTH_ALREADY_LINKED', ES_ERROR);
				return $this->view->call(__FUNCTION__, $clientType);
			}

			// If user logged in successfully, link the oauth account to this user account.
			$model = ES::model('Registration');
			$state = $model->linkOAuthUser($client, $my);

			if (!$state) {
				$this->view->setMessage($model->getError(), ES_ERROR);

				return $this->view->call(__FUNCTION__, $clientType);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_OAUTH_ACCOUNT_LINK_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $clientType);
	}

	/**
	 * Quick oauth sign up
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function oauthSignup()
	{
		// Get the current client
		$clientType = $this->input->get('client', '', 'word');
		$allowedClients	= array_keys((array) $this->config->get('oauth'));

		// Check for allowed clients.
		if (!in_array($clientType, $allowedClients)) {
			return $this->clientType->exception('COM_EASYSOCIAL_OAUTH_INVALID_CLIENT');
		}

		$client = ES::oauth($clientType);

		$session = JFactory::getSession();
		$accessToken = $session->get($clientType . '.access', '', SOCIAL_SESSION_NAMESPACE);
		$client->setAccess($accessToken->token, $accessToken->secret, $accessToken);

		if ($clientType === 'apple') {
			$client->setUserObj($accessToken->user);
		}

		$oauthUserId = $client->getUserId();
		$isRegistered = $client->isRegistered();

		// If user has already registered previously, just log them in.
		if ($isRegistered) {
			$state = $client->login();

			if ($state) {
				$this->view->setMessage('COM_EASYSOCIAL_OAUTH_AUTHENTICATED_ACCOUNT_SUCCESS', SOCIAL_MSG_SUCCESS);
			}

			$returnUrl = '';

			// fix for google onetap redirection issue #5221
			if ($clientType === 'google') {
				$returnUrl = $this->input->get('returnUrl', '', 'base64');
				$returnUrl = !empty($returnUrl) ? base64_decode($returnUrl) : '';
			}

			return $this->view->call(__FUNCTION__, $returnUrl);
		}

		// Retrieve user's information
		try {
			$meta = $client->getUserMeta();
		} catch (Exception $e) {

			return $this->view->exception(JText::sprintf('COM_EASYSOCIAL_OAUTH_FACEBOOK_ERROR_MESSAGE', $e->getMessage()));
		}

		// Get the registration type.
		$registrationType = $this->config->get('oauth.' . $clientType . '.registration.type');

		$model = ES::model('Registration');

		// If this is a simplified registration, check if the user name exists.
		if ($registrationType === 'simplified') {

			// If the username or email exists
			$emailExists = $model->isEmailExists($meta['email']);
			$usernameExists = $model->isUsernameExists($meta['username']);

			// Check if the email address is valid
			jimport('joomla.mail.helper');
			$validEmail = JMailHelper::isEmailAddress($meta['email']);

			// In apple sign in, user has the option to hide email. for now we just treat them as no email.
			if ($clientType === 'apple' && strpos($meta['email'], '@privaterelay.appleid.com') !== false) {
				$validEmail = false;
				$meta['email'] = '';
				$meta['username'] = '';
			}

			// If this is a twitter client, we need to always retrieve it's email
			if ($emailExists || $usernameExists || !$validEmail || ($clientType == 'apple' && empty($meta['name']))) {
				return $this->view->call('oauthPreferences', $meta['profileId'], $meta['username'], $meta['email'], $clientType);
			}
		}

		// Create user account
		$user = $model->createOauthUser($accessToken, $meta, $client);

		if (!$user) {
			$this->view->setMessage($model->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If the profile type is auto login, we need to log the user in
		$profile = ES::table('Profile');
		$profile->load($meta['profileId']);

		$params = $profile->getParams();

		// Send notification email to admin
		if ($params->get('email.moderators', true)) {
			$model->notifyAdmins($meta, $user, $profile, true);
		}

		if ($profile->getParams()->get('custom.emails')) {
			$model->notifyCustom($meta, $user, $profile, true);
		}

		// Send registration confirmation email to user.
		if ($params->get('email.users', true)) {
			$model->notify($meta, $user, $profile, true);
		}

		ES::badges()->log('com_easysocial', 'registration.create', $user->id, 'COM_EASYSOCIAL_REGISTRATION_BADGE_REGISTERED');

		// Check if the profile type requires activation. Only log the user in when user is supposed to automatically login.
		$type = $profile->getRegistrationType(false, true);

		JFactory::getSession()->clear('user');

		// Only log the user in if the profile allows this.
		if ($type == 'auto') {
			$client->login();
		}

		return $this->view->call('oauthCreateAccount', $user);
	}


	/**
	 * Allows admin to approve a user via email
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function approveUser()
	{
		$key = $this->input->get('key', '', 'default');
		$id = $this->input->get('id', 0, 'int');

		$user = ES::user($id);
		$hash = md5($user->password . $user->email . $user->name . $user->username);

		// If the key provided is not valid, we do not do anything
		if ($hash != $key) {
			$this->view->setMessage('COM_EASYSOCIAL_REGISTRATION_MODERATION_FAILED_KEY_DOES_NOT_MATCH', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$user->approve();

		// once the user activated, process the pending referral
		$referral = $user->getEsParams()->get('referral');

		if ($referral) {
			$invites = ES::invites();
			$invites->registered($referral, $user->id);
		}

		$this->view->setMessage('COM_EASYSOCIAL_REGISTRATION_USER_ACCOUNT_APPROVED_SUCCESSFULLY');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows admin to reject a user via email
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function rejectUser()
	{
		$key = $this->input->get('key', '', 'default');
		$id = $this->input->get('id', 0, 'int');

		$user = ES::user($id);
		$hash = md5($user->password . $user->email . $user->name . $user->username);

		// If the key provided is not valid, we do not do anything
		if ($hash != $key) {
			$this->view->setMessage('COM_EASYSOCIAL_REGISTRATION_MODERATION_FAILED_KEY_DOES_NOT_MATCH', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$user->reject();

		$this->view->setMessage('COM_EASYSOCIAL_REGISTRATION_USER_ACCOUNT_REJECTED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * This is the registration API for modules. We allow modules to allow quick registration
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function quickRegister()
	{
		// Get current user's session
		$session = JFactory::getSession();

		// Get necessary info about the current registration process.
		$registration = ES::table('Registration');
		$registration->load($session->getId());

		// Get a new registry object
		$params = ES::get('Registry');

		if (!empty($registration->values)) {
			$params->load($registration->values);
		}

		// The profile id is definitely required otherwise we will skip this.
		$profileId = $registration->profile_id;

		if (empty($profileId)) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGISTRATIONS_MODULE_PROFILE_TYPE_REQUIRED'), ES_ERROR);
			return $this->view->call('selectProfile');
		}

		// Convert the params data into an array so we can manipulate this as an array.
		$data = $params->toArray();

		// Get the fields for quick registration
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getQuickRegistrationFields($profileId);

		$fieldsLib = ES::fields();

		// Get the trigger handler
		$handler = $fieldsLib->getHandler();

		$args = array(&$data, &$registration);

		// Get error messages
		$errors = $fieldsLib->trigger('onRegisterMiniValidate', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		$registration->setErrors($errors);

		// The values needs to be stored in a JSON notation.
		$registration->values = json_encode($data);

		// Store registration into the temporary table.
		$registration->store();

		// Saving was intercepted by one of the field applications.
		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGISTRATION_FORM_ERROR_PROCEED_WITH_REGISTRATION'), ES_ERROR);

			return $this->view->call(__FUNCTION__, $profileId);
		}

		// Load up the registration model
		$model = ES::model('Registration');
		$user = $model->createUser($registration, true);

		// If there's no id, we know that there's some errors.
		if (empty($user->id)) {

			$errors = $model->getError();

			$this->view->setMessage($errors, ES_ERROR);

			return $this->view->call(__FUNCTION__, $profileId);
		}

		// After account has been created, we should delete the registration object
		$registration->delete();

		// Redirection will be dependent on the profile type's registration behavior.
		// If the profile type is auto login, we need to log the user in
		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Force unset on the user first to reload the user object
		SocialUser::$userInstances[$user->id] = null;

		// Get the current registered user data.
		$my = ES::user($user->id);

		// We need to send the user an email with their password
		$my->password_clear	= $user->password_clear;

		// Send notification to admin if necessary.
		if ($profile->getParams()->get('email.moderators', true)) {
			$model->notifyAdmins($data, $my, $profile);
		}

		// Send notification to admin if necessary.
		if ($profile->getParams()->get('custom.emails')) {
			$model->notifyCustom($data, $my, $profile);
		}

		// If everything goes through fine, we need to send notification emails out now.
		$model->notify($data, $my, $profile);

		// add new registered user into indexer
		$my->syncIndex();

		// We need to log the user in after they have successfully registered.
		if ($profile->getRegistrationType() == 'auto') {
			$app = JFactory::getApplication();

			$credentials = array('username' => $my->username, 'password' => $my->password_clear);

			// Try to log the user in
			$app->login($credentials);
		}

		// Store the user's custom fields data now.
		return $this->view->complete($my, $profile);
	}

	/**
	 * Processes quick registrations
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function miniRegister()
	{
		ES::checkToken();

		// Get current user's info
		$session = JFactory::getSession();

		// Get necessary info about the current registration process.
		$registration = ES::table('Registration');
		$registration->load($session->getId());

		// Create a new object to store the params
		$params = new stdClass();

		// If registration values was previously set
		if (!empty($registration->values)) {
			$params = json_decode($registration->values);
		}

		// Get post values
		$post = $this->input->post->getArray();

		// Keys to exclude
		$exclude = array(ES::token(), 'option', 'controller', 'task');

		// Go through each of the post vars
		foreach ($post as $key => $value) {

			if (!in_array($key, $exclude)) {

				if (is_array($value)) {
					$value = json_encode($value);
				}

				$params->$key = $value;
			}
		}

		// Capture honeypot field
		if ($this->config->get('registrations.honeypot')) {
			$honeypot = ES::honeypot();
			$trapped = $honeypot->isTrapped('registration');

			if ($trapped) {
				$this->view->setMessage(JText::_('Registration completed'));

				return $this->view->selectType();
			}
		}

		// Determines the mini registration mode
		$mode = $this->config->get('registrations.mini.mode', 'quick');

		// Determines the profile?
		$defaultProfile = $this->config->get('registrations.mini.profile', 'default');

		// Might be coming from module, in which we have to respect module settings
		if (isset($post['modRegisterType'])) {
			$mode = $post['modRegisterType'];
		}

		if (isset($post['modRegisterProfile'])) {
			$defaultProfile = $post['modRegisterProfile'];
		}

		// Get the default profile id that we should use.
		$profileModel = ES::model('Profiles');

		// If selected profile is default, then we check how many profiles are there
		if ($defaultProfile === 'default') {

			// We no longer allow the ability for user to select profile
			// This is because the rendered field might be different from user selected profile
			// Under that case, the mapping of the fields will be off and unable to validate/store accordingly
			// EG. Profile 1 has a password field with id 3, while Profile 2 has a password field id 5, if the rendered field is 3, but user selected profile 2, validation will fail because of field mismatch
			// Hence if the settings is set to default profile, then we always use default profile
			$defaultProfile = $profileModel->getDefaultProfile()->id;
		}

		// Set the profile id directly
		if (!empty($defaultProfile)) {

			$registration->profile_id = $defaultProfile;

			// Set the profile id in the params
			$params->profile_id = $defaultProfile;

			// Directly set the registration step as 1
			$registration->step = 1;
			$registration->addStepAccess(1);
		}

		// Convert the
		$registration->values = json_encode($params);

		// Store the registration
		$registration->store();

		// Decide what to do here based on the configuration
		// FULL -> Registration page, registration page then decides if there is 1 or more profile to choose
		// QUICK && profile id assigned -> quickRegistration
		// QUICK && no profile id -> Registration page with parameter quick=1

		// If mode is set to full, then we redirect to registration page
		if ($mode === 'full') {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_REGISTRATIONS_COMPLETE_REGISTRATION'), SOCIAL_MSG_INFO);

			return $this->view->call('fullRegister', $defaultProfile);
		}

		// If this is quick mode, we need to check whether there's a default profile
		if ($mode == 'quick' && !$defaultProfile) {
			return $this->view->call('selectProfile');
		}

		if ($mode == 'quick') {
			return $this->quickRegister();
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows user to confirmation their user email whether this is really you
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function confirmationUserEmail()
	{
		// Get the id from the request
		$id = $this->input->get('userid', 0, 'int');
		$currentUser = ES::user($id);

		// If user is already logged in, redirect to the dashboard.
		if ($this->my->isLoggedIn()) {
			return $this->view->call(__FUNCTION__, $currentUser);
		}

		// Get the token
		$token = $this->input->get('token', '', 'default');

		// If token is empty, warn the user.
		if (empty($token) || strlen($token) !== 32) {
			$this->view->setMessage(JText::_('COM_ES_REGISTRATION_CONFIRMATION_USER_EMAIL_AUTHENTICATION_FAILED'), ES_ERROR);
			return $this->view->call(__FUNCTION__, $currentUser);
		}

		// Verify the user based on the token.
		$model = ES::model('Registration');
		$user = $model->confirmationUserEmail($token);

		if ($user === false) {
			$this->view->setMessage($model->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $currentUser);
		}

		// notify site moderator new registered user awaiting for approval part
		$jConfig = ES::jConfig();
		$username = $user->username;

		// Generate a key for the admin's actions.
		$key = md5($user->password . $user->email . $user->name . $username);

		if ($this->config->get('registrations.emailasusername')) {
			$username = $user->email;
		}

		// Get the user profile link
		$profileLink = $user->getPermalink(true, true);

		// Push arguments to template variables so users can use these arguments
		$params = array(
							'username' => $username,
							'name' => $user->getName(),
							'avatar' => $user->getAvatar(SOCIAL_AVATAR_LARGE),
							'profileLink' => $profileLink,
							'email' => $user->email,
							'reject' => ESR::controller('registration' , array('external' => true, 'task' => 'rejectUser', 'id' => $user->id, 'key' => $key)),
							'approve' => ESR::controller('registration' , array('external' => true, 'task' => 'approveUser', 'id' => $user->id, 'key' => $key))
						);

		$title = JText::sprintf('COM_ES_EMAILS_REGISTRATION_MODERATOR_EMAIL_TITLE', $jConfig->getValue('sitename'));

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		if ($admins) {
			foreach ($admins as $admin) {
				$params['adminName'] = $admin->name;
				$namespace = 'site/registration/moderator.approvals';

				$mailer = ES::mailer();
				$template = $mailer->getTemplate();

				$template->setRecipient($admin->name, $admin->email);
				$template->setTitle($title);
				$template->setTemplate($namespace, $params);
				$template->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

				// Try to send out email to the admin now.
				$state = $mailer->create($template);
			}
		}

		$this->view->setMessage(JText::_('COM_ES_REGISTRATION_CONFIRMATION_EMAIL_ACCOUNT_COMPLETED_SUCCESS'), SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $user);
	}
}
