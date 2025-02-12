<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/country/helper');

class SocialFieldsUserCountry extends SocialFieldItem
{
	/**
	 * Displays the form during registration
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function onRegister(&$post, &$registration)
	{
		$source = $this->params->get('data_source', 'regions');

		$countries = SocialFieldsUserCountryHelper::getHTMLContentCountries($source);
		$selected = $this->processSelectedData(!empty($post[$this->inputName]) ? $post[$this->inputName] : '', '');

		// Set errors
		$error = $registration->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('countries', $countries);
		$this->set('selected', $selected);

		return $this->display();
	}

	/**
	 * Processes the post data and validates the country data.
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * Displays the edit form when a user edits their profile.
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$source = $this->params->get('data_source', 'regions');
		$countries = SocialFieldsUserCountryHelper::getHTMLContentCountries($source);
		$selected = $this->processSelectedData(!empty($post[$this->inputName]) ? $post[$this->inputName] : '', $this->value);

		$this->set('countries', $countries);
		$this->set('selected', $selected);
		$this->set('error', $this->getError($errors));

		return $this->display();
	}

	/**
	 * Processes the post data and validates the edit
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function onEditValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	private function validateInput($value)
	{
		if ($this->isRequired() && empty($value)) {
			return $this->setError(JText::_('PLG_FIELDS_COUNTRY_VALIDATION_REQUIRED'));
		}

		$value = !empty($value) ? ES::makeArray($value) : array();

		if ($this->isRequired() && (empty($value) || (isset($value[0]) && empty($value[0])))) {
			$this->setError(JText::_('PLG_FIELDS_COUNTRY_VALIDATION_REQUIRED'));
			return false;
		}

		if (!$this->isRequired() && empty($value)) {
			return true;
		}

		$count = count($value);

		if ($this->params->get('min') > 0 && $count < $this->params->get('min')) {
			$this->setError(JText::_('PLG_FIELDS_COUNTRY_VALIDATION_MINIMUM_ERROR'));
			return false;
		}

		if ($this->params->get('max') > 0 && $count > $this->params->get('max')) {
			$this->setError(JText::_('PLG_FIELDS_COUNTRY_VALIDATION_MAXIMUM_ERROR'));
			return false;
		}

		return true;
	}

	public function onDisplay($user)
	{
		$value = $this->value;

		if (!$value) {
			return;
		}

		$value = ES::makeArray($value);

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$field = $this->field;

		$countries = array();
		$aslink = array();

		foreach ($value as $v) {
			$country = SocialFieldsUserCountryHelper::getCountryName($v, $this->params->get('data_source', 'regions'));

			if ($country) {
				if ($field->type == SOCIAL_FIELDS_GROUP_USER && $field->searchable) {
					$params = array('layout' => 'advanced');
					$params['criterias[]'] = $field->unique_key . '|' . $field->element;
					$params['operators[]'] = 'equal';

					$countryCode = SocialFieldsUserCountryHelper::getCountryCode($country, 'regions');
					$tmp = explode(':', $countryCode);

					$cid =$tmp[1]; 
					$code =$tmp[0]; 

					$params['conditions[]'] = $code . '|' . $countryCode . '|' . $country;

					$advsearchLink = FRoute::search($params);
					$aslink[] = $advsearchLink;
				} else {
					$aslink[] = '';// give empty value so that the array tally with the countries size.
				}

				$countries[] = $country;
			}
		}

		if (count($countries) === 0) {
			return;
		}

		$this->set('advancedsearchlinks', $aslink);
		$this->set('countries', $countries);

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
		if (empty($this->value)) {
			return '';
		}

		$value = ES::makeArray($this->value);

		// retrieve formatted value
		$formattedValue = implode(", ", $value);

		// retrieve field data
		$field = $this->field;

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $formattedValue;

		return $data;
	}

	/**
	 * Check if this field is complete
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function onFieldCheck($user)
	{
		return $this->validateInput($this->value);
	}

	/**
	 * Check if this field is filled in
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!ES::config()->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		if (empty($this->value)) {
			return false;
		}

		$value = ES::makeArray($this->value);

		if (empty($value)) {
			return false;
		}

		return true;
	}

	public function onRegisterBeforeSave(&$post, &$user)
	{
		$selectedCountries = $this->processSelectedData(!empty($post[$this->inputName]) ? $post[$this->inputName] : '', '', true);
		$post[$this->inputName] = $selectedCountries;
	}

	/**
	 * Processes the posted data
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		if (empty($post[$this->inputName])) {
			$post[$this->inputName] = array();
		} else {

			$selectedCountries = $this->processSelectedData(!empty($post[$this->inputName]) ? $post[$this->inputName] : '', $this->value, true);
			$post[$this->inputName] = $selectedCountries;
		}
	}

	private function processSelectedData($post, $value, $format = false)
	{
		$value = !empty($post) ? $post : $this->value;
		$selection = ES::makeArray($value);

		$selected = [];

		foreach ($selection as $item) {
			$name = SocialFieldsUserCountryHelper::getCountryName($item, $this->params->get('data_source', 'regions'));
			
			if ($name) {
				if ($this->params->get('select_type') === 'textboxlist') {
					$obj = new stdClass();
					$obj->id = $item;
					$obj->title = $name;

					$selected[] = $obj;
				} else {
					$selected[] = $item;
				}
			}
		}

		// If there are no formatting required, just return the select value
		if (!$format) {
			return $selected;
		}

		$values = [];

		if ($selected) {
			foreach ($selected as $country) {
				if (is_string($country)) {
					$values[] = $country;
				} else {
					$country->id = SocialFieldsUserCountryHelper::getCountryCode($country->title, $this->params->get('data_source', 'regions'));
					$values[] = ['data' => $country->id, 'raw' => $country->title];
				}
			}
		}

		return $values;
	}
}
