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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationProfiles extends EasySocialSetupController
{
	/**
	 * Install default custom profiles and fields
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$this->engine();

		$results = array();

		// Create the default custom profile first.
		$results[] = $this->createCustomProfile();

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class 	= $obj->state ? 'success' : 'error';
			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}


		return $this->output( $result );
	}

	/**
	 * Creates the default custom profile
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function createCustomProfile()
	{
		$this->engine();

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_profiles');
		$sql->column('id');
		$sql->limit(0, 1);

		$db->setQuery($sql);
		$id = $db->loadResult();

		// We don't have to do anything since there's already a default profile
		if ($id) {
			$this->updateConfig('oauth.facebook.registration.profile', $id);

			$result = $this->getResultObj('Skipping custom profile creation as there are other custom profiles already installed.', true);
			return $result;
		}

		// If it doesn't exist, we'll have to create it.
		$profile = ES::table('Profile');
		$profile->title = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_PROFILE_TITLE');
		$profile->description = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_PROFILE_DESC');

		// Get the default user group that the site is configured and select this group as the default for this profile.
		$usersConfig = JComponentHelper::getParams('com_users');
		$group = array($usersConfig->get('new_usertype'));

		// Set the group for this default profile
		$profile->gid = json_encode($group);
		$profile->default = 1;
		$profile->state = SOCIAL_STATE_PUBLISHED;
		$profile->alias = '';
		$profile->apps = '';

		// Set the default params for profile
		$params = ES::registry();
		$params->set('delete_account', 0);
		$params->set('theme', '');
		$params->set('registration', 'approvals');

		$profile->params = $params->toString();

		// Try to save the profile.
		$state = $profile->store();

		// Assign default workflow
		$profile->assignWorkflow();

		// Assign default privacy
		$profile->assigneDefaultPrivacy();


		if (!$state) {
			$result = $this->getResultObj('COM_EASYSOCIAL_INSTALLATION_ERROR_CREATE_DEFAULT_PROFILE', false);
			return $result;
		}

		$this->updateConfig('oauth.facebook.registration.profile', $profile->id);

		$result = $this->getResultObj('Created default profile successfully.', true);

		return $result;
	}
}
