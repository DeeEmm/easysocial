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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserBoolean extends SocialFieldItem
{
	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$value = $this->value;

		if ($value == '') {
			return;
		}

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		// linkage to advanced search page.
		$field = $this->field;

		$advGroups = array(SOCIAL_FIELDS_GROUP_GROUP, SOCIAL_FIELDS_GROUP_USER, SOCIAL_FIELDS_GROUP_EVENT, SOCIAL_FIELDS_GROUP_PAGE);

		// reset the search link. #4168
		$this->set('advancedsearchlink', '');

		if (in_array($field->type, $advGroups) && $field->searchable) {
			$params = array('layout' => 'advanced');

			if ($field->type != SOCIAL_FIELDS_GROUP_USER) {
				$params['type'] = $field->type;
				$params['uid'] = $field->uid;
			}

			$params['criterias[]'] = $field->unique_key . '|' . $field->element;
			$params['operators[]'] = 'equal';
			$params['conditions[]'] = $this->value;

			$advsearchLink = ESR::search($params);
			$this->set('advancedsearchlink', $advsearchLink);
		}

		// Push variables into theme.
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$value = $this->value;

		if ($value == '') {
			return;
		}

		// retrieve field data
		$field = $this->field;

		$formattedValue = JText::_('PLG_FIELDS_BOOLEAN_FALSE');

		if ($value === '1') {
			$formattedValue = JText::_('PLG_FIELDS_BOOLEAN_TRUE');
		}

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $formattedValue;

		return $data;
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get the value
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		// Set the value
		$this->set('value', $value);

		// Check for errors
		$error = $this->getError($errors);

		// Set errors.
		$this->set('error', $error);

		// Display the output.
		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Check for post value
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->params->get('default');

		// Set value
		$this->set('value', $value);

		// Check for errors
		$error = $registration->getErrors($this->inputName);

		// Set errors
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Determine if the app published or unpublished 
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	protected function appEnabled($groupType)
	{
		// Retrieve the few data
		$field = $this->field;

		$app = ES::table('App');
		$app->load(array('group' => $groupType, 'element' => $field->element, 'type' => 'apps'));

		// If app has been unpublished, skip this field altogether
		if (!$app->id || !$app->state) {
			return false;
		}

		return true;
	}	
}