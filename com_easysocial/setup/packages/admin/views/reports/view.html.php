<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewReports extends EasySocialAdminView
{
	/**
	 * Renders the list of reports
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		JToolbarHelper::custom('purge', 'trash', '', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_PURGE_REPORTS'), false);
		JToolbarHelper::deleteList();

		// Set the structure heading here.
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_REPORTS');

		$model = ES::model('Reports', array('initState' => true, 'namespace' => 'reports.listing'));

		$search = $model->getState('search');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$reports = $model->getReports();

		// Get pagination
		$pagination	= $model->getPagination();

		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('search', $search);
		$this->set('limit', $limit);
		$this->set('pagination', $pagination);
		$this->set('reports', $reports);

		return parent::display('admin/reports/default/default');
	}

	/**
	 * Post process after reports is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		return $this->redirect('index.php?option=com_easysocial&view=reports');
	}

	/**
	 * Post processing after reports has been purged
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purge()
	{
		return $this->redirect('index.php?option=com_easysocial&view=reports');
	}
}