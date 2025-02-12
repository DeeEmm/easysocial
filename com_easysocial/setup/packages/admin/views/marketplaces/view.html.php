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

class EasySocialViewMarketplaces extends EasySocialViewWorkflowsAbstract
{
	protected $workflowType = SOCIAL_TYPE_MARKETPLACE;

	/**
	 * Renders the list of items
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_TOOLBAR_TITLE_MARKETPLACES', 'COM_ES_DESCRIPTION_MARKETPLACES');

		JToolbarHelper::addNew('create', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::custom('setFeatured', '', '', JText::_('COM_ES_FEATURE'));
		JToolbarHelper::custom('removeFeatured', '', '', JText::_('COM_ES_UNFEATURE'));
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Gets a list of items from the system.
		$model = ES::model('marketplaces', array('initState' => true, 'namespace' => 'marketplaces.listing'));

		$state = $model->getState('state');
		$limit = $model->getState('limit');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$search = $model->getState('search');

		// Load front end language file
		ES::language()->loadSite();

		if ($state != 'all') {
			$state = (int) $state;
		}

		// Load a list of extensions so that users can filter them.
		$items = $model->getItems();

		$pagination	= $model->getPagination();

		if ($this->input->getString('tmpl') == 'component') {
			$pagination->setVar('tmpl', 'component');
		}

		$this->set('direction', $direction);
		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('state', $state);
		$this->set('search', $search);
		$this->set('items', $items);
		$this->set('pagination', $pagination);
		$this->set('simple', $this->input->getString('tmpl') == 'component');

		parent::display('admin/marketplaces/default/default');
	}

	/**
	 * Displays a list of pending listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function pending($tpl = null)
	{
		$this->setHeading('COM_ES_TOOLBAR_TITLE_PENDING_MARKETPLACES', 'COM_ES_DESCRIPTION_PENDING_MARKETPLACES');

		JToolbarHelper::custom('approve', 'publish', 'social-publish-hover', JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'), true);
		JToolbarHelper::custom('reject', 'unpublish', 'social-unpublish-hover', JText::_('COM_EASYSOCIAL_REJECT_BUTTON'), true);
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		$model = ES::model('Marketplaces', array('initState' => true, 'namespace' => 'marketplaces.pending'));

		$model->setState('state', SOCIAL_MARKETPLACE_PENDING);

		$listings = $model->getItems();

		$pagination = $model->getPagination();

		$this->set('listings', $listings);
		$this->set('pagination', $pagination);

		$search = $model->getState('search');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$state = $model->getState('state');
		$type = $model->getState('type');
		$limit = $model->getState('limit');

		$callback = $this->input->get('callback', '', 'default');

		$this->set('callback', $callback);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('type', $type);
		$this->set('limit', $limit);

		echo parent::display('admin/marketplaces/pending/default');
	}

	/**
	 * Displays the category listings
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function categories($tpl = null)
	{
		// Set the structure heading here.
		$this->setHeading('COM_ES_TOOLBAR_TITLE_MARKETPLACES_CATEGORIES', 'COM_ES_TOOLBAR_TITLE_MARKETPLACES_CATEGORIES_DESC');

		// Add buttons for the groups
		JToolbarHelper::addNew('categoryForm', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::publishList('publishCategory');
		JToolbarHelper::unpublishList('unpublishCategory');
		JToolbarHelper::deleteList('', 'deleteCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		// Gets a list of profiles from the system.
		$model = ES::model('MarketplaceCategories', array('initState' => true, 'marketplaces.categories'));

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

		parent::display('admin/marketplaces/categories/default');
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

		$category = ES::table('MarketplaceCategory');

		// By default the published state should be published.
		$category->state = SOCIAL_STATE_PUBLISHED;

		// If there's an id, try to load it
		$category->load($id);

		$this->setHeading('COM_ES_TOOLBAR_TITLE_CREATE_MARKETPLACE_CATEGORY', 'COM_ES_TOOLBAR_TITLE_CREATE_MARKETPLACE_CATEGORY_DESC');

		// Set the structure heading here.
		if ($category->id) {
			$this->setHeading($category->get('title'), 'COM_ES_TOOLBAR_TITLE_EDIT_MARKETPLACE_CATEGORY_DESC');
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

		$this->set('category', $category);

		$exclusion = [];

		if ($category->id) {
			$exclusion[] = $category->id;
		}

		// We try to get the parent list
		$parentList = ES::populateCategories('parent_id', $category->parent_id, $exclusion, 'marketplace');
		$this->set('parentList', $parentList);

		parent::display('admin/marketplaces/category.form/default');
	}

	/**
	 * Displays the item creation form
	 *
	 * @since	3.3
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

		$item = ES::marketplace($id);

		// Load front end's language file
		ES::language()->loadSite();

		// Get the category
		$categoryId = $this->input->get('category_id', 0, 'int');

		// Default heading and description
		$this->setHeading('COM_ES_TOOLBAR_TITLE_CREATE_MARKETPLACE', 'COM_ES_TOOLBAR_TITLE_CREATE_MARKETPLACE_DESC');

		// Set the structure heading here.
		if ($item->id) {
			$this->setHeading($item->getTitle(), 'COM_ES_TOOLBAR_TITLE_EDIT_MARKETPLACE_DESC');

			$categoryId = $item->category_id;
		}

		$category = ES::table('MarketplaceCategory');
		$category->load($categoryId);

		// Get the steps
		$stepsModel = ES::model('Steps');
		$steps = $stepsModel->getSteps($category->getWorkflow()->id, SOCIAL_TYPE_MARKETPLACES);

		// Get the fields
		$lib = ES::fields();
		$fieldsModel = ES::model('Fields');

		$post = $this->input->getArray('post');
		$args = array(&$post, &$item, &$errors);

		$conditionalFields = array();

		foreach ($steps as &$step) {
			if ($item->id) {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $item->id, 'dataType' => 'marketplace'));
			}
			else {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id));
			}

			// @trigger onAdminEdit
			if (!empty($step->fields)) {
				$lib->trigger('onAdminEdit', SOCIAL_FIELDS_GROUP_MARKETPLACE, $step->fields, $args);
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
		$this->set('item', $item);
		$this->set('steps', $steps);
		$this->set('category', $category);

		$activeTab = $this->input->get('activeTab', 'profile', 'word');

		$this->set('activeTab', $activeTab);
		$this->set('isNew', empty($item->id));

		parent::display('admin/marketplaces/form/default');
	}

	/**
	 * Post process after save happens
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function store($task, $listing)
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form($listing);
		}

		$activeTab = $this->input->get('activeTab', 'profile', 'word');

		if ($task == 'apply' || $task == 'savecopy') {
			return $this->redirect('index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id . '&activeTab=' . $activeTab);
		}

		if ($task == 'save') {
			return $this->redirect('index.php?option=com_easysocial&view=marketplaces');
		}

		if ($task == 'savenew') {
			$categoryId = $listing->category_id;

			return $this->redirect('index.php?option=com_easysocial&view=marketplaces&layout=form&category_id=' . $categoryId);
		}
	}

	/**
	 * Post processing after a category is created
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function saveCategory($category = null)
	{
		$task = $this->input->get('task');

		$activeTab = $this->input->get('activeTab', 'settings', 'word');

		$redirect = 'index.php?option=com_easysocial&view=marketplaces&layout=categories';

		if ($task == 'applyCategory' && !is_null($category)) {
			$redirect = 'index.php?option=com_easysocial&view=marketplaces&layout=categoryForm&id=' . $category->id . '&activeTab=' . $activeTab;
		}

		if ($task == 'saveCategoryNew' || $this->hasErrors()) {
			$redirect = 'index.php?option=com_easysocial&view=marketplaces&layout=categoryForm&activeTab=' . $activeTab;
		}

		return $this->redirect($redirect);
	}

	/**
	 * Post action of delete to redirect to marketplace listing.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function delete()
	{
		$this->info->set($this->getMessage());

		$layout = $this->input->get('layout', '', 'string');

		return $this->redirect(ESR::url(array('view' => 'marketplaces', 'layout' => $layout)));
	}


	/**
	 * Standard redirection to all marketplaces
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function redirectToMarketplaces()
	{
		return $this->redirect('index.php?option=com_easysocial&view=marketplaces');
	}

	/**
	 * Post process after categories has been toggled published.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function redirectToCategories()
	{
		return $this->redirect('index.php?option=com_easysocial&view=marketplaces&layout=categories');
	}

	/**
	 * Post process after moving item order
	 *
	 * @since  4.0
	 * @access public
	 */
	public function move($layout = null)
	{
		$this->redirect('index.php?option=com_easysocial&view=marketplaces&layout=' . $layout);
	}

	/**
	 * Standard redirection to pending listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function redirectToPending()
	{
		return $this->redirect('index.php?option=com_easysocial&view=marketplaces&layout=pending');
	}

	/**
	 * Post processing for updating ordering.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveorder()
	{
		return $this->redirect('index.php?option=com_easysocial&view=marketplaces&layout=categories');
	}
}
