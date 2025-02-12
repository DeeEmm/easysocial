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

class EasySocialViewWorkflowsAbstract extends EasySocialAdminView
{
	protected $workflowType = SOCIAL_TYPE_USER;

	public function getViewType()
	{
		return $this->workflowType . 's';
	}

	/**
	 * Abstract method for rendering custom fields listing
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function fields($tpl = null)
	{
		$viewType = $this->getViewType();

		// Set the page heading
		$this->setHeading('COM_ES_CUSTOM_FIELDS_' . strtoupper($viewType));

		// Add Joomla buttons here.
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::deleteList('', 'uninstall', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_UNINSTALL'));

		// Get the applications model.
		$model = ES::model('Apps', ['initState' => true, 'namespace' => 'apps.fields']);
		$model->setState('group', $this->workflowType);

		// Get the current ordering.
		$search = $this->input->get('search', $model->getState('search'));
		$state = $this->input->get('state', $model->getState('state'));

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$search = $model->getState('search');

		$options = [
			'filter' => 'fields',
			'ordering' => $ordering,
			'direction' => $direction,
		];

		$apps = $model->getItemsWithState($options);

		// Get the pagination.
		$pagination	= $model->getPagination();

		$this->set('view', $viewType);
		$this->set('search', $search);
		$this->set('limit', $limit);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('apps', $apps);
		$this->set('pagination', $pagination);

		parent::display('admin/workflows/fields/default');
	}

	/**
	 * Renders the custom fields form
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function fieldsform()
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

		$access = $app->getAccess();
		$selectedAccess = $access->getAllowed();

		$showDefaultSetting = false;

		if ($app->type == SOCIAL_TYPE_APPS && !$app->system && $app->group != SOCIAL_TYPE_GROUP && $app->group != SOCIAL_TYPE_PAGE) {
			$showDefaultSetting = true;
		}

		$meta = $app->getMeta();

		// Default view
		$view = 'users';

		if ($app->group != 'user') {
			$view = $app->group . 's';
		}


		$customRedirect = 'index.php?option=com_easysocial&view=' . $view . '&layout=fields';

		$this->set('customRedirect', $customRedirect);
		$this->set('meta', $meta);
		$this->set('selectedAccess', $selectedAccess);
		$this->set('app', $app);
		$this->set('showDefaultSetting', $showDefaultSetting);

		parent::display('admin/apps/form/default');
	}

	/**
	 * Renders the list of workflows on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function workflows($tpl = null)
	{
		$viewType = $this->getViewType();

		$this->setHeading('COM_ES_WORKFLOWS_' . strtoupper($viewType));

		JToolbarHelper::addNew();
		JToolbarHelper::custom('duplicate', '', '', JText::_('COM_ES_DUPLICATE'));
		JToolbarHelper::deleteList();

		$model = ES::model('Workflows', array('initState' => true));
		$model->setState('type', $this->workflowType);

		$workflows = $model->getItems();

		$pagination = $model->getPagination();
		$search = $model->getState('search');
		$limit = $model->getState('limit');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'));
		$direction = $this->input->get('direction', $model->getState('direction'));

		$this->set('view', $viewType);
		$this->set('type', $this->workflowType);
		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('workflows', $workflows);
		$this->set('pagination', $pagination);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);

		parent::display('admin/workflows/default/default');
	}
}
