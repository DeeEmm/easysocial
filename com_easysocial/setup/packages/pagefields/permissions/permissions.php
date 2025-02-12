<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('fields:/user/header/header');

class SocialFieldsPagePermissions extends SocialFieldItem
{
	/**
	 * Displays the form for page owner to define permissions
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onRegister(&$post, &$session)
	{
		// Get the posted value if there's any
		$value = !empty($post['stream_permissions']) ? $post['stream_permissions'] : '';

		// Ensure that it's an array
		$value = ES::makeArray($value);
		$checkboxForm = $this->getCheckboxForm($value);

		$this->set('checkboxForm', $checkboxForm);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Displays the form for page owner to define permissions when page is being edited
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onEdit(&$post, SocialPage &$page, $errors)
	{
		$permissions = $page->getParams()->get('stream_permissions', array());

		$value = !empty($post['stream_permissions']) ? $post['stream_permissions'] : $permissions;
		$value = ES::makeArray($value);
		$checkboxForm = $this->getCheckboxForm($value);

		$this->set('value', $value);
		$this->set('checkboxForm', $checkboxForm);

		return $this->display();
	}

	/**
	 * Processes the save for new page creation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$page)
	{
		return $this->onBeforeSave($post, $page);
	}

	/**
	 * Processes the save for page editing
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, SocialPage &$page)
	{
		return $this->onBeforeSave($post, $page);
	}

	/**
	 * Before the form is saved, we need to store these data into the page properties
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onBeforeSave(&$post, SocialPage &$page)
	{
		$value = !empty($post['stream_permissions']) ? $post['stream_permissions'] : [];

		// Pre-fill the default value
		$defaultOptions = ['admin', 'member'];
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

		// Set it into the page params so that we can retrieve this later
		$params = $page->getParams();
		$params->set('stream_permissions', $value);

		$page->params = $params->toString();

		unset($post['stream_permissions']);
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

			$obj = new stdClass();
			$obj->name = $checkbox;
			$obj->selected = !empty($value) && in_array($checkbox, $value) ? true : false;

			$checkboxForm[] = $obj;
		}

		return $checkboxForm;
	}
}
