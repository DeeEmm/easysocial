<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewStream extends EasySocialAdminView
{
	/**
	 * Displays the backgrounds listing
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function background()
	{
		$this->setHeading('COM_ES_HEADING_STREAM_BACKGROUND');

		JToolbarHelper::addNew();
		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::divider();
		JToolbarHelper::deleteList('Are you sure you want to delete the selected backgrounds?');
		
		$model = ES::model('background', array('initState' => true, 'namespace' => 'background.listing'));
		$items = $model->getItemsWithState();

		$pagination = $model->getPagination();
		$state = $model->getState('state');
		$limit = $model->getState('limit');
		$search = $model->getState('search');

		if ($items) {
			foreach ($items as &$row) {
				$row->params = new JRegistry($row->params);
			}
		}

		$this->set('search', $search);
		$this->set('pagination', $pagination);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('items', $items);

		parent::display('admin/stream/background/default');
	}

	/**
	 * Displays the scheduled posts listing
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function scheduled()
	{
		$this->setHeading('COM_ES_HEADING_STREAM_SCHEDULED');

		$model = ES::model('stream', array('namespace' => 'stream.listing'));
		$model->setState('state', SOCIAL_STREAM_STATE_SCHEDULED);
		$model->setState('ordering', 'scheduledDate');

		$items = $model->getItemsWithState();

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$state = $model->getState('state');
		$pagination = $model->getPagination();

		// Add button
		$this->toolbar($state);

		$this->set('pagination', $pagination);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('items', $items);

		parent::display('admin/stream/scheduled/default');
	}

	/**
	 * Displays the backgrounds listing
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function backgroundForm()
	{
		$this->setHeading('COM_ES_HEADING_STREAM_BACKGROUND');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));
		
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Background');
		$table->load($id);
		$params = $table->getParams();

		if (!$params) {
			$params = new JRegistry();
		}

		if (!$id) {
			$table->state = true;
		}

		$this->set('params', $params);
		$this->set('table', $table);

		parent::display('admin/stream/background/form');
	}

	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_STREAM', 'COM_EASYSOCIAL_DESCRIPTION_STREAM');

		// Check if this is from after execution
		$success = $this->input->get('success', 0, 'int');

		$model = ES::model('stream', array('initState' => true, 'namespace' => 'stream.listing'));
		$items = $model->getItemsWithState();

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$state = $model->getState('state');
		$pagination = $model->getPagination();

		// Add button
		$this->toolbar($state);


		// set variable into themes.
		$this->set('pagination', $pagination);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('items', $items);

		parent::display('admin/stream/default/default');
	}

	/**
	 * Standard redirection after performing actions
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function standardRedirection()
	{
		$layout = $this->input->get('layout', false, 'word');

		$redirect = 'index.php?option=com_easysocial&view=stream';

		if ($layout) {
			$redirect .= '&layout=' . $layout;
		}
		
		return $this->redirect($redirect);
	}

	private function toolbar($state)
	{
		if ($state == SOCIAL_STREAM_STATE_TRASHED) {
			JToolbarHelper::custom('purge', 'trash', '', JText::_('COM_EASYSOCIAL_STREAM_DELETE'), true);
		}

		if ($state == 'all' || $state == SOCIAL_STREAM_STATE_RESTORED) {
			JToolbarHelper::custom('trash', 'trash' , '' , JText::_('COM_EASYSOCIAL_STREAM_TRASH'), true);
		}

		if ($state == SOCIAL_STREAM_STATE_TRASHED) {
			JToolbarHelper::custom('restoreTrash', 'refresh', '', JText::_('COM_EASYSOCIAL_STREAM_RESTORE'), true);
		}

		if ($state == SOCIAL_STREAM_STATE_ARCHIVED) {
			JToolbarHelper::custom('restore', 'refresh', '', JText::_('COM_EASYSOCIAL_STREAM_RESTORE'), true);
		}

		if ($state == 'all' || $state == SOCIAL_STREAM_STATE_RESTORED) {
			JToolbarHelper::custom('archive', '', '', JText::_('COM_EASYSOCIAL_STREAM_ARCHIVE'), true);
		}

		if ($state == SOCIAL_STREAM_STATE_SCHEDULED) {
			JToolbarHelper::publishList();
			JToolbarHelper::custom('purge', 'trash', '', JText::_('COM_EASYSOCIAL_STREAM_DELETE'), true);
		}
	}
}