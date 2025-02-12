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

class SocialCurrency
{
	public $id = null;
	public $title = null;
	public $symbol = null;
	public $separator = null;

	private $currencies = [];
	private $isNew = true;

	public static function factory($id = null)
	{
		return new SocialCurrency($id);
	}

	public function __construct($id = null)
	{
		$this->config = ES::config();

		$model = ES::model('Currencies');
		$this->currencies = $model->getCurrencies();

		if ($id) {
			$currency = $model->load($id);

			if ($currency !== false) {
				$this->id = $currency->id;
				$this->title = $currency->title;
				$this->symbol = $currency->symbol;
				$this->separator = isset($currency->separator) ? $currency->separator : '.';

				$this->isNew = false;
			}
		}
	}

	/**
	 * Binds a set of data to the currency
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function bind($data = [])
	{
		foreach(['id', 'title', 'symbol', 'separator'] as $key) {
			if (isset($data[$key])) {
				$this->$key = $data[$key];
			}
		}
	}

	/**
	 * Deletes a currency
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function delete()
	{
		$model = ES::model('Currencies');
		$index = $model->getIndex($this->id);

		if (!$this->id || $index === false) {
			throw new Exception('Invalid parameters to delete currency');
		}

		unset($this->currencies[$index]);

		$this->currencies = array_values($this->currencies);

		$this->updateCurrenciesList();

		return true;
	}

	/**
	 * Deletes multiple currencies
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function deleteMultiple($ids)
	{
		$model = ES::model('Currencies');

		foreach ($ids as $id) {
			$index = $model->getIndex($id);
			unset($this->currencies[$index]);
		}

		$this->currencies = array_values($this->currencies);
		$this->updateCurrenciesList();

		return true;
	}

	/**
	 * Saves the current currency
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function store()
	{
		if (!$this->id || !$this->title || !$this->symbol) {
			throw new Exception('Invalid parameters to save currency');
		}

		$currency = new stdClass();
		$currency->id = $this->id;
		$currency->title = $this->title;
		$currency->symbol = $this->symbol;
		$currency->separator = $this->separator;

		if ($this->isNew) {
			$this->currencies[] = $currency;
		}

		if (!$this->isNew) {
			$model = ES::model('Currencies');
			$index = $model->getIndex($this->id);

			$this->currencies[$index] = $currency;
		}

		$this->updateCurrenciesList();

		return true;
	}

	/**
	 * Updates the currency list
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	private function updateCurrenciesList()
	{
		$configModel = ES::model('Config');
		return $configModel->updateConfig([
			'currencies' => json_encode($this->currencies)
		]);
	}
}
