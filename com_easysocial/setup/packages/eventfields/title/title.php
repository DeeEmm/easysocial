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

ES::import('fields:/user/textbox/textbox');

class SocialFieldsEventTitle extends SocialFieldsUserTextbox
{
	/**
	 * Support for generic getFieldValue('TITLE')
	 *
	 * @since  1.3.9
	 * @access public
	 */
	public function getValue()
	{
		$container = $this->getValueContainer();

		if ($this->field->type == SOCIAL_TYPE_EVENT && !empty($this->field->uid)) {
			$event = ES::event($this->field->uid);

			$container->value = $event->getName();

			$container->data = $event->title;
		}

		return $container;
	}

	/**
	 * Displays the event title textbox.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $errors)
	{
		// The value will always be the event title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $cluster->getName();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		$isDisabled = $cluster->parent_id && $this->config->get('events.recurring.appendTitle');

		$this->params->set('readonly', $isDisabled);
		$this->set('params', $this->params);

		// Add one more flag to bypass the disable input not submitting the value into the form.
		$parentEvent = ES::event($cluster->parent_id);

		$this->set('isDisabled', $isDisabled);
		$this->set('disabledInfo', JText::sprintf('COM_ES_EVENTS_EDIT_RECURRING_EVENT_TITLE_INFO', '<a target="_blank" href="' . $parentEvent->getEditPermalink() . '">', '</a>'));

		return $this->display();
	}

	/**
	 * Displays the event description textbox.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onAdminEdit(&$post, &$cluster, $errors)
	{
		$clusterName = JText::_($this->params->get('default'), true);

		if ($cluster->id) {
			$clusterName = $cluster->getName();
		}

		// The value will always be the event title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $clusterName;

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Responsible to output the html codes that is displayed to a user.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onDisplay($cluster)
	{
		$this->value = $cluster->getName();

		return parent::onDisplay($cluster);
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$cluster)
	{
		return $this->processDateTitle($post, $cluster);
	}

	/**
	 * Executes before the event is save.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		return $this->processDateTitle($post, $cluster);
	}

	/**
	 * Executes before the event is save.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$post, &$cluster)
	{
		return $this->processDateTitle($post, $cluster);
	}

	/**
	 * Executes before the event is save.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function processDateTitle(&$post, &$cluster)
	{
		$title = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';
		$format = $this->params->get('title_date_format');

		// Set the title on the event
		$model = ES::model('Clusters');

		if ($cluster->parent_id && $this->config->get('events.recurring.appendTitle')) {

			$parentEvent = ES::event($cluster->parent_id);

			$oriStartDate = $cluster->getEventStart();
			$processed = false;

			if (isset($post['isNew']) && !$post['isNew'] && isset($post['applyRecurring']) && $post['applyRecurring']) {
				// we now this is editing recurring event with applyRecurring == true, we should not change the startdate
				// 5399
				$title = $parentEvent->title . ' - ' . $oriStartDate->format($format, true);
				$processed = true;
			}

			// we now this is a recurring event creation.
			// lets add a date into the title.
			if (!$processed && isset($post['startDatetime']) && $post['startDatetime']) {

				$startDate = $post['startDatetime'];

				// Get the correct event timezone. #2444
				// Joomla timezone
				$original_TZ = new DateTimeZone(JFactory::getConfig()->get('offset'));
				$eventTimezone = isset($post['startendTimezone']) ? $post['startendTimezone'] : false;

				// Get the date with timezone
				$newStartDate = JFactory::getDate($startDate, $original_TZ);

				// Check for timezone. If the timezone has been changed, get the new startend date
				if (!empty($eventTimezone) && $eventTimezone !== 'UTC') {
					$dtz = new DateTimeZone($eventTimezone);

					// Creates a new datetime string with user input timezone as predefined timezone
					$newStartDate = JFactory::getDate($startDate, $dtz);
				}

				$title = $parentEvent->title . ' - ' . $newStartDate->format($format, true);
			}

		}

		$cluster->title = $model->getUniqueTitle($title, SOCIAL_TYPE_EVENT, $cluster->id);

		unset($post[$this->inputName]);
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function onGetValue($cluster)
	{
		return $cluster->getName();
	}
}
