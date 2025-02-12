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

class EasySocialViewAudios extends EasySocialAdminView
{
	/**
	 * Renders the audios from the back end
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_AUDIOS');

		// Add Joomla buttons here
		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::divider();
		JToolbarHelper::deleteList();
		JToolbarHelper::divider();
		JToolbarHelper::custom('setFeatured', 'featured', '', JText::_('COM_ES_FEATURE'));
		JToolbarHelper::custom('removeFeatured', '', '', JText::_('COM_ES_UNFEATURE'));
		JToolbarHelper::custom('switchOwner', 'switchowner', '', JText::_('COM_EASYSOCIAL_CHANGE_OWNER'));


		$model = ES::model('Audios', array('initState' => true, 'namespace' => 'audios.listing'));

		$filter = $model->getState('filter');
		$state = $model->getState('published');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$search = $model->getState('search');

		if ($filter != 'all') {
			$filter = (int) $filter;
		}

		// Load a list of extensions so that users can filter them.
		$audios = $model->getItems();

		// Get pagination
		$pagination = $model->getPagination();

		if ($this->input->getString('tmpl') == 'component') {
			$pagination->setVar('tmpl', 'component');
		}

		$this->set('filter', $filter);
		$this->set('direction', $direction);
		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('audios', $audios);
		$this->set('pagination', $pagination);
		$this->set('simple', $this->input->getString('tmpl') == 'component');

		parent::display('admin/audios/default/default');
	}

	/**
	 * Renders the audio form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function form()
	{
		// Try to load the audio that needs to be edited
		$id = $this->input->get('id', 0, 'int');

		$this->setHeading('COM_EASYSOCIAL_HEADING_AUDIO_EDIT_AUDIO');
		$this->setDescription('');

		$table = ES::table('Audio');
		$table->load($id);

		$audio = ES::audio($table);

		// Load front end's language file
		ES::language()->loadSite();

		$model = ES::model('Audios');
		$genres = $model->getGenres();

		// Retrieve the privacy library
		$privacy = ES::privacy();

		// Retrieve audio tags
		$userTags = $audio->getEntityTags();
		$userTagItemList = array();

		if ($userTags) {
			foreach($userTags as $userTag) {
				$userTagItemList[] = $userTag->item_id;
			}
		}

		$hashtags = $audio->getTags(true);

		$this->set('privacy', $privacy);
		$this->set('hashtags', $hashtags);
		$this->set('userTags', $userTags);
		$this->set('genres', $genres);
		$this->set('table', $table);
		$this->set('audio', $audio);

		// Add Joomla buttons here
		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		parent::display('admin/audios/form');
	}

	/**
	 * Post process after an audio is saved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save(SocialAudio $audio, $task)
	{
		$redirect = 'index.php?option=com_easysocial&view=audios';

		if ($task == 'apply') {
			$redirect .= '&layout=form&id=' . $audio->id;
		}

		return $this->redirect($redirect);
	}

	/**
	 * Standard redirection to audios listing
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToAudios()
	{
		return $this->redirect('index.php?option=com_easysocial&view=audios');
	}
}
