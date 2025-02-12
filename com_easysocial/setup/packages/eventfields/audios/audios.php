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

ES::import('fields:/user/boolean/boolean');

class SocialFieldsEventAudios extends SocialFieldsUserBoolean
{
	private function canUseAudio($access)
	{
		// We need to know if the app is published
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_EVENT) || !$access->get('audios.create', true) || !$this->config->get('audio.enabled')) {
			return false;
		}

		return true;
	}

	/**
	 * Displays the form when user tries to create a new event
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegister(&$post, &$session)
	{
		$access = ES::access($session->uid, SOCIAL_TYPE_CLUSTERS);

		if (!$this->canUseAudio($access)) {
			return;
		}

		$value = $this->normalize($post, 'audios', $this->params->get('audios', $this->params->get('default', true)));
		$value = (bool) $value;

		// Detect if there's any errors
		$error = $session->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}
	
	/**
	 * Executes after the event is created
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function onRegisterBeforeSave(&$data, &$event)
	{
		$access = $event->getAccess();
		
		if (!$this->canUseAudio($access)) {
			return;
		}

		$value = $this->normalize($data, 'audios', $this->params->get('audios', $this->params->get('default', true)));
		$value = (bool) $value;

		$event->params = $this->setParams($event, $value);
	}

	/**
	 * Displays the output form when someone tries to edit a event.
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function onEdit(&$data, &$event, $errors)
	{
		$access = $event->getAccess();

		if (!$this->canUseAudio($access)) {
			return;
		}

		$params	= $event->getParams();
		$value = $event->getParams()->get('audios', $this->params->get('audios', $this->params->get('default', true)));
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Executes after the event is created
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function onEditBeforeSave(&$data, &$event)
	{
		$access = $event->getAccess();
		
		if (!$this->canUseAudio($access)) {
			return;
		}

		// Get the posted value
		$value = $this->normalize($data, 'audios', $event->getParams()->get('audios', $this->params->get('default', true)));
		$value = (bool) $value;

		$event->params = $this->setParams($event, $value);
	}

	/**
	 * Given the value, set the params to the event
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setParams($event, $value)
	{
		$params = $event->getParams();
		$params->set('audios', $value);

		return $params->toString();
	}

	/**
	 * Override the parent's onDisplay
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onDisplay($event)
	{
		return;
	}
}
