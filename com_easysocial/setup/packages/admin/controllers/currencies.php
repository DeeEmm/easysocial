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

class EasySocialControllerCurrencies extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
	}

	/**
	 * Removes emoticons from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', [], 'default');

		if (!$ids) {
			return $this->view->exception('Invalid ids provideed');
		}

		if (count($ids) > 1) {
			$currency = ES::currency();
			$currency->deleteMultiple($ids);

			$this->actionlog->log('COM_ES_ACTION_LOG_MULTIPLE_CURRENCIES_DELETED', 'currencies');

			$this->view->setMessage('COM_ES_CURRENCIES_DELETED');
			return $this->view->call(__FUNCTION__, $task);
		}

		foreach ($ids as $id) {
			$currency = ES::currency($id);
			$currency->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_CURRENCY_DELETED', 'currencies', [
					'name' => $currency->title
				]);
		}

		$this->view->setMessage('COM_ES_CURRENCIES_DELETED');
		return $this->view->call(__FUNCTION__, $task);
	}

	/**
	 * Saves a currency
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		$post = $this->input->post->getArray();
		$currency = ES::currency($post['id']);

		$isNew = $currency->id ? false : true;

		if (empty($post['id'])) {
			$this->view->setMessage('Please enter a valid ID for your currency', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $currency);
		}

		if (empty($post['title'])) {
			$this->view->setMessage('Please set a title for your currency', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $currency);
		}

		if (empty($post['symbol'])) {
			$this->view->setMessage('Please enter a symbol for your currency', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $currency);
		}

		// Ensure that there are no conflicting ids
		if ($isNew) {
			$model = ES::model('Currencies');

			if ($model->isDuplicate($post['id'])) {
				$this->view->setMessage('The ID that you have entered is already being used by another currency. Please specify a different ID', ES_ERROR);
				return $this->view->call(__FUNCTION__, $this->getTask(), $currency);
			}
		}

		$currency->bind($post);
		$currency->store();

		$actionString = $id ? 'COM_ES_ACTION_LOG_CURRENCIES_UPDATED' : 'COM_ES_ACTION_LOG_CURRENCIES_CREATED';

		$this->actionlog->log($actionString, 'currencies', [
				'name' => $currency->title,
				'link' => 'index.php?option=com_easysocial&view=currencies&layout=form&id=' . $currency->id
			]);

		$this->view->setMessage('Currency updated successfully');

		return $this->view->call(__FUNCTION__, $this->getTask(), $currency);
	}
}
