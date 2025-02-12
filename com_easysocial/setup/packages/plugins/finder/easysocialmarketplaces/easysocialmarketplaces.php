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

class plgFinderEasySocialMarketplaces extends ESFinderIndexerAdapterBase
{
	protected $context = 'EasySocial.Marketplaces';
	protected $extension = 'com_easysocial';
	protected $layout = 'item';
	protected $type_title = 'EasySocial.Marketplaces';
	protected $table = '#__social_marketplaces';

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Ensure that component really exists on the site first
	 *
	 * @since	4.0.0
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
		if ($context == 'easysocial.marketplaces') {
			$id = $table->id;

			$db = ES::db();
			$sql = $db->sql();

			$query = "select `link_id` from `#__finder_links` where `url` like '%option=com_easysocial&view=marketplaces&id=$id%'";
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
		if ($context == 'easysocial.marketplaces' && $row && $row->state == '1') {

			// Reindex the item
			$this->reindex($row->id);
		}

		return true;
	}

	/**
	 * Indexes item on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function proxyIndex($item, $format = 'html')
	{
		if (!$this->exists()) {
			return;
		}

		// Prevent possibility of indexer running more than once.
		static $indexedItems = array();

		// If this was already indexed on the same page request, do not try to index it again.
		if (isset($indexedItems[$item->id])) {
			return;
		}

		$access = 1;

		// album onwer
		$user = ES::user($item->user_id);
		$userAlias = $user->getAlias(false);

		$listing = ES::marketplace($item->id);
		$item->url = 'index.php?option=com_easysocial&view=marketplaces&id=' . $listing->id . '&layout=item';

		$item->route = $listing->getPermalink(true, $item->uid, $item->type, null, false, false);
		$item->route = $this->removeAdminSegment($item->route);

		if (!ES::isJoomla4()) {
			// Get the content path only require in Joomla 3.x
			$item->path = FinderIndexerHelper::getContentPath($item->route);
		}

		$item->access = 1;
		$item->alias = $listing->getAlias();
		$item->state = 1;
		$item->catid = $listing->category_id;
		$item->start_date = $listing->created;
		$item->created_by = $listing->user_id;
		$item->created_by_alias = $userAlias;
		$item->modified = $listing->created;
		$item->modified_by = $listing->user_id;
		$item->params = '';
		$item->metakey = $item->category . ' ' . $listing->title;
		$item->metadesc = $listing->title . ' ' . $listing->description;
		$item->metadata = '';
		$item->publish_start_date = $listing->created;
		$item->category = $item->category;
		$item->cat_state = 1;
		$item->cat_access = 0;

		$item->summary = $listing->title . ' ' . $listing->description;
		$item->body = $listing->title . ' ' . $listing->description;

		// Add the meta-author.
		$item->metaauthor = $userAlias;
		$item->author = $userAlias;

		// add image param
		$registry = ES::registry();
		$registry->set('image', $listing->getSinglePhoto());

		$item->params = $registry;

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'EasySocial.Marketplaces');

		// Add the author taxonomy data.
		$item->addTaxonomy('Author', $userAlias);

		// Add the category taxonomy data.
		if (!$item->category) {
			$item->category = 'Uncategorised';
		}

		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		$item->language = '*';

		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		ESFinderHelper::getContentExtras($item);

		// Cache the indexed item now to prevent multiple storage.
		$indexedItems[$item->id] = true;

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

		$sql->select('a.*, b.title AS category');
		$sql->select('a.id AS ordering');
		$sql->from('#__social_marketplaces AS a');
		$sql->join('INNER', '#__social_marketplaces_categories AS b on a.category_id = b.id');
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
		if ($context === 'easysocial.marketplaces') {

			$db = ES::db();

			$indexedURL = "'%option=com_easysocial&view=marketplaces&id=$id%'";

			$query = 'UPDATE `#__finder_links` SET ' . $db->nameQuote('state') . ' = ' . $db->Quote($value);
			$query .= ' , ' . $db->nameQuote('published') . ' = ' . $db->Quote($value);
			$query .= ' WHERE ' . $db->nameQuote('url') . ' LIKE ' . $indexedURL;

			$db->setQuery($query);
			$db->query();
		}
	}
}
