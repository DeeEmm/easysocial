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

class plgFinderEasySocialAudios extends ESFinderIndexerAdapterBase
{
	protected $context = 'EasySocial.Audios';
	protected $extension = 'com_easysocial';
	protected $layout = 'item';
	protected $type_title = 'EasySocial.Audios';
	protected $table = '#__social_audios';

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
		if ($context == 'easysocial.audios') {
			$id = $table->id;

			$db = ES::db();
			$sql = $db->sql();

			$query = "select `link_id` from `#__finder_links` where `url` like '%option=com_easysocial&view=audios&id=$id%'";
			$sql->raw($query);
			$db->setQuery($sql);
			$item = $db->loadResult();

			if ($item) {
				// Index the item.
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
		if ($context == 'easysocial.audios'
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

		// $sql->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body');
		// $sql->select('a.state, a.catid, a.created AS start_date, a.created_by');
		// $sql->select('a.created_by_alias, a.modified, a.modified_by, a.attribs AS params');
		// $sql->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.version, a.ordering');
		// $sql->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
		// $sql->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');

		// audio onwer
		$user = ES::user($item->user_id);
		$userAlias = $user->getAlias(false);

		$audioTbl = ES::table('Audio');
		$audioTbl->load($item->id);

		$clusterId = $audioTbl->type != SOCIAL_TYPE_USER ? $audioTbl->uid : 0;
		$clusterType = $audioTbl->type != SOCIAL_TYPE_USER ? $audioTbl->type : '';
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
				$privacyValue = $privacy->getValue('audios', 'view');
				$item->privacy = $privacyValue;
			}

			if ($item->privacy == SOCIAL_PRIVACY_PUBLIC) {
				$access = 1;
			} else if ($item->privacy == SOCIAL_PRIVACY_MEMBER) {
				$access = 2;
			} else {
				// this is not public / member items. do not index this item
				// also we need to delete this item if there is indexed previously.
				$audioTbl = ES::table('Audio');
				$audioTbl->load($item->id);
				$this->onFinderAfterDelete('easysocial.audios', $audioTbl);
				return;
			}
		}

		$audio = ES::audio($audioTbl->uid, $audioTbl->type, $audioTbl->id);

		// Build the necessary route and path information.
		// index.php?option=com_easysocial&view=audios&id=7&layout=item&Itemid=799

		$item->url = 'index.php?option=com_easysocial&view=audios&id=' .  $audio->id . '&layout=item';

		$item->route = $audio->getPermalink(true, null, null, false, false, false);
		$item->route = $this->removeAdminSegment($item->route);

		if (!ES::isJoomla4()) {
			// Get the content path only require in Joomla 3.x
			$item->path = FinderIndexerHelper::getContentPath($item->route);
		}

		$item->access = $access;
		$item->alias = JFilterOutput::stringURLSafe($item->title);
		$item->state = 1;
		$item->catid = $audio->genre_id;
		$item->start_date = $audio->created;
		$item->created_by = $audio->user_id;
		$item->created_by_alias	= $userAlias;
		$item->modified = $audio->created;
		$item->modified_by = $audio->user_id;
		$item->params = '';
		$item->metakey = $item->genre . ' ' . $audio->title;
		$item->metadesc = $audio->title . ' ' . $audio->description;
		$item->metadata = '';
		$item->publish_start_date = $audio->created;
		$item->genre = $item->genre;
		$item->cat_state = 1;
		$item->cat_access = 0;

		$item->summary = $audio->title . ' ' . $audio->description;
		$item->body = $audio->title . ' ' . $audio->description;

		// Add the meta-author.
		$item->metaauthor = $userAlias;
		$item->author = $userAlias;

		// add image param
		$registry = ES::registry();
		$registry->set('image', $audio->getAlbumArt());

		$item->params = $registry;

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'EasySocial.Audios');

		// Add the author taxonomy data.
		$item->addTaxonomy('Author', $userAlias);

		// Add the genre taxonomy data.
		$item->addTaxonomy('Genre', $item->genre, $item->cat_state, $item->cat_access);

		// Add the language taxonomy data.
		// $langParams 	= JComponentHelper::getParams('com_languages');
		// $item->language = $langParams->get('site', 'en-GB');

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

		$sql->select('a.*, b.title AS genre');
		$sql->select('a.id AS ordering');
		$sql->select('c.value AS privacy');
		$sql->from('#__social_audios AS a');
		$sql->join('INNER', '#__social_audios_genres AS b on a.genre_id = b.id');
		$sql->join('LEFT', '#__social_privacy_items AS c ON a.id = c.uid and c.type = ' . $db->Quote('audios'));
		$sql->where('a.state = 1');

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
		if ($context === 'easysocial.audios') {

			$db = ES::db();

			$indexedURL = "'%option=com_easysocial&view=audios&id=$id%'";

			$query = 'UPDATE `#__finder_links` SET ' . $db->nameQuote('state') . ' = ' . $db->Quote($value);
			$query .= ' , ' . $db->nameQuote('published') . ' = ' . $db->Quote($value);
			$query .= ' WHERE ' . $db->nameQuote('url') . ' LIKE ' . $indexedURL;

			$db->setQuery($query);
			$db->query();
		}
	}
}
