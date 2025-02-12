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

class EasySocialViewAlbums extends EasySocialAdminView
{
	/**
	 * Renders the list of albums created on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_ALBUMS', 'COM_EASYSOCIAL_DESCRIPTION_ALBUMS');

		// Load frontend language files
		ES::language()->loadSite();

		// Get the model
		$model = ES::model('Albums', array('initState' => true, 'namespace' => 'albums.listing'));

		// Get filter states.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'string');
		$direction = $this->input->get('direction'	, $model->getState('direction'), 'string');
		$limit = $model->getState('limit');
		$published = $model->getState('published');
		$search = $this->input->get('search'	, $model->getState('search'), 'string');

		// Add Joomla buttons
		JToolbarHelper::deleteList();

		$albums = $model->getDataWithState();
		$pagination = $model->getPagination();

		$callback = $this->input->get('callback', '', 'default');

		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('direction', $direction);
		$this->set('callback', $callback);
		$this->set('search', $search);
		$this->set('published', $published);
		$this->set('pagination', $pagination);
		$this->set('albums', $albums);

		parent::display('admin/albums/default');
	}

	/**
	 * Post process after an album is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		$this->redirect('index.php?option=com_easysocial&view=albums');
	}
}
