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

defined('JPATH_BASE') or die;

jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class plgFinderEasySocialPhotos extends ESFinderIndexerAdapterBase
{
	protected $context = 'EasySocial.Photos';
	protected $extension = 'com_easysocial';
	protected $layout = 'item';
	protected $type_title = 'EasySocial.Photos';
	protected $table = '#__social_photos';

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
		if ($context == 'easysocial.photos') {
			$id = $table->id;

			$db = ES::db();
			$sql = $db->sql();

			$query = "select `link_id` from `#__finder_links` where `url` like '%option=com_easysocial&view=photos&layout=item&id=$id%'";
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
		if ($context == 'easysocial.photos' && $row && $row->type != SOCIAL_TYPE_MARKETPLACE) {
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

		// photo onwer
		$user = ES::user($item->user_id);
		$userAlias = $user->getAlias();

		$photo = ES::table('Photo');
		$photo->load($item->id);

		// We dont want to index marketplace photo
		if ($photo->type == SOCIAL_TYPE_MARKETPLACE) {
			return;
		}

		$album = ES::table('album');
		$album->load($photo->album_id);

		// Exclude password protected album
		if ($album->id && trim($album->password) != '') {
			return;
		}

		$clusterId = $photo->type != SOCIAL_TYPE_USER ? $photo->uid : 0;
		$clusterType = $photo->type != SOCIAL_TYPE_USER ? $photo->type : '';
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
				$privacyValue = $privacy->getValue('photos', 'view');
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

		// Build the necessary route and path information.
		// index.php?option=com_easysocial&view=photos&layout=item&id=510:00000690&type=user&uid=84:jenny-siew

		// $item->url = 'index.php?option=com_easysocial&view=photos&layout=item&id=' . $photo->getAlias() . '&type=' . $photo->type;

		// if ($photo->isCluster()) {
		// 	$item->url .= '&uid=' . ES::cluster($photo->type, $photo->uid)->getAlias();
		// } else {
		// 	$item->url .= '&uid=' . $userAlias;
		// }

		$item->url = 'index.php?option=com_easysocial&view=photos&layout=item&id=' . $photo->id;

		$item->route = $photo->getPermalink(true, false, 'item', false);
		$item->route = $this->removeAdminSegment($item->route);

		if (!ES::isJoomla4()) {
			// Get the content path only require in Joomla 3.x
			$item->path = FinderIndexerHelper::getContentPath($item->route);
		}

		$category = 'user photo';
		if ($item->type == SOCIAL_TYPE_GROUP) {
			$category = 'group photo';
		}

		$item->access = $access;
		$item->alias = $photo->getAlias();
		$item->state = 1;
		$item->catid = $photo->type == SOCIAL_TYPE_GROUP ? 2 : 1 ;
		$item->start_date = $photo->created;
		$item->created_by = $photo->user_id;
		$item->created_by_alias = $userAlias;
		$item->modified = $photo->assigned_date == '0000-00-00 00:00:00' ? $photo->created : $photo->assigned_date;
		$item->modified_by = $photo->user_id;
		$item->params = '';
		$item->metakey = $category . ' ' . $photo->title;
		$item->metadesc = $category . ' ' . $photo->title;
		$item->metadata = '';
		$item->publish_start_date = $item->modified;
		$item->category = $category;
		$item->cat_state = 1;
		$item->cat_access = 0;

		$item->summary = $photo->title;
		$item->body = $photo->title;

		// Add the meta-author.
		$item->metaauthor = $userAlias;
		$item->author = $userAlias;

		// add image param
		$registry = ES::registry();
		$registry->set('image' , $photo->getSource());

		$item->params = $registry;

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'EasySocial.Photos');

		// Add the author taxonomy data.

		$item->addTaxonomy('Author', $userAlias);

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

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

	private function removeAdminSegment($url = '')
	{
		if ($url) {
			$url = ltrim($url , '/');
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
	 * @param   mixed  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : $db->getQuery(true);

		$sql->select('a.*, b.value AS privacy');
		$sql->select('a.id AS ordering');
		$sql->from('#__social_photos AS a');
		$sql->join('LEFT', '#__social_privacy_items AS b ON a.id = b.uid and b.type = ' . $db->Quote('photos'));

		return $sql;
	}
}
