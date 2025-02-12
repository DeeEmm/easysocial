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

class SocialFieldsPageEventcreate extends SocialFieldItem
{
	public function onRegister(&$post, &$session)
	{
		$access = ES::access($session->uid, SOCIAL_TYPE_PAGE);

		if (!$access->get('events.pageevent', true)) {
			return;
		}

		$value = !empty($post['eventcreate']) ? $post['eventcreate'] : '[]';
		$value = ES::makeArray($value);

		$checkboxForm = $this->getCheckboxForm($value);

		$this->set('value', $value);
		$this->set('checkboxForm', $checkboxForm);

		return $this->display();
	}

	public function onEdit(&$post, &$page, $errors)
	{
		// Get access
		$access = $page->getAccess();

		if (!$access->get('events.pageevent', true)) {
			return;
		}

		$value = !empty($post['eventcreate']) ? $post['eventcreate'] : $page->getParams()->get('eventcreate', '[]');
		$value = ES::makeArray($value);

		$checkboxForm = $this->getCheckboxForm($value);

		$this->set('checkboxForm', $checkboxForm);
		$this->set('value', $value);

		return $this->display();
	}

	public function onRegisterBeforeSave(&$post, &$page)
	{
		return $this->onBeforeSave($post, $page);
	}

	public function onEditBeforeSave(&$post, &$page)
	{
		return $this->onBeforeSave($post, $page);
	}

	public function onBeforeSave(&$post, &$page)
	{
		$value = !empty($post['eventcreate']) ? $post['eventcreate'] : [];

		// Pre-fill the default value
		$defaultOptions = ['admin'];
		$checkboxOptions = $this->params->get('create_options');

		$value = ES::makeArray($value);
		$defaultValue = $this->params->get('create_default');

		if (!empty($defaultValue)) {
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
		}

		$params = $page->getParams();
		$params->set('eventcreate', $value);

		$page->params = $params->toString();

		unset($post['eventcreate']);
	}

	public function getCheckboxForm($value)
	{
		$defaultOptions = array('admin');
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
