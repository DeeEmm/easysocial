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

class EasySocialModelCurrencies extends EasySocialModel
{
	public function __construct($config = [])
	{
		parent::__construct('currencies', $config);
	}

	/**
	 * Retrieves currencies from the site
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function getCurrencies()
	{
		$result = null;

		if (is_null($result)) {
			$config = ES::config();
			$currencies = $config->get('currencies');

			if (!is_array($currencies)) {
				$currencies = json_decode($config->get('currencies'));
			}

			foreach ($currencies as &$currency) {
				$currency = (object) $currency;
			}

			$result = $currencies;
		}

		return $result;
	}

	/**
	 * Retrieves currencies from the site
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function getCurrencyOptions()
	{
		$options = null;

		if (is_null($options)) {
			$currencies = $this->getCurrencies();

			$options = [];

			foreach ($currencies as $currency) {
				$option = [
					'value' => $currency->id,
					'text' => $currency->title
				];

				$options[] = $option;
			}
		}

		return $options;
	}

	/**
	 * Retrieves the index of the currency
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function getIndex($id)
	{
		$currencies = $this->getCurrencies();

		// Convert the objects into an array first
		static $arrayCurrencies = null;

		if (is_null($arrayCurrencies)) {
			$arrayCurrencies = $currencies;

			foreach ($arrayCurrencies as &$currency) {
				$currency = (array) $currency;
			}
		}

		$index = array_search($id, array_column($arrayCurrencies, 'id'));

		return $index;
	}

	/**
	 * Detects if there are any similar id in the currency list
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function isDuplicate($id)
	{
		$currency = $this->load($id);

		if ($currency !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Loads a specific currency given the id of the currency
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function load($id)
	{
		$currencies = $this->getCurrencies();
		$index = $this->getIndex($id);

		if ($index === false) {
			return false;
		}

		$currency = $currencies[$index];

		return $currency;
	}
}
