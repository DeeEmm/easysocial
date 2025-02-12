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

class EasySocialViewSefUrls extends EasySocialAdminView
{
	/**
	 * Renders the list of sef urls on the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_MENU_SEFURLS');

		JToolbarHelper::deleteList();
		JToolbarHelper::trash('purgeAll' , JText::_( 'COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_PURGE_ALL'), false);

		$model = ES::model('Urls', array('initState' => true));
		$urls = $model->getItems();

		$pagination = $model->getPagination();
		$search = $model->getState('search');
		$type = $model->getState('type');
		$limit = $model->getState('limit');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'));
		$direction = $this->input->get('direction', $model->getState('direction'));

		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('type', $type);
		$this->set('urls', $urls);
		$this->set('pagination', $pagination);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);

		parent::display('admin/sefurls/default/default');
	}

	/**
	 * Renders sef url edit form
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function form()
	{
		// setup buttons
		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$this->setHeading('COM_ES_MENU_SEFURLS_EDIT');

		// load url
		$id = $this->input->get('id', 0, 'int');

		$url = ES::table('Urls');
		$url->load($id);

		$this->set('url', $url);

		parent::display('admin/sefurls/form/default');
	}


	/**
	 * Post processing after sef url is saved
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function save($url, $task)
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form();
		}

		if ($task == 'apply') {
			return $this->redirect('index.php?option=com_easysocial&view=sefurls&layout=form&id=' . $url->id);
		}

		return $this->redirect('index.php?option=com_easysocial&view=sefurls');
	}

	/**
	 * Post processing after sef urls are deleted
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function remove()
	{
		return $this->redirect('index.php?option=com_easysocial&view=sefurls');
	}

	/**
	 * Post processing after sef urls are deleted
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function purge()
	{
		return $this->redirect('index.php?option=com_easysocial&view=sefurls');
	}
}
