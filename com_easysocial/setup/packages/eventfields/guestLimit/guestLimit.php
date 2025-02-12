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

class SocialFieldsEventGuestLimit extends SocialFieldItem
{
	/**
	 * Displays the field for creation.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegister(&$post, &$session)
	{
		// Get any previously submitted data
		$value = isset($post['guestlimit']) ? (int) $post['guestlimit'] : $this->params->get('default', 0);

		// Detect if there's any errors
		$error = $session->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $value);

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
		$value = isset($post['guestlimit']) ? (int) $post['guestlimit'] : (int) $cluster->getParams()->get('guestlimit', 0);
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Validates the attendee limit before save occurs
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function onEditValidate(&$post, &$event)
	{
		$dispatcher = ES::dispatcher();
		$newLimit = (int) ES::normalize($post, 'guestlimit', 0);
		$existingLimit = $event->getTotalSeats();
		$total = $event->getTotalGuests() - $event->getTotalNotGoing();

		// An event to allow update the total members of the event
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onUpdateEventTotalMembers', [$event, &$total]);

		if ($existingLimit != $newLimit && $newLimit && $total > $newLimit) {
			$this->setError('FIELDS_EVENT_GUESTLIMIT_LOWER_THAN_EXISTING_GUESTS');
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
		return $this->beforeSave($post, $cluster);
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		return $this->beforeSave($post, $cluster);
	}

	public function beforeSave(&$post, &$cluster)
	{
		// Get the posted value
		$value = isset($post['guestlimit']) ? (int) $post['guestlimit'] : 0;

		$registry = $cluster->getParams();
		$registry->set('guestlimit', $value);

		$cluster->params = $registry->toString();

		unset($post['guestlimit']);

		return true;
	}
}
