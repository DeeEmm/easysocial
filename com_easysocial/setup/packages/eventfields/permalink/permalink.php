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
ES::import('fields:/event/permalink/helper');

class SocialFieldsEventPermalink extends SocialFieldItem
{
	/**
	 * Displays the field for creation.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegister(&$post, &$session)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$error = $session->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $this->escape($value));
		$this->set('clusterid', null);

		return $this->display();
	}

	/**
	 * Displays the field for edit.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $errors)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $cluster->alias;

		$error = $this->getError($errors);

		$this->set('value', $this->escape($value));
		$this->set('error', $error);
		$this->set('clusterid', $cluster->id);

		return $this->display();
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterValidate(&$post, &$session)
	{
		$state = $this->validate($post);

		return $state;
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditValidate(&$post, &$cluster)
	{
		// Support for recurring event
		// If this is a recurring event, and is coming from applyRecurring, then we do not want to process this
		if ($cluster->isRecurringEvent() && !empty($post['applyRecurring'])) {
			return true;
		}

		$state = $this->validate($post, $cluster);

		return $state;
	}

	/**
	 * Performs validation for the gender field.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function validate($post, $cluster = null)
	{
		// Get the current value
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		if (!$this->isRequired() && empty($value)) {
			return true;
		}

		// Catch for errors if this is a required field.
		if ($this->isRequired() && empty($value)) {
			$this->setError(JText::_('FIELDS_EVENT_PERMALINK_REQUIRED'));

			return false;
		}

		if ($this->params->get('max') > 0 && ESJString::strlen($value) > $this->params->get('max')) {
			$this->setError(JText::_('FIELDS_EVENT_PERMALINK_EXCEEDED_MAX_LENGTH'));
			return false;
		}

		if (!SocialFieldsEventPermalinkHelper::allowed($value)) {
			$this->setError(JText::_('PLG_FIELDS_PERMALINK_CONFLICTS_WITH_SYSTEM'));
			return false;
		}


		// If the permalink is the same, just return true.
		if (!empty($cluster) && $cluster->alias == $value) {
			return true;
		}

		if (SocialFieldsEventPermalinkHelper::exists($value)) {
			$this->setError(JText::_('FIELDS_EVENT_PERMALINK_NOT_AVAILABLE'));

			return false;
		}

		if (!SocialFieldsEventPermalinkHelper::valid($value, $this->params)) {
			$this->setError(JText::_('FIELDS_EVENT_PERMALINK_INVALID_PERMALINK'));

			return false;
		}

		return true;
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$cluster)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $cluster->title;

		// For some of the cases the permalink field is not set to required
		// so it might be the user do not have enter any value from this field
		// do not proceed this if the case is like this
		if (!$value) {
			return true;
		}

		$model = ES::model('Events');

		// if ($cluster->parent_id) {
		// 	// we now this is a recurring event creation.
		// 	// lets add a date into the alias.
		// 	if (isset($post['startDatetime']) && $post['startDatetime']) {
		// 		$startDate = ES::date($post['startDatetime']);
		// 		$value .= ' - ' . $startDate->format(JText::_('d.m.Y'));
		// 	}
		// }

		$alias = $model->getUniqueAlias($value);
		$cluster->alias = $alias;

		unset($post[$this->inputName]);

		return true;
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		// Support for recurring event
		// If this is a recurring event, then we do not want to process this
		if ($cluster->isRecurringEvent() && !empty($post['applyRecurring'])) {
			unset($post[$this->inputName]);
			return true;
		}

		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $cluster->title;

		// Delete old record from the finder to avoid search duplication. #2467
		if ($cluster->alias && $value != $cluster->alias) {
			ES::search()->deleteFromSmartSearch($cluster->getAlias());
		}

		$model = ES::model('Events');
		$alias = $model->getUniqueAlias($value, $cluster->id);

		$cluster->alias = $alias;

		unset($post[$this->inputName]);

		return true;
	}
}
