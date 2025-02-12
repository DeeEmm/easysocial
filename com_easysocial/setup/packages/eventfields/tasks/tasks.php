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

ES::import('fields:/user/boolean/boolean');

class SocialFieldsEventTasks extends SocialFieldsUserBoolean
{
	/**
	 * Triggered when a group is being saved
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post, SocialEvent $event)
	{
		// We need to know if the app is published
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_EVENT)) {
			return;
		}

		// Get the posted value
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : $this->params->get('default', true);
		$value = (bool) $value;

		$registry = $event->getParams();
		$registry->set('tasks', $value);

		$event->params = $registry->toString();
	}

	/**
	 * Override the editing of the title since the value is different
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$event)
	{
		// We need to know if the app is published
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_EVENT)) {
			return;
		}

		// Allow author modification during creation
		$allowModify = $this->params->get('allow_modification', true);

		if (!$allowModify) {
			return;
		}
		
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : $this->params->get('default', true);

		// Set the value.
		$this->set('value', $this->escape($value));

		return $this->display();
	}

	/**
	 * Override the editing of the title since the value is different
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEdit(&$post, &$event, $errors)
	{
		// We need to know if the app is published
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_EVENT)) {
			return;
		}

		// The value will always be the page title
		$params = $event->getParams();

		// Get the real value for this item
		$value = $params->get('tasks', $this->params->get('default', true));

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Override the editing of the news value
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$event)
	{
		// We need to know if the app is published
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_EVENT)) {
			return;
		}

		$params = $event->getParams();
				
		// Get the posted value
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : $params->get('tasks', $this->params->get('default', true));
		$value = (bool) $value;

		$registry = $event->getParams();
		$registry->set('tasks', $value);

		$event->params = $registry->toString();
	}

	/**
	 * Override the parent's onDisplay
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onDisplay($event)
	{
		return;
	}
}
