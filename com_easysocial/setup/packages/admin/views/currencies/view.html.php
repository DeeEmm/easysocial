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

class EasySocialViewCurrencies extends EasySocialAdminView
{
	/**
	 * Main method to display the emoticons view.
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_HEADING_CURRENCIES');

		JToolbarHelper::addNew();
		JToolbarHelper::deleteList();

		$model = ES::model('Currencies');
		$currencies = $model->getCurrencies();

		$this->set('currencies', $currencies);

		parent::display('admin/currencies/default/default');
	}

	/**
	 * Main method to display the currency form.
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function form($tpl = null)
	{
		// Get the id from the request.
		$id = $this->input->get('id', 0, 'word');

		// Add heading here.
		$this->setHeading('COM_ES_CREATE_NEW_CURRENCY');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$currency = ES::currency($id);

		if ($id) {
			$this->setHeading('COM_ES_EDITING_CURRENCY');
		}

		$this->set('currency', $currency);

		parent::display('admin/currencies/form/default');
	}

	/**
	 * Post process after a currency is deleted
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function remove()
	{
		return $this->redirect('index.php?option=com_easysocial&view=currencies');
	}

	/**
	 * Post process after a currency is stored
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function store($task = null, $currency = null)
	{
		$url = 'index.php?option=com_easysocial&view=currencies';

		if ($task == 'apply' || $this->hasErrors()) {
			return $this->redirect($url . '&layout=form&id=' . $currency->id);
		}

		return $this->redirect($url);
	}
}
