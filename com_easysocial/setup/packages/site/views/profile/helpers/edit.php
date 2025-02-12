<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewProfileEditHelper extends EasySocial
{
	/**
	 * Init user data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function initData()
	{
		static $data = null;

		if (is_null($data)) {

			// There is instances where we can allow user to edit another person's profile through an app
			$id = $this->input->get('id', $this->my->id, 'int');
			$canEdit = $id == $this->my->id;

			$user = ES::user($id);

			$arguments = array(&$user, &$canEdit);

			$dispatcher = ES::dispatcher();
			$dispatcher->trigger(SOCIAL_TYPE_USER, 'onDisplayProfileEdit', $arguments);

			$data = new stdClass();
			$data->user = $user;
			$data->canEdit = $canEdit;
		}

		return $data;
	}

	/**
	 * Get all steps related data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getStepsData($user, $errors = '')
	{
		static $data = null;

		if (is_null($data)) {

			$activeStep = $this->getActiveStep();

			// Get list of steps for this user's profile type.
			$profile = $user->getProfile();

			// Get workflow that are associated with this profile type
			$workflow = $profile->getWorkflow();

			// Get the steps model
			$stepsModel = ES::model('Steps');
			$isLastStep = false;
			$steps = array();

			$allSteps = $stepsModel->getSteps($workflow->id, SOCIAL_TYPE_PROFILES, SOCIAL_PROFILES_VIEW_EDIT);

			if ($this->config->get('users.profile.editLogic', 'default') == 'steps') {
				// we only want the first step
				$steps = array($allSteps[0]);

				if ($activeStep) {
					// we only want the specific step
					$step = ES::table('FieldStep');
					$step->load($activeStep);

					$isLastStep = $step->sequence == count($allSteps) ? true : false;
					$steps = array($step);
				}
			}

			// this is true if edit logic is set to default
			if (! $steps) {
				$steps = $allSteps;
			}

			// Get custom fields model.
			$fieldsModel = ES::model('Fields');

			// Get custom fields library.
			$fields = ES::fields();

			// Set the callback for the triggered custom fields
			$callback = array($fields->getHandler(), 'getOutput');

			$conditionalFields = array();

			// preload fields for each steps.
			ES::cache()->cacheProfileStepsFields($steps, SOCIAL_PROFILES_VIEW_EDIT);

			// preload fields privacy.
			$tmpFields = array();
			foreach ($steps as &$step) {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => 'edit'));
				if ($step->fields) {
					$tmpFields = array_merge($tmpFields, $step->fields);
				}
			}

			ES::cache()->cacheFieldsPrivacy($user->id, $tmpFields);

			// Get the custom fields for each of the steps.
			foreach ($steps as &$step) {

				// Trigger onEdit for custom fields.
				if (!empty($step->fields)) {

					$post = $this->input->post->getArray();
					$args = array( &$post, &$user, $errors);
					$fields->trigger('onEdit', SOCIAL_FIELDS_GROUP_USER, $step->fields, $args, $callback);
				}

				foreach ($step->fields as $field) {
					if ($field->isConditional()) {
						$conditionalFields[$field->id] = false;
					}
				}
			}

			if ($conditionalFields) {
				$conditionalFields = json_encode($conditionalFields);
			} else {
				$conditionalFields = false;
			}

			$data = new stdClass();

			$data->workflow = $workflow;
			$data->conditionalFields = $conditionalFields;
			$data->allSteps = $allSteps;
			$data->steps = $steps;
			$data->isLastStep = $isLastStep;

		}

		return $data;

	}

	/**
	 * Get current active step
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveStep()
	{
		$activeStep = $this->input->get('activeStep', 0, 'int');
		return $activeStep;
	}

	/**
	 * Determine if we need to show verification link
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function showVerificationLink()
	{
		static $showVerificationLink = null;

		if (is_null($showVerificationLink)) {
			$verification = ES::verification();
			$showVerificationLink = false;

			if ($verification->canRequest($this->my->id, SOCIAL_TYPE_USER)) {
				$showVerificationLink = true;
			}
		}

		return $showVerificationLink;
	}

	/**
	 * Get user's oauth clients
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getOauthClients($user)
	{
		static $oauthClients = null;

		if (is_null($oauthClients)) {

			// Get oauth clients
			$oauthModel = ES::model('OAuth');
			$oauthClients = $oauthModel->getOauthClients($user->id);
		}

		return $oauthClients;
	}


	/**
	 * Get user's profile count
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getProfileCount($user)
	{
		static $profilesCount = null;

		if (is_null($profilesCount)) {

			$profilesCount = 0;

			if ($user->canSwitchProfile()) {
				$profileModel = ES::model('Profiles');
				$profilesCount = $profileModel->getTotalProfiles();
			}

		}

		return $profilesCount;
	}


}
