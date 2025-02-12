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

ES::import('admin:/tables/table');

class SocialTableTagsFilter extends SocialTable
{
	public $id = null;
	public $cid = null;
	public $filter_type = null;
	public $cluster_type = null;
	public $title = null;
	public $alias = null;

	public function __construct($db)
	{
		parent::__construct('#__social_tags_filter', 'id', $db);
	}

	/**
	 * Override parent's store function
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		// Generate an alias for this filter if it is empty.
		if (empty($this->alias)) {
			$alias = $this->title;
			$alias = JFilterOutput::stringURLSafe($alias);
			$tmp = $alias;

			$i = 1;

			while ($this->aliasExists($alias)) {
				$alias = $tmp . '-' . $i;
				$i++;
			}

			$this->alias = $alias;
		}

		$state = parent::store($updateNulls);
	}

	/**
	 * Checks the database to see if there are any same alias
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function aliasExists($alias)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_tags_filter');
		$sql->column('COUNT(1)', 'total');
		$sql->where('alias', $alias);

		$db->setQuery($sql);

		$exists = $db->loadResult() > 0 ? true : false;

		return $exists;
	}

	/**
	 * Retrieves the alias of this filter
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlias()
	{
		$alias = $this->id . '-' . $this->alias;

		return $alias;
	}

	/**
	 * Method to delete the cached sef alias when item being removed.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function deleteSEFCache()
	{
		$alias = $this->getAlias();
		$state = ESR::deleteSEFCache($this, $alias);

		return $state;
	}

	public function getHashTag($display = false)
	{
		if (! $this->id) {
			return '';
		}

		$filterItem = ES::table('TagsFilterItem');
		$filterItem->load(array('filter_id' => $this->id, 'type' => 'hashtag'));

		if ($display) {
			//for display
			$filterItem->content = str_replace(',', ', #', $filterItem->content);
			$filterItem->content = '#' . $filterItem->content;

			return $filterItem->content;
		} else {
			return $filterItem->content;
		}
	}

	/**
	 * Deprecated. See ES::tagFilter()->getLinks
	 *
	 * @deprecated		3.0
	 */
	public function getHashtagsLink($hashtags, $type, $clusterId = null, $clusterType = null)
	{
		return ES::tagFilter()->getLinks($hashtags, $type, $clusterId, $clusterType);
	}

	/**
	 * Get edit filter permalink
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getEditLink()
	{
		$options = array('filter' => 'filterForm', 'id' => $this->id);

		$filterType = $this->filter_type;

		if ($this->cid) {

			$options['uid'] = $this->cid;

			// Check for cluster
			$cluster = array('group', 'event', 'page');

			if (in_array($this->cluster_type, $cluster)) {
				$cluster = ES::cluster($this->cluster_type, $this->cid);

				$options['uid'] = $cluster->getAlias();
				$options['type'] = $cluster->cluster_type;
			}
		}

		$editLink = ESR::$filterType($options);

		return $editLink;
	}

	public function deleteItem($type = '')
	{
		if (! $this->id)
			return;

		$db = ES::db();
		$sql = $db->sql();

		$query = 'delete from `#__social_tags_filter_item` where `filter_id` = ' . $db->Quote($this->id);

		if ($type) {
			$query .= ' and `type` = ' . $db->Quote($type);
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$db->query();

		return true;
	}
}
