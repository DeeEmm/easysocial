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

require_once(dirname(__DIR__) . '/abstracts/workflows.php');
require_once(dirname(__DIR__) . '/traits/verifications.php');

class EasySocialViewGroups extends EasySocialViewWorkflowsAbstract
{
	use EasySocialViewVerificationsTrait;

	protected $workflowType = SOCIAL_TYPE_GROUP;
	protected $verificationType = SOCIAL_TYPE_GROUP;

	/**
	 * Renders the list of groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_GROUPS', 'COM_EASYSOCIAL_DESCRIPTION_GROUPS');

		// Add buttons for the groups
		JToolbarHelper::addNew('create', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::custom('makeFeatured', '', '', JText::_('COM_ES_FEATURE'));
		JToolbarHelper::custom('removeFeatured', '', '', JText::_('COM_ES_UNFEATURE'));
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Gets a list of profiles from the system.
		$model = ES::model('Groups', array('initState' => true, 'namespace' => 'groups.listing'));

		// Get the search query from post
		$search = $model->getState('search');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'));
		$direction = $this->input->get('direction', $model->getState('direction'));
		$state = $this->input->get('state', $model->getState('state'));
		$type = $this->input->get('type', $model->getState('type'), 'int');
		$category = $this->input->get('category', $model->getState('category'), 'int');
		$limit = $model->getState('limit');

		// Load front end language file
		ES::language()->loadSite();

		// Prepare options
		$groups = $model->getItemsWithState();
		$pagination	= $model->getPagination();

		$callback = $this->input->get('callback', '', 'default');

		// Set properties for the template.
		$this->set('category', $category);
		$this->set('type', $type);
		$this->set('layout', $this->getLayout());
		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('direction', $direction);
		$this->set('callback', $callback);
		$this->set('pagination'	, $pagination);
		$this->set('groups', $groups);
		$this->set('search', $search);

		parent::display('admin/groups/default/default');
	}

	/**
	 * Displays a list of pending groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function pending($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_PENDING_GROUPS', 'COM_EASYSOCIAL_DESCRIPTION_PENDING_GROUPS');

		// Display buttons on this page.
		JToolbarHelper::custom('approve', 'publish', 'social-publish-hover', JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'), true);
		JToolbarHelper::custom('reject', 'unpublish', 'social-unpublish-hover', JText::_('COM_EASYSOCIAL_REJECT_BUTTON'), true);
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Gets a list of profiles from the system.
		$model = ES::model('Groups', array('initState' => true, 'namespace' => 'groups.pending'));

		// Get the search query from post
		$search = $this->input->get('search', $model->getState('search'), 'string');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'word');
		$direction = $this->input->get('direction', $model->getState('direction'), 'word');
		$limit = $model->getState('limit');

		// Prepare options
		$groups = $model->getItems(array('pending' => true));
		$pagination = $model->getPagination();

		$callback = $this->input->get('callback', '', 'default');

		// Set properties for the template.
		$this->set('layout', $this->getLayout());
		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('direction', $direction);
		$this->set('callback', $callback);
		$this->set('pagination', $pagination);
		$this->set('groups', $groups);
		$this->set('search', $search);

		echo parent::display('admin/groups/pending/default');
	}

	/**
	 * Displays the category listings form this group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function categories($tpl = null)
	{
		// Set the structure heading here.
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_GROUPS_CATEGORIES', 'COM_EASYSOCIAL_TOOLBAR_TITLE_GROUPS_CATEGORIES_DESC');

		// Add buttons for the groups
		JToolbarHelper::addNew('categoryForm', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::publishList('publishCategory');
		JToolbarHelper::unpublishList('unpublishCategory');
		JToolbarHelper::deleteList('', 'deleteCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Gets a list of profiles from the system.
		$model = ES::model('GroupCategories', array('initState' => true, 'groups.categories'));

		$search = $model->getState('search');
		$order = $model->getState('ordering', 'lft');
		$direction = $model->getState('direction', 'asc');
		$state = $model->getState('state');
		$limit = $model->getState('limit');

		$ordering = array();

		// Prepare options
		$categories	= $model->getItems();
		$pagination	= $model->getPagination();

		foreach ($categories as $category) {
			$ordering[$category->parent_id][] = $category->id;
		}

		// Changing order only allowed when ordered by lft and asc
		$saveOrder = $order == 'lft' && $direction == 'asc';

		$callback = $this->input->get('callback', '', 'default');

		// Set properties for the template.
		$this->set('layout', $this->getLayout());
		$this->set('order', $order);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('direction', $direction);
		$this->set('callback', $callback);
		$this->set('pagination', $pagination);
		$this->set('categories', $categories);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('saveOrder', $saveOrder);

		$this->set('simple', $this->input->getString('tmpl') == 'component');

		parent::display('admin/groups/categories/default');
	}

	/**
	 * Post process after save happens
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function store($task, $group)
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form($group);
		}

		$activeTab = $this->input->get('activeTab', 'profile', 'word');

		if ($task == 'apply' || $task == 'savecopy') {
			return $this->redirect('index.php?option=com_easysocial&view=groups&layout=form&id=' . $group->id . '&activeTab=' . $activeTab);
		}

		if ($task == 'save') {
			return $this->redirect('index.php?option=com_easysocial&view=groups');
		}

		if ($task == 'savenew') {
			$categoryId = $group->category_id;

			return $this->redirect('index.php?option=com_easysocial&view=groups&layout=form&category_id=' . $categoryId);
		}
	}

	/**
	 * Displays the group creation form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form($errors = [])
	{
		// Perhaps this is an edited category
		$id = $this->input->get('id', 0, 'int');

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('savenew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));

		if ($id) {
			JToolbarHelper::save2copy('savecopy', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AS_COPY'));
		}

		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$group = ES::table('Group');
		$group->load($id);

		// Load front end's language file
		ES::language()->loadSite();

		// Get the category
		$categoryId = $this->input->get('category_id', 0, 'int');

		// Default heading and description
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_GROUP', 'COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_GROUP_CATEGORY_DESC');

		// Set the structure heading here.
		if ($group->id) {
			$this->setHeading($group->get('title'), 'COM_EASYSOCIAL_TOOLBAR_TITLE_EDIT_GROUP_DESC');

			$categoryId = $group->category_id;
			$group = ES::group($id);
		} else {
			ES::import('admin:/includes/group/group');
			$group = new SocialGroup();
		}

		$category = ES::table('GroupCategory');
		$category->load($categoryId);

		// Get the steps
		$stepsModel = ES::model('Steps');
		$steps = $stepsModel->getSteps($category->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS);

		// Get the fields
		$lib = ES::fields();
		$fieldsModel = ES::model('Fields');

		$post = $this->input->getArray('post');
		$args = array(&$post, &$group, &$errors);

		$conditionalFields = array();

		foreach ($steps as &$step) {
			if ($group->id) {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $group->id, 'dataType' => SOCIAL_TYPE_GROUP));
			}
			else {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id));
			}

			// @trigger onAdminEdit
			if (!empty($step->fields)) {
				$lib->trigger('onAdminEdit', SOCIAL_FIELDS_GROUP_GROUP, $step->fields, $args);
			}

			foreach ($step->fields as $field) {
				if ($field->isConditional()) {
					$conditionalFields[$field->id] = false;
				}
			}
		}

		if ($conditionalFields) {
			$conditionalFields = json_encode($conditionalFields);
		} else {
			$conditionalFields = false;
		}

		$this->set('conditionalFields', $conditionalFields);
		$this->set('group', $group);
		$this->set('steps', $steps);
		$this->set('category', $category);

		$model = ES::model('GroupMembers', array('initState' => true, 'groups.members'));
		$members = $model->getItems(array('groupid' => $group->id, 'includeBlockUser' => true));

		$pagination = $model->getPagination();

		$this->set('members', $members);
		$this->set('ordering', $model->getState('ordering'));
		$this->set('direction', $model->getState('direction'));
		$this->set('limit', $model->getState('limit'));
		$this->set('search', $model->getState('search'));
		$this->set('pagination', $pagination);

		$activeTab = $this->input->get('activeTab', 'profile', 'word');

		$this->set('activeTab', $activeTab);
		$this->set('isNew', empty($group->id));

		parent::display('admin/groups/form/default');
	}

	/**
	 * Displays the category form for groups
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function categoryForm($tpl = null)
	{
		$id = $this->input->get('id', 0, 'int');

		$category = ES::table('GroupCategory');

		// By default the published state should be published.
		$category->state = SOCIAL_STATE_PUBLISHED;

		// If there's an id, try to load it
		$category->load($id);

		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_GROUP_CATEGORY', 'COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_GROUP_CATEGORY_DESC');

		// Set the structure heading here.
		if ($category->id) {
			$this->setHeading($category->get('title'), 'COM_EASYSOCIAL_TOOLBAR_TITLE_EDIT_GROUP_CATEGORY_DESC');
		}

		// Load front end's language file
		ES::language()->loadSite();

		JToolbarHelper::apply('applyCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('saveCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('saveCategoryNew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));

		if ($id) {
			JToolbarHelper::save2copy('saveCategoryCopy', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AS_COPY'));
		}

		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$activeTab	= $this->input->get('activeTab', 'settings', 'word');

		// Get the creation access
		$createAccess = '';

		if ($category->id) {
			$createAccess = $category->getAccess('create');
		}


		$childTabs = array('files', 'videos', 'photos', 'events', 'points', 'announcements', 'polls', 'tasks', 'discussions', 'files');

		if (in_array($activeTab, $childTabs)) {
			$activeTab = 'access';
		}

		// Set properties for the template.
		$this->set('activeTab', $activeTab);
		$this->set('category', $category);

		if ($category->id) {
			// Render the access form.
			$accessModel = ES::model('Access');
			$accessForm = $accessModel->getForm($category->id, SOCIAL_TYPE_GROUP, 'access');

			$this->set('accessForm', $accessForm);
		}

		$exclusion = [];

		if ($category->id) {
			$exclusion[] = $category->id;
		}

		// We try to get the parent list
		$parentList = ES::populateCategories('parent_id', $category->parent_id, $exclusion, SOCIAL_TYPE_GROUP);
		$this->set('parentList', $parentList);

		// Set the profiles allowed to create groups
		$this->set('createAccess', $createAccess);
		$this->set('clusterType', SOCIAL_TYPE_GROUP);
		$this->set('controller', 'groups');

		parent::display('admin/clusters/category.form/default');
	}

	/**
	 * Post processing after a category is created
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveCategory($category = null)
	{
		$task = $this->input->get('task');

		$activeTab = $this->input->get('activeTab', 'settings', 'word');

		$redirect = 'index.php?option=com_easysocial&view=groups&layout=categories';

		if ($task == 'applyCategory' && !is_null($category)) {
			$redirect = 'index.php?option=com_easysocial&view=groups&layout=categoryForm&id=' . $category->id . '&activeTab=' . $activeTab;
		}

		if ($task == 'saveCategoryNew' || $this->hasErrors()) {
			$redirect = 'index.php?option=com_easysocial&view=groups&layout=categoryForm&activeTab=' . $activeTab;
		}

		return $this->redirect($redirect);
	}


	/**
	 * Post process after switching group owners
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function switchOwner()
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups');
	}

	/**
	 * Post process after groups are rejected
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function reject()
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups&layout=pending');
	}

	/**
	 * Post process after groups are approved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function approve()
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups&layout=pending');
	}

	/**
	 * Post process after a category is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteCategory()
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups&layout=categories');
	}

	/**
	 * Post process after categories has been toggled published.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function togglePublishCategory()
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups&layout=categories');
	}

	/**
	 * Standard redirection to groups page
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToGroupForm($id)
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups&layout=form&activeTab=members&id=' . $id);
	}

	/**
	 * Standard redirection to all groups
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToGroups()
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups');
	}

	/**
	 * Post process after moving groups order
	 *
	 * @since  1.2
	 * @access public
	 */
	public function move($layout = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups&layout=' . $layout);
	}

	/**
	 * Post processing for updating ordering.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveorder()
	{
		return $this->redirect('index.php?option=com_easysocial&view=groups&layout=categories');
	}

	/**
	 * Post action of delete to redirect to group listing.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function delete()
	{
		$this->info->set($this->getMessage());

		$layout = $this->input->get('layout', '', 'string');

		return $this->redirect(ESR::url(array('view' => 'groups', 'layout' => $layout)));
	}
}
