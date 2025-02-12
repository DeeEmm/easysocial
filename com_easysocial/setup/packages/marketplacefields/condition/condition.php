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

class SocialFieldsMarketplaceCondition extends SocialFieldItem
{
	/**
	 * Method to retrieve the options for this field
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getOptions()
	{
		$options = $this->field->getOptions('items');

		if (empty($options)) {
			return [];
		}

		$result = [];

		foreach ($options as $option) {
			$result[$option->value] = $option->title;
		}

		return $result;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Current selected value.
		$selected = null;

		$conditions = $this->params->get('items');

		if (empty($conditions)) {
			return;
		}

		// If the value exists in the post data, it means that the user had previously set some values.
		if (isset($post[$this->inputName]) && !empty($post[$this->inputName])) {
			$selected = $post[$this->inputName];
		}

		// Detect if there's any errors.
		$errors = $registration->getErrors($this->inputName);

		$this->set('error', $errors);
		$this->set('selected', $selected);
		$this->set('conditions', $conditions);

		// Display the output.
		return $this->display();
	}

	/**
	 * Trigger field validation during registration
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$user)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Displays the field input for user when they edit their profile.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onEdit(&$post, &$item, $errors)
	{
		$conditions = $this->params->get('items');
		$selected = !empty($post[$this->inputName]) ? $post[$this->inputName] : $item->getCondition();

		// Get any errors
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('selected', $selected);
		$this->set('conditions', $conditions);

		return $this->display();
	}

	/**
	 * Trigger validation during field editing
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onDisplay($listing)
	{
		// Push variables into theme.
		$condition = $listing->getCondition();

		if (!$condition) {
			return;
		}

		$options = $this->getOptions();

		$this->set('value', $options[$condition]);

		return $this->display();
	}

	/**
	 * return formated string from the fields value
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onIndexer($userFieldData)
	{
		if (!$this->field->searchable) {
			return false;
		}

		$content = trim($userFieldData);

		if ($content) {
			return $content;
		} else {
			return false;
		}
	}

	/**
	 * Method to validate the field
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function validate($value)
	{
		if ($this->isRequired() && (is_null($value) || $value == '')) {
			$this->setError(JText::_('PLG_FIELDS_DROPDOWN_VALIDATION_PLEASE_SELECT_A_VALUE'));
			return false;
		}

		return true;
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function onGetValue($listing)
	{
		// Push variables into theme.
		$condition = $listing->getCondition();

		if (!$condition) {
			return;
		}

		$options = $this->getOptions();

		return $options[$condition];
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$data, &$item)
	{
		return $this->onBeforeSave($data, $item);
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$data, &$item)
	{
		return $this->onBeforeSave($data, $item);
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onBeforeSave(&$data, &$item)
	{
		// default value is store as 0 if the user doens't set any condition for this product.
		$condition = !empty($data[$this->inputName]) ? $data[$this->inputName] : 0;

		$item->condition = $condition;
		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the listing is created.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$data, &$item)
	{
		return $this->onBeforeSave($data, $item);
	}
}
