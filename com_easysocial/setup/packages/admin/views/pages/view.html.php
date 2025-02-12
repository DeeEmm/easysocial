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

class EasySocialViewPages extends EasySocialViewWorkflowsAbstract
{
	use EasySocialViewVerificationsTrait;

	protected $workflowType = SOCIAL_TYPE_PAGE;
	protected $verificationType = SOCIAL_TYPE_PAGE;

	/**
	 * Display a list of pages
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function display($tpl=null)
	{
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_PAGES', 'COM_EASYSOCIAL_DESCRIPTION_PAGES');

		// Add a buttons for the pages
		JToolbarHelper::addNew('create', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::custom('makeFeatured', '', '', JText::_('COM_ES_FEATURE'));
		JToolbarHelper::custom('removeFeatured', '', '', Jtext::_('COM_ES_UNFEATURE'));
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Get the page model
		$model = ES::model('Pages', array('initState' => true, 'namespace' => 'pages.listing'));

		// Get the search query if any
		$search = $model->getState('search');

		// Get the current ordering
		$ordering = $this->input->get('ordering', $model->getState('ordering'));
		$direction = $this->input->get('direction', $model->getState('direction'));
		$state = $this->input->get('state', $model->getState('state'));
		$type = $this->input->get('type', $model->getState('type'), 'int');
		$limit = $this->input->get('limit', $model->getState('limit'));
		$category = $this->input->get('category', $model->getState('category'), 'int');

		//Load the frontend language
		ES::language()->loadSite();

		// Prepare the options
		$pages = $model->getItemsWithState();
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
		$this->set('pages', $pages);
		$this->set('search', $search);

		parent::display('admin/pages/default/default');

	}

	/**
	 * Display categories listing for pages
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function categories($tpl = null)
	{
		// Set the heading here
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_PAGES_CATEGORIES', 'COM_EASYSOCIAL_TOOLBAR_TITLE_PAGES_CATEGORIES_DESC');

		// Add buttons for pages
		JToolbarHelper::addNew('categoryForm', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::divider();
		JToolbarHelper::publishList('publishCategory');
		JToolbarHelper::unpublishList('unpublishCategory');
		JToolbarHelper::divider();
		JToolbarHelper::deleteList('', 'deleteCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Get the list of categories
		$model = ES::model('PageCategories', array('initState' => true, 'namespace' => 'pages.categories'));

		$search = $model->getState('search');
		$order = $model->getState('ordering', 'lft');
		$direction = $model->getState('direction', 'asc');
		$state = $model->getState('state');
		$limit = $model->getState('limit');

		$ordering = array();

		// Prepare the options
		$categories = $model->getItems();
		$pagination = $model->getPagination();

		foreach ($categories as $category) {
			$ordering[$category->parent_id][] = $category->id;
		}

		$callback = $this->input->get('callback', '', 'default');

		// Save ordering
		$saveOrder = $order == 'lft' && $direction == 'asc';

		// Set properties for the template
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

		parent::display('admin/pages/categories/default');
	}

	/**
	 * Display category form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function categoryForm($tpl = null)
	{
		// Maybe this is editing category
		$id = $this->input->get('id', 0, 'int');

		$category = ES::table('PageCategory');

		// By default we set it to published
		$category->state = SOCIAL_STATE_PUBLISHED;

		// If there is an id, we load it.
		$category->load($id);

		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_PAGE_CATEGORY', 'COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_PAGE_CATEGORY_DESC');

		// if this is editing category, set the heading
		if ($category->id) {
			$this->setHeading($category->get('title'), 'COM_EASYSOCIAL_TOOLBAR_TITLE_EDIT_PAGE_CATEGORY_DESC');
		}

		// Load the frontend language file
		ES::language()->loadSite();

		JToolbarHelper::apply('applyCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('saveCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('saveCategoryNew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));

		if ($id) {
			JToolbarHelper::save2copy('saveCategoryCopy', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AS_COPY'));
		}

		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$activeTab = $this->input->get('activeTab', 'settings', 'word');
		$createAccess = '';

		if ($category->id) {
			$createAccess = $category->getAccess('create');
		}

		$childTabs = array('files', 'videos', 'photos', 'events', 'points', 'announcements', 'polls', 'tasks', 'discussions', 'files');

		if (in_array($activeTab, $childTabs)) {
			$activeTab = 'access';
		}

		// Set the properties for the template
		$this->set('activeTab', $activeTab);
		$this->set('category', $category);

		if ($category->id) {

			$accessModel = ES::model('Access');
			$accessForm = $accessModel->getForm($category->id, SOCIAL_TYPE_PAGE, 'access');

			$this->set('accessForm', $accessForm);
		}

		$exclusion = [];

		if ($category->id) {
			$exclusion[] = $category->id;
		}

		// We try to get the parent list
		$parentList = ES::populateCategories('parent_id', $category->parent_id, $exclusion, SOCIAL_TYPE_PAGE);
		$this->set('parentList', $parentList);

		$this->set('createAccess', $createAccess);
		$this->set('clusterType', SOCIAL_TYPE_PAGE);
		$this->set('controller', 'pages');

		parent::display('admin/clusters/category.form/default');
	}

	/**
	 * Displays the page creation form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form($errors = array())
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

		$page = ES::table('Page');
		$page->load($id);

		// Load front end's language file
		ES::language()->loadSite();

		// Get the category
		$categoryId = $this->input->get('category_id', 0, 'int');

		// Default heading and description
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_PAGE', 'COM_EASYSOCIAL_TOOLBAR_TITLE_CREATE_PAGE_CATEGORY_DESC');

		// Set the structure heading here.
		if ($page->id) {
			$this->setHeading($page->get('title'), 'COM_EASYSOCIAL_TOOLBAR_TITLE_EDIT_PAGE_DESC');

			$categoryId = $page->category_id;
			$page = ES::page($id);
		} else {
			ES::import('admin:/includes/page/page');
			$page = new SocialPage();
		}

		$category = ES::table('PageCategory');
		$category->load($categoryId);

		// Get the steps
		$stepsModel = ES::model('Steps');
		$steps = $stepsModel->getSteps($category->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS);

		// Get the fields
		$lib = ES::fields();
		$fieldsModel = ES::model('Fields');

		$post = $this->input->getArray('post');
		$args = array(&$post, &$page, &$errors);

		$conditionalFields = array();

		foreach ($steps as &$step) {
			if ($page->id) {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $page->id, 'dataType' => SOCIAL_TYPE_PAGE));
			}
			else {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id));
			}

			// @trigger onAdminEdit
			if (!empty($step->fields)) {
				$lib->trigger('onAdminEdit', SOCIAL_FIELDS_GROUP_PAGE, $step->fields, $args);
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
		$this->set('page', $page);
		$this->set('steps', $steps);
		$this->set('category', $category);

		$model = ES::model('PageMembers', array('initState' => true, 'namespace' => 'pages.members'));
		$followers = $model->getItems(array('pageid' => $page->id, 'includeBlockUser' => true));

		$pagination = $model->getPagination();

		$this->set('followers', $followers);
		$this->set('ordering', $model->getState('ordering'));
		$this->set('direction', $model->getState('direction'));
		$this->set('limit', $model->getState('limit'));
		$this->set('pagination', $pagination);

		$activeTab = $this->input->get('activeTab', 'profile', 'word');

		$this->set('activeTab', $activeTab);
		$this->set('isNew', empty($page->id));

		parent::display('admin/pages/form/default');
	}

	/**
	 * Display a list of pending pages
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function pending()
	{
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_PENDING_PAGES', 'COM_EASYSOCIAL_DESCRIPTION_PENDING_PAGES');

		// Display buttons on this page.
		JToolbarHelper::custom('approve', 'publish', 'social-publish-hover', JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'), true);
		JToolbarHelper::custom('reject', 'unpublish', 'social-unpublish-hover', JText::_('COM_EASYSOCIAL_REJECT_BUTTON'), true);
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Gets a list of profiles from the system.
		$model = ES::model('Pages', array('initState' => true, 'namespace' => 'pages.pending'));

		// Get the search query from post
		$search = $this->input->get('search', $model->getState('search'), 'string');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'word');
		$direction = $this->input->get('direction', $model->getState('direction'), 'word');
		$limit = $model->getState('limit');

		// Prepare options
		$pages = $model->getItems(array('pending' => true));
		$pagination = $model->getPagination();

		$callback = $this->input->get('callback', '', 'default');

		// Set properties for the template.
		$this->set('layout', $this->getLayout());
		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('direction', $direction);
		$this->set('callback', $callback);
		$this->set('pagination', $pagination);
		$this->set('pages', $pages);
		$this->set('search', $search);

		parent::display('admin/pages/pending/default');
	}

	/**
	 * Gets triggered when the save & close button is clicked.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function store($task, $page)
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form($page);
		}

		$activeTab = $this->input->get('activeTab', 'profile', 'word');

		if ($task == 'apply' || $task == 'savecopy') {
			return $this->redirect('index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id . '&activeTab=' . $activeTab);
		}

		if ($task == 'save') {
			return $this->redirect('index.php?option=com_easysocial&view=pages');
		}

		if ($task == 'savenew') {

			// Get the current page category
			$categoryId 	= $page->category_id;

			return $this->redirect('index.php?option=com_easysocial&view=pages&layout=form&category_id=' . $categoryId);
		}
	}

	/**
	 * Post processing after a category is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveCategory($category = null)
	{
		$task = $this->input->get('task', '', 'cmd');
		$activeTab = $this->input->get('activeTab', '', 'cmd');

		$redirect = 'index.php?option=com_easysocial&view=pages&layout=categories';

		if ($task == 'applyCategory' && !is_null($category)) {
			$redirect = 'index.php?option=com_easysocial&view=pages&layout=categoryForm&id=' . $category->id . '&activeTab=' . $activeTab;
		}

		if ($task == 'saveCategoryNew' || $this->hasErrors()) {
			$redirect = 'index.php?option=com_easysocial&view=pages&layout=categoryForm&activeTab=' .  $activeTab;
		}

		return $this->redirect($redirect);
	}

	/**
	 * Standard redirection to pages listing
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToPages()
	{
		return $this->redirect('index.php?option=com_easysocial&view=pages');
	}

	/**
	 * Standard redirection to page categories
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToPageCategories()
	{
		return $this->redirect('index.php?option=com_easysocial&view=pages&layout=categories');
	}

	/**
	 * Standard redirection to page form
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToPageForm($pageId)
	{
		return $this->redirect('index.php?option=com_easysocial&view=pages&layout=form&activeTab=followers&layout=form&id=' . $pageId);
	}

	/**
	 * Standard redirection to pending pages
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirectToPending()
	{
		return $this->redirect('index.php?option=com_easysocial&view=pages&layout=pending');
	}

	/**
	 * Post process after moving pages order
	 *
	 * @since  2.0
	 * @access public
	 */
	public function move($layout = null)
	{
		$this->redirect('index.php?option=com_easysocial&view=pages&layout=' . $layout);
	}

	/**
	 * Post action of delete to redirect to page listing.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function delete()
	{
		$this->info->set($this->getMessage());

		$layout = $this->input->get('layout', '', 'string');

		return $this->redirect(ESR::url(array('view' => 'pages', 'layout' => $layout)));
	}
}
