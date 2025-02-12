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

ES::import('admin:/tables/table');

class SocialTableClusterCategory extends SocialTable
{
	public $id = '';
	public $type = '';
	public $title = null;
	public $alias = null;
	public $description = null;
	public $created = null;
	public $state = null;
	public $uid = null;
	public $ordering = null;
	public $parent_id = null;
	public $lft = null;
	public $rgt = null;
	public $container = null;
	public $params = null;
	public $site_id = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_clusters_categories', 'id', $db);
	}

	public function load($keys = null, $reset = true)
	{
		if (!is_array($keys)) {

			// attempt to get from cache
			$catKey = 'cluster.category.'. $keys;

			if (ES::cache()->exists($catKey)) {
				$state = parent::bind(ES::cache()->get($catKey));
				return $state;
			}
		}

		$state = parent::load($keys, $reset);
		return $state;
	}

	/**
	 * Override parent's store function
	 *
	 * @since  2.1
	 * @access public
	 */
	public function store($updateNulls = null)
	{
		// Store this flag first because there are some actions that we might need to do only after saving, and that point isNew() won't return the correct flag.
		$isNew = $this->isNew();

		// Check alias
		$alias = !empty($this->alias) ? $this->alias : $this->title;
		$alias = JFilterOutput::stringURLSafe($alias);

		$model = ES::model('clusters');

		$i = 2;

		do {
			$aliasExists = $model->clusterCategoryAliasExists($alias, $this->id);

			if ($aliasExists) {
				$alias .= '-' . $i++;
			}
		} while($aliasExists);

		$this->alias = $alias;

		if (empty($this->ordering)) {
			$this->ordering = $this->getNextOrder('type = ' . ES::db()->quote($this->type));
		}

		if (empty($this->created)) {
			$this->created = ES::date()->toSql();
		}

		if (empty($this->uid)) {
			$this->uid = ES::user()->id;
		}

		// Figure out the proper nested set model
		if (!$this->id && !$this->lft) {

			// No parent id, we use the current lft,rgt
			if ($this->parent_id) {
				$left = $this->getLeft($this->parent_id);
				$this->lft = $left;
				$this->rgt = $this->lft + 1;

				// Update parent's right
				$this->updateRight($left);
				$this->updateLeft($left);
			} else {
				$this->lft = $this->getLeft() + 1;
				$this->rgt = $this->lft + 1;
			}
		}

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$this->params) {
			$this->params = '';
		}

		$state = parent::store($updateNulls);

		return $state;
	}

	public function updateLeft($left, $limit = 0)
	{
		$db = ES::db();
		$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' '
				. 'SET ' . $db->nameQuote('lft') . '=' . $db->nameQuote('lft') . ' + 2 '
				. 'WHERE ' . $db->nameQuote('lft') . '>=' . $db->Quote($left)
				. ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

		if (!empty($limit)) {
			$query .= ' and `lft`  < ' . $db->Quote($limit);
		}

		$db->setQuery($query);
		$db->Query();
	}

	public function updateRight($right, $limit = 0)
	{
		$db = ES::db();
		$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' '
				. 'SET ' . $db->nameQuote('rgt') . '=' . $db->nameQuote('rgt') . ' + 2 '
				. 'WHERE ' . $db->nameQuote('rgt') . '>=' . $db->Quote($right)
				. ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

		if (!empty($limit)) {
			$query .= ' and `rgt` < ' . $db->Quote($limit);
		}

		$db->setQuery($query);
		$db->Query();
	}

	public function getLeft($parentId = 0)
	{
		$db = ES::db();

		if ($parentId != 0) {
			$query = 'SELECT `rgt`' . ' '
					. 'FROM ' . $db->nameQuote($this->_tbl) . ' '
					. 'WHERE ' . $db->nameQuote('id') . '=' . $db->Quote($parentId)
					. ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);
		} else {
			$query = 'SELECT MAX(' . $db->nameQuote('rgt') . ') '
					. 'FROM ' . $db->nameQuote($this->_tbl)
					. 'WHERE ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);
		}

		$db->setQuery($query);

		$left = (int) $db->loadResult();

		return $left;
	}

	public function getDepth()
	{
		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_clusters_categories');
		$sql->column('COUNT(id)');
		$sql->where('lft', $this->lft, '<');
		$sql->where('rgt', $this->rgt, '>');
		$sql->where('lft', '0', '!=');
		$sql->where('type', $this->type);
		$db->setQuery($sql);

		$left = (int) $db->loadResult();

		return $left;
	}

	/**
	 * Update the lft value to a particular parent's lft
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateLftValue($parentId)
	{
		$db = ES::db();

		$query = 'SELECT max(lft) from ' . $db->nameQuote($this->_tbl);
		$query .= ' WHERE ' . $db->nameQuote('parent_id') . ' = ' . $db->Quote($parentId);
		$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

		$db->setQuery($query);
		$lft = $db->loadResult();

		if ($lft) {
			$update = "UPDATE " . $db->nameQuote($this->_tbl) . " set `lft` = `lft` + $lft";
			$update .= " where " . $db->nameQuote('lft') . " >= " . $db->Quote($this->lft);
			$update .= " and " . $db->nameQuote('rgt') . " <= " . $db->Quote($this->rgt);
			$update .= " AND " . $db->nameQuote('type') . " = " . $db->Quote($this->type);

			$db->setQuery($update);
			$db->query();
		}

		return true;
	}

	/**
	 * Rebuilding the lft and rgt column for all childs
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function rebuildOrdering($parentId = null, $leftId = 0)
	{
		$db = ES::db();

		$query = 'SELECT `id` from ' . $db->nameQuote($this->_tbl);
		$query .= ' WHERE ' . $db->nameQuote('parent_id') . ' = ' . $db->Quote($parentId);
		$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);
		$query .= ' order by lft';

		$db->setQuery($query);
		$children = $db->loadObjectList();

		// The right value of this node is the left value + 1
		$rightId = $leftId + 1;

		// execute this function recursively over all children
		foreach ($children as $node) {
			// $rightId is the current right value, which is incremented on recursion return.
			// Increment the level for the children.
			// Add this item's alias to the path (but avoid a leading /)
			$rightId = $this->rebuildOrdering($node->id, $rightId);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false) {
				return false;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		$updateQuery = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' set';
		$updateQuery .= ' ' . $db->nameQuote('lft') . ' = ' . $db->Quote($leftId);
		$updateQuery .= ', ' . $db->nameQuote('rgt') . ' = ' . $db->Quote($rightId);
		$updateQuery .= ' where ' . $db->nameQuote('id') . ' = ' . $db->Quote($parentId);
		$updateQuery .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

		$db->setQuery($updateQuery);

		// If there is an update failure, return false to break out of the recursion.
		if (! $db->query()) {
			return false;
		}

		// Return the right value of this node + 1.
		return $rightId + 1;
	}

	/**
	 * Update table's ordering column
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateOrdering()
	{
		$db = ES::db();

		$query = 'SELECT `id` from ' . $db->nameQuote($this->_tbl);
		$query .= ' WHERE ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);
		$query .= ' order by lft';

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows) > 0) {
			$orderNum = '1';

			foreach ($rows as $row) {
				$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' set';
				$query .= ' ' . $db->nameQuote('ordering') . ' = ' . $db->Quote($orderNum);
				$query .= ' WHERE ' . $db->nameQuote('id') . ' = ' . $db->Quote($row->id);
				$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

				$db->setQuery($query);
				$db->query();

				$orderNum++;
			}
		}

		return true;
	}

	/**
	 * Method to assign workflow to this cluster
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function assignWorkflow($workflowId = null)
	{
		// Automatically get default workflow
		if (!$workflowId) {
			$model = ES::model('Workflows');
			$workflows = $model->getWorkflowByType($this->type);

			if (!$workflows) {
				return false;;
			}

			$workflowId = $workflows[0]->id;
		}

		// Assign workflow
		$workflow = ES::workflows($workflowId);
		$workflow->assignWorkflows($this->id, $this->type);
	}

	/**
	 * Removes the category avatar
	 *
	 * @since   1.2
	 * @access  public
	 * @return  bool    Returns the state of the action
	 */
	public function removeAvatar()
	{
		$avatar = ES::Table('Avatar');
		$state = $avatar->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_CLUSTERS));

		if ($state) {
			return $avatar->delete();
		}

		return false;
	}

	/**
	 * Retrieves the ACL for this category
	 *
	 * @since   1.3
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getAcl()
	{
		$acl = ES::access($this->id, SOCIAL_TYPE_CLUSTERS);

		return $acl;
	}

	/**
	 * Retrieves the total number of nodes contained within this category.
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getTotalNodes($options = array())
	{
		static $total = array();

		$index = $this->id;

		if (!isset($total[$index])) {
			$model = ES::model('Clusters');
			$total[$index] = $model->getTotalNodes($this->id , $options);
		}

		return $total[$index];
	}

	/**
	 * Retrieves the permalink of a category
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getAlias()
	{
		return $this->id . ':' . $this->alias;
	}

	/**
	 * Retrieve the permalink of the category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getPermalink($xhtml = true, $external = false)
	{
		$options = array(array('layout' => 'category', 'id' => $this->getAlias()), $xhtml);

		$routerType = 'groups';

		if ($this->type == SOCIAL_TYPE_PAGE) {
			$routerType = 'pages';
		}

		if ($this->type == SOCIAL_TYPE_EVENT) {
			$routerType = 'events';
		}

		if ($external) {
			$options[0]['external'] = true;
		}

		$url = call_user_func_array(array('ESR', $routerType), $options);

		return $url;
	}

	/**
	 * Retrieve the permalink of the category filter page #707
	 * Temporary remove the filter option from this function #4829
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getFilterPermalink($xhtml = true, $external = false, $canonicalOptions = [])
	{
		// #4829
		// $options = array('filter' => 'all' ,'ordering' => 'latest', 'categoryid' => $this->getAlias());
		$options = array('categoryid' => $this->getAlias());

		$routerType = 'groups';

		if ($this->type == SOCIAL_TYPE_PAGE) {
			$routerType = 'pages';
		}

		if ($this->type == SOCIAL_TYPE_EVENT) {
			$routerType = 'events';
			// unset($options['ordering']);
		}

		if ($external) {
			$options['external'] = true;
		}

		if ($canonicalOptions) {
			$options = array_merge($options, $canonicalOptions);
		}

		$url = call_user_func_array(array('ESR', $routerType), array($options, $xhtml));

		return $url;
	}


	/**
	 * Retrieves the title of the category
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getTitle()
	{
		$title = JText::_($this->title);

		return $title;
	}

	/**
	 * Retrieves the description of the category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getDescription()
	{
		$description = JText::_($this->description);

		return $description;
	}

	/**
	 * Gets the sequence from the current index (sequence does not obey published state while index is reordered from published state)
	 *
	 * @since   1.0
	 * @access  public
	 * @param   int     Current index
	 * @param   string  Mode/event to check against
	 * @return  int     The reverse mapped sequence
	 */
	public function getSequenceFromIndex($index, $mode = null)
	{
		$steps = $this->getSteps($mode);

		if (!isset($steps[$index - 1])) {
			return 1;
		}

		return $steps[$index - 1]->sequence;
	}

	/**
	 * Logics to store a profile avatar.
	 *
	 * @since   1.0
	 * @access  public
	 * @author  Mark Lee <mark@stackideas.com>
	 */
	public function uploadAvatar($file)
	{
		$avatar = ES::table('Avatar');
		$state = $avatar->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_CLUSTERS));

		if (!$state) {
			$avatar->uid = $this->id;
			$avatar->type = SOCIAL_TYPE_CLUSTERS;

			$avatar->store();
		}

		// Determine the state of the upload.
		$state = $avatar->upload($file);

		if (!$state) {
			$this->setError(JText::_('COM_EASYSOCIAL_GROUPS_CATEGORY_ERROR_UPLOADING_AVATAR'));
			return false;
		}

		// Store the data.
		$avatar->store();

		return;
	}

	/**
	 * Retrieves the total number of steps for this particular profile type.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getTotalSteps($mode = null)
	{
		static $total = array();

		$totalKey = empty($mode) ? 'all' : $mode;

		if (!isset($total[$totalKey])) {
			$model = ES::model('Fields');
			$total[$totalKey] = $model->getTotalSteps($this->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, $mode);
		}

		return $total[$totalKey];
	}

	/**
	 * Get total cluster based on the cluster type
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalCluster($type, $options = false)
	{
		if ($type == SOCIAL_TYPE_GROUP) {
			$table = ES::table('GroupCategory');
			$table->bind($this);
			return $table->getTotalGroups(array('types' => ES::user()->isSiteAdmin() ? 'all' : 'user'));
		}

		if ($type == SOCIAL_TYPE_PAGE) {
			$table = ES::table('PageCategory');
			$table->bind($this);
			return $table->getTotalPages(array('types' => ES::user()->isSiteAdmin() ? 'all' : 'user'));
		}

		if ($type == SOCIAL_TYPE_EVENT) {
			$cluster = isset($options['cluster']) ? $options['cluster'] : false;

			$table = ES::table('EventCategory');
			$table->bind($this);
			return $table->getTotalEvents(array('ongoing' => true, 'upcoming' => true, 'cluster' => $cluster));
		}
	}

	/**
	 * Checks if this step is valid depending on the mode/event
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function isValidStep($step, $mode = null)
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__social_fields_steps')
			->where('workflow_id', $this->getWorkflow()->id)
			->where('type', SOCIAL_TYPE_CLUSTERS)
			->where('state', 1)
			->where('sequence', $step);

		if (!empty($mode)) {
			$sql->where('visible_' . $mode, 1);
		}

		$db->setQuery($sql);

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Retrieves the list of steps for this particular profile type.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getSteps($type = null)
	{
		// Load language file from the back end as the steps title are most likely being translated.
		JFactory::getLanguage()->load('com_easysocial' , JPATH_ROOT . '/administrator');

		$model = ES::model('Steps');
		$steps = $model->getSteps($this->getWorkflow()->id , SOCIAL_TYPE_CLUSTERS , $type);

		return $steps;
	}

	/**
	 * Check if this profile have avatar uploaded
	 *
	 * @since   1.0
	 * @access  public
	 * @return  bool    True if this profile have avatar uploaded
	 */
	public function hasAvatar()
	{
		$avatar = ES::Table('Avatar');
		$state = $avatar->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_CLUSTERS));

		return (bool) $state;
	}

	/**
	 * Retrieves the profile avatar.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getAvatar($size = SOCIAL_AVATAR_MEDIUM)
	{
		$avatar = ES::Table('Avatar');
		$state = $avatar->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_CLUSTERS));

		if (!$state) {
			return $this->getDefaultAvatar($size);
		}

		return $avatar->getSource($size);
	}

	/**
	 * Bind the access for a category node.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function bindCategoryAccess($type = 'create', $profiles = [])
	{
		// Delete all existing create access for this category first.
		$model = ES::model('ClusterCategory');

		$model->insertAccess($this->id, $type, $profiles);

		return true;
	}

	/**
	 * Bind the props
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function bind($data, $ignore = array())
	{
		// Request the parent to bind the data.
		$state = parent::bind($data, $ignore);

		// Try to see if there's any params being set to the property as an array.
		if (!is_null($this->params) && is_array($this->params)) {

			$registry = ES::registry();

			foreach ($this->params as $key => $value) {
				$registry->set($key, $value);
			}

			// Set the params to a proper string.
			$this->params = $registry->toString();
		}

		return true;
	}

	/**
	 * Binds the access for this group category
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function bindAccess($post)
	{
		if (!is_array($post) || empty($post)) {
			return false;
		}

		// Load up the access table binding.
		$access = ES::table('Access');

		// Try to load the access records.
		$access->load(array('uid' => $this->id, 'type' => SOCIAL_TYPE_CLUSTERS));

		// Load the registry
		$registry = ES::registry($access->params);

		foreach ($post as $key => $value) {
			$key = str_ireplace('_', '.', $key);

			$registry->set($key, $value);
		}

		$access->uid = $this->id;
		$access->type = SOCIAL_TYPE_CLUSTERS;
		$access->params = $registry->toString();

		// Try to store the access item
		if (!$access->store()) {
			$this->setError($access->getError());

			return false;
		}

		return true;
	}


	/**
	 * Retrieves the category access
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function hasAccess($type = 'create', $profileId = 0)
	{
		// Delete all existing create access for this category first.
		$model = ES::model('ClusterCategory');

		$accessible = $model->hasAccess($this->id, $type, $profileId);

		return $accessible;
	}

	/**
	 * Determine if this category has child or not
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function hasSubcategories($profileTypeId = '')
	{
		$model = ES::model('ClusterCategory');

		$subcategories = $model->getChildCategories($this->id, array(), $this->type, array('state' => SOCIAL_STATE_PUBLISHED, 'profileTypeId' => $profileTypeId));

		return empty($subcategories) ? false : true;
	}

	/**
	 * Determine if this category has immediate childs or not.
	 *
	 * @since   3.1.8
	 * @access  public
	 */
	public function hasImmediateCategories($profileTypeId = '')
	{
		$model = ES::model('ClusterCategory');

		$subcategories = $model->getImmediateChildCategories($this->id, $this->type, $profileTypeId);

		return empty($subcategories) ? false : true;
	}

	/**
	 * Retrieves the category access
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getAccess($type = 'create')
	{
		$model = ES::model('ClusterCategory');

		$ids = $model->getAccess($this->id, $type);

		return $ids;
	}

	/**
	 * Override parent's delete behavior
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if (!$state) {
			return false;
		}

		// Delete all existing create access for this category first.
		ES::model('ClusterCategory')->deleteAccess($this->id);

		// Remove workflow association with this cluster category
		ES::workflows()->unassignedWorkflows($this->id, $this->type);

		return $state;
	}

	/**
	 * Retrieves the default avatar for this cluster category
	 *
	 * @since   1.2
	 */
	public function getDefaultAvatar($size = SOCIAL_AVATAR_MEDIUM)
	{
		$app = JFactory::getApplication();

		$file = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/com_easysocial/avatars/clusterscategory/' . $size . '.png';
		$uri = rtrim(JURI::root(), '/') . '/templates/' . $app->getTemplate() . '/html/com_easysocial/avatars/clusterscategory/' . $size . '.png';

		if (JFile::exists($file)) {
			$default = $uri;
		} else {
			$default = rtrim(JURI::root() , '/') . ES::config()->get('avatars.default.clusterscategory.' . $size);
		}

		return $default;
	}

	/**
	 * Check if this record is new.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isNew()
	{
		return empty($this->id);
	}

	/**
	 * Create a blank cluster category.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function createBlank($type)
	{
		// If created date is not provided, we generate it automatically.
		if (is_null($this->created)) {
			$this->created = ES::date()->toMySQL();
		}

		if (is_null($this->alias)) {
			$this->alias = 'temp';
		}

		if (is_null($this->description)) {
			$this->description = 'temp category';
		}

		if (is_null($this->state)) {
			$this->state = 0;
		}

		if (is_null($this->type)) {
			$this->type = $type;
		}


		if (is_null($this->uid)) {
			$this->uid = ES::user()->id;
		}

		// Update ordering column.
		$this->ordering = $this->getNextOrder('type = ' . ES::db()->quote($type));

		// Store the item now so that we can get the incremented category id.
		$state = parent::store();

		return $state;
	}

	/**
	 * Copy avatar if the category is copied from other category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function copyAvatar($targetCategoryId)
	{
		// get avatar from target cluster
		$targetAvatar = ES::table('Avatar');
		$targetAvatar->load(array('uid' => $targetCategoryId, 'type' => SOCIAL_TYPE_CLUSTERS));

		if (!$targetAvatar->id) {
			return false;
		}

		if ($targetAvatar->storage != SOCIAL_STORAGE_JOOMLA) {
			return false;
		}

		$avatar = ES::table('Avatar');
		$avatar->uid = $this->id;
		$avatar->type = SOCIAL_TYPE_CLUSTERS;
		$avatar->photo_id = 0;
		$avatar->small = $targetAvatar->small;
		$avatar->medium = $targetAvatar->medium;
		$avatar->square = $targetAvatar->square;
		$avatar->large = $targetAvatar->large;
		$avatar->modified = ES::date()->toMySQL();
		$avatar->storage = SOCIAL_STORAGE_JOOMLA;
		$avatar->store();

		// lets copy the avatar images.
		$config = ES::config();

		// Get the avatars storage path.
		$avatarsPath = ES::cleanPath($config->get('avatars.storage.container'));

		// Let's construct the final path.
		$sourcePath = JPATH_ROOT . '/' . $avatarsPath . '/clusters/' . $targetCategoryId;
		$targetPath = JPATH_ROOT . '/' . $avatarsPath . '/clusters/' . $this->id;

		if (! JFolder::exists($targetPath)) {
			// now we are save to copy.
			if (JFolder::exists($sourcePath)) {
				JFolder::copy($sourcePath, $targetPath);
			}
		}

		return true;
	}

	/**
	 * Method to retrieve the workflow used by this category
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getWorkflow()
	{
		$workflow = ES::workflows()->getWorkflow($this->id, $this->type);

		// Legacy workflow
		if (!$workflow->id) {
			$worfklow = $this;
		}

		return $workflow;
	}

	public function move($direction, $where = '')
	{
		$db = ES::db();

		if ($direction == -1) {

			$query = 'select `id`, `lft`, `rgt` from ' . $db->nameQuote($this->_tbl) . ' where `lft` < ' . $db->Quote($this->lft);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			if ($this->parent_id == 0) {
				$query .= ' and parent_id = 0';
			} else {
				$query .= ' and parent_id = ' . $db->Quote($this->parent_id);
			}

			$query .= ' order by lft desc limit 1';

			//echo $query;exit;
			$db->setQuery($query);
			$preParent  = $db->loadObject();

			// calculating new lft
			$newLft = $this->lft - $preParent->lft;
			$preLft = (($this->rgt - $newLft) + 1) - $preParent->lft;

			//get prevParent's id and all its child ids
			$query = 'select `id` from ' . $db->nameQuote($this->_tbl);
			$query .= ' where lft >= ' . $db->Quote($preParent->lft) . ' and rgt <= ' . $db->Quote($preParent->rgt);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);

			// echo '<br>' . $query;
			$preItemChilds = $db->loadColumn();
			$preChildIds = implode(',', $preItemChilds);
			$preChildCnt = count($preItemChilds);

			//get current item's id and it child's id
			$query  = 'select `id` from ' . $db->nameQuote($this->_tbl);
			$query  .= ' where lft >= ' . $db->Quote($this->lft) . ' and rgt <= ' . $db->Quote($this->rgt);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);

			$itemChilds = $db->loadColumn();

			$childIds = implode(',', $itemChilds);
			$ChildCnt = count($itemChilds);

			//now we got all the info we want. We can start process the
			//re-ordering of lft and rgt now.
			//update current parent block
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' lft = lft - ' . $db->Quote($newLft);

			if ($ChildCnt == 1) {
				$query  .= ', `rgt` = `lft` + 1';
			} else {
				$query  .= ', `rgt` = `rgt` - ' . $db->Quote($newLft);
			}

			$query .= ' where `id` in (' . $childIds . ')';
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			//echo '<br>' . $query;
			$db->setQuery($query);
			$db->query();

			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' lft = lft + ' . $db->Quote($preLft);
			$query .= ', rgt = rgt + ' . $db->Quote($preLft);
			$query .= ' where `id` in (' . $preChildIds . ')';
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			//echo '<br>' . $query;
			//exit;
			$db->setQuery($query);
			$db->query();

			//now update the ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` - 1';
			$query .= ' where `id` = ' . $db->Quote($this->id);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);
			$db->query();

			//now update the previous parent's ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` + 1';
			$query .= ' where `id` = ' . $db->Quote($preParent->id);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);
			$db->query();

			return true;

		} else {

			// getting next parent
			$query = 'select `id`, `lft`, `rgt` from ' . $db->nameQuote($this->_tbl) . ' where `lft` > ' . $db->Quote($this->lft);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			if ($this->parent_id == 0) {
				$query  .= ' and parent_id = 0';
			} else {
				$query  .= ' and parent_id = ' . $db->Quote($this->parent_id);
			}

			$query .= ' order by lft asc limit 1';

			$db->setQuery($query);
			$nextParent  = $db->loadObject();

			$nextLft = $nextParent->lft - $this->lft;
			$newLft = (($nextParent->rgt - $nextLft) + 1) - $this->lft;

			//get nextParent's id and all its child ids
			$query = 'select `id` from ' . $db->nameQuote($this->_tbl);
			$query .= ' where lft >= ' . $db->Quote($nextParent->lft) . ' and rgt <= ' . $db->Quote($nextParent->rgt);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);
			$db->setQuery($query);

			$nextItemChilds = $db->loadColumn();
			$nextChildIds = implode(',', $nextItemChilds);
			$nextChildCnt = count($nextItemChilds);

			//get current item's id and it child's id
			$query = 'select `id` from ' . $db->nameQuote($this->_tbl);
			$query .= ' where lft >= ' . $db->Quote($this->lft) . ' and rgt <= ' . $db->Quote($this->rgt);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);
			$db->setQuery($query);

			//echo '<br>' . $query;
			$itemChilds = $db->loadColumn();
			$childIds   = implode(',', $itemChilds);

			//update next parent block
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `lft` = `lft` - ' . $db->Quote($nextLft);

			if ($nextChildCnt == 1) {
				$query .= ', `rgt` = `lft` + 1';
			} else {
				$query .= ', `rgt` = `rgt` - ' . $db->Quote($nextLft);
			}

			$query .= ' where `id` in (' . $nextChildIds . ')';
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);
			$db->query();

			//update current parent
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' lft = lft + ' . $db->Quote($newLft);
			$query .= ', rgt = rgt + ' . $db->Quote($newLft);
			$query .= ' where `id` in (' . $childIds . ')';
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);
			$db->query();

			//now update the ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` + 1';
			$query .= ' where `id` = ' . $db->Quote($this->id);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);
			$db->query();

			//now update the previous parent's ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` - 1';
			$query .= ' where `id` = ' . $db->Quote($nextParent->id);
			$query .= ' AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($this->type);

			$db->setQuery($query);
			$db->query();

			return true;
		}
	}

	public function hasPointsToCreate($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		$valid = true;

		$access = ES::access($this->id, SOCIAL_TYPE_CLUSTERS);

		if ($access->get('userpoints.limit', 0)) {

			$points = $user->getPoints();

			if ($points < $access->get('userpoints.threshold')) {
				$valid = false;
			}
		}

		return $valid;
	}

	public function getPointsToCreate($userId = null)
	{
		$user = ES::user($userId);

		$access = ES::access($this->id, SOCIAL_TYPE_CLUSTERS);

		if ($access->get('userpoints.limit', 0)) {
			return $access->get('userpoints.threshold');
		}

		return 0;
	}

	/**
	 * Retrieve the params
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getParams()
	{
		static $registry = array();

		if (!isset($registry[$this->id])) {
			$registry[$this->id] = ES::get('Registry', $this->params);
		}

		return $registry[$this->id];
	}

	/**
	 * Exports cluster category data
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $extended = false)
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$result = array(
			'id' => $this->id,
			'title' => JText::_($this->title),
			'description' => JText::_($this->description),
			'alias' => $this->getAlias()
		);

		if ($extended) {
			$result['author'] = ES::user($this->uid)->toExportData($viewer, false);

			// Get the access data.
			$access	= ES::table('Access');
			$state = $access->load(array('uid' => $this->id, 'type' => SOCIAL_TYPE_CLUSTERS));

			if ($state) {
				$acls = json_decode($access->params);
			} else {
				// Get default acls
				$defaultAcls = ES::access()->getDefaultValues($this->type);
				$acls = $defaultAcls->getData();
			}

			$result['acls'] = $acls;
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}
}
