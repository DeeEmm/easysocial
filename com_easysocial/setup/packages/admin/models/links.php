<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelLinks extends EasySocialModel
{
	private $data = null;

	public function __construct($config = array())
	{
		parent::__construct('links', $config);
	}

	/**
	 * Purges the URL cache from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function clear()
	{
		$db = ES::db();

		$sql = $db->sql();
		$sql->delete('#__social_links');

		$db->setQuery($sql);
		return $db->Query();
	}

	/**
	 * Purges the URL cache from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function clearExpired($months)
	{
		$db = ES::db();
		$sql = $db->sql();
		$date = ES::date();

		$query = 'DELETE FROM `#__social_links` WHERE DATE_ADD(`created`, INTERVAL ' . $months . ' MONTH) <= ' . $db->Quote($date->toMySQL());

		$sql->raw($query);

		$db->setQuery($sql);

		return $db->Query();
	}

	/**
	 * Retrieves a list of cached images
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getCachedImages($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_links_images');

		if (isset($options['storage'])) {
			$sql->where('storage', $options['storage']);
		}

		if (isset($options['exclusion']) && !empty($options['exclusion'])) {
			$sql->where('id', $options['exclusion'], 'NOT IN');
		}

		if (isset($options['limit'])) {
			$sql->limit($options['limit']);
		}

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$images = array();

		foreach ($result as $row) {
			$linkImage = ES::table('LinkImage');
			$linkImage->bind($row);

			$images[] = $linkImage;
		}

		return $images;
	}

	/**
	 * Retrieves the list of items which stored in Amazon
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function getLinkImagesStoredExternally($storageType = 'amazon', $options = array())
	{
		// Get the number of files to process at a time
		$config = ES::config();
		$limit = $config->get('storage.amazon.limit', 10);

		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_links_images');
		$sql->where('storage', $storageType);

		// If there's an exclusion list, exclude it
		$exclusion = isset($options['exclusion']) ? $options['exclusion'] :'';

		if (!empty($exclusion)) {

			// Ensure that it's an array
			$exclusion = ES::makeArray($exclusion);

			foreach ($exclusion as $id) {
				$sql->where('id', $id, '!=', 'AND');
			}
		}

		$sql->limit($limit);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves a list of files for a particular stream.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getStreamLink($streamId)
	{
		$db = ES::db();

		$query = "select a.* from `#__social_stream_assets` as a";
		$query .= " where a.`stream_id` = " . $db->Quote($streamId);
		$query .= " and a.`type` = " . $db->Quote('links');

		$db->setQuery($query);
		$result = $db->loadObject();

		$link = ES::json()->decode($result->data);

		$assets = ES::registry($result->data);

		$table = ES::table('Link');
		$table->loadByLink($link->link);

		$link->allowRemoveThumbnail = false;

		// we need to check if this link has image or not.
		$link->images = array();
		if ($link->image) {
			$image = $table->getImage($assets);
			$link->images[] = $image;

			$link->allowRemoveThumbnail = true;
		}

		$link->description = $link->content;
		$link->url = $link->link;

		if (isset($link->oembed->html) && $link->oembed->html) {
			$link->allowRemoveThumbnail = false;
		}

		$link->isEmbedExternalVideo = false;

		if ($table->renderEmbedExternalVideoLink()) {
			$link->isEmbedExternalVideo = true;
			$link->externalVideoProviderEmbed = $table->getOembed();
		}

		// TODO: not sure what is this for but for now we will set to false.
		$link->video = false;

		return $link;
	}

	/**
	 * update link from stream
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateStreamLink($streamId, $data)
	{
		$db = ES::db();
		$config = ES::config();

		$asset = ES::table('StreamAsset');
		$asset->load(array('stream_id' => $streamId, 'type' => 'links'));

		$dataLink = ES::json()->decode($asset->data);

		// determine if this is a new link
		$isNewLink = $data['url'] == $dataLink->link ? false : true;
		$newLink =ES::registry();

		if ($isNewLink) {
			$newLink->set('title', $data['title']);
			$newLink->set('content', $data['description']);
			$newLink->set('image', $data['image']);
			$newLink->set('link', $data['url']);
		} else {
			$newLink->set('title', $data['title']);
			$newLink->set('content', $data['description']);
			$newLink->set('image', $data['image']);
			$newLink->set('link', $dataLink->link);
		}

		$asset->data = $newLink->toString();
		$asset->store();

		return true;
	}

}
