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

class EasySocialViewPoints extends EasySocialAdminView
{
	/**
	 * Main method to display the points view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_POINTS', 'COM_EASYSOCIAL_DESCRIPTION_POINTS');

		// Add Joomla buttons here
		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::divider();
		JToolbarHelper::deleteList();

		$model = ES::model('Points' , array('initState' => true, 'namespace' => 'points.listing'));
		$state = $model->getState('published');
		$extension = $model->getState('filter');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$search = $model->getState('search');

		// Load a list of extensions so that users can filter them.
		$extensions	= $model->getExtensions();

		// Load the language files for each available extension
		$langlib = ES::language();
		
		foreach ($extensions as $e) {
			$langlib->load($e, JPATH_ROOT);
			$langlib->load($e, JPATH_ADMINISTRATOR);
		}

		$points = $model->getItems();
		$pagination = $model->getPagination();

		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('selectedExtension', $extension);
		$this->set('search', $search);
		$this->set('pagination', $pagination);
		$this->set('extensions', $extensions);
		$this->set('extension', $extension);
		$this->set('points', $points);
		$this->set('state', $state);

		$this->addHelpButton('/administrators/points/points');

		parent::display('admin/points/default/default');
	}

	/**
	 * Post process points saving
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save($task, $point)
	{
		$url = 'index.php?option=com_easysocial&view=points';

		if ($this->hasErrors()) {
			return $this->redirect($url . '&layout=form&id=' . $point->id);
		}

		if ($task == 'apply') {
			return $this->redirect($url . '&layout=form&id=' . $point->id);
		}

		return $this->redirect($url);
	}

	/**
	 * Generates the reporting section for points
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function achievers()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_POINTS_ACHIEVERS');

		$search = $this->input->get('search', '', 'default');

		// Get the current date
		$startDate = ES::date('first day of this month');
		$endDate = ES::date('last day of this month');

		$start = $this->input->get('start', $startDate->format('d-m-Y'), 'default');
		$end = $this->input->get('end', $endDate->format('d-m-Y'), 'default');
		$reports = array();

		if ($start && $end) {
			$model = ES::model('Points');
			$reports = $model->getReports($start, $end, $search);

			if ($reports) {
				foreach ($reports as &$report) {
					$report->user = ES::user($report->user_id);
				}
			}
		}

		$this->set('reports', $reports);
		$this->set('search', $search);
		$this->set('start', $start);
		$this->set('end', $end);
		$this->set('limit', 10);

		parent::display('admin/points/achievers/default');	
	}

	/**
	 * Main method to display the form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');

		$point = ES::table('Points');
		$state = $point->load($id);

		$this->setHeading('COM_EASYSOCIAL_HEADING_EDIT_POINTS', 'COM_EASYSOCIAL_DESCRIPTION_EDIT_POINTS');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel();

		$params = $point->getParams();

		if (!$params) {
			$params = array();
		} else {
			$params = $params->toArray();
		}

		$this->set('params', $params);
		$this->set('point', $point);

		parent::display('admin/points/form/default');
	}

	/**
	 * Redirects user back to the points listing once it's installed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		return $this->redirect('index.php?option=com_easysocial&view=points&layout=install');
	}

	/**
	 * Displays the CSV upload form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function csv($tpl = null)
	{
		// Add heading here.
		$this->setHeading('COM_EASYSOCIAL_HEADING_UPLOAD_CSV_POINTS', 'COM_EASYSOCIAL_DESCRIPTION_UPLOAD_CSV_POINTS');

		JToolbarHelper::custom('massAssign', 'upload', '', JText::_('Upload File'), false);

		$this->addHelpButton('/administrators/points/mass-assignment');

		parent::display('admin/points/csv/default');
	}

	/**
	 * Post process after the mass assignment is completed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function massAssign($success = array(), $failed = array())
	{
		return $this->redirect('index.php?option=com_easysocial&view=points&layout=csv');
	}

	/**
	 * Displays the installation layout for points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install($tpl = null)
	{
		// Add heading here.
		$this->setHeading('COM_EASYSOCIAL_HEADING_INSTALL_POINTS', 'COM_EASYSOCIAL_DESCRIPTION_INSTALL_POINTS');

		JToolbarHelper::custom('upload', 'upload', '', JText::_('COM_EASYSOCIAL_UPLOAD_AND_INSTALL'), false);

		$this->addHelpButton('/administrators/points/points#install');

		parent::display('admin/points/install/default');
	}

	/**
	 * Displays the discover layout for points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_DISCOVER_POINTS', 'COM_EASYSOCIAL_DESCRIPTION_INSTALL_POINTS');

		JToolbarHelper::custom('discover', 'download', '', JText::_('COM_EASYSOCIAL_DISCOVER_BUTTON'), false);

		$this->addHelpButton('/administrators/points/points#discover');

		parent::display('admin/points/discover');
	}

	/**
	 * Post processing for publishing and unpublishing an item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function publish()
	{
		return $this->redirect('index.php?option=com_easysocial&view=points');
	}

	/**
	 * Post processing for deleting an item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		return $this->redirect('index.php?option=com_easysocial&view=points');
	}
}
