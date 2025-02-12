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

class EasySocialViewAccount extends EasySocialSiteView
{
	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isLockDown()
	{
		$layout = $this->getLayout();
		$allowed = array( 'forgetUsername' , 'forgetPassword' , 'confirmReset' , 'confirmResetPassword' , 'resetUser' , 'completeResetPassword', 'completeReset' );

		if ($this->config->get('general.site.lockdown.registration') || in_array($layout, $allowed)) {
			return false;
		}

		return true;
	}

	/**
	 * There is no display method for this view, we need to redirect it back to dashboard
	 *
	 * @since  1.3.9
	 * @access public
	 */
	public function display($tpl = null)
	{
		return $this->redirect(FRoute::dashboard());
	}

	/**
	 * Post process after reminding username
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remindUsername()
	{
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->redirect(ESR::account(array('layout' => 'forgetUsername'), false));
		}

		$this->redirect(ESR::login(array(), false));
	}

	/**
	 * Post process after reminding password
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remindPassword()
	{
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->redirect(ESR::account(array('layout' => 'forgetPassword'), false));
		}

		$url = ESR::account(array('layout' => 'confirmReset'), false);

		$this->redirect($url);
	}

	/**
	 * Post process after user resets the password
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function completeResetPassword()
	{
		ES::setMeta();

		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->redirect(ESR::account(array('layout' => 'completeReset'), false));
		}

		$this->redirect(ESR::login(array(), false));
	}

	/**
	 * Post process after user enters the verification code
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmResetPassword()
	{
		ES::setMeta();

		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->redirect(ESR::account(array('layout' => 'confirmReset'), false));
		}

		$redirect = ESR::account(array('layout' => 'completeReset'), false);

		$this->redirect($redirect);
	}

	/**
	 * Displays the forget username form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function forgetUsername()
	{
		if ($this->my->id) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		ES::setMeta();

		// Set the page title
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_REMIND_USERNAME');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_REMIND_USERNAME');

		return parent::display('site/account/forgetusername/default');
	}

	/**
	 * Displays the forget password form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function forgetPassword()
	{
		// If user is already logged in, do not allow them here.
		if ($this->my->id) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		ES::setMeta();

		// Set the document properties
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_REMIND_PASSWORD');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_REMIND_PASSWORD');

		return parent::display('site/account/forgetpassword/default');
	}

	/**
	 * Displays the forget password form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmReset()
	{
		// Check if token is exists from the request.
		$token = $this->input->get('token', '', 'raw');
		$username = $this->input->Get('username', '', 'raw');

		if ($token && $username) {
			$model = ES::model('Users');
			$state = $model->verifyResetPassword($username, $token);

			// Password reset request verified.
			if ($state) {
				return $this->confirmResetPassword();
			}

			// If the above process fail, we let the user to reset the password manually
			ES::info()->set($model->getError(), SOCIAL_MSG_ERROR);
		}

		return parent::display('site/account/confirmreset/default');
	}

	/**
	 * Displays the forget password form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function completeReset()
	{
		$token = $this->app->getUserState('com_users.reset.token', null);
		$userId = $this->app->getUserState('com_users.reset.user', null);

		$enableValidation = false;

		// Skip here if there doesn't have any valid token and user id
		if (!$token && !$userId) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Lets check if user has the joomla password field enabled or not.
		$user = ES::user($userId);

		$model = ES::model('Fields');
		$options = array('group' => SOCIAL_TYPE_USER, 'workflow_id' => $user->getWorkflow()->id, 'data' => false , 'dataId' => $user->id , 'dataType' => SOCIAL_TYPE_USER, 'element' => 'joomla_password');

		$items = $model->getCustomFields($options);

		if ($items) {
			$passwordField = $items[0];

			$params = $passwordField->getParams();
			$this->set('params', $params);

			$enableValidation = true;
		}

		$this->set('enableValidation', $enableValidation);

		parent::display('site/account/completereset/default');
	}


	/**
	 * Displays the password reset form
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function requirePasswordReset()
	{
		return parent::display('site/account/require.reset.password');
	}

	/**
	 * Displays the password reset form
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function completeRequireResetPassword()
	{
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			$redirect = FRoute::account(array('layout' => 'requirePasswordReset'), false);
			return $this->redirect($redirect);
		}

		$redirect = FRoute::dashboard(array(), false);

		if (!$this->my->hasCommunityAccess()) {
			$redirect = ESR::profile(array('layout' => 'edit'), false);
		}

		$this->redirect($redirect);
	}
}
