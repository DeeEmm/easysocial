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
	 * Allows caller to take a picture
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function saveCamPicture()
	{
		// Ensure that the user is a valid user
		ES::requireLogin();

		$image = $this->input->get('image', '', 'default');
		$image = imagecreatefrompng($image);

		ob_start();
		imagepng($image, null, 9);
		$contents = ob_get_contents();
		ob_end_clean();

		// Store this in a temporary location
		$file = md5(ES::date()->toSql()) . '.png';
		$tmp = JPATH_ROOT . '/tmp/' . $file;
		$uri = JURI::root() . 'tmp/' . $file;

		JFile::write($tmp, $contents);

		$result = new stdClass();
		$result->file = $file;
		$result->url = $uri;

		return $this->ajax->resolve($result);
	}

	/**
	 * Allows caller to take a picture
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function takePicture()
	{
		// Ensure that the user is logged in
		ES::requireLogin();

		$theme = ES::themes();
		$theme->set('uid', $this->my->id);

		$output = $theme->output('site/avatar/dialogs/capture.picture');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the popbox of a user when hovering over the name or avatar.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function popbox()
	{
		// Load front end's language file
		ES::language()->loadSite();

		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$user = ES::user($id);

		if (!$this->my->canView($user)) {
			$theme = ES::themes();
			$theme->set('user', $user);
			$contents = $theme->output('site/profile/popbox/restricted');

			return $this->ajax->resolve($contents);
		}

		$badges = $user->getBadges();

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('badges', $badges);

		$contents = $theme->output('site/profile/popbox/user');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation dialog to delete a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Only registered users can see this
		ES::requireLogin();

		$theme = ES::themes();
		$contents = $theme->output('site/profile/dialogs/profile.delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays error message when user tries to save an invalid form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function showFormError()
	{
		ES::requireLogin();
		ES::language()->loadAdmin();

		$stepIds = $this->input->get('stepIds', array(), 'array');
		$fieldIds = $this->input->get('fieldIds', array(), 'array');

		$results = array();
		$stepData = array();
		$fieldData = array();

		$activeStep = ES::table('FieldStep');
		$fieldsModel = ES::model('Fields');

		// Process the step id if there has required field haven't fill in yet
		if ($stepIds) {

			// Remove duplicate step value
			$stepIds = array_unique($stepIds);

			foreach ($stepIds as $stepId) {

				$activeStep = ES::table('FieldStep');
				$state = $activeStep->load($stepId);

				if ($state) {
					$obj = new stdClass;
					$obj->stepId = $stepId;
					$obj->title = $activeStep->title;

					$stepData[] = $obj;
				}
			}
		}

		// Process the field id if there has required field haven't fill in yet
		if ($fieldIds) {

			foreach ($fieldIds as $fieldId) {

				// Retrieve the certain field data
				$data = $fieldsModel->getSpecificFieldData($fieldId);

				if ($data) {
					// This data only contain stepid and the field title
					$fieldData[] = $data[0];
				}
			}
		}

		$theme = ES::themes();
		$theme->set('stepData', $stepData);
		$theme->set('fieldData', $fieldData);

		$contents = $theme->output('site/profile/dialogs/profile.error');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation dialog to delete a user
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmDeleteUser()
	{
		// Only registered users can see this
		ES::requireLogin();

		// Only site admins can access
		if (!$this->my->isSiteAdmin()) {
			return;
		}

		$theme = ES::themes();

		$uid = $this->input->get('id', 0, 'int');

		// check if user exists or not.
		$user = JFactory::getUser($uid);

		if (! $user->id) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_INVALID_USER'));
		}

		if (! $this->my->canDeleteUser($user)) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_DELETE_USER'));
		}

		$theme = ES::themes();

		$content = $theme->output('site/profile/dialogs/user.delete');

		return $this->ajax->resolve($content);
	}

	/**
	 * Confirmation to delete user
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function deleteUser()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$message = $this->getMessage();

		$theme = ES::themes();

		$theme->set('msgObj', $message);
		$theme->set('userListingLink', FRoute::users());
		$theme->set('dashboardLink', FRoute::dashboard());

		$contents = $theme->output('site/profile/dialogs/delete.success');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post operation once an account is unblocked from the site
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function unbanUser($user)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}
		$message = JText::_('COM_EASYSOCIAL_USER_UNBANNED_SUCCESS_MESSAGE');

		$theme = ES::themes();
		$theme->set('message', $message);
		$theme->set('user', $user);
		$contents = $theme->output('site/profile/dialogs/unban.success');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post operation after a user is banned on the site
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function banUser($user)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$message = $this->getMessage();

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('msgObj', $message);
		$theme->set('userListingLink', ESR::users());
		$theme->set('dashboardLink', ESR::dashboard());

		$contents = $theme->output('site/profile/dialogs/dialog.user.ban.success');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation dialog to ban a user
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmUnban()
	{
		// Only registered users can see this
		ES::requireLogin();

		$theme = ES::themes();

		$uid = $this->input->get('id', 0, 'int');

		$user = ES::user($uid);

		if (!$this->my->canBanUser($user)) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_BAN_USER'));
		}

		if (!$user->isBanned()) {
			return $this->ajax->reject(JText::_('COM_ES_PROFILE_HAS_BEEN_UNBANNED_ALREADY'));
		}

		$contents = $theme->output('site/profile/dialogs/unban');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation dialog to ban a user
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmBanUser()
	{
		// Only registered users can see this
		ES::requireLogin();

		$theme = ES::themes();

		$uid = $this->input->get('id', 0, 'int');

		$user = ES::user($uid);

		if (!$user->id) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_INVALID_USER'));
		}

		if ($user->isBanned()) {
			return $this->ajax->reject(JText::_('COM_ES_PROFILE_HAS_BEEN_BANNED_ALREADY'));
		}

		if (!$this->my->canBanUser($user)) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_BAN_USER'));
		}

		$contents = $theme->output('site/profile/dialogs/dialog.user.ban');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Confirmation to remove an avatar
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmRemoveAvatar()
	{
		// Only registered users can do this
		ES::requireLogin();

		$userId = $this->input->get('id', 0, 'int');
		$user = ES::user($userId);

		$theme = ES::themes();
		$theme->set('user', $user);
		$contents = $theme->output('site/profile/dialogs/remove.avatar');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation to delete a post on the report page
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function confirmDownload()
	{
		$userId = $this->my->id;

		$table = ES::table('download');
		$table->load(array('userid' => $userId));
		$state = $table->getState();


		$email = $this->my->email;
		$emailPart = explode('@', $email);
		$email = ESJString::substr($emailPart[0], 0, 2) . '****' . ESJString::substr($emailPart[0], -1) . '@' . $emailPart[1];

		$theme = ES::themes();
		$theme->set('userId', $userId);
		$theme->set('email', $email);
		$output = $theme->output('site/profile/dialogs/gdpr.confirm');

		return $this->ajax->resolve($output);
	}
}
