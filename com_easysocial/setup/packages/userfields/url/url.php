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

class SocialFieldsUserUrl extends SocialFieldItem
{
	/**
	 * About profile output
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$value = $this->formatUrl($this->value);

		if (!$value) {
			return;
		}

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		if (stristr($value, 'http://') === false && stristr($value, 'https://') === false) {

			// Determine what is the current site domain protocol
			$uri = JURI::getInstance();
			$scheme = $uri->toString(array('scheme'));

			$value = $scheme . $value;
		}

		$this->set('value', $this->escape($value));

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : JText::_($this->params->get('default'), true);

		if ($value) {
			$value = $this->formatUrl($value);
		}
		// Set the value.
		$this->set('value', $this->escape($value));

		// Get any errors for this field.
		$error = $registration->getErrors($this->inputName);

		// Set the error.
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		if ($value) {
			$value = $this->formatUrl($value);
		}

		// Set the value.
		$this->set('value', $this->escape($value));

		// Get the error.
		$error = $this->getError($errors);

		// Set the error.
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the user and cluster URL from backend.
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function onAdminEdit(&$post, &$user, $errors)
	{
		$value = JText::_($this->params->get('default'), true);

		// Determine if the user or clusters item whether create it on the site or not
		if ($user->id) {
			$value = $this->value;
		}

		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $value;

		if ($value) {
			$value = $this->formatUrl($value);
		}

		// Set the value.
		$this->set('value', $this->escape($value));

		// Get the error.
		$error = $this->getError($errors);

		// Set the error.
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Validate input when user edit their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		// Selected value
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * return formated string from the fields value
	 *
	 * @since	1.0
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

	public function onIndexerSearch($itemCreatorId, $keywords, $userFieldData)
	{
		if (!$this->field->searchable) {
			return false;
		}

		$data = trim($userFieldData);

		$content = '';

		if (ESJString::stristr($data, $keywords) !== false) {
			$content = $data;
		}

		if ($content) {

			if (!$this->my->getPrivacy()->validate('core.view', $this->field->id, SOCIAL_TYPE_FIELD, $itemCreatorId)) {
				return -1;
			} else {

				// building the pattern for regex replace
				$searchworda = preg_replace('#\xE3\x80\x80#s', ' ', $keywords);
				$searchwords = preg_split("/\s+/u", $searchworda);
				$needle = $searchwords[0];
				$searchwords = array_unique($searchwords);

				$pattern = '#(';
				$x = 0;

				foreach ($searchwords as $k => $hlword) {
					$pattern .= $x == 0 ? '' : '|';
					$pattern .= preg_quote($hlword, '#');
					$x++;
				}

				$pattern .= ')#iu';

				$content = preg_replace($pattern, '<span class="search-highlight">\0</span>', $content);
				$content = JText::sprintf('PLG_FIELDS_URL_SEARCH_RESULT', $content);
			}
		}

		if ($content) {
			return $content;
		} else {
			return false;
		}
	}

	/**
	 * General validation function
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function validateInput($value)
	{
		// Ensure that there is no extra spacing in the value
		$value = trim($value);

		// If this is required, check for the value.
		if ($this->isRequired() && empty($value)) {
			return $this->setError(JText::_('PLG_FIELDS_URL_VALIDATION_EMPTY_URL'));
		}

		return true;
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onGetValue($user)
	{
		return $this->getValue();
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

		return !empty($this->value);
	}

	/**
	 * Remove whitespace from URL value
	 *
	 * @since  1.2
	 * @access public
	 */
	private function formatUrl($value)
	{
		$value = trim($value);

		return $value;
	}
}
