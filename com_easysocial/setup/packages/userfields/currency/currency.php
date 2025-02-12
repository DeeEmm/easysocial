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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserCurrency extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		$dollarsLabel = $this->getLabel('DOLLARS');
		$centsLabel = $this->getLabel('CENTS');
		$unitsLabel = $this->getLabel('UNIT');

		$dollar = '';
		$cent = '';

		// Get value for this field
		if (isset($post[$this->inputName])) {
			$data = $this->getCurrencyValue($post[$this->inputName]);
			$dollar = $data->dollar;
			$cent = $data->cent;
		}

		// Get any errors for this field.
		$error = $registration->getErrors($this->inputName);

		// Push to template
		$this->set('error', $error);
		$this->set('unitsLabel', $unitsLabel);
		$this->set('dollarsLabel', $dollarsLabel);
		$this->set('centsLabel', $centsLabel);
		$this->set('dollar', $this->escape($dollar));
		$this->set('cent', $this->escape($cent));

		// Display the output.
		return $this->display();
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get the currency to use.
		$dollarsLabel = $this->getLabel('DOLLARS');
		$centsLabel = $this->getLabel('CENTS');
		$unitsLabel = $this->getLabel('UNIT');

		$dollar = '';
		$cent = '';

		// Get value for this field
		if (isset($post[$this->inputName])) {
			$data = $this->getCurrencyValue($post[$this->inputName]);
			$dollar = $data->dollar;
			$cent = $data->cent;
		} else {
			$data = json_decode($this->value);
			$dollar = isset($data->dollar) ? $data->dollar : '';
			$cent = isset($data->cent) ? $data->cent : '';
		}

		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('unitsLabel', $unitsLabel);
		$this->set('dollarsLabel', $dollarsLabel);
		$this->set('centsLabel', $centsLabel);
		$this->set('dollar', $this->escape($dollar));
		$this->set('cent', $this->escape($cent));

		return $this->display();
	}

	/**
	 * Validates the field input for user when they register their account.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * Validates the field input for user when they edit their account.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * General validation function
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function validateInput($value)
	{
		if ($this->isRequired() && empty($value)) {
			return $this->setError(JText::_('PLG_FIELDS_CURRENCY_VALIDATION_INPUT_REQUIRED'));
		}

		$value = ES::makeArray($value);

		if (!isset($value['dollar']) || !isset($value['cent'])) {
			return $this->setError(JText::_('PLG_FIELDS_CURRENCY_VALIDATION_INPUT_REQUIRED'));
		}

		if (!$value['dollar'] || !$value['cent']) {
			return $this->setError(JText::_('PLG_FIELDS_CURRENCY_VALIDATION_INPUT_REQUIRED'));
		}

		return true;
	}

	private function getCurrencyValue($data)
	{
		$newData = new stdClass();

		$newData->dollar = isset($data->dollar) ? $data->dollar : '';
		$newData->cent = isset($data->cent) ? $data->cent : '';

		return $newData;
	}

	/**
	 * Get label for currency.
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function getLabel($type)
	{
		// Get the currency to use.
		$text = 'PLG_FIELDS_CURRENCY_' . strtoupper($this->params->get('format')) . '_' . strtoupper($type);

		return JText::_($text);
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!$this->config->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		if (empty($this->value)) {
			return false;
		}

		$obj = ES::makeObject($this->value);

		if (empty($obj->dollar) || empty($obj->cent)) {
			return false;
		}

		return true;
	}
}
