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

class EasySocialViewProfiles extends EasySocialAdminView
{
	/**
	 * Renders a list of profiles at the back end
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_PROFILE_TYPES');

		JToolbarHelper::addNew('form', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::deleteList('', 'delete', JText::_( 'COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		$model = ES::model('Profiles' , array('initState' => true, 'namespace' => 'profiles.listing'));

		// perform some maintenance actions here
		$model->deleteOrphanItems();

		// Get the search query from post
		$search = $this->input->get('search', $model->getState('search'), 'default');

		// Get the current ordering.
		$ordering = $this->input->get( 'ordering' , $model->getState( 'ordering' ), 'word' );
		$direction = $this->input->get( 'direction' , $model->getState( 'direction' ), 'word' );
		$state = $this->input->get( 'state', $model->getState( 'state' ), 'default' );
		$limit = $model->getState( 'limit' );

		// Prepare options
		$profiles	= $model->getItems();
		$pagination	= $model->getPagination();

		$callback 	= $this->input->get( 'callback' , '', 'default' );

		$orphanCount = $model->getOrphanMembersCount( false );

		// Set properties for the template.
		$this->set('limit', $limit );
		$this->set('state', $state );
		$this->set('ordering', $ordering );
		$this->set('direction', $direction );
		$this->set('callback', $callback );
		$this->set('pagination', $pagination );
		$this->set('profiles', $profiles );
		$this->set('search', $search );
		$this->set('orphanCount', $orphanCount );

		parent::display('admin/profiles/default/default');
	}

	/**
	 * Displays a the profile form when someone creates a new profile type or edits an existing profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form($profile = '')
	{
		$id = $this->input->get('id', 0, 'int');

		// Test if id is provided by the query string
		if (!$profile) {
			$profile = ES::table('Profile');

			if ($id) {
				$state = $profile->load($id);

				if (!$state) {
					$this->info->set($this->getMessage());

					return $this->redirect('index.php?option=com_easysocial&view=profiles');
				}
			}
		}

		// Set the structure heading here.
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_NEW_PROFILE_TYPE', 'COM_EASYSOCIAL_DESCRIPTION_PROFILES_FORM');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('savenew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));

		if ($id) {
			JToolbarHelper::save2copy('savecopy', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AS_COPY'));
		}

		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		// If this is an edited profile, display the profile title.
		if (!empty($id)) {
			$this->setHeading($profile->_('title'), 'COM_EASYSOCIAL_DESCRIPTION_PROFILES_FORM');
		}

		// Default Values
		$defaultAvatars = array();

		// load frontend language so that the custom fields languages display properly.
		ES::language()->loadSite();

		// Only process the rest of the blocks of this is not a new item.
		if ($id) {

			// Get the default avatars for this profile
			$avatarsModel = ES::model('Avatars');
			$defaultAvatars = $avatarsModel->getDefaultAvatars($profile->id);

			// Render the access form.
			$accessModel = ES::model('Access');
			$accessForm = $accessModel->getForm($id, SOCIAL_TYPE_PROFILES, 'access', '', false);

			// Get the total number of members in this profile type
			$membersCount = $profile->getMembersCount();

			// Get a list of user apps installed on the site
			$appsModel = ES::model('Apps');
			$apps = $appsModel->getApps(array('type' => SOCIAL_APPS_TYPE_APPS, 'group' => SOCIAL_FIELDS_GROUP_USER, 'state' => SOCIAL_STATE_PUBLISHED));

			$this->set('selectedApps', $profile->getDefaultApps());
			$this->set('apps', $apps);
			$this->set('accessForm', $accessForm);
			$this->set('fieldGroup', SOCIAL_FIELDS_GROUP_USER);
		}

		// Get a list of themes.
		$themesModel = ES::model('Themes');
		$themes = $themesModel->getThemes();

		// Get profile parameters
		$params = $profile->getParams();

		// Get default privacy
		$privacy = ES::privacy($profile->id, SOCIAL_PRIVACY_TYPE_PROFILES);

		// We need to hide the guest user group that is defined in com_users options.
		// Public group should also be hidden.
		$userOptions = JComponentHelper::getComponent('com_users')->params;
		$defaultRegistrationGroup = $userOptions->get('new_usertype');
		$guestGroup = array(1, $userOptions->get('guest_usergroup'));

		// Set the default registration group for new items
		if (!$id) {
			$profile->gid = $defaultRegistrationGroup;
		}

		// Get the active tab
		$activeTab = $this->input->get('activeTab', 'settings', 'word');

		// Get a list of default groups
		$defaultGroups = $profile->getDefaultClusters('groups');

		// Exclude groups from being suggested
		$excludeGroups = array();

		if ($defaultGroups) {
			foreach ($defaultGroups as $group) {
				$excludeGroups[] = (int) $group->id;
			}
		}

		// Get a list of default pages
		$defaultPages = $profile->getDefaultClusters('pages');

		// Exclude pages from being suggested
		$excludePages = array();

		if ($defaultPages) {
			foreach ($defaultPages as $page) {
				$excludePages[] = (int) $page->id;
			}
		}

		$this->set('excludePages', $excludePages);
		$this->set('defaultPages', $defaultPages);
		$this->set('excludeGroups', $excludeGroups);
		$this->set('defaultGroups', $defaultGroups);
		$this->set('activeTab', $activeTab);
		$this->set('defaultAvatars', $defaultAvatars);
		$this->set('guestGroup', $guestGroup);
		$this->set('id', $id);
		$this->set('themes', $themes);
		$this->set('param', $params);
		$this->set('profile', $profile);
		$this->set('privacy', $privacy);

		parent::display('admin/profiles/form/default');
	}

	/**
	 * Post processing for updating ordering.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveorder()
	{
		return $this->redirect('index.php?option=com_easysocial&view=profiles');
	}

	/**
	 * Post processing for storing. What the view should do after a storing is executed.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($profile = '', $task = '')
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form($profile);
		}

		$activeTab = $this->input->get('activeTab', 'settings', 'cmd');

		if ($task == 'apply') {
			return $this->redirect('index.php?option=com_easysocial&view=profiles&id=' . $profile->id . '&layout=form&activeTab=' . $activeTab);
		}

		if ($task == 'savenew') {
			return $this->redirect('index.php?option=com_easysocial&view=profiles&layout=form');
		}

		return $this->redirectToProfiles();
	}

	/**
	 * Stores the profile and redirect back to the same edit page.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function apply($profile = '')
	{
		return $this->form($profile);
	}

	/**
	 * Standard redirection back to profiles listing
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToProfiles()
	{
		return $this->redirect('index.php?option=com_easysocial&view=profiles');
	}
}
