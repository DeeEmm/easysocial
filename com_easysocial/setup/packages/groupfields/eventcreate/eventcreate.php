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

class SocialFieldsGroupEventcreate extends SocialFieldItem
{
	public function onRegister(&$post, &$session)
	{
		// Get access
		$access = ES::access($session->uid, SOCIAL_TYPE_GROUP);

		if (!$access->get('events.groupevent', true)) {
			return;
		}

		$value = !empty($post['eventcreate']) ? $post['eventcreate'] : '[]';
		$value = ES::makeArray($value);

		$checkboxForm = $this->getCheckboxForm($value);

		$this->set('value', $value);
		$this->set('checkboxForm', $checkboxForm);

		return $this->display();
	}

	public function onEdit(&$post, &$group, $errors)
	{
		// Get access
		$access = $group->getAccess();

		if (!$access->get('events.groupevent', true)) {
			return;
		}

		$value = !empty($post['eventcreate']) ? $post['eventcreate'] : $group->getParams()->get('eventcreate', '[]');
		$value = ES::makeArray($value);
		$checkboxForm = $this->getCheckboxForm($value);

		$this->set('checkboxForm', $checkboxForm);
		$this->set('value', $value);

		return $this->display();
	}

	public function onRegisterBeforeSave(&$post, &$group)
	{
		return $this->onBeforeSave($post, $group);
	}

	public function onEditBeforeSave(&$post, &$group)
	{
		return $this->onBeforeSave($post, $group);
	}

	public function onBeforeSave(&$post, &$group)
	{
		$value = !empty($post['eventcreate']) ? $post['eventcreate'] : '[]';

		// Pre-fill the default value
		$defaultOptions = ['admin', 'member'];
		$checkboxOptions = $this->params->get('create_options');

		$value = ES::makeArray($value);
		$defaultValue = $this->params->get('create_default');

		if ($defaultValue) {
			$defaultValue = array_flip($defaultValue);

			if ($checkboxOptions) {
				foreach ($defaultOptions as $option) {
					if (!isset($checkboxOptions[$option])) {
						if (isset($defaultValue[$option])) {
							$value[] = $option;
						}
					}
				}
			}

			$params = $group->getParams();
			$params->set('eventcreate', $value);

			$group->params = $params->toString();
		}

		if (isset($post['eventcreate'])) {
			unset($post['eventcreate']);
		}
	}

	public function getCheckboxForm($value)
	{
		$defaultOptions = array('admin', 'member');
		$checkboxOptions = $this->params->get('create_options');

		if (!$checkboxOptions) {
			$checkboxOptions = $defaultOptions;
		}

		if (empty($value)) {
			$value = $this->params->get('create_default');
		}

		$checkboxForm = array();

		foreach ($checkboxOptions as $checkbox) {

			if ($checkbox == 'owner') {
				continue;
			}

			$obj = new stdClass();
			$obj->name = $checkbox;
			$obj->selected = !empty($value) && in_array($checkbox, $value) ? true : false;

			$checkboxForm[] = $obj;
		}

		return $checkboxForm;
	}
}
