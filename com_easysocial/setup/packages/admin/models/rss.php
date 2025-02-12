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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelRss extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('rss', $config);
	}

	public function cleanup($contents)
	{
		// Cleanup the contents by ensuring that there's no whitespaces or any funky chars before the xml tag
		$pattern = '/(.*?)<\?xml version/is';
		$replace = '<?xml version';

		$contents = preg_replace($pattern, $replace, $contents, 1);

		// If there's a missing xml definition because some sites are just messed up, manually prepend them
		if (strpos($contents, '<?xml version') === false) {
			$contents = '<?xml version="1.0" encoding="utf-8"?>' . $contents;
		}

		return $contents;
	}

	/**
	 * Format feed items
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function formatItems($parser, $limit)
	{
		$items = [];

		for ($i = 0, $max = min(count($parser), $limit); $i < $max; $i++) {

			$item = $parser[$i];

			// Format the content of the items
			$item->link = $item->uri || !$item->isPermaLink ? trim($item->uri) : trim($item->guid);
			$item->link = !$item->link || stripos($item->link, 'http') !== 0 ? $feed->url : $item->link;
			$item->text = $item->content !== '' ? trim($item->content) : '';

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Retrieves a list of feeds created by a particular user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItems($id, $type)
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_rss');
		$sql->where('uid', $id);
		$sql->where('type', $type);
		$sql->order('created', 'DESC');
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	public function getItem($id)
	{
		$db = $this->db;

		$sql = "SELECT * FROM `#__social_rss` where `id` = " . $db->Quote($id);

		$db->setQuery($sql);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Initializes the parser
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getParser($url)
	{
		// Trim extra spacing in url so that the connector can reach the target url correctly.
		$feedUrl = trim($url);

		// Setup the outgoing connection to the feed source
		$connector = ES::connector($feedUrl);
		$contents = $connector
						->execute()
						->getResult();

		// If contents is empty, we know something failed
		if (!$contents) {
			return ES::response(JText::sprintf('COM_EASYSOCIAL_FEEDS_UNABLE_TO_REACH_TARGET_URL', $feedUrl), ES_ERROR);
		}

		// Get the cleaner to clean things up
		$contents = $this->cleanup($contents);

		// Try to parse the feed to get the description
		$feed = new JFeedFactory();
		$parser = $feed->getFeed($url);

		return $parser;
	}

	/**
	 * Retrieves a list of rss feed added by the user
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getFeedsGDPR($userId, $excludeIds, $limit = 20, $options = array())
	{
		$db = ES::db();
		$query = array();

		$socialFeedsId = array();
		$socialRssId = array();

		if ($excludeIds) {
			foreach ($excludeIds as $id) {
				$key = explode(':', $id);

				if ($key[1] == 'user') {
					$socialFeedsId[] = $key[0];
				} else {
					$socialRssId[] = $key[0];
				}
			}
		}

		// #__social_rss is used by clusters
		$query[] = 'SELECT CONCAT(a.`id`, ":", a.`type`) as idx, a.`user_id`, a.`title`, a.`url`, a.`created`, a.`uid`, a.`type`, c.`title` as actor FROM ' . $db->nameQuote('#__social_rss') . ' AS a';

		$query[] = 'LEFT JOIN ' . $db->nameQuote('#__social_clusters') . ' AS c';
		$query[] = 'ON c.`id` = a.`uid`';

		$query[] = 'WHERE a.`user_id` = ' . $db->Quote($userId);

		if ($socialRssId) {
			$query[] = 'AND a.`id` NOT IN (' . implode(',', $socialRssId) . ')';
		}

		// #__social_feeds is used by user.
		$query[] = 'UNION SELECT CONCAT(b.`id`, ":user") as idx, b.`user_id`, b.`title`, b.`url`, b.`created`, "0" as `uid`, "user" as `type`, "" as actor FROM ' . $db->nameQuote('#__social_feeds') . ' AS b';
		$query[] = 'WHERE b.`user_id` = ' . $db->Quote($userId);

		if ($socialFeedsId) {
			$query[] = 'AND b.`id` NOT IN (' . implode(',', $socialFeedsId) . ')';
		}

		$query[] = 'LIMIT ' . $limit;

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		return $result;
	}
}
