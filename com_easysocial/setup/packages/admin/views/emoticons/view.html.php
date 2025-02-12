<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewEmoticons extends EasySocialAdminView
{
	/**
	 * Main method to display the emoticons view.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_HEADING_EMOTICONS');

		JToolbarHelper::addNew();
		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::divider();
		JToolbarHelper::deleteList();

		// Default filters
		$options = array('initState' => true, 'namespace' => 'emoticons.listing');
		$model = ES::model('Emoticons', $options);
		$search = $model->getState('search');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'word');
		$direction = $this->input->get('direction', $model->getState('direction'), 'word');
		$state = $this->input->get('state', $model->getState('state'), 'default');
		$limit = $model->getState('limit');

		$emoticons = $model->getItemsWithState();

		// Get pagination
		$pagination = $model->getPagination();

		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('emoticons', $emoticons);
		$this->set('pagination', $pagination);

		parent::display('admin/emoticons/default/default');
	}

	/**
	 * Main method to display the form.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function form($tpl = null)
	{
		// Get the id from the request.
		$id = $this->input->get('id', 0, 'int');

		// Get the table object
		$emoticon = ES::table('Emoticon');
		$state = $emoticon->load($id);

		// Add heading here.
		$this->setHeading('COM_ES_CREATE_NEW_EMOTICON');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));
		
		if ($id) {
			$this->setHeading($emoticon->get('title'), 'COM_ES_DESCRIPTION_EDIT_EMOTICON');
		}

		// Default value for new emoticon
		if (!$emoticon->id) {
			$emoticon->state = true;
			$emoticon->type = 'image';
		}

		$this->set('emoticon', $emoticon);

		parent::display('admin/emoticons/form/default');
	}

	/**
	 * Post process after a emoticon is deleted
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function remove($task = null, $emoticon = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=emoticons');
	}

	/**
	 * Post process after emoticons has been published / unpublished
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function togglePublish($task = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=emoticons');
	}

	/**
	 * Post process after a emoticon is stored
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function store($task = null, $emoticon = null)
	{
		$url = 'index.php?option=com_easysocial&view=emoticons';
		
		if ($task == 'apply' || $this->hasErrors()) {
			return $this->redirect($url . '&layout=form&id=' . $emoticon->id);
		}

		return $this->redirect($url);
	}
}