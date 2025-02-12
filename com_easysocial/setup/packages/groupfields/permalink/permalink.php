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
ES::import('fields:/group/permalink/helper');

class SocialFieldsGroupPermalink extends SocialFieldItem
{
	/**
	 * Saves the permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save(&$post, &$group)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// There could be possibility that the user removes their permalink so
		// we should not check for empty value here.
		if (empty($value) && !empty($group->title)) {
			$value = ES::generatePermalink($group->title);
		}

		// Delete old record from the finder to avoid search duplication. #2467
		if ($group->alias && $value != $group->alias) {
			ES::search()->deleteFromSmartSearch($group->getAlias());
		}

		$model = ES::model('groups');
		$group->alias = $model->getUniqueAlias($value, $group->id);

		$post[$this->inputName] = $group->alias;
	}

	/**
	 * Before we store the group, we need to update the group's `permalink` column
	 * Previously is onRegisterAfterSave. Changed to before save so that we can retrieve the permalink during saving process
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post, &$group)
	{
		return $this->save($post, $group);
	}

	/**
	 * Once the registration is stored, we need to update the user's `permalink` column
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterAfterSave(&$post, &$group)
	{
		return $this->save($post, $group);
	}

	/**
	 * Saves the permalink after their profile is edited.
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$group)
	{
		return $this->save($post, $group);
	}

	/**
	 * Saves the permalink after their profile is edited.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditAfterSave(&$post, &$group)
	{
		return $this->save($post, $group);
	}

	/**
	 * Performs validation for the gender field.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function validate($post, $group = null, $isCopy = false)
	{
		$key = $this->inputName;

		// Get the current value
		$value 	= isset($post[$key]) ? $post[$key] : '';

		if (!$this->isRequired() && empty($value)) {
			return true;
		}

		// Catch for errors if this is a required field.
		if ($this->isRequired() && empty($value)) {
			$this->setError(JText::_('PLG_FIELDS_GROUP_PERMALINK_REQUIRED'));

			return false;
		}

		if ($this->params->get('max') > 0 && ESJString::strlen($value) > $this->params->get('max')) {
			$this->setError(JText::_('PLG_FIELDS_GROUP_PERMALINK_EXCEEDED_MAX_LENGTH'));
			return false;
		}

		if (!SocialFieldsGroupPermalinkHelper::allowed($value)) {
			$this->setError(JText::_('PLG_FIELDS_PERMALINK_CONFLICTS_WITH_SYSTEM'));
			return false;
		}

		// Determine the current user that is being edited
		$current 	= '';

		if ($group) {
			$current 	= $group->id;
		}

		if ($current) {
			$group 	= ES::group($current);

			// If the permalink is the same, just return true.
			if ($group->alias == $value) {
				return true;
			}
		}

		if ($isCopy) {
			// lets auto append the alias so that there will not be any conflict.
			$i = 0;
			$iterate = true;
			do {
				if (SocialFieldsGroupPermalinkHelper::exists($value)) {
					$value = $value . '-' . ++$i;
				} else {
					$iterate = false;
				}
			} while ($iterate);

			// var_dump($value);
		}

		if (SocialFieldsGroupPermalinkHelper::exists($value)) {
			$this->setError(JText::_('PLG_FIELDS_GROUP_PERMALINK_NOT_AVAILABLE'));

			return false;
		}

		if (!SocialFieldsGroupPermalinkHelper::valid($value, $this->params)) {
			$this->setError(JText::_('PLG_FIELDS_GROUP_PERMALINK_INVALID_PERMALINK'));

			return false;
		}

		// now lets reset the value is this is a copy operation.
		if ($isCopy) {
			$post[$key] = $value;
		}

		return true;
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$session)
	{
		$state 	= $this->validate($post);

		return $state;
	}

	/**
	 * Performs validation when a user updates their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post, &$group, $isCopy = false)
	{
		$state 	= $this->validate($post, $group, $isCopy);

		return $state;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$session)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// Detect if there's any errors.
		$error 	= $session->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $this->escape($value));
		$this->set('groupid', null);

		return $this->display();
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when they edit their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$group, $errors)
	{
		$value = $group->alias;

		$error = $this->getError($errors);

		$this->set('value', $this->escape($value));
		$this->set('error', $error);
		$this->set('groupid', $group->id);

		return $this->display();
	}
}
