<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialTag extends EasySocial
{
	private $adapter = null;

	// Determines the type id where the tags should appear in
	public $uid = null;

	// Determines the type where the tags should appear in
	public $type = null;

	public function __construct($uid = null, $type = null)
	{
		parent::__construct();

		$this->uid = $uid;
		$this->type = $type;
	}

	public function factory($uid = null, $type = null)
	{
		$obj = new self($uid, $type);

		return $obj;
	}

	/**
	 * Insert a list of tags
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function insert($tags = array(), $type = SOCIAL_TYPE_USER)
	{
		$handler = 'insert' . ucfirst($type);

		return $this->$handler($tags);
	}

	/**
	 * Inserts a list of tagged users
	 *
	 * @since	1.4
	 * @access	public
	 */
	private function insertUser($tags = array(), $authorId = null, $authorType = SOCIAL_TYPE_USER)
	{
		if (!$tags) {
			return false;
		}

		$result = array();

		$author = ES::user($authorId);

		foreach ($tags as $userId) {
			$userId = (int) $userId;

			$table = ES::table('Tag');
			$table->type = 'entity';
			$table->target_id = $this->uid;
			$table->target_type = $this->type;
			$table->item_id = $userId;
			$table->item_type = SOCIAL_TYPE_USER;
			$table->creator_id = $author->id;
			$table->creator_type = $authorType;
			$table->offset = 0;
			$table->length = 0;
			$table->store();

			$result[] = $table;
		}

		return $result;
	}

	/**
	 * Responsible to add the items on the database
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function insertTag($data)
	{
		$table = ES::table('Tag');
		$table->bind($data);

		return $table->store();
	}

	/**
	 * Responsible to add the multiple items on the database
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function insertTags($data)
	{
		if (!$data) {
			return false;
		}

		$options = array('target_id' => $this->uid, 'target_type' => $this->type, 'type' => 'hashtag');

		// Delete any tags that are associated with target id.
		$this->cleanTags($options);

		foreach ($data as $tag) {

			if (!$tag) {
				continue;
			}

			// Remove any whitespace
			$tag = trim($tag);

			// Remove # portions of the string.
			if ($tag[0] == '#') {
				$tag = ltrim($tag, '#');
			}

			$table = ES::table('Tag');
			$table->title = $tag;
			$table->type = 'hashtag';
			$table->target_id = $this->uid;
			$table->target_type = $this->type;
			$table->creator_id = $this->my->id;
			$table->creator_type = SOCIAL_TYPE_USER;

			$length = strlen($tag);
			$table->offset = 0;
			$table->length = $length;

			$table->store();
		}

		return true;
	}

	/**
	 * Cleanup tags
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function cleanTags($options = array())
	{
		$targetId = $this->normalize($options, 'target_id', $this->uid);
		$targetType = $this->normalize($options, 'target_type', $this->type);
		$type = $this->normalize($options, 'type', null);

		if (!$targetId && !$targetType) {
			return;
		}

		$model = ES::model('Tags');

		$model->cleanTags($targetId, $targetType, $type);

		return true;
	}

	/**
	 * Retrieves a list of hashtag filters from the site.
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 */
	public function getFilters($userId, $filterType = 'videos', $cluster = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_tags_filter' );
		$sql->where('user_id', $userId);
		$sql->where('filter_Type', $filterType);

		if ($cluster && $cluster->id) {
			$sql->where('cid', $cluster->id);
		} else {
			$sql->where('cid', 0);
		}

		$db->setQuery($sql);
		$items = $db->loadObjectList();

		if (!$items) {
			return $items;
		}

		$filters = array();

		foreach ($items as $item) {
			$filter = ES::table('TagsFilter');
			$filter->bind($item);

			$permalink = $this->processPermalink($filter, $filterType, $cluster);

			$filter->permalink = $permalink;

			$filters[] = $filter;
		}

		return $filters;
	}

	/**
	 * Process permalink based on the filter type and cluster
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function processPermalink($item, $filterType, $cluster)
	{
		$options = array('hashtagFilterId' => $item->getAlias());

		if ($cluster) {
			$options['uid'] = $cluster->id;
			$options['type'] = $cluster->cluster_type;
		}

		$permalink = ESR::$filterType($options);

		return $permalink;
	}

	/**
	 * Algorithm to save filters tag
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function saveFilters($options = array())
	{
		// permission checking?

		$id = $this->normalize($options, 'id');
		$title = $this->normalize($options, 'title');
		$hashtag = $this->normalize($options, 'hashtag');
		$cid = $this->normalize($options, 'cid');
		$filterType = $this->normalize($options, 'filterType');

		// Load the filter table
		$filter = ES::table('TagsFilter');

		// If id is exist means user are tyring to edit an existing filters
		if ($id) {
			$filter->load($id);
		}

		$cluster = ES::Table('Cluster');

		if ($cid) {
			$cluster->load($cid);
		}

		// Set the filter attributes
		$filter->title = $title;
		$filter->cid = $cluster->id;
		$filter->filter_type = $filterType;
		$filter->cluster_type = $cluster->cluster_type;
		$filter->user_id = $this->my->id;

		$filter->store();

		// Process the hashtags
		if ($hashtag) {
			$hashtag = ESJString::str_ireplace('#', '', $hashtag);
			$hashtag = ESJString::str_ireplace(' ', '', $hashtag);
			$hashtag = str_replace(' ', '', $hashtag);

			// Get the filter item
			$item = ES::table('TagsFilterItem');
			$item->load(array('filter_id' => $filter->id, 'type' => 'hashtag'));

			$item->filter_id = $filter->id;
			$item->type = 'hashtag';
			$item->content = $hashtag;
			$item->store();
		} else {
			$filter->deleteItem('hashtag');
		}

		return true;
	}

	/**
	 * Algorithm to delete the filters
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function deleteFilter($id)
	{
		if (!$id) {
			return false;
		}

		// Load the filter table.
		$filter = ES::table('TagsFilter');

		// Check if the filters is valid
		$filter->load($id);


		if (!$filter->id) {
			return false;
		}

		// If everything is checked out, lets delete the filter
		// Delete filter items
		$filter->deleteItem('hashtag');

		// Delete the filter
		$filter->delete();

		return true;
	}
}
