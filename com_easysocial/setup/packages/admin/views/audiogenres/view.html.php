<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewAudioGenres extends EasySocialAdminView
{
	/**
	 * Main method to display the audio genres
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_AUDIO_GENRES');

		// Insert Joomla buttons
		JToolbarHelper::addNew();
		JToolbarHelper::divider();
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::deleteList();

		// Get the model
		$model = ES::model('Audios', array('initState' => true, 'namespace' => 'audiogenres.listing'));

		// Remember the states
		$search = $model->getState('search');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');

		// Get the genres
		$genres = $model->getGenres(array('search' => $search, 'administrator' => true, 'ordering' => $ordering, 'direction' => $direction));

		// Get the pagination 
		$pagination = $model->getPagination();

		$this->set('simple', $this->input->getString('tmpl') == 'component');
		$this->set('genres', $genres);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('limit', $limit);
		$this->set('pagination', $pagination);
		$this->set('search', $search);

		return parent::display('admin/audiogenres/default');
	}

	/**
	 * Displays the genre form
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');

		$this->setHeading('COM_EASYSOCIAL_HEADING_AUDIO_GENRES_CREATE');

		$genre = ES::table('AudioGenre');
		$genre->load($id);

		if ($id) {
			$this->setHeading('COM_EASYSOCIAL_HEADING_AUDIO_GENRES');
			$this->setDescription('COM_EASYSOCIAL_HEADING_AUDIO_GENRES_DESC');            
		} else {
			// If new record, it should be published by default.
			$genre->state = SOCIAL_STATE_PUBLISHED;
		}

		// Get the active genre
		$activeTab = $this->input->get('active', 'settings', 'cmd');

		// Get the acl for creation access
		$createAccess = $genre->getProfileAccess();

		// Insert Joomla buttons
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::save2new();
		JToolbarHelper::cancel();

		$this->set('createAccess', $createAccess);
		$this->set('activeTab', $activeTab);
		$this->set('genre', $genre);

		return parent::display('admin/audiogenres/forms/default');
	}

	/**
	 * Post process after publishing
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function publish()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=audiogenres');
	}

	/**
	 * Post process after unpublishing
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function unpublish()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=audiogenres');
	}

	/**
	 * Post process after deleting genre
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function delete()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=audiogenres');
	}

	/**
	 * Post process after saving
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function save()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=audiogenres');
	}

	/**
	 * Post process after saving
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function save2new()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=audiogenres&layout=form');
	}

	/**
	 * Post process after a genre is set as default
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function toggleDefault()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=audiogenres');
	}

	/**
	 * Post process after apply is clicked
	 *
	 * @since   2.1
	 * @access  public 
	 */
	public function apply($genre)
	{
		$this->info->set($this->getMessage());
		
		return $this->redirect('index.php?option=com_easysocial&view=audiogenres&layout=form&id=' . $genre->id);
	}

	/**
	 * Post processing for updating ordering.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function saveorder()
	{
		return $this->redirect('index.php?option=com_easysocial&view=audiogenres');
	}

	/**
	 * Post process after moving audio genre order
	 *
	 * @since  2.1
	 * @access public
	 */
	public function move($layout = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=audiogenres');
	}
}
