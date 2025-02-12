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

class EasySocialViewProfileSwitchHelper extends EasySocial
{

	public function getNewProfileType()
	{
		static $profile = null;

		if (is_null($profile)) {
			$newProfileId = $this->input->get('profile_id', 0, 'int');

			$profile = ES::table('Profile');
			$profile->load($newProfileId);
		}

		return $profile;
	}

	/**
	 * Get all steps related data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getStepsData($errors = '')
	{
		static $data = null;

		if (is_null($data)) {

			$profile = $this->getNewProfileType();

			// Get user's installed apps
			$appsModel = ES::model('Apps');
			$userApps = $appsModel->getUserApps($this->my->id);

			// Get the steps model
			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($profile->getWorkflow()->id, SOCIAL_TYPE_PROFILES, SOCIAL_PROFILES_VIEW_EDIT);

			// Get custom fields model.
			$fieldsModel = ES::model('Fields');

			// Get custom fields library.
			$fields = ES::fields();

			// Set the callback for the triggered custom fields
			$callback = array($fields->getHandler(), 'getOutput');

			$conditionalFields = array();

			// Get the custom fields for each of the steps.
			foreach ($steps as &$step) {

				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $this->my->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => 'edit'));

				// Trigger onEdit for custom fields.
				if (!empty($step->fields)) {

					// here we need to inject the value from current profile.
					$post = array();
					foreach ($step->fields as $field) {
						$value = $this->my->getFieldData($field->unique_key);

						$fieldKey = SOCIAL_FIELDS_PREFIX . $field->id;
						$post[$fieldKey] = $value;
					}

					$args = array( &$post, &$this->my, $errors);
					$fields->trigger('onEdit' , SOCIAL_FIELDS_GROUP_USER , $step->fields , $args, $callback );
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

			$data->conditionalFields = $conditionalFields;
			$data->steps = $steps;

		}

		return $data;

	}

}
