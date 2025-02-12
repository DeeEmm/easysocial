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

class EasySocialViewHoneypot extends EasySocialAdminView
{
	/**
	 * Main method to display the points view.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_HEADING_HONEYPOT');

		JToolbarHelper::deleteList();
		JToolbarHelper::custom('purge', '', '', JText::_('COM_ES_PURGE_ALL_LOGS'), false);

		$model = ES::model('Honeypot', array('initState' => true, 'namespace' => 'honeypot.listing'));

		$search = $model->getState('search');
		$type = $model->getState('type');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');

		$logs = $model->getItems();
		$pagination = $model->getPagination();

		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('type', $type);
		$this->set('pagination', $pagination);
		$this->set('logs', $logs);

		parent::display('admin/honeypot/default/default');
	}

	public function remove()
	{
		return $this->redirectToView('honeypot');
	}

	public function purge()
	{
		return $this->redirectToView('honeypot');
	}
}
