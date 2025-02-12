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

ES::import('fields:/user/numeric/numeric');

class SocialFieldsMarketplaceStock extends SocialFieldsUserNumeric
{

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onEdit(&$post, &$item, $errors)
	{
		$value = $item->getStock();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $value);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onAdminEdit(&$post, &$item, $errors)
	{
		$value = $item->getStock();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $value);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$data, &$item)
	{
		return $this->onBeforeSave($data, $item);
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$data, &$item)
	{
		return $this->onBeforeSave($data, $item);
	}

		/**
	 * Executes before the listing is created.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$data, &$item)
	{
		return $this->onBeforeSave($data, $item);
	}

	/**
	 * Executes before the listing is created.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onBeforeSave(&$data, &$item)
	{
		// default value is store as 0 if the user doens't set any stock quantity for this product.
		$value = !empty($data[$this->inputName]) ? $data[$this->inputName] : 0;

		$item->stock = $value;

		unset($data[$this->inputName]);
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onDisplay($listing)
	{
		if (!$listing->showStock()) {
			return;
		}

		$this->set('value', $listing->getStock());

		return $this->display();
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function onGetValue($listing)
	{
		return $listing->getStock();
	}
}
