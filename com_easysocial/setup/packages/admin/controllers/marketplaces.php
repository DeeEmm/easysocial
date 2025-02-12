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

class EasySocialControllerMarketplaces extends EasySocialController
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

		$this->registerTask('setFeatured', 'toggleDefault');
		$this->registerTask('removeFeatured', 'toggleDefault');
	}

	/**
	 * Saves an item
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function store()
	{
		ES::checkToken();
		ES::language()->loadSite();

		$task = $this->getTask();
		$id = $this->input->get('id', 0, 'int');

		// Flag to see if this is new or edit
		$isNew = empty($id);

		$isCopy = $task == 'savecopy' ? true : false;

		// Get the posted data
		$post = $this->input->getArray('post');
		$options = array();

		if ($isNew || $isCopy) {
			$listing = ES::marketplace();

			$categoryId = $this->input->get('category_id', 0, 'int');

			if ($isCopy) {
				$citem = ES::marketplace($id);
				$categoryId = $citem->category_id;

				// lets unset the id here.
				$post['id'] = 0;
			}
		} else {
			$listing = ES::marketplace($id);

			$options['data'] = true;
			$options['dataId'] = $listing->id;
			$options['dataType'] = SOCIAL_TYPE_MARKETPLACE;
			$categoryId = $listing->category_id;
		}

		$category = ES::table('MarketplaceCategory');
		$category->load($categoryId);

		$options['workflow_id'] = $category->getWorkflow()->id;
		$options['group'] = SOCIAL_TYPE_MARKETPLACE;

		// Get fields model
		$fieldsModel = ES::model('Fields');

		// Get the custom fields
		$fields = $fieldsModel->getCustomFields($options);

		// Initialize default registry
		$registry = ES::registry();

		// Get disallowed keys so we wont get wrong values.
		$disallowed = array(ES::token(), 'option' , 'task' , 'controller', 'autoapproval');

		// Process $_POST vars
		foreach ($post as $key => $value) {

			if (!in_array($key, $disallowed)) {

				if (is_array($value)) {
					$value = json_encode($value);
				}

				$registry->set($key, $value);
			}
		}

		// Convert the values into an array.
		$data = $registry->toArray();

		$data['uid'] = $this->my->id;
		$data['type'] = SOCIAL_TYPE_USER;

		// Get the fields lib
		$fieldsLib = ES::fields();

		// Build arguments to be passed to the field apps.
		$args = array(&$data, &$listing, &$isCopy);

		// @trigger onAdminEditValidate
		$errors = $fieldsLib->trigger('onAdminEditValidate', $options['group'], $fields, $args);

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			$this->input->setVars($data);

			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_FORM_SAVE_ERRORS', ES_ERROR);
			return $this->view->call('form', $errors);
		}

		// @trigger onAdminEditBeforeSave
		$errors = $fieldsLib->trigger('onAdminEditBeforeSave', $options['group'], $fields, $args);

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			$this->input->setVars($data);

			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_FORM_SAVE_ERRORS', ES_ERROR);
			return $this->view->call('form', $errors);
		}

		// Initialise item data for new item
		if ($isNew || $isCopy) {
			// Set the category id for the item
			$listing->category_id = $categoryId;
			$listing->state = SOCIAL_STATE_PUBLISHED;
		}

		$listing->bind($data);
		$listing->save();

		// Reconstruct args
		$args = array(&$data, &$listing);

		$fieldsLib->trigger('onAdminEditAfterSave', $options['group'], $fields, $args);
		$listing->bindCustomFields($data);

		// Reconstruct args
		$args = array(&$data, &$listing);

		$fieldsLib->trigger('onAdminEditAfterSaveFields', $options['group'], $fields, $args);

		$log = $isNew ? 'COM_ES_ACTION_LOG_MARKETPLACE_CREATED' : 'COM_ES_ACTION_LOG_MARKETPLACE_UPDATED';
		$this->actionlog->log($log, 'marketplace', [
				'listingTitle' => $listing->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id
			]);

		$message = 'COM_ES_MARKETPLACES_FORM_CREATE_SUCCESS';

		if ($isCopy) {
			$message = 'COM_ES_MARKETPLACES_FORM_COPIED_SUCCESS';
		}

		if ($id) {
			$message = 'COM_ES_MARKETPLACES_FORM_SAVE_UPDATE_SUCCESS';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task, $listing);
	}

	/**
	 * Allows admin to toggle featured marketplaces
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function toggleDefault()
	{
		ES::checkToken();

		$task = $this->getTask();
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {

			// Load the listing
			$listing = ES::marketplace($id);

			if ($task == 'toggleDefault') {
				$task = $listing->featured ? 'removeFeatured' : 'setFeatured';
			}

			$state = $listing->$task();
			$message = 'COM_ES_MARKETPLACES_' . strtoupper($task);

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_' . strtoupper($task), 'marketplace', [
						'listingTitle' => $listing->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id
					]);
			}
		}

		$this->view->setMessage($message);
		return $this->view->call('redirectToMarketplaces');
	}

	/**
	 * Removes the group category avatar
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function removeCategoryAvatar()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$category = ES::table('MarketplaceCategory');
		$category->load($id);

		// Try to remove the avatar
		$state = $category->removeAvatar();

		if ($state) {
			$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_REMOVE_CATEGORY_AVATAR', 'marketplace', [
					'categoryTitle' => $category->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=categoryForm&id=' . $category->id
				]);
		}
	}

	/**
	 * Deletes a list of group from the site.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function delete()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_MARKETPLACES_DELETE_FAILED');
		}

		foreach ($ids as $id) {
			$listing = ES::marketplace((int) $id);
			$state = $listing->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_DELETED', 'marketplace', ['listingTitle' => $listing->getTitle()]);
			}
		}

		$this->view->setMessage('COM_ES_MARKETPLACES_DELETED_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes a marketplace category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function deleteCategory()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_CATEGORY_DELETED_FAILED');
		}

		foreach($ids as $id) {
			$category = ES::table('MarketplaceCategory');
			$category->load((int) $id);

			$total = $category->getTotalListings();

			// Check if deleting the category having the group will throw error.
			if ($total) {
				$this->view->setMessage('COM_ES_MARKETPLACES_DELETE_ERROR_LISTING_NOT_EMPTY', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			$state = $category->delete();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_CATEGORY_DELETED', 'marketplace', ['categoryTitle' => $category->getTitle()]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_CATEGORY_DELETED_SUCCESS');
		return $this->view->call('redirectToCategories');
	}

	/**
	 * Toggles publishing state of listings
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$task = $this->getTask();
		$indexer = ES::get('Indexer');
		$toggleValue = $task === 'publish' ? SOCIAL_STATE_PUBLISHED : SOCIAL_STATE_UNPUBLISHED;

		foreach ($ids as $id) {
			$listing = ES::marketplace((int) $id);

			$state = $listing->$task();

			if ($state) {
				// need to update from the indexed item as well
				$indexer->itemStateChange('easysocial.marketplaces', $id, $toggleValue);
				$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_' . strtoupper($task), 'marketplace', [
						'listingTitle' => $listing->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id
					]);
			}
		}

		$message = 'COM_ES_MARKETPLACES_PUBLISHED_SUCCESS';

		if ($task == 'unpublish') {
			$message = 'COM_ES_MARKETPLACES_UNPUBLISHED_SUCCESS';
		}

		$this->view->setMessage($message);
		return $this->view->call('redirectToMarketplaces');
	}

	/**
	 * Toggle publishing state of a group category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function togglePublishCategory()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		foreach ($ids as $id) {
			$category = ES::table('MarketplaceCategory');
			$category->load((int) $id);

			$task = $this->getTask() == 'publishCategory' ? 'publish' : 'unpublish';

			// Perform the action now
			$state = $category->$task();

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_CATEGORY_' . strtoupper($task), 'marketplace', [
						'categoryTitle' => $category->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=categoryForm&id=' . $category->id
					]);
			}
		}

		$message = 'COM_EASYSOCIAL_GROUPS_CATEGORY_UNPUBLISHED_SUCCESS';

		if ($task == 'publish') {
			$message = 'COM_EASYSOCIAL_GROUPS_CATEGORY_PUBLISHED_SUCCESS';
		}

		$this->view->setMessage($message);

		return $this->view->call('redirectToCategories');
	}

	/**
	 * Allows caller to approve a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function approve()
	{
		ES::checkToken();

		$ids = $this->input->get('id', array(), 'int');
		$email = $this->input->get('email', '', 'default');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$listing = ES::marketplace((int) $id);
			$state = $listing->approve($email);

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_APPROVE', 'marketplace', [
						'listingTitle' => $listing->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_MARKETPLACES_APPROVE_SUCCESS');

		return $this->view->call('redirectToPending');
	}

	/**
	 * Allows caller to reject a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function reject()
	{
		ES::checkToken();

		$ids = $this->input->get('id', array(), 'array');
		$email = $this->input->get('email', '', 'default');
		$delete = $this->input->get('delete', '', 'default');
		$reason = $this->input->get('reason', '', 'default');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$listing = ES::marketplace((int) $id);
			$state = $listing->reject($reason, $email, $delete);

			if ($state) {
				$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_REJECT', 'marketplace', [
						'listingTitle' => $listing->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id
					]);
			}
		}

		$this->view->setMessage('COM_ES_MARKETPLACES_REJECT_SUCCESS');
		return $this->view->call('redirectToPending');
	}

	/**
	 * Stores a category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function saveCategory()
	{
		ES::checkToken();

		$post = $this->input->getArray('post');
		$id = $this->input->get('id', 0, 'int');
		$cid = $this->input->get('cid', 0, 'int');
		$task = $this->getTask();
		$isCopy = $task == 'saveCategoryCopy' ? true : false;
		$oriParentId = $this->input->get('oriParentId', 0);

		// Flag to see if this is new or edit
		$isNew = empty($id);

		//unset oriParentId since we no longer needed
		unset($post['oriParentId']);

		// Category title is compulsory
		if (empty($post['title'])) {
			$this->view->setMessage('COM_ES_CLUSTER_CATEGORY_TITLE_MISSING', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$category = ES::table('MarketplaceCategory');

		if ($cid && $isCopy) {
			$category->load($cid);
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

		$state = $category->store();

		if ($state) {
			$log = $isNew ? 'COM_ES_ACTION_LOG_MARKETPLACE_CATEGORY_CREATED' : 'COM_ES_ACTION_LOG_MARKETPLACE_CATEGORY_UPDATED';
			$this->actionlog->log($log, 'marketplace', [
						'categoryTitle' => $category->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=categoryForm&id=' . $category->id
					]);
		}

		// we need to check if the parent_id has changed or not. if yes,
		// we need to re-calcuate the lft and rgt boundary
		if ($oriParentId != $category->parent_id) {
			$category->updateLftValue($category->parent_id);
		}

		// lets re-arrange the lft right hierachy and ordering
		$category->rebuildOrdering();

		//now we need to update the ordering.
		$category->updateOrdering();

		// Store the avatar for this profile.
		$file = $this->input->files->get('avatar', '');

		// Try to upload the profile's avatar if required
		if (!empty($file['tmp_name'])) {
			$category->uploadAvatar($file);
		}

		// If this is a copy, copy over the avatar
		if ($isCopy) {
			$category->copyAvatar($id);
		}

		$category->assignWorkflow($workflowId);

		// Set the message
		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_CATEGORY_SAVED_SUCCESS');
		return $this->view->call(__FUNCTION__, $category);
	}

	/**
	 * Create blank category.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function createBlankCategory()
	{
		// Check for request forgeries.
		ES::checkToken();

		// Create the new category
		$newCategory = ES::table('MarketplaceCategory');
		$newCategory->title = 'temp';
		$newCategory->createBlank(SOCIAL_TYPE_MARKETPLACE);
		$id = $newCategory->id;

		return $this->view->call(__FUNCTION__, $id);
	}

	public function moveUp()
	{
		return $this->move(-1);
	}

	public function moveDown()
	{
		return $this->move(1);
	}

	private function move($index)
	{
		// $layout could be categories (to add group in the future)
		$layout = $this->input->get('layout', '', 'string');
		$tablename = $layout === 'categories' ? 'marketplacecategory' : '';

		if (empty($tablename)) {
			return $this->view->move($layout);
		}

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_CATEGORIES_INVALID_IDS');
		}

		$db = ES::db();

		if (isset($ids[0])) {
			$table = ES::table($tablename);
			$table->load($ids[0]);

			$table->move($index);

			$table->updateOrdering();
		}

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_CATEGORIES_ORDERED_SUCCESSFULLY');
		return $this->view->move($layout);
	}

	/**
	 * Method to update categories ordering
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function saveorder()
	{
		ES::checkToken();

		$cid = $this->input->get('cid', array(), 'array');

		if (!$cid) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_ORDERING_NO_ITEMS');
		}

		$catId = $cid[0];
		$category = ES::table('marketplacecategory');
		$category->load($catId);
		$category->rebuildOrdering();

		$model = ES::model('MarketplaceCategories');

		$i = 1;

		foreach ($cid as $id) {
			$model->updateCategoriesOrdering($id, $i);
			$i++;
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_ORDERING_UPDATED');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows admin to switch category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function switchCategory()
	{
		ES::checkToken();

		$ids = ES::makeArray($this->input->get('cid'));

		if (!$ids) {
			$this->view->setMessage('COM_ES_NO_ITEMS_SELECTED', ES_ERROR);
			return $this->view->setRedirection('index.php?option=com_easysocial&view=marketplaces');
		}

		$categoryId = $this->input->getInt('category');

		$category = ES::table('MarketplaceCategory');
		$category->load($categoryId);

		if (!$category->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_CATEGORIES_INVALID_IDS');
		}

		$model = ES::model('MarketplaceCategories');

		foreach ($ids as $id) {
			$listing = ES::marketplace((int) $id);
			$model->updateCategory($listing->id, $categoryId);

			$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_CATEGORY_SWITCH', 'marketplace', [
					'listingTitle' => $listing->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id,
					'catName' => $category->getTitle(),
					'catLink' => 'index.php?option=com_easysocial&view=marketplaces&layout=categoryForm&id=' . $category->id,
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_SWITCH_CATEGORY_SUCCESSFUL');
		return $this->view->call('redirectToMarketplaces');
	}

	/**
	 * Uploading photos in marketplace
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function uploadPhotos()
	{
		ES::requireLogin();
		ES::checkToken();

		$ajax = ES::ajax();
		$access = $this->my->getAccess();
		$limit = $access->get('photos.uploader.maxsize') . 'M';

		$options = array('name' => 'file', 'maxsize' => $limit);
		$inputName = $this->input->get('inputName', '', 'string');

		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialResponse) {
			$this->view->setMessage($file->message, ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load up the image library so we can get the appropriate extension
		$image 	= ES::image();
		$image->load($file['tmp_name']);

		// Copy this to temporary location first
		$date = ES::date();

		$session = JFactory::getSession();
		$uniquePath = md5($session->getId() . '_listing');
		$folder = 'tmp/' . $uniquePath . '_listing_photos';

		$tmpPath = SOCIAL_MEDIA . '/' . $folder;

		if (!JFolder::exists($tmpPath)) {
			ES::makeFolder($tmpPath);
		}

		$tmpName = $file['name'];
		$target = $tmpPath . '/' . $tmpName;

		$state = $image->save($target);

		if (!$state) {
			$this->view->setMessage(JText::_('PLG_FIELDS_AVATAR_ERROR_UNABLE_TO_MOVE_FILE'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$relPath = $folder . '/' . $tmpName;

		// Generate a tempory url for the image
		$uri = rtrim(JURI::root() , '/') . '/media/com_easysocial/' . $relPath;

		return $this->view->call(__FUNCTION__, $uri, $relPath, $inputName);
	}

	/**
	 * Switch listing owner
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function switchOwner()
	{
		ES::checkToken();

		$ids = $this->input->get('ids', array(), 'int');
		$userId = $this->input->get('userId', 0, 'int');

		if (!$ids || !$userId) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_SWITCH_OWNER_FAILED');
		}

		foreach ($ids as $id) {
			$listing = ES::marketplace($id);

			ES::access()->switchLogAuthor('marketplaces.limit', $listing->getCreator()->id, $listing->id, SOCIAL_TYPE_MARKETPLACE, $userId);

			$listing->switchOwner($userId);

			$user = ES::user($userId);

			$this->actionlog->log('COM_ES_ACTION_LOG_MARKETPLACE_OWNER_SWITCH', 'marketplace', [
					'listingTitle' => $listing->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $listing->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id,
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_SWITCH_OWNER_SUCCESS');
		return $this->view->call('redirectToMarketplaces');
	}
}
