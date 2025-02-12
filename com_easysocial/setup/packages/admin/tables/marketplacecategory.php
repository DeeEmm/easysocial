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
ES::import('admin:/includes/indexer/indexer');

class SocialTableMarketplaceCategory extends SocialTable implements ISocialIndexerTable
{
	public $id = '';
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

	public function __construct($db)
	{
		parent::__construct('#__social_marketplaces_categories', 'id', $db);
	}

	public function syncIndex()
	{
	}

	public function deleteIndex()
	{
	}

	/**
	 * Override parent's store function
	 *
	 * @since  4.0
	 * @access public
	 */
	public function store($updateNulls = null)
	{
		// Check alias
		$alias = !empty($this->alias) ? $this->alias : $this->title;
		$alias = JFilterOutput::stringURLSafe($alias);

		$model = ES::model('Marketplaces');

		$i = 2;

		do {
			$aliasExists = $model->categoryAliasExists($alias, $this->id);

			if ($aliasExists) {
				$alias .= '-' . $i++;
			}
		} while($aliasExists);

		$this->alias = $alias;

		if (empty($this->ordering)) {
			$this->ordering = $this->getNextOrder();
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

		$state = parent::store($updateNulls);

		return $state;
	}

	/**
	 * Bind the props
	 *
	 * @since	4.0
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

	public function updateLeft($left, $limit = 0)
	{
		$db = ES::db();
		$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' '
				. 'SET ' . $db->nameQuote('lft') . '=' . $db->nameQuote('lft') . ' + 2 '
				. 'WHERE ' . $db->nameQuote('lft') . '>=' . $db->Quote($left);

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
				. 'WHERE ' . $db->nameQuote('rgt') . '>=' . $db->Quote($right);

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
					. 'WHERE ' . $db->nameQuote('id') . '=' . $db->Quote($parentId);
		} else {
			$query = 'SELECT MAX(' . $db->nameQuote('rgt') . ') '
					. 'FROM ' . $db->nameQuote($this->_tbl);
		}

		$db->setQuery($query);
		$left = (int) $db->loadResult();

		return $left;
	}

	public function getDepth()
	{
		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_marketplaces_categories');
		$sql->column('COUNT(id)');
		$sql->where('lft', $this->lft, '<');
		$sql->where('rgt', $this->rgt, '>');
		$sql->where('lft', '0', '!=');
		$db->setQuery($sql);

		$left = (int) $db->loadResult();

		return $left;
	}

	/**
	 * Retrieves the category avatar.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getAvatar($size = SOCIAL_AVATAR_MEDIUM)
	{
		$avatar = ES::Table('Avatar');
		$state = $avatar->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_MARKETPLACE));

		if (!$state) {
			return $this->getDefaultAvatar($size);
		}

		return $avatar->getSource($size);
	}

	/**
	 * Removes the category avatar
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function removeAvatar()
	{
		$avatar = ES::Table('Avatar');
		$state = $avatar->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_MARKETPLACE));

		if ($state) {
			return $avatar->delete();
		}

		return false;
	}

	/**
	 * Retrieves the default avatar for this misc category
	 *
	 * @since   4.0
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
	 * Check if this category have avatar uploaded
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function hasAvatar()
	{
		$avatar = ES::Table('Avatar');
		$state = $avatar->load(array('uid' => $this->id , 'type' => SOCIAL_TYPE_MARKETPLACE));

		return (bool) $state;
	}

	/**
	 * Method to retrieve the workflow used by this category
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getWorkflow()
	{
		$workflow = ES::workflows()->getWorkflow($this->id, SOCIAL_TYPE_MARKETPLACE);

		// Legacy workflow
		if (!$workflow->id) {
			$worfklow = $this;
		}

		return $workflow;
	}

	/**
	 * Logics to store a category avatar.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function uploadAvatar($file)
	{
		$avatar = ES::table('Avatar');
		$state = $avatar->load([
			'uid' => $this->id,
			'type' => SOCIAL_TYPE_MARKETPLACE
		]);

		if (!$state) {
			$avatar->uid = $this->id;
			$avatar->type = SOCIAL_TYPE_MARKETPLACE;

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
	 * Update the lft value to a particular parent's lft
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function updateLftValue($parentId)
	{
		$db = ES::db();

		$query = 'SELECT max(lft) from ' . $db->nameQuote($this->_tbl);
		$query .= ' WHERE ' . $db->nameQuote('parent_id') . ' = ' . $db->Quote($parentId);

		$db->setQuery($query);
		$lft = $db->loadResult();

		if ($lft) {
			$update = "UPDATE " . $db->nameQuote($this->_tbl) . " set `lft` = `lft` + $lft";
			$update .= " where " . $db->nameQuote('lft') . " >= " . $db->Quote($this->lft);
			$update .= " and " . $db->nameQuote('rgt') . " <= " . $db->Quote($this->rgt);

			$db->setQuery($update);
			$db->query();
		}

		return true;
	}

	/**
	 * Rebuilding the lft and rgt column for all childs
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function rebuildOrdering($parentId = 0, $leftId = 0)
	{
		$db = ES::db();

		$query = 'SELECT `id` from ' . $db->nameQuote($this->_tbl);
		$query .= ' WHERE ' . $db->nameQuote('parent_id') . ' = ' . $db->Quote($parentId);
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
	 * @since   4.0
	 * @access  public
	 */
	public function updateOrdering()
	{
		$db = ES::db();

		$query = 'SELECT `id` from ' . $db->nameQuote($this->_tbl);
		$query .= ' order by lft';

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows) > 0) {
			$orderNum = '1';

			foreach ($rows as $row) {
				$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' set';
				$query .= ' ' . $db->nameQuote('ordering') . ' = ' . $db->Quote($orderNum);
				$query .= ' WHERE ' . $db->nameQuote('id') . ' = ' . $db->Quote($row->id);

				$db->setQuery($query);
				$db->query();

				$orderNum++;
			}
		}

		return true;
	}

	/**
	 * Copy avatar if the category is copied from other category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function copyAvatar($targetCategoryId)
	{
		// get avatar from target cluster
		$targetAvatar = ES::table('Avatar');
		$targetAvatar->load(array('uid' => $targetCategoryId, 'type' => SOCIAL_TYPE_MARKETPLACE));

		if (!$targetAvatar->id) {
			return false;
		}

		if ($targetAvatar->storage != SOCIAL_STORAGE_JOOMLA) {
			return false;
		}

		$avatar = ES::table('Avatar');
		$avatar->uid = $this->id;
		$avatar->type = SOCIAL_TYPE_MARKETPLACE;
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
		$sourcePath = JPATH_ROOT . '/' . $avatarsPath . '/marketplaces/' . $targetCategoryId;
		$targetPath = JPATH_ROOT . '/' . $avatarsPath . '/marketplaces/' . $this->id;

		if (! JFolder::exists($targetPath)) {
			// now we are save to copy.
			if (JFolder::exists($sourcePath)) {
				JFolder::copy($sourcePath, $targetPath);
			}
		}

		return true;
	}

	/**
	 * Method to assign workflow to this category
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function assignWorkflow($workflowId = null)
	{
		// Automatically get default workflow
		if (!$workflowId) {
			$model = ES::model('Workflows');
			$workflows = $model->getWorkflowByType(SOCIAL_TYPE_MARKETPLACE);

			if (!$workflows) {
				return false;;
			}

			$workflowId = $workflows[0]->id;
		}

		// Assign workflow
		$workflow = ES::workflows($workflowId);
		$workflow->assignWorkflows($this->id, SOCIAL_TYPE_MARKETPLACE);
	}

	/**
	 * Returns the total number of listings in this category.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function getTotalListings($options = [])
	{
		static $total = [];

		$defaultOptions = [
			'state' => SOCIAL_STATE_PUBLISHED,
			'category' => (int) $this->id
		];

		if ($this->container) {
			// Get all child ids from this category
			$categoryModel = ES::model('MarketplaceCategories');
			$childs = $categoryModel->getChildCategories($this->id, [], ['state' => SOCIAL_STATE_PUBLISHED]);

			$childIds = [];

			foreach ($childs as $child) {
				$childIds[] = $child->id;
			}

			if (!empty($childIds)) {
				$defaultOptions['category'] = $childIds;
			}
		}

		if (!isset($options['type'])) {
			$user = ES::user();
			$options['type'] = $user->isSiteAdmin() ? 'all' : 'user';
		}

		// If this is from page/group event listing
		// We need to get a correct count
		if (isset($options['cluster']) && $options['cluster']) {
			$cluster = $options['cluster'];
			$options['uid'] = $cluster->id;
			$options['type'] = $cluster->getType();

			unset($options['cluster']);
		}

		if (isset($options['user']) && $options['user']) {
			$options['uid'] = $options['user'];
			$options['type'] = SOCIAL_TYPE_USER;
		}

		$options = array_merge($defaultOptions, $options);

		ksort($options);
		$key = serialize($options);

		$model = ES::model('Marketplaces');

		if (!isset($total[$this->id][$key])) {
			$total[$this->id][$key] = $model->getTotalItems($options);
		}

		return $total[$this->id][$key];
	}

	/**
	 * Retrieve the permalink for the filter listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getFilterPermalink($xhtml = true)
	{
		$url = ESR::marketplaces(array('categoryid' => $this->getAlias()), $xhtml);
		return $url;
	}

	/**
	 * Retrieves the permalink for a marketplace category
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $uid = null, $type = null)
	{
		$options = array('id' => $this->getAlias());

		if ($uid && $type) {
			$options['uid'] = $uid;
			$options['type'] = $type;

			if (is_numeric($uid) && $type == SOCIAL_TYPE_USER) {
				$user = ES::user($uid);
				$options['uid'] = $user->getAlias();
			}

			if (is_numeric($uid) && $type != SOCIAL_TYPE_USER) {
				$cluster = ES::cluster($type, $uid);
				$options['uid'] = $cluster->getAlias();
			}

		}

		$url = FRoute::marketplaces($options, $xhtml);

		return $url;
	}

	/**
	 * Build's the category alias
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAlias()
	{
		$alias = $this->alias;
		$alias = JFilterOutput::stringURLSafe($alias);

		if (!$alias) {
			$alias = JFilterOutput::stringURLUnicodeSlug($this->title);
		}

		$alias = $this->id . ':' . $alias;
		return $alias;
	}

	/**
	 * Retrieve the title of the category
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTitle()
	{
		return JText::_($this->title);
	}

	/**
	 * Retrieves the category access
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function hasAccess($type = 'create', $profileId = null)
	{
		// Delete all existing create access for this category first.
		$model = ES::model('MarketplaceCategories');

		$accessible = $model->hasAccess($this->id, $type, $profileId);

		return $accessible;
	}

	/**
	 * Gets the sequence from the current index (sequence does not obey published state while index is reordered from published state)
	 *
	 * @since   4.0
	 * @access  public
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
	 * Retrieves the list of steps for this particular profile type.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getSteps($type = null)
	{
		// Load language file from the back end as the steps title are most likely being translated.
		JFactory::getLanguage()->load('com_easysocial' , JPATH_ROOT . '/administrator');

		$model = ES::model('Steps');
		$steps = $model->getSteps($this->getWorkflow()->id , SOCIAL_TYPE_MARKETPLACES, $type);

		return $steps;
	}

	/**
	 * Checks if this step is valid depending on the mode/event
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isValidStep($step, $mode = null)
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__social_fields_steps')
			->where('workflow_id', $this->getWorkflow()->id)
			->where('type', SOCIAL_TYPE_MARKETPLACES)
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
	 * Retrieves the total number of steps for this particular marketplace.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getTotalSteps($mode = null)
	{
		static $total = array();

		$totalKey = empty($mode) ? 'all' : $mode;

		if (!isset($total[$totalKey])) {
			$model = ES::model('Fields');
			$total[$totalKey] = $model->getTotalSteps($this->getWorkflow()->id, SOCIAL_TYPE_MARKETPLACES, $mode);
		}

		return $total[$totalKey];
	}

	/**
	 * For function compatibility
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTotalCluster($type, $options = [])
	{
		$total = (int) $this->getTotalListings($options);

		return $total;
	}

	/**
	 * Determine if this category has immediate childs or not.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function hasImmediateCategories($profileTypeId = '')
	{
		$model = ES::model('MarketplaceCategories');

		$subcategories = $model->getImmediateChildCategories($this->id, $profileTypeId);

		return empty($subcategories) ? false : true;
	}

	/**
	 * Move category ordering
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function move($direction, $where = '')
	{
		$db = ES::db();

		if ($direction == -1) {

			$query = 'select `id`, `lft`, `rgt` from ' . $db->nameQuote($this->_tbl) . ' where `lft` < ' . $db->Quote($this->lft);

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

			$db->setQuery($query);

			// echo '<br>' . $query;
			$preItemChilds = $db->loadColumn();
			$preChildIds = implode(',', $preItemChilds);
			$preChildCnt = count($preItemChilds);

			//get current item's id and it child's id
			$query  = 'select `id` from ' . $db->nameQuote($this->_tbl);
			$query  .= ' where lft >= ' . $db->Quote($this->lft) . ' and rgt <= ' . $db->Quote($this->rgt);

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

			//echo '<br>' . $query;
			$db->setQuery($query);
			$db->query();

			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' lft = lft + ' . $db->Quote($preLft);
			$query .= ', rgt = rgt + ' . $db->Quote($preLft);
			$query .= ' where `id` in (' . $preChildIds . ')';

			//echo '<br>' . $query;
			//exit;
			$db->setQuery($query);
			$db->query();

			//now update the ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` - 1';
			$query .= ' where `id` = ' . $db->Quote($this->id);

			$db->setQuery($query);
			$db->query();

			//now update the previous parent's ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` + 1';
			$query .= ' where `id` = ' . $db->Quote($preParent->id);

			$db->setQuery($query);
			$db->query();

			return true;

		} else {

			// getting next parent
			$query = 'select `id`, `lft`, `rgt` from ' . $db->nameQuote($this->_tbl) . ' where `lft` > ' . $db->Quote($this->lft);

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
			$db->setQuery($query);

			$nextItemChilds = $db->loadColumn();
			$nextChildIds = implode(',', $nextItemChilds);
			$nextChildCnt = count($nextItemChilds);

			//get current item's id and it child's id
			$query = 'select `id` from ' . $db->nameQuote($this->_tbl);
			$query .= ' where lft >= ' . $db->Quote($this->lft) . ' and rgt <= ' . $db->Quote($this->rgt);
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

			$db->setQuery($query);
			$db->query();

			//update current parent
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' lft = lft + ' . $db->Quote($newLft);
			$query .= ', rgt = rgt + ' . $db->Quote($newLft);
			$query .= ' where `id` in (' . $childIds . ')';

			$db->setQuery($query);
			$db->query();

			//now update the ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` + 1';
			$query .= ' where `id` = ' . $db->Quote($this->id);

			$db->setQuery($query);
			$db->query();

			//now update the previous parent's ordering.
			$query = 'update ' . $db->nameQuote($this->_tbl) . ' set';
			$query .= ' `ordering` = `ordering` - 1';
			$query .= ' where `id` = ' . $db->Quote($nextParent->id);

			$db->setQuery($query);
			$db->query();

			return true;
		}
	}

	/**
	 * Retrieves the description of the category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getDescription()
	{
		$description = JText::_($this->description);

		return $description;
	}

	/**
	 * Exports category data
	 *
	 * @since	4.0.0
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
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}

	/**
	 * Retrieves the total number of nodes contained within this category.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getTotalNodes($options = array())
	{
		static $total = array();

		$index = $this->id;

		if (!isset($total[$index])) {
			$model = ES::model('Marketplaces');
			$total[$index] = $model->getTotalNodes($this->id , $options);
		}

		return $total[$index];
	}

	/**
	 * Create a blank category.
	 *
	 * @since   4.0
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


		if (is_null($this->uid)) {
			$this->uid = ES::user()->id;
		}

		// Update ordering column.
		$this->ordering = $this->getNextOrder();

		// Store the item now so that we can get the incremented category id.
		$state = parent::store();

		return $state;
	}
}
