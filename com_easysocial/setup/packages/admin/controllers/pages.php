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

class EasySocialControllerPages extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		// Map the alias methods here.
		$this->registerTask('unpublishCategory', 'togglePublishCategory');
		$this->registerTask('publishCategory', 'togglePublishCategory');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('applyCategory', 'saveCategory');
		$this->registerTask('saveCategoryNew', 'saveCategory');
		$this->registerTask('saveCategory', 'saveCategory');
		$this->registerTask('saveCategoryCopy', 'saveCategory');

		$this->registerTask('apply', 'store');
		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('savecopy', 'store');

		$this->registerTask('makeFeatured', 'toggleDefault');
		$this->registerTask('removeFeatured', 'toggleDefault');

		$this->registerTask('setVerified', 'setVerifyAction');
		$this->registerTask('removeVerified', 'setVerifyAction');
	}

	/**
	 * Save a page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function store()
	{
		ES::checkToken();

		// Load front end's language file
		ES::language()->loadSite();

		$task = $this->getTask();
		$id = $this->input->get('id', 0, 'int');
		$cid = $id;

		// Add a flag if this is being edited or not
		$isNew = empty($id);

		$isCopy = $task == 'savecopy' ? true : false;

		// Get the posted data
		$post = $this->input->getArray('post');
		$options = array();

		if ($isNew || $isCopy) {
			// Include the page library
			ES::import('admin:/includes/page/page');

			$page = new SocialPage();
			$categoryId = $this->input->get('category_id', 0, 'int');

			if ($isCopy) {
				$cpage = ES::page($id);
				$categoryId = $cpage->category_id;

				$post['id'] = 0;
			}
		} else {
			$page = ES::page($id);

			$options['data'] = true;
			$options['dataId'] = $page->id;
			$options['dataType'] = SOCIAL_FIELDS_GROUP_PAGE;
			$categoryId = $page->category_id;
		}

		$category = ES::table('PageCategory');
		$category->load($categoryId);

		// Set the necessary data
		// $options['uid'] = $categoryId;
		$options['workflow_id'] = $category->getWorkflow()->id;
		$options['group'] = SOCIAL_FIELDS_GROUP_PAGE;

		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields($options);

		$registry = ES::registry();
		$disallowed = array(ES::token(), 'option', 'task', 'controller', 'autoapproval');

		// Process the $_POST
		foreach ($post as $key => $value) {
			if (!in_array($key, $disallowed)) {

				if (is_array($value)) {
					$value = json_encode($value);
				}

				$registry->set($key, $value);
			}
		}

		// Convert the value into array
		$data = $registry->toArray();

		$fieldsLib = ES::fields();

		// Get the general field trigger handler
		$handler = $fieldsLib->getHandler();

		// Build arguments to be passed to the field apps.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$page, &$isCopy);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args, array($handler));

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$page, &$isCopy);

		// @trigger onAdminEditValidate
		$errors = $fieldsLib->trigger('onAdminEditValidate', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// If got error, exit here
		if (is_array($errors) && count($errors) > 0) {
			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			$this->view->setMessage('COM_EASYSOCIAL_PAGES_FORM_SAVE_ERRORS', ES_ERROR);

			return $this->view->call('form', $errors);
		}

		// @trigger onAdminEditBeforeSave
		$errors = $fieldsLib->trigger('onAdminEditBeforeSave', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			$this->view->setMessage('COM_EASYSOCIAL_PAGES_FORM_SAVE_ERRORS', ES_ERROR);

			return $this->view->call('form', $errors);
		}

		// Initialize page data for the new page
		if ($isNew || $isCopy) {
			$page->category_id = $categoryId;
			$page->creator_uid = $this->my->id;
			$page->creator_type = SOCIAL_TYPE_USER;
			$page->state = SOCIAL_STATE_PUBLISHED;
			$page->hits = 0;

			// Generate a uniqu key for this page which serves as password
			$page->key = md5(ES::date()->toSql() . $this->my->password . uniqid());
		}

		// If there is still no alias generated, we need to automatically build one for the page
		if (!$page->alias) {
			$model = ES::model('Pages');
			$page->alias = $model->getUniqueAlias($page->getName());
		}

		$page->bind($data);
		$page->save();

		// After the page is created, assign the current user as the node item
		if ($isNew || $isCopy) {
			ES::access()->log('pages.limit', $this->my->id, $page->id, SOCIAL_TYPE_PAGE);

			$page->createOwner($this->my->id);
		}

		 // Reconstruct args
		$args = array(&$data, &$page);
		$fieldsLib->trigger('onAdminEditAfterSave', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// Bind the custom fields for the page.
		$page->bindCustomFields($data);

		// Reconstruct args
		$args = array(&$data, &$page);
		$fieldsLib->trigger('onAdminEditAfterSaveFields', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// now we need to copy the avatar and cover if there are any.
		if ($isCopy) {
			$page->copyAvatar($cid);
			$page->copyCover($cid);
		}

		$message = 'COM_EASYSOCIAL_PAGES_FORM_CREATE_SUCCESS';
		$actionString = 'COM_ES_ACTION_LOG_PAGE_CREATED';

		if ($isCopy) {
			$message = 'COM_EASYSOCIAL_PAGES_FORM_COPIED_SUCCESS';
			$actionString = 'COM_ES_ACTION_LOG_PAGE_COPIED';
		} else if ($id) {
			$message = 'COM_EASYSOCIAL_PAGES_FORM_SAVE_UPDATE_SUCCESS';
			$actionString = 'COM_ES_ACTION_LOG_PAGE_UPDATED';
		}

		$this->actionlog->log($actionString, 'pages', [
				'name' => $page->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id
			]);

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task, $page);
	}

	/**
	 * Remove category avatar
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function removeCategoryAvatar()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$category = ES::table('PageCategory');
		$category->load($id);

		$category->removeAvatar();
	}

	/**
	 * Save a page category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function saveCategory()
	{
		ES::checkToken();

		// Get the posted data
		$post = $this->input->getArray('post');

		// Get the id
		$id = $this->input->get('id', 0, 'int');
		$cid = $this->input->get('cid', 0, 'int');

		// Get the current task
		$task = $this->getTask();
		$isCopy = $task == 'saveCategoryCopy' ? true : false;

		// Assign original parent_id to tmp variable
		$oriParentId = $this->input->get('oriParentId', 0);

		// Unset from post array
		unset($post['oriParentId']);

		// Category title is compulsory
		if (empty($post['title'])) {
			$this->view->setMessage('COM_ES_CLUSTER_CATEGORY_TITLE_MISSING', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try load the category
		$category = ES::table('PageCategory');

		if ($cid && $isCopy) {
			$category->load($cid);

			//reset the id
			$post['id'] = $cid;
		} else {
			$category->load($id);
		}

		$category->bind($post);

		$workflowId = $this->input->get('workflow_id');

		// There workflow must be selected in order to proceed
		if (!$workflowId) {
			$this->view->setMessage('COM_ES_WORKFLOW_NOT_SELECTED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $category);
		}

		// Try store the category
		$state = $category->store();

		// Bind the creation access
		if ($state) {
			$categoryAccess = $this->input->get('create_access', '', 'default');
			$category->bindCategoryAccess('create', $categoryAccess);
		}

		// Check if the parent id has changed
		if ($oriParentId != $category->parent_id) {
			// We need to recalculate the lft column
			$category->updateLftValue($category->parent_id);
		}

		// Re-arrange lft and rgt column and ordering
		$category->rebuildOrdering();

		// Update the ordering
		$category->updateOrdering();

		// Store the avatar
		$file = $this->input->files->get('avatar', '');

		// Try to upload the avatar
		if (!empty($file['tmp_name'])) {
			$category->uploadAvatar($file);
		}

		// If this is a copy, copy over the avatar
		if ($isCopy) {
			$category->copyAvatar($id);
		}

		$category->assignWorkflow($workflowId);

		// Bind the access
		if (isset($post['access']) && !empty($post['access'])) {
			$category->bindAccess($post['access']);
		}

		$actionString = 'COM_ES_ACTION_LOG_PAGE_CATEGORY_CREATED';

		if ($id) {
			$actionString = 'COM_ES_ACTION_LOG_PAGE_CATEGORY_UPDATED';
		}

		if ($isCopy) {
			$actionString = 'COM_ES_ACTION_LOG_PAGE_CATEGORY_COPIED';
		}

		$this->actionlog->log($actionString, 'pages', [
				'name' => $category->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=pages&layout=categoryForm&id=' . $category->id
			]);

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_CATEGORY_SAVED_SUCCESS');
		return $this->view->call(__FUNCTION__, $category);
	}

	/**
	 * Create blank category.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function createBlankCategory()
	{
		// Check for request forgeries.
		ES::checkToken();

		// Create the new category
		$newCategory = ES::table('PageCategory');
		$newCategory->title = 'temp';
		$newCategory->createBlank(SOCIAL_TYPE_PAGE);

		$id = $newCategory->id;

		$this->view->call(__FUNCTION__, $id);
	}

	/**
	 * Delete a page category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteCategory()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_CATEGORY_DELETED_FAILED');
		}

		foreach($ids as $id) {
			$id = (int) $id;
			$category = ES::table('PageCategory');
			$category->load($id);

			$total = $category->getTotalPages();
			$title = $category->getTitle();

			// Do not allow deletion of category if there are pages in the category
			if ($total) {
				$this->view->setMessage('COM_EASYSOCIAL_CATEGORIES_DELETE_ERROR_PAGE_NOT_EMPTY', ES_ERROR);
				return $this->view->call('redirectToPageCategories');
			}

			$state = $category->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_CATEGORY_DELETED', 'pages', [
					'name' => $title
				]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_CATEGORY_DELETED_SUCCESS');
		return $this->view->call('redirectToPageCategories');
	}

	/**
	 * Publish/Unpublish Page category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function togglePublishCategory()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$message = 'COM_EASYSOCIAL_PAGES_CATEGORY_UNPUBLISHED_SUCCESS';
		$actionString = 'COM_ES_ACTION_LOG_PAGE_CATEGORY_UNPUBLISHED';
		$task = 'unpublish';

		if ($this->getTask() == 'publishCategory') {
			$task = 'publish';
			$message = 'COM_EASYSOCIAL_PAGES_CATEGORY_PUBLISHED_SUCCESS';
			$actionString = 'COM_ES_ACTION_LOG_PAGE_CATEGORY_PUBLISHED';
		}

		foreach ($ids as $id) {
			$category = ES::table('ClusterCategory');
			$category->load((int) $id);

			$category->$task();

			$this->actionlog->log($actionString, 'pages', [
						'name' => $category->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=pages&layout=categoryForm&id=' . $category->id
					]);
		}

		return $this->view->call('redirectToPageCategories');
	}

	 /**
	 * Add followers into this page
	 *
	 * @since  2.0
	 * @access public
	 */
	public function addMembers()
	{
		$pageId = $this->input->get('id', 0, 'int');
		$userIds = $this->input->get('followers', '', 'string');
		$userIds = json_decode($userIds);

		$count = 0;
		$exists = array();

		foreach ($userIds as $id) {

			$follower = ES::table('PageMember');
			$state = $follower->load(array('uid' => $id, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $pageId));

			if ($state) {
				$exists[] = $id;
				continue;
			}

			// Admin adding followers shouldn't worry about pending state. It should all go through regardless of the page openness.
			$follower->cluster_id = $pageId;
			$follower->uid = $id;
			$follower->type = SOCIAL_TYPE_USER;
			$follower->created = ES::date()->toSql();
			$follower->state = SOCIAL_STATE_PUBLISHED;
			$follower->owner = 0;
			$follower->admin = 0;
			$follower->invited_by = 0;
			$follower->store();

			$count++;

			$page = ES::page($pageId);
			$user = ES::user($id);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_FOLLOWER_ADDED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);
		}

		$msgType = SOCIAL_MSG_SUCCESS;
		$message = JText::sprintf('COM_EASYSOCIAL_PAGES_ADD_FOLLOWERS_SUCCESS', $count);
		if ($exists) {
			if ($count) {
				$message = JText::sprintf('COM_ES_PAGES_ADD_FOLLOWERS_SUCCESS_WITH_WARNING', $count);
			} else {
				$message = JText::_('COM_ES_PAGES_ADD_FOLLOWERS_ALREADT_EXISTS');
				$msgType = SOCIAL_MSG_WARNING;
			}
		}

		$this->view->setMessage($message, $msgType);
		return $this->view->call('redirectToPageForm', $pageId);
	}

	/**
	 * Allows caller to reject a page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function reject()
	{
		ES::checkToken();

		$ids = $this->input->get('id', array(), 'int');
		$email = $this->input->get('email', '', 'default');
		$delete = $this->input->get('delete', false, 'bool');
		$reason = $this->input->get('reason', '', 'default');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$page = ES::page((int) $id);
			$page->reject($reason, $email, $delete);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_REJECTED', 'pages', [
						'name' => $page->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id
					]);
		}

		$this->view->setMessage('Page has been rejected successfully.');
		return $this->view->call('redirectToPending');
	}

	/**
	 * Method to update categories ordering
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function saveorder()
	{
		ES::checkToken();

		$cid = $this->input->get('cid', array(), 'array');

		if (!$cid) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_ORDERING_NO_ITEMS');
		}

		$model = ES::model('ClusterCategory');

		$i = 1;

		foreach ($cid as $id) {
			$model->updateCategoriesOrdering($id, $i);
			$i++;
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_ORDERING_UPDATED');
		return $this->view->call('redirectToPageCategories');
	}

	/**
	 * Allows caller to approve a page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function approve()
	{
		ES::checkToken();

		$ids = $this->input->get('id', [], 'array');
		$email = $this->input->get('email', '', 'default');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$page = ES::page((int) $id);
			$page->approve($email);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_APPROVED', 'pages', [
						'name' => $page->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id
					]);
		}

		$this->view->setMessage('Page has been approved successfully.');
		return $this->view->call('redirectToPending');
	}

	/**
	 * Toggles publishing state for pages
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$task = $this->getTask();

		$indexer = ES::get('Indexer');
		$toggleValue = $task === 'publish' ? SOCIAL_CLUSTER_PUBLISHED : SOCIAL_CLUSTER_UNPUBLISHED;
		$actionString = $task === 'publish' ? 'COM_ES_ACTION_LOG_PAGE_PUBLISHED' : 'COM_ES_ACTION_LOG_PAGE_UNPUBLISHED';

		foreach ($ids as $id) {
			$page = ES::page($id);
			$state = $page->$task();

			if ($state) {
				// need to update from the indexed item as well
				$indexer->itemStateChange('easysocial.pages', $id, $toggleValue);

				$this->actionlog->log($actionString, 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id
				]);
			}
		}

		$message = 'COM_EASYSOCIAL_PAGES_PUBLISHED_SUCCESS';

		if ($task == 'unpublish') {
			$message = 'COM_EASYSOCIAL_PAGES_UNPUBLISHED_SUCCESS';
		}

		$this->view->setMessage($message);
		return $this->view->call('redirectToPages');
	}

	/**
	 * Allows admin to toggle featured pages
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function toggleDefault()
	{
		ES::checkToken();


		$task = $this->getTask();
		$message = 'COM_EASYSOCIAL_PAGES_SET_FEATURED_SUCCESSFULLY';
		$actionString = 'COM_ES_ACTION_LOG_PAGE_FEATURED';

		// Get the page object
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			// Load the page
			$page = ES::page($id);

			if ($task == 'toggleDefault') {

				if ($page->featured) {
					$page->removeFeatured();
					$message = 'COM_EASYSOCIAL_PAGES_REMOVED_FEATURED_SUCCESSFULLY';
					$actionString = 'COM_ES_ACTION_LOG_PAGE_UNFEATURED';
				}

				if (!$page->featured) {
					$page->setFeatured();
				}
			}

			if ($task == 'makeFeatured') {
				$page->setFeatured();
			}

			if ($task == 'removeFeatured') {
				$page->removeFeatured();
				$message = 'COM_EASYSOCIAL_PAGES_REMOVED_FEATURED_SUCCESSFULLY';
				$actionString = 'COM_ES_ACTION_LOG_PAGE_UNFEATURED';
			}

			$this->actionlog->log($actionString, 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id
				]);
		}

		$this->view->setMessage($message);
		return $this->view->call('redirectToPages');
	}

	/**
	 * Allows caller to change a page owner
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function switchOwner()
	{
		ES::checkToken();

		$ids = $this->input->get('ids', array(), 'int');
		$userId = $this->input->get('userId', 0, 'int');
		$adminRights = $this->input->get('adminRights', '', 'default');

		if (!$ids || !$userId) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$page = ES::page((int) $id);

			ES::access()->switchLogAuthor('pages.limit', $page->getCreator()->id, $page->id, SOCIAL_TYPE_PAGE, $userId);

			$page->switchOwner($userId, $adminRights);

			$user = ES::user($userId);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_OWNER_SWITCHED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id,
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_PAGE_OWNER_UPDATED_SUCCESS');
		return $this->view->call('redirectToPages');
	}

	/**
	 * Deletes a list of pages from the site
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_DELETE_FAILED');
		}

		foreach ($ids as $id) {
			$page = ES::page((int) $id);
			$title = $page->getTitle();

			$page->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_DELETED', 'pages', [
					'name' => $title
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_DELETED_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows admin to switch a page's category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function switchCategory()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$categoryId = $this->input->getInt('category');

		if (!$ids) {
			$this->view->setMessage('COM_ES_NO_ITEMS_SELECTED', ES_ERROR);
			return $this->view->setRedirection('index.php?option=com_easysocial&view=pages');
		}

		$category = ES::table('ClusterCategory');
		$category->load($categoryId);

		if (!$category->id) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_CATEGORIES_INVALID_IDS');
		}

		$categoryModel = ES::model('PageCategories');

		foreach ($ids as $id) {
			$categoryModel->updatePageCategory($id, $categoryId);

			$page = ES::page($id);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_CATEGORY_SWITCHED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'catName' => $category->getTitle(),
					'catLink' => 'index.php?option=com_easysocial&view=pages&layout=categoryForm&id=' . $category->id,
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_SWITCH_CATEGORY_SUCCESSFUL');
		return $this->view->call('redirectToPages');
	}

	/**
	 * Publish a page member
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function publishUser()
	{
		ES::checkToken();

		$pageId = $this->input->get('id', 0, 'int');
		$cids = $this->input->get('cid', array(), 'int');

		if (!$cids) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_PUBLISH_FOLLOWERS_FAILED');
		}

		$page = ES::page($pageId);

		foreach ($cids as $cid) {
			$node = ES::table('PageMember');
			$node->load((int) $cid);

			if ($node->state == 1) {
				continue;
			}

			$node->state = 1;

			if (!$node->store()) {
				$this->view->setMessage('COM_EASYSOCIAL_PAGES_PUBLISH_FOLLOWERS_FAILED', ES_ERROR);
				return $this->view->call('redirectToPageForm', $pageId);
			}

			$user = ES::user($node->uid);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_FOLLOWER_PUBLISHED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_PUBLISH_FOLLOWERS_FOLLOWERS_SUCCESS');
		return $this->view->call('redirectToPageForm', $pageId);
	}

	/**
	 * Unpublishes a page member
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function unpublishUser()
	{
		ES::checkToken();

		$pageId = $this->input->get('id', 0, 'int');
		$cids = $this->input->get('cid', array(), 'int');

		if (!$cids || !$pageId) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_UNPUBLISH_FOLLOWERS_FAILED');
		}

		$page = ES::page($pageId);

		foreach ($cids as $cid) {
			$node = ES::table('PageMember');
			$node->load($cid);

			if ($node->state == 0 || $node->isAdmin() || $node->isOwner()) {
				continue;
			}

			$node->state = 0;

			if (!$node->store()) {
				$this->view->setMessage('COM_EASYSOCIAL_PAGES_UNPUBLISH_FOLLOWERS_FAILED', ES_ERROR);
				return $this->view->call('redirectToPageForm', $pageId);
			}

			$user = ES::user($node->uid);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_FOLLOWER_UNPUBLISHED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_UNPUBLISH_FOLLOWERS_FOLLOWERS_SUCCESS');
		return $this->view->call('redirectToPageForm', $pageId);
	}

	/**
	 * Remove followers from the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function removeMembers()
	{
		ES::checkToken();

		$pageId = $this->input->get('id', 0, 'int');
		$ids = $this->input->get('cid', [], 'array');
		$count = 0;

		foreach ($ids as $id) {
			$follower = ES::table('PageMember');
			$follower->load($id);

			if ($follower->isAdmin() || $follower->isOwner()) {
				$this->view->setMessage('COM_EASYSOCIAL_PAGES_REMOVE_FOLLOWERS_REMOVE_ADMIN_FAILED', ES_ERROR);
				return $this->view->call('redirectToPageForm', $pageId);
			}

			$follower->delete();
			$count++;

			$page = ES::page($pageId);
			$user = ES::user($follower->uid);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_FOLLOWER_REMOVED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_REMOVE_FOLLOWERS_SUCCESS', $count));
		return $this->view->call('redirectToPageForm', $pageId);
	}

	/**
	 * Make a user as admin to that page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function promoteMembers()
	{
		ES::checkToken();

		$pageId = $this->input->get('id', 0, 'int');
		$cids = $this->input->getVar('cid');

		if (empty($cids)) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_PROMOTE_MEMBERS_FAILED');
		}

		ES::language()->loadSite();

		$page = ES::page($pageId);

		$user = ES::table('PageMember');
		$user->load(array('cluster_id' => $page->id, 'uid' => $this->my->id, 'type' => SOCIAL_TYPE_USER));

		if (!$this->my->isSiteAdmin() && !$user->isAdmin() && !$user->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_PROMOTE_MEMBERS_FAILED', ES_ERROR);
			return $this->view->call('redirectToPageForm', $pageId);
		}

		$count = 0;

		foreach ($cids as $id) {
			$member = ES::table('PageMember');
			$member->load($id);

			$member->makeAdmin();

			$page->createStream($member->uid, 'makeadmin');

			// Notify the person that they are now a page admin
			$emailOptions   = array(
				'title' => 'COM_EASYSOCIAL_PAGES_EMAILS_PROMOTED_AS_PAGE_ADMIN_SUBJECT',
				'template' => 'site/page/promoted',
				'permalink' => $page->getPermalink(true, true),
				'actor' => $this->my->getName(),
				'actorAvatar' => $this->my->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $this->my->getPermalink(true, true),
				'page' => $page->getName(),
				'groupLink' => $page->getPermalink(true, true)
			);

			$systemOptions  = array(
				'context_type' => 'pages.page.promoted',
				'url' => $page->getPermalink(false, false, 'item', false),
				'actor_id' => $this->my->id,
				'uid' => $page->id
			);

			// Notify the owner first
			ES::notify('pages.promoted', array($member->uid), $emailOptions, $systemOptions);

			$count++;

			$user = ES::user($member->uid);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_FOLLOWER_PROMOTED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_PROMOTE_MEMBERS_SUCCESS', $count));
		return $this->view->call('redirectToPageForm', $pageId);
	}

	/**
	 * Revoke admin from a user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function demoteMembers()
	{
		ES::checkToken();

		$pageId = $this->input->get('id', 0, 'int');
		$cids = $this->input->getVar('cid');

		if (!$cids) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_DEMOTE_MEMBERS_FAILED');
		}

		$page = ES::page($pageId);

		$user = ES::table('PageMember');
		$user->load(array('cluster_id' => $page->id, 'uid' => $this->my->id, 'type' => SOCIAL_TYPE_USER));

		if (!$this->my->isSiteAdmin() && !$user->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_DEMOTE_MEMBERS_FAILED', ES_ERROR);
			return $this->view->call('redirectToPageForm', $pageId);
		}

		$count = 0;

		foreach ($cids as $id) {
			$member = ES::table('PageMember');
			$member->load($id);

			$member->revokeAdmin();

			$count++;

			$user = ES::user($member->uid);

			$this->actionlog->log('COM_ES_ACTION_LOG_PAGE_FOLLOWER_REVOKED', 'pages', [
					'name' => $page->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_DEMOTE_MEMBERS_SUCCESS', $count));
		return $this->view->call('redirectToPageForm', $pageId);
	}

	public function moveDown()
	{
		return $this->move(1);
	}

	public function moveUp()
	{
		return $this->move(-1);
	}

	private function move($index)
	{
		$layout = $this->input->get('layout', '', 'string');
		$tablename = $layout === 'categories' ? 'pagecategory' : '';

		if (!$tablename) {
			return $this->view->move();
		}

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_CATEGORIES_INVALID_IDS');
		}

		$db = ES::db();

		$filter = $db->nameQuote('type') . ' = ' . $db->quote(SOCIAL_TYPE_PAGE);

		if (isset($ids[0])) {
			$table = ES::table($tablename);
			$table->load($ids[0]);

			$table->move($index, $filter);

			//now we need to update the ordering.
			$table->updateOrdering();

		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_CATEGORIES_ORDERED_SUCCESSFULLY');
		return $this->view->move($layout);
	}

	/**
	 * Perform verify action on the users
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setVerifyAction()
	{
		$action = $this->getTask();
		$ids = $this->input->get('cid', [], 'array');
		$redirection = 'index.php?option=com_easysocial&view=pages';
		$actionString = $action == 'setVerified' ? 'COM_ES_ACTION_LOG_PAGE_VERIFIED' : 'COM_ES_ACTION_LOG_PAGE_UNVERIFIED';

		if (!$ids) {
			$this->view->setMessage('No pages being selected', ES_ERROR);

			return $this->view->setRedirection($redirection);
		}

		foreach ($ids as $id) {
			$page = ES::page($id);

			$page->$action();

			$this->actionlog->log($actionString, 'verifications', [
				'name' => $page->getName(),
				'link' => 'index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id
			]);
		}

		$message = 'COM_ES_SELECTED_PAGES_UNVERIFIED';

		if ($action == 'setVerified') {
			$message = 'COM_ES_SELECTED_PAGES_VERIFIED';
		}

		$this->view->setMessage($message);
		return $this->view->setRedirection($redirection);
	}
}
