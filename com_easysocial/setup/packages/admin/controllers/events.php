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

class EasySocialControllerEvents extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('publishCategory', 'togglePublishCategory');
		$this->registerTask('unpublishCategory', 'togglePublishCategory');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('saveCategory', 'saveCategory');
		$this->registerTask('applyCategory', 'saveCategory');
		$this->registerTask('saveCategoryNew', 'saveCategory');
		$this->registerTask('saveCategoryCopy', 'saveCategory');

		$this->registerTask('makeFeatured', 'toggleDefault');
		$this->registerTask('removeFeatured', 'toggleDefault');

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('savenew', 'store');
	}

	/**
	 * Saves an event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();
		ES::language()->loadSite();

		$task = $this->getTask();

		$id = $this->input->get('id', 0, 'int');
		$isLastRecurringEvent = $this->input->getInt('isLastRecurringEvent');

		$event = ES::event($id);
		$isNew = empty($event->id);

		$post = $this->input->post->getArray();
		$options = array();

		if ($isNew) {
			$event->category_id = $this->input->get('category_id', 0, 'int');
			$event->creator_uid = $this->my->id;
			$event->creator_type = SOCIAL_TYPE_USER;
			$event->state = SOCIAL_STATE_PUBLISHED;
			$event->key = md5(ES::date()->toSql() . $this->my->password . uniqid());
		} else {
			$options['data'] = true;
			$options['dataId'] = $event->id;
			$options['dataType'] = SOCIAL_FIELDS_GROUP_EVENT;
		}

		$eventCategory = ES::table('EventCategory');
		$eventCategory->load($event->category_id);

		// $options['uid'] = $event->category_id;
		$options['workflow_id'] = $eventCategory->getWorkflow()->id;
		$options['group'] = SOCIAL_FIELDS_GROUP_EVENT;

		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields($options);

		$registry = ES::registry();

		$disallowed = array(ES::token(), 'option', 'task', 'controller');

		foreach ($post as $key => $value) {
			if (!in_array($key, $disallowed)) {
				if (is_array($value)) {
					$value = ES::json()->encode($value);
				}

				$registry->set($key, $value);
			}
		}

		$data = $registry->toArray();

		$fieldsLib = ES::fields();

		// Retrieve the recurring field id
		$recurringFieldId = $fieldsModel->getSpecificFieldId($eventCategory->getWorkflow()->id, SOCIAL_FIELDS_GROUP_EVENT, 'recurring');
		$hasRecurringFieldData = false;

		// Determine whether this workflow field got recurring field or not
		if ($recurringFieldId) {
			$recurringFieldPrefix = SOCIAL_FIELDS_PREFIX . $recurringFieldId;

			if (isset($data[$recurringFieldPrefix]) && $data[$recurringFieldPrefix]) {

				$recurringObj = json_decode($data[$recurringFieldPrefix]);
				$hasRecurringFieldData = true;

				// If the recurring field set to none
				if ($recurringObj->type == 'none') {
					$hasRecurringFieldData = false;
				}
			}
		}

		// Determine whether has recurring field or not
		$data['hasRecurringFieldData'] = $hasRecurringFieldData;

		// Determine if this recurring event is the last one
		$data['isLastRecurringEvent'] = $isLastRecurringEvent;
		$data['isNew'] = $isNew;

		// Get the general field trigger handler
		$handler = $fieldsLib->getHandler();

		// Build arguments to be passed to the field apps.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$event);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args, array($handler));

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$event);

		$errors = $fieldsLib->trigger('onAdminEditValidate', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);

		// Render errors
		if (!empty($errors)) {
			$this->input->setVars($data);

			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_FORM_SAVE_ERRORS', ES_ERROR);
			return $this->view->call('form', $errors);
		}

		$errors = $fieldsLib->trigger('onAdminEditBeforeSave', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);

		if (!empty($errors)) {
			$this->input->setVars($data);

			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_FORM_SAVE_ERRORS', ES_ERROR);
			return $this->view->call('form', $errors);
		}

		// If the event alias is still empty at this point, there is instance where the permalink field isn't enabled.
		if (!$event->alias) {
			$model = ES::model('Events');
			$event->alias = $model->getUniqueAlias($event->getName());
		}

		$event->bind($data);

		$eventOwner = ES::user($event->creator_uid);

		// Trigger events
		$dispatcher = ES::dispatcher();
		$triggerArgs = array(&$event, &$eventOwner, $isNew);

		// @trigger: onEventBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventBeforeSave', $triggerArgs);

		$event->save();

		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventAfterSave', $triggerArgs);

		if ($isNew) {
			ES::access()->log('events.limit', $this->my->id, $event->id, SOCIAL_TYPE_EVENT);
			$event->createOwner();
		}

		$args = array(&$data, &$event);
		$fieldsLib->trigger('onAdminEditAfterSave', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);
		$event->bindCustomFields($data);

		$args = array(&$data, &$event);
		$fieldsLib->trigger('onAdminEditAfterSaveFields', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);

		if ($isNew) {
			$event->createStream($event->creator_uid, 'create', $event->creator_type);
		}

		$message = $isNew ? 'COM_EASYSOCIAL_EVENTS_FORM_CREATE_SUCCESS' : 'COM_EASYSOCIAL_EVENTS_FORM_UPDATE_SUCCESS';
		$actionString = $isNew ? 'COM_ES_ACTION_LOG_EVENT_CREATED' : 'COM_ES_ACTION_LOG_EVENT_UPDATED';

		$this->actionlog->log($actionString, 'events', [
				'name' => $event->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id
			]);

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task, $event, (int) $isNew);
	}

	/**
	 * Deletes the event from the site.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function delete()
	{
		ES::checkToken();

		// Get the event id's.
		$ids = $this->input->get('cid', '', 'array');

		// Check for empty id's.
		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_DELETE_FAILED');
		}

		// Go through each of the event
		foreach ($ids as $id) {
			$event = ES::event((int) $id);

			if (!$event->id) {
				continue;
			}

			$title = $event->getTitle();

			$event->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_DELETED', 'events', [
					'name' => $title
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_DELETE_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes past events
	 *
	 * @since	3.2.16
	 * @access	public
	 */
	public function deletePastEvents()
	{
		$totalToDelete = $this->input->get('totalToDelete', 20, 'int');

		$model = ES::model('Events');
		$events = $model->getPastEvents($totalToDelete);

		foreach ($events as $event) {
			$event->delete();
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_EVENTS_PAST_PURGED', 'events');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Stores an event category
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function saveCategory()
	{
		ES::checkToken();

		$post = $this->input->getArray('post');
		$id = $this->input->get('id', 0, 'int');
		$cid = $this->input->get('cid', 0, 'int');

		$task = $this->getTask();
		$isCopy = $task == 'saveCategoryCopy' ? true : false;

		// Assign original parent id to tmp variable
		$oriParentId = $this->input->get('oriParentId', 0);

		// Unset from post array
		unset($post['oriParentId']);

		// Category title is compulsory
		if (empty($post['title'])) {
			$this->view->setMessage('COM_ES_CLUSTER_CATEGORY_TITLE_MISSING', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$category = ES::table('EventCategory');

		if ($isCopy && $cid) {
			$category->load($cid);

			// reset the id
			$post['id'] = $cid;
		} else {
			$category->load($id);
		}

		$isNew = empty($category->id);

		$category->bind($post);

		$workflowId = $this->input->get('workflow_id');

		// There workflow must be selected in order to proceed
		if (!$workflowId) {
			$this->view->setMessage('COM_ES_WORKFLOW_NOT_SELECTED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $category);
		}

		$state = $category->store();

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENT_CATEGORY_SAVE_ERROR', $category->getError()), ES_ERROR);
			return $this->view->call(__FUNCTION__, $category);
		}

		$categoryAccess = $this->input->get('create_access', '', 'default');
		$category->bindCategoryAccess('create', $categoryAccess);

		// Check if the parent id has changed
		if ($oriParentId != $category->parent_id) {
			// Recalculate the lft column
			$category->updateLftValue($category->parent_id);
		}

		// Re-arrange lft and rgt column
		$category->rebuildOrdering();

		// Update the ordering
		$category->updateOrdering();

		$file = $this->input->files->get('avatar', '');

		if (!empty($file['tmp_name'])) {
			$category->uploadAvatar($file);
		}

		// If this is a copy, copy over the avatar
		if ($isCopy) {
			$category->copyAvatar($id);
		}

		$category->assignWorkflow($workflowId);

		if (isset($post['access'])) {
			$category->bindAccess($post['access']);
		}

		$message = $isNew ? 'COM_EASYSOCIAL_EVENT_CATEGORY_CREATE_SUCCESS' : 'COM_EASYSOCIAL_EVENT_CATEGORY_UPDATE_SUCCESS';
		$actionString = 'COM_ES_ACTION_LOG_EVENT_CATEGORY_CREATED';

		if ($id) {
			$actionString = 'COM_ES_ACTION_LOG_EVENT_CATEGORY_UPDATED';
		}

		if ($isCopy) {
			$actionString = 'COM_ES_ACTION_LOG_EVENT_CATEGORY_COPIED';
		}

		$this->actionlog->log($actionString, 'events', [
				'name' => $category->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=events&layout=categoryForm&id=' . $category->id
			]);

		$this->view->setMessage($message, SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $category);
	}

	/**
	 * Create blank Category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function createBlankCategory()
	{
		ES::checkToken();

		$newCategory = ES::table('EventCategory');
		$newCategory->title = 'temp';
		$newCategory->createBlank(SOCIAL_TYPE_EVENT);
		$id = $newCategory->id;

		return $this->view->call(__FUNCTION__, $id);
	}

	/**
	 * Delete an event category
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function deleteCategory()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_EVENT_CATEGORY_DELETE_FAILED');
		}

		foreach ($ids as $id) {
			$category = ES::table('EventCategory');
			$category->load($id);

			$total = $category->getTotalEvents();
			$title = $category->getTitle();

			// Check if deleting the category having the event will throw error.
			if ($total) {
				$this->view->setMessage('COM_EASYSOCIAL_CATEGORIES_DELETE_ERROR_EVENT_NOT_EMPTY', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			$category->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_CATEGORY_DELETED', 'events', [
				'name' => $title
			]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_EVENT_CATEGORY_DELETE_SUCCESS');
		return $this->view->call(__FUNCTION__);
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
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Toggles publishing state of an event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$action = $this->getTask();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			$message = $action === 'publish' ? 'COM_EASYSOCIAL_EVENTS_PUBLISHED_FAILED' : 'COM_EASYSOCIAL_EVENTS_UNPUBLISHED_FAILED';
			return $this->view->exception($message);
		}

		$indexer = ES::get('Indexer');
		$toggleValue = $action === 'publish' ? SOCIAL_CLUSTER_PUBLISHED : SOCIAL_CLUSTER_UNPUBLISHED;
		$actionString = $action === 'publish' ? 'COM_ES_ACTION_LOG_EVENT_PUBLISHED' : 'COM_ES_ACTION_LOG_EVENT_UNPUBLISHED';

		foreach ($ids as $id) {
			$table = ES::table('event');
			$table->load($id);

			if (!$table->id) {
				continue;
			}

			$state = $table->$action($id);

			if ($state) {
				// need to update from the indexed item as well
				$indexer->itemStateChange('easysocial.events', $id, $toggleValue);

				$this->actionlog->log($actionString, 'events', [
						'name' => $table->title,
						'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $table->id
					]);
			}
		}

		$message = $action === 'publish' ? 'COM_EASYSOCIAL_EVENTS_PUBLISHED_SUCCESS' : 'COM_EASYSOCIAL_EVENTS_UNPUBLISHED_SUCCESS';

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Toggles publishing state of an event category
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function togglePublishCategory()
	{
		ES::checkToken();

		$action = str_replace('Category', '', $this->getTask());

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			$message = $action === 'publish' ? 'COM_EASYSOCIAL_EVENT_CATEGORY_PUBLISHED_FAILED' : 'COM_EASYSOCIAL_EVENT_CATEGORY_UNPUBLISHED_FAILED';
			return $this->view->exception($message);
		}

		$actionString = $action == 'publish' ? 'COM_ES_ACTION_LOG_EVENT_CATEGORY_PUBLISHED' : 'COM_ES_ACTION_LOG_EVENT_CATEGORY_UNPUBLISHED';

		foreach ($ids as $id) {
			$category = ES::table('EventCategory');
			$category->load((int) $id);

			if (!$category->id) {
				continue;
			}

			$category->$action();

			$this->actionlog->log($actionString, 'events', [
				'name' => $category->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=events&layout=categoryForm&id=' . $category->id
			]);
		}

		$message = $action === 'publish' ? 'COM_EASYSOCIAL_EVENT_CATEGORY_PUBLISHED_SUCCESS' : 'COM_EASYSOCIAL_EVENT_CATEGORY_UNPUBLISHED_SUCCESS';

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__);
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

		$ids = $this->input->get('id', array(), 'int');
		$email = $this->input->get('email');

		// Prevent errors
		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$id = (int) $id;
			$event = ES::event($id);

			$event->approve($email);

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_APPROVED', 'events', [
						'name' => $event->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id
					]);
		}

		$this->view->setMessage('Event has been approved successfully.');
		return $this->view->call(__FUNCTION__, $ids);
	}

	/**
	 * Allows caller to reject a event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function reject()
	{
		ES::checkToken();

		$ids = $this->input->get('id', array(), 'int');
		$email = $this->input->get('email');
		$delete = $this->input->get('delete', false, 'bool');
		$reason = $this->input->get('reason', '', 'default');

		// Prevent errors
		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_IDS');
		}

		foreach ($ids as $id) {
			$id = (int) $id;
			$event = ES::event($id);

			$event->reject($reason, $email, $delete);

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_REJECTED', 'events', [
						'name' => $event->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id
					]);
		}

		$this->view->setMessage('Event has been rejected successfully.');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Removes an event category avatar
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function removeCategoryAvatar()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$category = ES::table('EventCategory');
		$category->load($id);
		$category->removeAvatar();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Invite guests to an event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function inviteGuests()
	{
		$id = $this->input->get('id', 0, 'int');
		$userIds = $this->input->get('guests', '', 'string');
		$userIds = json_decode($userIds);

		if (!$id || !$userIds || !is_array($userIds)) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVITE_GUESTS_FAILED');
		}

		$event = ES::event($id);
		$now = ES::date()->toSql();
		$count = 0;
		$exists = array();

		foreach ($userIds as $userId) {
			$member = ES::table('EventGuest');
			$state = $member->load(array('uid' => $userId, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $event->id));

			if ($state) {
				$exists[] = $id;
				continue;
			}

			$event->invite($userId, $this->my->id);
			$count++;

			$user = ES::user($userId);

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_GUEST_INVITED', 'events', [
					'name' => $event->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);
		}

		$msgType = SOCIAL_MSG_SUCCESS;
		$message = JText::sprintf('COM_EASYSOCIAL_EVENTS_INVITE_GUESTS_SUCCESS', $count);
		if ($exists) {
			if ($count) {
				$message = JText::sprintf('COM_ES_EVENTS_ADD_GUESTS_SUCCESS_WITH_WARNING', $count);
			} else {
				$message = JText::_('COM_ES_EVENTS_ADD_GUESTS_ALREADT_EXISTS');
				$msgType = SOCIAL_MSG_WARNING;
			}
		}

		$this->view->setMessage($message, $msgType);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to remove a guest from an event
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function removeGuests()
	{
		ES::checkToken();

		$cids = $this->input->get('cid', array(), 'array');
		$id = $this->input->get('id', 0, 'int');

		if (!$cids) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_REMOVE_GUESTS_FAILED');
		}

		$count = 0;
		$event = ES::event($id);

		foreach ($cids as $cid) {
			$node = ES::table('EventGuest');
			$state = $node->load($cid);

			if (!$state || $node->isAdmin() || $node->isOwner()) {
				continue;
			}

			$state = $node->remove();

			if ($state) {
				$count++;

				$user = ES::user($node->uid);

				$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_GUEST_REMOVED', 'events', [
						'name' => $event->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id,
						'userName' => $user->getName(),
						'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
					]);
			}
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_REMOVE_GUESTS_SUCCESS', $count));
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Approves a guest that is trying to attend an event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function approveGuests()
	{
		ES::checkToken();

		$cids = $this->input->get('cid', [], 'array');
		$id = $this->input->get('id', 0, 'int');

		if (!$cids) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_APPROVE_GUESTS_FAILED');
		}

		$count = 0;
		$event = ES::event($id);

		foreach ($cids as $cid) {
			$node = ES::table('EventGuest');
			$state = $node->load($cid);

			// If node is not in pending, we do not want to forcefully change the guest's state to going/maybe/notgoing/etc.
			// We only strictly approve guest that is in pending.
			if (!$state || !$node->isPending() || $node->isAdmin() || $node->isOwner()) {
				continue;
			}

			$state = $node->approve();

			if ($state) {
				$count++;

				$user = ES::user($node->uid);

				$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_GUEST_APPROVED', 'events', [
						'name' => $event->getTitle(),
						'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id,
						'userName' => $user->getName(),
						'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
					]);
			}
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_APPROVE_GUESTS_SUCCESS', $count));
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Switch event owner
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function switchOwner()
	{
		ES::checkToken();

		$ids = $this->input->get('ids', array(), 'int');
		$userId = $this->input->get('userId', 0, 'int');
		$adminRights = $this->input->get('adminRights', '', 'default');

		if (!$ids || !$userId) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_SWITCH_OWNER_FAILED');
		}

		foreach ($ids as $id) {
			$event = ES::event($id);

			ES::access()->switchLogAuthor('events.limit', $event->getCreator()->id, $event->id, SOCIAL_TYPE_EVENT, $userId);

			$event->switchOwner($userId, $adminRights);

			$user = ES::user($userId);

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_OWNER_SWITCHED', 'events', [
					'name' => $event->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id,
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_SWITCH_OWNER_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Promote an event attendee to be an event admin
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function promoteGuests()
	{
		ES::checkToken();

		$cids = $this->input->get('cid', array(), 'int');
		$id = $this->input->get('id', 0, 'int');

		if (!$cids || !$id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_PROMOTE_GUESTS_FAILED');
		}

		$event = ES::event($id);
		$guest = $event->getGuest($this->my->id);

		if (!$this->my->isSiteAdmin() && !$guest->isAdmin() && !$guest->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_PROMOTE_GUESTS_FAILED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$count = 0;

		foreach ($cids as $cid) {
			$table = ES::table('EventGuest');
			$table->load((int) $cid);
			$table->makeAdmin();

			$user = ES::user($table->uid);

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_GUEST_PROMOTED', 'events', [
					'name' => $event->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);

			$count++;
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_PROMOTE_GUESTS_SUCCESS', $count));
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows admin to toggle featured groups
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function toggleDefault()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');
		$task = $this->getTask();

		// Default message
		$message = 'COM_EASYSOCIAL_EVENTS_SET_FEATURED_SUCCESSFULLY';
		$actionString = 'COM_ES_ACTION_LOG_EVENT_FEATURED';

		foreach ($ids as $id) {
			$id = (int) $id;
			$event = ES::event($id);

			if ($task == 'toggleDefault') {

				if ($event->featured) {
					$event->removeFeatured();
					$message = 'COM_EASYSOCIAL_EVENTS_REMOVED_FEATURED_SUCCESSFULLY';
					$actionString = 'COM_ES_ACTION_LOG_EVENT_UNFEATURED';
				} else {
					$event->setFeatured();
				}
			}

			if ($task == 'makeFeatured') {
				$event->setFeatured();
			}

			if ($task == 'removeFeatured') {
				$event->removeFeatured();
				$message = 'COM_EASYSOCIAL_EVENTS_REMOVED_FEATURED_SUCCESSFULLY';
				$actionString = 'COM_ES_ACTION_LOG_EVENT_UNFEATURED';
			}

			$this->actionlog->log($actionString, 'events', [
					'name' => $event->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id
				]);
		}

		$this->view->setMessage($message);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Demotes an attendee to be normal attendee
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function demoteGuests()
	{
		ES::checkToken();

		$cids = $this->input->get('cid', array(), 'int');
		$id = $this->input->get('id', 0, 'int');

		if (!$cids || !$id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_DEMOTE_GUESTS_FAILED');
		}

		$event = ES::event($id);
		$guest = $event->getGuest($this->my->id);

		if (!$this->my->isSiteAdmin() && !$guest->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_DEMOTE_GUESTS_FAILED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$count = 0;

		foreach ($cids as $cid) {
			$table = ES::table('EventGuest');
			$table->load((int) $cid);
			$table->revokeAdmin();

			$user = ES::user($table->uid);

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_GUEST_REVOKED', 'events', [
					'name' => $event->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id,
					'userName' => $user->getName(),
					'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
				]);

			$count++;
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_DEMOTE_GUESTS_SUCCESS', $count));
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Move the ordering of the event category up
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function moveUp()
	{
		return $this->move(-1);
	}

	/**
	 * Move the ordering of the event category down
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function moveDown()
	{
		return $this->move(1);
	}

	/**
	 * The mechanics behind moving a category
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function move($index)
	{
		$layout = $this->input->getString('layout');

		$tablename = $layout === 'categories' ? 'eventcategory' : '';

		if (empty($tablename)) {
			return $this->view->move();
		}

		$ids = $this->input->get('cid', '', 'var');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_CATEGORIES_INVALID_IDS');
		}

		$db = ES::db();
		$filter = $db->nameQuote('type') . ' = ' . $db->quote(SOCIAL_TYPE_EVENT);

		if (isset($ids[0])) {
			$table = ES::table($tablename);
			$table->load($ids[0]);

			$table->move($index, $filter);

			$table->updateOrdering();
		}

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_CATEGORIES_ORDERED_SUCCESSFULLY');
		return $this->view->move($layout);
	}

	/**
	 * Switches the event's category
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function switchCategory()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$categoryId = $this->input->getInt('category');

		if (!$ids) {
			$this->view->setMessage('COM_ES_NO_ITEMS_SELECTED', ES_ERROR);
			return $this->view->setRedirection('index.php?option=com_easysocial&view=events');
		}

		$category = ES::table('ClusterCategory');
		$category->load($categoryId);

		if (!$category->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_CATEGORIES_INVALID_IDS');
		}

		$model = ES::model('EventCategories');

		foreach ($ids as $id) {
			$model->updateEventCategory((int) $id, $categoryId);

			$event = ES::event($id);

			$this->actionlog->log('COM_ES_ACTION_LOG_EVENT_CATEGORY_SWITCHED', 'events', [
					'name' => $event->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=events&layout=form&id=' . $event->id,
					'catName' => $category->getTitle(),
					'catLink' => 'index.php?option=com_easysocial&view=events&layout=categoryForm&id=' . $category->id,
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_SWITCH_CATEGORY_SUCCESSFUL');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Create a recurring event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function createRecurring()
	{
		ES::checkToken();

		$eventId = $this->input->getInt('eventId');
		$schedule = $this->input->getString('datetime');
		$isLastRecurringEvent = $this->input->get('isLastRecurringEvent', 0, 'int');
		$isNew = $this->input->get('isNew', 0, 'int');

		$parentEvent = ES::event($eventId);
		$duration = $parentEvent->hasEventEnd() ? $parentEvent->getEventEnd()->toUnix() - $parentEvent->getEventStart()->toUnix() : false;

		$postData = $this->input->post->getArray([], null, 'raw');
		$data = $postData['postdata'];

		// Because this comes form a form, the $data['id'] might be an existing id especially if the create recurring comes from "edit"
		unset($data['id']);

		// Because this comes from a form, $data['applyRecurring'] might be 1 for applying purposes, but for creation, we do not this flag
		unset($data['applyRecurring']);

		// Mark the data as createRecurring
		$data['createRecurring'] = true;

		// Determine if this is new recurring event
		$data['isNew'] = $isNew;

		// Manually change the start end time
		$data['startDatetime'] = ES::date($schedule)->toSql();

		// Determine if this recurring event is the last one
		$data['isLastRecurringEvent'] = $isLastRecurringEvent;

		if ($duration) {
			$data['endDatetime'] = ES::date($schedule + $duration)->toSql();
		} else {
			unset($data['endDatetime']);
		}

		$fieldsLib = ES::fields();

		$options = array();
		$options['workflow_id'] = $parentEvent->getWorkflow()->id;
		$options['group'] = SOCIAL_FIELDS_GROUP_EVENT;

		$fields = ES::model('fields')->getCustomFields($options);

		$event = new SocialEvent;
		$event->category_id = $parentEvent->category_id;
		$event->creator_uid = $parentEvent->creator_uid;
		$event->creator_type = SOCIAL_TYPE_USER;
		$event->state = SOCIAL_STATE_PUBLISHED;
		$event->key = md5(ES::date()->toSql() . $this->my->password . uniqid());
		$event->parent_id = $parentEvent->id;
		$event->parent_type = SOCIAL_TYPE_EVENT;

		$isClusterEvent = $parentEvent->isClusterEvent();

		// Determine whether this parent event created from the clusters or not
		if ((!isset($data['group_id']) || !isset($data['page_id'])) && $isClusterEvent) {

			if ($parentEvent->isGroupEvent()) {
				$data['group_id'] = $parentEvent->meta->group_id;
				$event->setMeta('group_id', $data['group_id']);
			}

			if ($parentEvent->isPageEvent()) {
				$data['page_id'] = $parentEvent->meta->page_id;
				$event->setMeta('page_id', $data['page_id']);
			}
		}

		$args = array(&$data, &$event);

		$fieldsLib->trigger('onAdminEditBeforeSave', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);
		$event->bind($data);

		$eventOwner = ES::user($event->creator_uid);

		// Trigger events
		$dispatcher = ES::dispatcher();
		$triggerArgs = array(&$event, &$eventOwner, $isNew);

		// @trigger: onEventBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventBeforeSave', $triggerArgs);

		$event->save();

		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventAfterSave' , $triggerArgs);

		// Duplicate nodes from parent
		ES::model('Events')->duplicateGuests($parentEvent->id, $event->id);

		$args = array(&$data, &$event);
		$fieldsLib->trigger('onAdminEditAfterSave', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);
		$event->bindCustomFields($data);

		$args = array(&$data, &$event);
		$fieldsLib->trigger('onAdminEditAfterSaveFields', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);
		return $this->view->call(__FUNCTION__);
	}
}
