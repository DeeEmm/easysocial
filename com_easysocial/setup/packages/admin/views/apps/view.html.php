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

ES::import('admin:/views/views');

class EasySocialViewApps extends EasySocialAdminView
{
	public function display($tpl = null)
	{
		// Set the page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_APPS', 'COM_EASYSOCIAL_DESCRIPTION_APPS');

		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::divider();
		JToolbarHelper::deleteList('', 'uninstall', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_UNINSTALL'));
		JToolbarHelper::custom('refresh', 'loop', '', JText::_('Refresh Directory'), false);


		// Get the applications model.
		$model = ES::model('Apps', array('initState' => true, 'namespace' => 'apps.listing'));

		// Get the current ordering.
		$state = $this->input->get('state', $model->getState('state'));
		$group = $this->input->get('group', $model->getState('group'));
		$ordering = $this->input->get('ordering', $model->getState('ordering'));
		$direction = $this->input->get('direction', $model->getState('direction'));

		$limit = $model->getState('limit');
		$search = $model->getState('search');

		$options = array('filter' => 'apps', 'search' => $search, 'ordering' => $ordering, 'direction' => $direction);
		$apps = $model->getItemsWithState($options);

		// Get the pagination.
		$pagination	= $model->getPagination();

		$storeModel = ES::model('Store');
		$outdatedApps = $storeModel->getAppsRequiringUpdates();

		$filter = $this->input->get('filter', '');

		if ($filter == 'outdated' || $state == 'outdated') {
			$apps = $outdatedApps;
			$pagination = $storeModel->getPagination();
		}

		$this->set('filter', $filter);
		$this->set('outdatedApps', $outdatedApps);
		$this->set('layout', 'apps');
		$this->set('group', $group);
		$this->set('search', $search);
		$this->set('limit', $limit);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('apps', $apps);
		$this->set('pagination', $pagination);

		parent::display('admin/apps/default/default');
	}

	/**
	 * Renders the directory
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function store()
	{
		$isSearch = false;

		$this->setHeading('COM_EASYSOCIAL_HEADING_APPS_DIRECTORY');

		JToolbarHelper::custom('refresh', 'loop', '', JText::_('Refresh Directory'), false);

		// Retrieve a list of apps from the directory
		$model = ES::model('Store', array('initState' => true, 'namespace' => 'apps.store'));

		$search = $this->input->get('search', $model->getState('search'), 'default');
		$type = $this->input->get('type', $model->getState('type'), 'default');
		$category = $this->input->get('category', $model->getState('category'), 'default');
		$company = $this->input->get('company', $model->getState('company'), 'default');

		if ($search || $type || $category || $company) {
			$isSearch = true;
		}

		// Get featured apps
		$featuredApps = array();

		if (! $isSearch) {
			$featuredApps = $model->getFeaturedApps();
		}

		$options = array();

		if ($isSearch) {
			// lets include featured apps.
			$options['featured'] = true;
		}

		$apps = $model->getItemsWithState($options);
		$pagination = $model->getPagination();

		$categories = $model->getCategories();
		$types = $model->getTypes();
		$limit = $model->getState('limit');

		$this->set('company', $company);
		$this->set('featuredApps', $featuredApps);
		$this->set('category', $category);
		$this->set('type', $type);
		$this->set('pagination', $pagination);
		$this->set('types', $types);
		$this->set('categories', $categories);
		$this->set('apps', $apps);
		$this->set('isSearch', $isSearch);
		$this->set('search', $search);
		$this->set('state', '');
		$this->set('limit', $limit);

		parent::display('admin/apps/store/default');
	}

	/**
	 * Renders the directory
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function item()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_APPS_DIRECTORY');

		JToolbarHelper::back();

		$id = $this->input->get('id', 0, 'int');
		$app = ES::store()->getApp($id);

		$this->set('app', $app);

		parent::display('admin/store/item/default');
	}

	/**
	 * When a user cancels the payment, they will be redirected to this page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function fail()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_APPS_ORDER_CANCELLED');

		JToolbarHelper::back('Back', 'index.php?option=com_easysocial&view=apps&layout=store');

		$id = $this->input->get('id', 0, 'int');
		$app = ES::store()->getApp($id);

		$this->set('app', $app);

		parent::display('admin/store/fail/default');
	}

	/**
	 * Post processing after generating apps
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function generate()
	{
		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=apps&layout=directory');
	}

	/**
	 * Displays the installation page.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install()
	{
		$this->info->set($this->getMessage());

		// Set the page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_APPS');
		$this->setDescription('COM_EASYSOCIAL_DESCRIPTION_APPS_INSTALLER');

		// Set the default temporary path.
		$jConfig = JFactory::getConfig();
		$temporaryPath = $jConfig->get('tmp_path');

		// Retrieve folders.
		$appsModel = ES::model('Apps');
		$directories = $appsModel->getDirectoryPermissions();

		$this->set('directories', $directories);
		$this->set('temporaryPath', $temporaryPath);

		parent::display('admin/apps/install/default');
	}

	/**
	 * Post process after discovered items are purged
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purgeDiscovered()
	{
		ES::info()->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=apps&layout=discover');
	}

	/**
	 * Displays the installation page.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 */
	public function discover()
	{
		$this->info->set($this->getMessage());

		$this->setHeading('COM_EASYSOCIAL_HEADING_DISCOVER_APPS');
		$this->setDescription('COM_EASYSOCIAL_DESCRIPTION_DISCOVER_APPS');

		// Add Joomla buttons here.
		JToolbarHelper::custom('installDiscovered', 'upload', '', JText::_('COM_EASYSOCIAL_INSTALL_SELECTED_BUTTON') , false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('discover', 'refresh', '', JText::_('COM_EASYSOCIAL_DISCOVER_BUTTON') , false);
		JToolbarHelper::custom('purgeDiscovered', 'trash', '', JText::_('COM_EASYSOCIAL_PURGE_CACHE_BUTTON') , false);

		// Get the applications model.
		$model = ES::model('Apps', array('initState' => true, 'namespace' => 'apps.discover'));

		// Get the current ordering.
		$search = $this->input->get('search', $model->getState('search'), 'string');
		$filter = $this->input->get('filter', $model->getState('filter'), 'cmd');

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$search = $model->getState('search');

		// Load the applications.
		$apps = $model->getItemsWithState(array('discover' => true, 'filter' => $filter));

		// Get the pagination.
		$pagination = $model->getPagination();

		$this->set('search', $search);
		$this->set('limit', $limit);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('filter', $filter);
		$this->set('apps', $apps);
		$this->set('pagination', $pagination);

		parent::display('admin/apps/discover/default');
	}

	/**
	 * Post process after installing discovered apps
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function installDiscovered()
	{
		ES::info()->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=apps&layout=discover');
	}

	/**
	 * Displays installation completed page.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function installCompleted($app)
	{
		// Set the page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_APPS_INSTALL_SUCCESS');
		$this->setDescription('COM_EASYSOCIAL_DESCRIPTION_APPS_INSTALL_SUCCESS');

		$session = JFactory::getSession();
		$session->set('application.queue', null);

		// Get the apps meta.
		$meta = $app->getMeta();

		$this->set('meta', $meta);
		$this->set('app', $app);
		$this->set('output', $app->result->output);
		$this->set('desc', $meta->desc);

		echo parent::display('admin/apps/install/completed');
	}

	/**
	 * Post process after app is published
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function publish($type, $return = '')
	{
		$this->info->set($this->getMessage());

		$url = 'index.php?option=com_easysocial&view=apps';

		if ($type == 'fields') {
			$url = 'index.php?option=com_easysocial&view=workflows&layout=fields';
		}

		if ($return) {
			$url = $return;
		}

		// Reinitialize previous states
		$limitstart = $this->input->get('limitstart', '');
		$group = $this->input->get('group', '');
		$state = $this->input->get('state', '');

		if ($limitstart) {
			$url .= '&limitstart=' . $limitstart;
		}

		if ($group) {
			$url .= '&group=' . $group;
		}

		if ($state) {
			$url .= '&state=' . $state;
		}

		$this->redirect($url);
	}

	/**
	 * Post process after app is unpublished
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function unpublish($type, $return = '')
	{
		$this->info->set($this->getMessage());

		$url = 'index.php?option=com_easysocial&view=apps';

		if ($type == 'fields') {
			$url = 'index.php?option=com_easysocial&view=workflows&layout=fields';
		}

		if ($return) {
			$url = $return;
		}

		// Reinitialize previous states
		$limitstart = $this->input->get('limitstart', '');
		$group = $this->input->get('group', '');
		$state = $this->input->get('state', '');

		if ($limitstart) {
			$url .= '&limitstart=' . $limitstart;
		}

		if ($group) {
			$url .= '&group=' . $group;
		}

		if ($state) {
			$url .= '&state=' . $state;
		}

		$this->redirect($url);
	}

	/**
	 * Post process after apps has been uninstalled
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function uninstall()
	{
		ES::info()->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=apps');
		$this->close();
	}

	/**
	 * Post process after an app is saved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save($app = null, $task = '')
	{
		$this->info->set($this->getMessage());

		if ($app->type == 'fields') {
			$customRedirect = $this->input->get('customRedirect', '', 'default');

			$redirect = 'index.php?option=com_easysocial&view=workflows';

			if ($customRedirect) {
				$redirect = $customRedirect;
			}

			if ($task == 'apply') {
				return $this->redirect($redirect . '&layout=fieldsform&id=' . $app->id);
			}

			return $this->redirect($redirect . '&layout=fields');
		}

		$redirect = 'index.php?option=com_easysocial&view=apps';

		if ($task == 'apply') {
			return $this->redirect($redirect . '&layout=form&id=' . $app->id);
		}

		return $this->redirect($redirect);
	}

	/**
	 * Renders the form for the application
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');
		$app = ES::table('App');
		$app->load($id);

		if (!$id || !$app->id) {
			return $this->exception('COM_EASYSOCIAL_APP_INVALID_ID');
		}

		// Load front end's language
		ES::language()->loadSite();

		// Set the page heading
		$this->setHeading($app->_('title'), 'COM_EASYSOCIAL_DESCRIPTION_APPS_CONFIGURATION');

		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::cancel();

		$selectedAccess = false;

		if ($app->hasAccessSettings()) {
			$access = $app->getAccess();
			$selectedAccess = $access->getAllowed();
		}

		$showDefaultSetting = false;

		if ($app->type == SOCIAL_TYPE_APPS && !$app->system && $app->group != SOCIAL_TYPE_GROUP && $app->group != SOCIAL_TYPE_PAGE) {
			$showDefaultSetting = true;
		}

		$meta = $app->getMeta();

		$this->set('meta', $meta);
		$this->set('selectedAccess', $selectedAccess);
		$this->set('app', $app);
		$this->set('showDefaultSetting', $showDefaultSetting);
		$this->set('customRedirect', '');

		parent::display('admin/apps/form/default');
	}

	/**
	 * Displays when the installation is completed
	 *
	 * @access	public
	 */
	public function completed($app)
	{
		$this->set('app', $app);

		// Display the success messages.
		parent::display('admin.installer.completed');

		// Display the form again so that the user can continue with the installation if needed.
		$this->display();
	}

	public function errors($response)
	{
		$this->set('response', $response);

		parent::display('admin.installer.errors');
	}
	/**
	 * Post processing for updating ordering.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function saveorder()
	{
		$model = ES::model('Apps', array('initState' => true, 'namespace' => 'apps.listing'));
		$group = $this->input->get('group', $model->getState('group'));

		return $this->redirect('index.php?option=com_easysocial&view=apps&group=' . $group . '');
	}
	/**
	 * Post process after an app default status is toggled
	 *
	 * @since 	2.0
	 * @access	public
	 */
	public function toggleDefault()
	{
		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=apps');
	}
		/**
	 * Post process after moving app order
	 *
	 * @since  2.0
	 * @access public
	 */
	public function move($layout = null, $group = 'all')
	{
		$model = ES::model('Apps', array('initState' => true, 'namespace' => 'apps.listing'));
		$group = $this->input->get('group', $model->getState('group'));

		return $this->redirect('index.php?option=com_easysocial&view=apps&group=' . $group . '');
	}
}
