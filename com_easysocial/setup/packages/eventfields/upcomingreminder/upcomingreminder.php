<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventUpcomingreminder extends SocialFieldItem
{
	public function onRegister(&$post, &$session)
	{
		$reminderDuration = isset($post['event_reminder']) ? $post['event_reminder'] : $this->params->get('default', 0);

		$this->set('value', $reminderDuration);

		return $this->display();
	}

	public function onAdminEdit(&$post, &$cluster, $errors)
	{
		// The value will always be the event title
		$value = isset($post['event_reminder']) ? $post['event_reminder'] : $cluster->getReminder();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	public function onEdit(&$post, &$cluster, $errors)
	{
		// The value will always be the event title
		$value = isset($post['event_reminder']) ? $post['event_reminder'] : $cluster->getReminder();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$cluster)
	{
		// Get the posted value
		$value = isset($post['event_reminder']) ? $post['event_reminder'] : $this->params->get('default');
		$value = (int) $value;

		$cluster->meta->reminder = $value;

		unset($post['event_reminder']);
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		// Get the posted value
		$value = isset($post['event_reminder']) ? $post['event_reminder'] : $cluster->isAllDay();
		$value = (int) $value;

		$cluster->meta->reminder = $value;

		unset($post['event_reminder']);
	}

	/**
	 * Override the parent's onDisplay to not show this field.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onDisplay($cluster)
	{
		return;
	}
}
