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

jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgFinderEasySocialAlbums extends ESFinderIndexerAdapterBase
{
	protected $context = 'EasySocial.Albums';
	protected $extension = 'com_easysocial';
	protected $layout = 'item';
	protected $type_title = 'EasySocial.Albums';
	protected $table = '#__social_albums';

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Ensure that component really exists on the site first
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function exists()
	{
		// First we check if the extension is enabled.
		if (ESComponentHelper::isEnabled($this->extension) == false) {
			return;
		}

		$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';

		jimport('joomla.filesystem.file');

		if (!JFile::exists($file)) {
			return false;
		}

		require_once($file);

		return true;
	}

	/**
	 * Method to remove the link information for items that have been deleted
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'easysocial.albums') {
			$id = $table->id;

			$db = ES::db();
			$sql = $db->sql();

			$query = "select `link_id` from `#__finder_links` where `url` like '%option=com_easysocial&view=albums&id=$id%'";
			$sql->raw($query);
			$db->setQuery($sql);
			$item = $db->loadResult();

			if ($item) {
				if (ES::isJoomla30()) {
					$this->indexer->remove($item);
				} else {
					FinderIndexer::remove($item);
				}
			}

			return true;
		} elseif ($context == 'com_finder.index') {
			$id = $table->link_id;
		} else {
			return true;
		}

		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		if (!$this->exists()) {
			return;
		}

		// Reindex the item for albums context only
		if ($context == 'easysocial.albums' && $row && empty($row->core) && $row->type != SOCIAL_TYPE_MARKETPLACE) {
			$this->reindex($row->id);
		}

		return true;
	}

	/**
	 * Indexes item on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	protected function proxyIndex($item, $format = 'html')
	{
		if (!$this->exists() || !$item->id) {
			return;
		}

		// album onwer
		$user = ES::user($item->user_id);
		$userAlias = $user->getAlias(false);

		$album = ES::table('Album');
		$album->load($item->id);

		// We dont want to index marketplace album
		if ($album->type == SOCIAL_TYPE_MARKETPLACE) {
			return;
		}

		// Exclude password protected album
		if ($album->id && trim($album->password) != '') {
			return;
		}

		$clusterId = $album->type != SOCIAL_TYPE_USER ? $album->uid : 0;
		$clusterType = $album->type != SOCIAL_TYPE_USER ? $album->type : '';
		$isCluster = $clusterId && $clusterType ? true : false;

		$access = 1;

		if ($isCluster) {
			$cluster = ES::table('cluster');
			$cluster->load($clusterId);

			// if this content is belong to cluster, we will need to respect the cluster type instead.
			if ($cluster->type == SOCIAL_GROUPS_PRIVATE_TYPE || $cluster->type == SOCIAL_GROUPS_INVITE_TYPE) {
				// media from private clusters should not be indexed. #4687
				return;
			}

		} else {

			if (is_null($item->privacy)) {
				$privacy = ES::privacy($item->user_id);
				$privacyValue = $privacy->getValue('albums', 'view');
				$item->privacy = $privacyValue;
			}

			if ($item->privacy == SOCIAL_PRIVACY_PUBLIC) {
				$access = 1;
			} else if ($item->privacy == SOCIAL_PRIVACY_MEMBER) {
				$access = 2;
			} else {
				// this is not public / member items. do not index this item
				return;
			}
		}

		$albumAlias = $album->getAlias();

		// Build the necessary route and path information.
		// we need to pass in raw url so that smart search will not create another duplicate item.
		// index.php?option=com_easysocial&view=albums&id=171:collection&layout=item&uid=84:jenny-siew&type=user
		// $item->url = 'index.php?option=com_easysocial&view=albums&id=' . $albumAlias . '&layout=item&uid=' . $typeAlias . '&type=' . $album->type;
		$item->url = 'index.php?option=com_easysocial&view=albums&id=' . $album->id . '&layout=item';

		$item->route = $album->getPermalink(true, false, 'item', false);
		$item->route = $this->removeAdminSegment($item->route);

		if (!ES::isJoomla4()) {
			// Get the content path only require in Joomla 3.x
			$item->path = FinderIndexerHelper::getContentPath($item->route);
		}

		$category = '';
		if ($album->core == SOCIAL_ALBUM_PROFILE_PHOTOS) {
			$category = 'avatar album';
		} else if ($album->core == SOCIAL_ALBUM_PROFILE_COVERS) {
			$category = 'cover album';
		} else if ($album->core == SOCIAL_ALBUM_STORY_ALBUM) {
			$category = 'story album';
		} else {
			$category = 'user album';
		}

		$item->access = $access;
		$item->alias = $albumAlias;
		$item->state = 1;
		$item->catid = $album->core;
		$item->start_date = $album->created;
		$item->created_by = $album->user_id;
		$item->created_by_alias = $userAlias;
		$item->modified = $album->assigned_date;
		$item->modified_by = $album->user_id;
		$item->params = '';
		$item->metakey = $category . ' ' . $album->title;
		$item->metadesc = $album->title . ' ' . $album->caption;
		$item->metadata = '';
		$item->publish_start_date = $album->assigned_date;
		$item->category = $category;
		$item->cat_state = 1;
		$item->cat_access = 0;

		$item->summary = empty($album->caption) ? $album->title : $album->caption;
		$item->body = $album->title . ' ' . $album->caption;

		// Add the meta-author.
		$item->metaauthor = $userAlias;
		$item->author = $userAlias;

		// add image param
		$registry = ES::registry();
		$registry->set('image', $album->getCover());

		$item->params = $registry;

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'EasySocial.Albums');

		// Add the author taxonomy data.
		$item->addTaxonomy('Author', $userAlias);

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		// Add the language taxonomy data.
		$item->language = '*';
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		ESFinderHelper::getContentExtras($item);

		// Index the item.
		if (ES::isJoomla30()) {
			$this->indexer->index($item);
		} else {
			FinderIndexer::remove($item);
		}
	}

	private function removeAdminSegment($url = '')
	{
		if ($url) {
			$url = ltrim($url, '/');
			$url = str_replace('administrator/', '', $url);
		}

		return $url;
	}

	/**
	 * Method to setup the indexer to be run
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	protected function setup()
	{
		if (!$this->exists()) {
			return false;
		}

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();

		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : $db->getQuery(true);

		$sql->select('a.*, b.value AS privacy');
		$sql->select('a.id AS ordering');
		$sql->from('#__social_albums AS a');
		$sql->join('LEFT', '#__social_privacy_items AS b ON a.id = b.uid and b.type = ' . $db->Quote('albums'));
		$sql->where('a.core = 0');

		return $sql;
	}
}
