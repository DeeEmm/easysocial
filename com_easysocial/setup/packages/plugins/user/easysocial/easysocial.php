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

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/plugins.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class PlgUserEasySocial extends EasySocialPlugins
{
	/**
	 * This method would intercept logins for email, social logins
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onUserLogin($user, $options = array())
	{
		// Load the language string.
		ES::language()->load('plg_user_easysocial', JPATH_ADMINISTRATOR);

		if (isset($user['status']) && $user['status'] && ($user['type'] == 'Joomla' || $user['type'] == 'easysocial')) {

			//successful logged in.
			$my = JUser::getInstance();
			$id = intval(JUserHelper::getUserId($user['username']));

			if ($id) {
				$my->load($id);

				// Check if this user being blocked or not.
				if ($my->block == 1) {

					// Check if we need to release this user automatically or not.
					$userModel = ES::model('Users');
					$bannedUsers = $userModel->getExpiredBannedUsers($my->id);

					if ($bannedUsers) {
						$esUser = ES::user($my->id);
						$esUser->unblock();
						$userModel->updateBlockInterval($bannedUsers, '0');
					}
				}
			}

			$app = ES::table('App');
			$app->load(array('element' => 'users', 'group' => SOCIAL_TYPE_USER));

			$appParams = $app->getParams();
			$addStream = false;

			// Refresh the one time mobile token
			$esUser = ES::user($my->id);
			$esUser->getOnetimeMobileToken(true);

			if ($appParams->get('stream_login', false)) {
				$addStream	= true;
			}

			// If this is the admin area, skip this.
			if (ES::isFromAdmin()) {
				return;
			}

			// Only proceed if we need to add the stream
			if ($addStream) {
				$model = ES::model('Users');

				// Get the last login time the user previously logged in.
				$lastLogin = $model->getLastLogin($my->id);

				if ($lastLogin) {
					$lastLogin->count = (ES::isJoomla25()) ? $lastLogin->count + 1 : $lastLogin->count;

					if ($lastLogin->count >= 2 && $lastLogin->limit < $lastLogin->time) {
						$addStream = false;
					}
				}
			}

			if ($addStream) {
				$stream = ES::stream();

				$template = $stream->getTemplate();
				$template->setActor($my->id, SOCIAL_TYPE_USER);
				$template->setContext($my->id, SOCIAL_TYPE_USERS);
				$template->setVerb('login');
				$template->setAccess('core.view');
				$stream->add($template);
			}
		}
	}

	/**
	 * Perform clean ups before a user is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onUserBeforeDelete($user)
	{
		// There are instances where admin will remove the user directly from joomla user manager,
		
		$model = ES::model('Users');
		$state = $model->delete($user);

		// Internal Trigger for onUserBeforeDelete
		$dispatcher = ES::dispatcher();
		
		$args = array(&$user);

		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, __FUNCTION__, $args);

		return true;
	}

	/**
	 * Upon new registration, check if the user is associated with any profile type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		if (!$isnew) {
			return $this->processExistingUser($user, $isnew, $success, $msg);
		} else {
			return $this->processNewUser($user, $isnew, $success, $msg);
		}

		return true;
	}

	/**
	 * Method to prcess existing users
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function processExistingUser($user, $isnew, $success, $msg)
	{
		$options = $this->input->get('option', '', 'default');
		$tasks = $this->input->get('task', '', 'default');

		$isActivation = false;
		$isBlocking = false;
		$isPending = false;

		// this could be coming from com_users | com_payplans activiation instead of EasySocial activation. lets check who
		// is the caller
		if (($options == 'com_users' || $options == 'com_payplans') && ($tasks == 'activate' || $tasks == 'activate_user' || $tasks == 'unblock')) {

			$registry = ES::registry();
			$registry->load($user['params']);
			$userParams = $registry->toArray();

			$hasUserActivated = $userParams && isset($userParams['activate']) && $userParams['activate'] ? true : false;
			$hasActivationToken = $user['activation'];

			// check the Joomla user activation token and params has contain the "activate" code or not (basically the Joomla registration type is set to administrator)
			// if the activation column still have the token and the params contains {"activate":1} means this user still waiting for the admin approval after user confirmed the email account
			if (!empty($hasActivationToken) && $hasUserActivated) {
				$isPending = true;
			}

			$isActivation = true;

		} else if ($options == 'com_users' && $tasks == 'block') {
			$isBlocking = true;
		}

		$table = ES::table('Users');
		$state = $table->load($user['id']);

		// If user is already registered within EasySocial, all is good.
		if ($state) {
			// lets check if this user is activating account or not.
			// if yes, we update the state.
			if ($isActivation) {
				$table->state = $isPending ? SOCIAL_USER_STATE_PENDING : SOCIAL_USER_STATE_ENABLED;
				$table->store();
			}

			if ($isActivation || $isBlocking) {
				$easysocialUser = ES::user($user['id']);
				$easysocialUser->syncIndex();
			}

			// Link password reset state
			if ($user['requireReset'] != $table->require_reset) {
				$table->require_reset = $user['requireReset'];
				$table->store();
			}
		}

		return true;
	}

	/**
	 * Method to process new user registration
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function processNewUser($user, $isnew, $success, $msg)
	{
		$table = ES::table('Users');
		$table->load($user['id']);

		$model = ES::model('Users');

		// Determine which alias we should use
		$alias = $user['name'];

		if ($this->config->get('users.aliasName') == 'username') {
			$alias = $user['username'];

			// If admin configured to use email as username, or user enter their email as username, due to security concern, we will use fullname as alias.
			if ($this->config->get('registrations.emailasusername') || JMailHelper::isEmailAddress($alias)) {
				$alias = $user['name'];
			}
		}

		// if the activation column have the token and the params don't contain {'activate'} key means this user haven't activate anything yet
		$registry = ES::registry();
		$registry->load($user['params']);
		$userParams = $registry->toArray();

		$userState = SOCIAL_USER_STATE_ENABLED;
		$isBlocked = $user['block'] == SOCIAL_JOOMLA_USER_BLOCKED;
		$hasActivationToken = $user['activation'];
		$hasUserActivated = $userParams && isset($userParams['activate']) && $userParams['activate'] ? true : false;

		if ($isBlocked) {
			$userState = SOCIAL_USER_STATE_PENDING;
		}

		if (!empty($hasActivationToken) && $isBlocked && !$hasUserActivated) {
			$userState = SOCIAL_USER_STATE_ACTIVATION;
		}


		$table->user_id = $user['id'];
		$table->alias = $model->generateAlias($alias, $user['id']);
		$table->state = $userState;
		$table->type = 'joomla';
		$table->store();

		// Assign the user into a default profile type
		$profileModel = ES::model('Profiles');
		$profile = $profileModel->getDefaultProfile();

		if ($profile) {
			$profile->addUser($user['id']);
		}

		// to support payplan quick registration as well.
		$component = $this->input->get('option', '', 'default');
		$controller = $this->input->get('controller', '', 'default');

		// Additional processing if user is registered from outside EasySocial.
		if ($component != 'com_easysocial' && $controller != 'registration') {

			// Index the user
			$easysocialUser = ES::user($user['id']);
			$easysocialUser->syncIndex();

			// Assign points when user registers on the site.
			$points = ES::points();
			$points->assign('user.registration', 'com_easysocial', $user['id']);

			// Assign registration badge.
			$badge = ES::badges();
			$badge->log('com_easysocial', 'registration.create', $user['id'], JText::_('COM_EASYSOCIAL_REGISTRATION_BADGE_REGISTERED'));
		}

		return true;
	}
}
