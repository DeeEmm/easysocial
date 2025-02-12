<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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

class plgFinderEasySocialVideos extends ESFinderIndexerAdapterBase
{
	protected $context = 'EasySocial.Videos';
	protected $extension = 'com_easysocial';
	protected $layout = 'item';
	protected $type_title = 'EasySocial.Videos';
	protected $table = '#__social_videos';

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
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'easysocial.videos') {
			$id = $table->id;

			$db = ES::db();
			$sql = $db->sql();

			$query = "select `link_id` from `#__finder_links` where `url` like '%option=com_easysocial&view=videos&id=$id%'";
			$sql->raw($query);
			$db->setQuery($sql);
			$item = $db->loadResult();

			if ($item) {
				// Index the item.
				if( ES::isJoomla30() ) {
					$this->indexer->remove($item);
				} else {
					FinderIndexer::remove( $item );
				}
			}

			return true;

		} elseif ($context == 'com_finder.index') {
			$id = $table->link_id;
		} else {
			return true;
		}

		// Remove the items.
		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		if (!$this->exists()) {
			return;
		}

		// We only want to handle articles here
		if ($context == 'easysocial.videos'
			&& $row
			&& $row->state == '1') {
			// Reindex the item
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

		$videoTbl = ES::table('Video');
		$videoTbl->load($item->id);

		$clusterId = $videoTbl->type != SOCIAL_TYPE_USER ? $videoTbl->uid : 0;
		$clusterType = $videoTbl->type != SOCIAL_TYPE_USER ? $videoTbl->type : '';
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
				$privacyValue = $privacy->getValue('videos', 'view');
				$item->privacy = $privacyValue;
			}

			if ($item->privacy == SOCIAL_PRIVACY_PUBLIC) {
				$access = 1;
			} else if ($item->privacy == SOCIAL_PRIVACY_MEMBER) {
				$access = 2;
			} else {
				// this is not public / member items. do not index this item

				// also we need to delete this item if there is indexed previously.
				$videoTbl = ES::table('Video');
				$videoTbl->load($item->id);

				$this->onFinderAfterDelete('easysocial.videos', $videoTbl);
				return;
			}

		}

		$video = ES::video($videoTbl->uid, $videoTbl->type, $videoTbl->id);

		// Build the necessary route and path information.
		// index.php?option=com_easysocial&view=videos&id=7&layout=item&Itemid=799

		$item->url = 'index.php?option=com_easysocial&view=videos&id=' . $video->id . '&layout=item';

		$item->route = $video->getPermalink(true, null, null, false, false, false);
		$item->route = $this->removeAdminSegment($item->route);

		if (!ES::isJoomla4()) {
			// Get the content path only require in Joomla 3.x
			$item->path = FinderIndexerHelper::getContentPath($item->route);
		}

		$item->access = $access;
		$item->alias = JFilterOutput::stringURLSafe($item->title);
		$item->state = 1;
		$item->catid = $video->category_id;
		$item->start_date = $video->created;
		$item->created_by = $video->user_id;
		$item->created_by_alias	= $userAlias;
		$item->modified	 = $video->created;
		$item->modified_by = $video->user_id;
		$item->params = '';
		$item->metakey = $item->category . ' ' . $video->title;
		$item->metadesc = $video->title . ' ' . $video->description;
		$item->metadata = '';
		$item->publish_start_date = $video->created;
		$item->category = $item->category;
		$item->cat_state = 1;
		$item->cat_access = 0;

		$item->summary = $video->title . ' ' . $video->description;
		$item->body = $video->title . ' ' . $video->description;

		// Add the meta-author.
		$item->metaauthor = $userAlias;
		$item->author = $userAlias;

		// add image param
		$registry = ES::registry();
		$registry->set('image', $video->getThumbnail());

		$item->params = $registry;

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'EasySocial.Videos');

		// Add the author taxonomy data.

		$item->addTaxonomy('Author', $userAlias);

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		// Add the language taxonomy data.
		// $langParams 	= JComponentHelper::getParams('com_languages');
		// $item->language = $langParams->get( 'site', 'en-GB');

		$item->language = '*';

		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		ESFinderHelper::getContentExtras($item);

		// Index the item.
		if (ES::isJoomla30()) {
			$this->indexer->index($item);
		} else {
			FinderIndexer::index($item);
		}
	}

	/**
	 * Remove admin segments from the url
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function removeAdminSegment($url = '')
	{
		if ($url) {
			$url = ltrim( $url , '/' );
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
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @since	2.1
	 * @access	public
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : $db->getQuery(true);

		$sql->select( 'a.*, b.title AS category');
		$sql->select('a.id AS ordering');
		$sql->select('c.value AS privacy');
		$sql->from('#__social_videos AS a');
		$sql->join( 'INNER', '#__social_videos_categories AS b on a.category_id = b.id');
		$sql->join('LEFT', '#__social_privacy_items AS c ON a.id = c.uid and c.type = ' . $db->Quote('videos'));
		$sql->where( 'a.state = 1');

		return $sql;
	}

	/**
	 * Method to change the item state from the #__finder_links table during publish/unpublish action
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onFinderChangeState($context, $id, $value)
	{
		if ($context === 'easysocial.videos') {

			$db = ES::db();

			$indexedURL = "'%option=com_easysocial&view=videos&id=$id%'";

			$query = 'UPDATE `#__finder_links` SET ' . $db->nameQuote('state') . ' = ' . $db->Quote($value);
			$query .= ' , ' . $db->nameQuote('published') . ' = ' . $db->Quote($value);
			$query .= ' WHERE ' . $db->nameQuote('url') . ' LIKE ' . $indexedURL;

			$db->setQuery($query);
			$db->query();
		}
	}
}
