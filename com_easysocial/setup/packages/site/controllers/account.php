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

class EasySocialControllerAccount extends EasySocialController
{
	/**
	 * Method to remind username
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function remindUsername()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the current logged in user.
		$user = ES::user();

		if ($user->id) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PROFILE_YOU_ARE_ALREADY_LOGGED_IN'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the email address
		$email = $this->input->get('es-email', '', 'string');

		if (!$email) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_EMAIL_REQUIRED_ERROR'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$state = $user->remindUsername($email);

		if (!$state) {
			$this->view->setMessage($user->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PROFILE_USERNAME_SENT', $email));
		return $this->view->call(__FUNCTION__);
	}


	/**
	 * Processes username reminder
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remindPassword()
	{
		// Check for request forgeries
		ES::checkToken();

		$user = ES::user();

		if ($user->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_YOU_ARE_ALREADY_LOGGED_IN', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the email address
		$email = $this->input->get('es-email', '', 'string');

		if (!$email) {
			$this->view->setMessage('COM_EASYSOCIAL_EMAIL_REQUIRED_ERROR', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Remind password now
		$state = $user->remindPassword($email);

		if (!$state) {
			$this->view->setMessage($user->getError(), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PROFILE_USERNAME_SENT' , $email));
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Password reset confirmation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmResetPassword()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the current logged in user.
		$my = ES::user();

		if ($my->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_YOU_ARE_ALREADY_LOGGED_IN', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$username = $this->input->get('es-username', '', 'string');
		$code = $this->input->get('es-code', '', 'string');

		$model = ES::model('Users');
		$state = $model->verifyResetPassword($username, $code);

		if (!$state) {
			$this->view->setMessage($model->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Completes password reset
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function completeResetPassword()
	{
		ES::checkToken();

		if ($this->my->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_YOU_ARE_ALREADY_LOGGED_IN', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$password = $this->input->get('es-password', '', 'raw');
		$password2 = $this->input->get('es-password2', '', 'raw');

		// Check if the password matches
		if ($password != $password2) {
			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_PASSWORDS_NOT_MATCHING', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$model = ES::model('Users');
		$state = $model->resetPassword($password, $password2);

		if (!$state) {
			$this->view->setMessage($model->getError(), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_SUCCESSFUL', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Completes require password reset
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function completeRequireResetPassword()
	{
		// Check for request forgeries
		ES::checkToken();

		$password = $this->input->get('es-password', '', 'string');
		$password2 = $this->input->get('es-password2', '', 'string');

		// Check if the password matches
		if (!$password || !$password2 || $password != $password2) {
			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_PASSWORDS_NOT_MATCHING', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$model = ES::model('Users');
		$state = $model->resetRequirePassword($password , $password2);

		if (!$state) {
			$this->view->setMessage($model->getError(), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILE_REQUIRE_PASSWORD_UPDATE_SUCCESSFUL', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Replicate login behavior of Joomla
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function login()
	{
		ES::checkToken();

		$app = JFactory::getApplication();

		// Populate the data array:
		$data = array();
		$data['return'] = base64_decode($this->input->post->get('return', '', 'BASE64'));
		$data['username'] = $this->input->get('username', '', 'string');
		$data['password'] = $this->input->get('password', '', 'raw');
		$data['secretkey'] = $this->input->get('secretkey', '', 'raw');
		$data['fromSharer'] = $this->input->get('fromSharer', 0, 'int');

		// Get the user's state because there could be instances where Joomla is redirecting users
		$tmp = $app->getUserState('users.login.form.data');

		if (isset($tmp['return']) && !empty($tmp['return'])) {
			$data['return']	= $tmp['return'];
		}

		// Set the return URL if empty.
		if (empty($data['return'])) {
			$data['return'] = 'index.php?option=com_easysocial&view=login';
		}

		// Set the return URL in the user state to allow modification by plugins
		$app->setUserState('users.login.form.return', $data['return']);

		// Get the log in options.
		$options = array();
		$options['remember'] = $this->input->get('remember', false, 'bool');
		$options['return'] = $data['return'];
		$options['silent'] = true;

		// Get the log in credentials.
		$credentials = array();
		$credentials['username']  = $data['username'];
		$credentials['password']  = $data['password'];
		$credentials['secretkey'] = $data['secretkey'];

		// perform user login here.
		$state = $app->login($credentials, $options);

		// Perform the log in.
		if ($state === true) {

			// after login process, there are changes wehre system plugins altered the return url, e.g. language
			// filter plugins for menu item association redirection.
			$xReturn = $app->getUserState('users.login.form.return');

			if ($xReturn && $xReturn != $data['return'] && !$data['fromSharer']) {
				$data['return']	= $xReturn;
			}

			$userModel = ES::model('Users');

			// we need to check if user required to reset password or not.
			$jUser = JFactory::getUser();

			// Deprecated in 3.0 onwards, we should always use joomla reset password form. #1851
			// this is for Joomla's JUser->requireReset enabled
			// if (isset($jUser->requireReset) && $jUser->requireReset) {

				//@TODO:: if juser->requireReset, we need to reset this flag and enable the require_reset flag from our social_user
				// to avoid infnity loop caused by redirections
				// $userModel->updateJoomlaUserPasswordResetFlag($jUser->id, '0', '1');

				// $jUser->setProperties(array('requireReset' => '0'));
			// }

			// let get user data again.
			$user = ES::user($jUser->id);

			// Double check if user really logged in. #414
			if (!$user->id) {
				$url = ESR::login();
				return ES::redirect($url);
			}

			// If admin enforced a different login redirection, we need to redirect to the appropriate page
			$customReturnUrl = $user->getLoginRedirectionLink();

			if ($customReturnUrl !== false) {
				$data['return'] = $customReturnUrl;
			}

			// @TODO:: here we will redirect user to our password reset page. awesome possum.
			if ($user->require_reset) {
				$url = ESR::account([
					'layout' => 'requirePasswordReset'
				], false);

				return ES::redirect($url);
			}

			// let update the reminder_sent flag to 0.
			$userModel->updateReminderSentFlag($user->id, '0');

			// Set the remember state
			if ($options['remember'] == true) {
				$app->setUserState('rememberLogin', true);
			}

			// Success
			$app->setUserState('users.login.form.data', array());

			// Redirect link should use the return data instead of relying it on getUserState('users.login.form.return')
			// Because EasySocial has its own settings of login redirection, hence this should respect the return link passed
			// We cannot fallback because the return link needs to be set in the options before calling login, and as such, the fallback has been set before calling $app->login, and no fallback is needed here.
			ES::redirect(JRoute::_($data['return'], false));

			return;
		}

		// Login failed !
		$data['remember'] = (int) $options['remember'];
		$app->setUserState('users.login.form.data', $data);

		$authenticationMessage = JText::_('JGLOBAL_AUTH_INVALID_PASS');
		$returnFailed = base64_decode($app->input->post->get('returnFailed', '', 'BASE64'));

		if (empty($returnFailed)) {
			$returnFailed = ESR::login(array(), false);
		}

		// Try to check whether have this login failed message on session
		$session = JFactory::getSession();
		$authenticationLoginStatus = $session->get('easysocial.authentication.login.status', '', SOCIAL_SESSION_NAMESPACE);

		// Override the default authentication error message
		if ($authenticationLoginStatus) {
			$authenticationMessage = $authenticationLoginStatus;
		}

		// set the show login failed message on the page.
		$this->info->set(null, $authenticationMessage, ES_ERROR);

		// Clear the session after display this authentication login failed message.
		$session->clear('easysocial.authentication.login.status', SOCIAL_SESSION_NAMESPACE);

		// Redirect to the login page and display error message.
		ES::redirect($returnFailed);
	}

	/**
	 * Allows caller to log the user out from the site
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		// Perform the logout
		$error = $this->app->logout();

		// Check if the log out succeeded.
		if (!($error instanceof Exception)) {

			// PLEASE NOTE:
			// In easysocial system plugin, if site enable language filter plugin and enabled 'automatic url change',
			// there is a chance the return url get altered due to multilingual setup.
			// Please see easysocial sytem plugin under onUserLogout method. #63

			// Get the return url from the request and validate that it is internal.
			// $return = $this->input->get('return', '', 'base64');

			$return = $this->input->getBase64('return', '');
			$return = base64_decode($return);

			// Check for logout redirection
			$returnSamePage = $this->config->get('general.site.logout');

			// Check for the valid internal url if logout redirection is set to not stay on same page. #253
			if (!JUri::isInternal($return) && $returnSamePage != 'null') {
				$return = '';
			}

			// Redirect the user.
			ES::redirect(JRoute::_($return, false));
		}

		ES::redirect(FRoute::login(array(), false));
	}

	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isLockDown($task)
	{
		$allowed = array('login', 'confirmResetPassword', 'completeResetPassword', 'remindPassword', 'remindUsername');

		if (in_array($task, $allowed)) {
			return false;
		}
		return true;
	}

	/**
	 * Set the ageverification into session. Used by age verification plugin.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function ageVerification()
	{
		$sessionToken = $this->input->get('sessionToken', false);
		$session = JFactory::getSession();
		$session->set($sessionToken, 1);

		$return = $this->input->get('return', '/');
		ES::redirect(JRoute::_(base64_decode($return), false));
	}
}
