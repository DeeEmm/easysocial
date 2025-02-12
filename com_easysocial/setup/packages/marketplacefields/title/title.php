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

class SocialFieldsMarketplaceTitle extends SocialFieldsUserTextbox
{
	/**
	 * Executes before the marketplace item is created.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$data, &$item)
	{
		$title = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';

		$item->title = $title;

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$data, &$item)
	{
		$title = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';

		$item->title = $title;

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$data, &$item)
	{
		$title = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';

		$item->title = $title;

		unset($data[$this->inputName]);
	}

	/**
	 * Displays the marketplace item title textbox.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onEdit(&$post, &$item, $errors)
	{
		// The value will always be the marketplace item title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $item->getTitle();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the marketplace item description textbox.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onAdminEdit(&$post, &$item, $errors)
	{
		$itemName = JText::_($this->params->get('default'), true);

		if ($item->id) {
			$itemName = $item->getTitle();
		}

		// The value will always be the marketplace item title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $itemName;

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  3.3
	 * @access public
	 */
	public function onGetValue($item)
	{
		return $item->getTitle();
	}
}
