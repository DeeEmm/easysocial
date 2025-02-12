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

class SocialFieldsMarketplacePrice extends SocialFieldsUserNumeric
{
	/**
	 * Retrieve the default currency for marketplace
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	private function getDefaultCurrency($currency)
	{
		$config = ES::config();

		// The admin could decide to lock and use a default currency, so we need to fallback to the default
		if (!$config->get('marketplaces.multicurrency')) {
			$currency = $config->get('marketplaces.currency');
		}

		$defaultCurrency = ES::currency($currency);

		return $defaultCurrency;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onEdit(&$post, &$item, $errors)
	{
		$value = $item->getPriceCurrency();
		$error = $this->getError($errors);

		$this->set('price', $value->price);
		$this->set('error', $error);
		$this->set('defaultCurrency', $this->getDefaultCurrency($value->currency));
		$this->set('currencyDefault', $value->currency);
		$this->set('currencyLabel', ES::getCurrencyOptions());

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? json_decode($post[$this->inputName]) : '';

		$error = $registration->getErrors($this->inputName);

		$price = isset($value->price) ? $value->price : '';
		$currency = isset($value->currency) ? $value->currency : $this->config->get('marketplaces.currency');

		$this->set('error', $error);
		$this->set('price', $price);
		$this->set('defaultCurrency', $this->getDefaultCurrency($currency));
		$this->set('currencyDefault', $currency);
		$this->set('currencyLabel', ES::getCurrencyOptions());

		return $this->display();
	}

	/**
	 * Responsible to output the form when the user is being edited by the admin
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onAdminEdit(&$post, &$item, $errors)
	{
		$value = $item->getPriceCurrency();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('price', $value->price);
		$this->set('error', $error);
		$this->set('defaultCurrency', $this->getDefaultCurrency($value->currency));
		$this->set('currencyDefault', $value->currency);
		$this->set('currencyLabel', ES::getCurrencyOptions());

		$this->set('params', $this->params);

		return $this->display();
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$data, &$item)
	{
		$this->beforeSave($data, $item);
	}

	/**
	 * Executes before the marketplace item is save.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$data, &$item)
	{
		$this->beforeSave($data, $item);
	}

	public function beforeSave(&$data, &$item)
	{
		$value = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';
		$priceObj = json_decode($value);

		$item->price = $priceObj->price;
		$item->currency = isset($priceObj->currency) ? $priceObj->currency : $this->config->get('marketplaces.currency');

		$data[$this->inputName] = $item->price;
	}

	/**
	 * Executes before the listing is created.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$data, &$item)
	{
		$this->beforeSave($data, $item);
	}

	/**
	 * Validates the field input
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * Validates the field input for user when they edit their listing.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * General validation function
	 *
	 * @since	4.0
	 * @access	public
	 */
	private function validateInput($value)
	{
		$value = json_decode($value);

		if ($this->isRequired() && $value->price == '') {
			return $this->setError(JText::_('PLG_FIELDS_TEXTBOX_VALIDATION_INPUT_REQUIRED'));
		}

		return true;
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function onGetValue($listing)
	{
		return $listing->getPriceTag();
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onDisplay($listing)
	{
		$defaultCurrency = ES::currency($listing->currency);

		$this->set('value', $listing->getPriceTag());
		$this->set('price', $listing->price);
		$this->set('defaultCurrency', $defaultCurrency);
		$this->set('currencyDefault', $listing->currency);
		$this->set('currencyLabel', ES::getCurrencyOptions());

		return $this->display();
	}
}
