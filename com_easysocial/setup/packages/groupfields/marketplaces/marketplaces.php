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

ES::import('fields:/user/boolean/boolean');

class SocialFieldsGroupMarketplaces extends SocialFieldsUserBoolean
{
	public function canUseMarketplaces($access)
	{
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_GROUP) || !$access->allowed('marketplaces.grouplisting', true) || !$this->config->get('marketplaces.enabled')) {
			return false;
		}

		return true;
	}

	/**
	 * Displays the form when user tries to create a new group
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onRegister(&$post, &$session)
	{
		$access = ES::access($session->uid, SOCIAL_TYPE_CLUSTERS);

		if (!$this->canUseMarketplaces($access)) {
			return;
		}

		// Allow author modification during creation
		$allowModify = $this->params->get('allow_modification', true);

		if (!$allowModify) {
			return;
		}

		$value = $this->normalize($post, 'marketplaces', $this->params->get('marketplaces', $this->params->get('default', true)));
		$value = (bool) $value;

		// Detect if there's any errors
		$error = $session->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Displays the output form when someone tries to edit a group.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onEdit(&$data, &$group, $errors)
	{
		$access = $group->getAccess();

		if (!$this->canUseMarketplaces($access)) {
			return;
		}

		$params	= $group->getParams();
		$value = $group->getParams()->get('marketplaces', $this->params->get('marketplaces', $this->params->get('default', true)));
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Executes after the group is created
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$data, &$group)
	{
		$access = $group->getAccess();

		if (!$this->canUseMarketplaces($access)) {
			return;
		}

		// Get the posted value
		$value = $this->normalize($data, 'marketplaces', $group->getParams()->get('marketplaces', $this->params->get('default', true)));
		$value = (bool) $value;

		$group->params = $this->setParams($group, $value);
	}

	/**
	 * Executes after the group is created
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$data, &$group)
	{
		$access = $group->getAccess();

		if (!$this->canUseMarketplaces($access)) {
			return;
		}

		$value = $this->normalize($data, 'marketplaces', $this->params->get('marketplaces', $this->params->get('default', true)));
		$value = (bool) $value;

		$group->params = $this->setParams($group, $value);
	}

	/**
	 * Given the value, set the params to the group
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setParams($group, $value)
	{
		$params = $group->getParams();
		$params->set('marketplaces', $value);

		return $params->toString();
	}

	/**
	 * Override the parent's onDisplay
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onDisplay($group)
	{
		return;
	}
}
