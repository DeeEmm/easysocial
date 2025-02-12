<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

// Include main model file.
ES::import( 'admin:/includes/model' );

class EasySocialModelHashtags extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct( 'hashtags' );
	}

	/**
	 * Searches for a particular hash tag given the current keyword
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function search($keyword, $type = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		if ($type) {
			return $this->searchTags($keyword, $type);
		}

		return $this->searchStream($keyword);
	}

	/**
	 * Search hashtags streams
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function searchStream($keyword)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream_tags' , 'a');
		$sql->where('a.utype', 'hashtag');
		$sql->where('a.title', '%' . $keyword . '%', 'LIKE');
		$sql->group('a.title');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;		
	}

	/**
	 * Search hashtags in tags table base on type given
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function searchTags($keyword, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_tags', 'a');
		$sql->where('a.type', 'hashtag');
		$sql->where('a.target_type', $type);
		$sql->where('a.title', '%' . $keyword . '%', 'LIKE');
		$sql->group('a.title');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieve a list of popular tags
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function getPopularTags($options = array())
	{
		$db = ES::db();
		$query = array();

		$limit = isset($options['total']) && $options['total'] ? $options['total'] : '';

		$query[] = 'SELECT a.`title`, COUNT(a.`stream_id`) AS `post_count`';
		$query[] = 'FROM (';
		$query[] = 'SELECT DISTINCT `stream_id`, `title`, `length`';
		$query[] = 'FROM ' . $db->nameQuote('#__social_stream_tags');
		$query[] = 'WHERE ' . $db->nameQuote('utype') . '=' . $db->Quote('hashtag');
		$query[] = ') AS a';
		$query[] = 'GROUP BY a.' . $db->nameQuote('title') . ', a.' . $db->nameQuote('length');
		$query[] = 'ORDER BY `post_count` DESC';

		if ($limit) {
			$query[] = 'LIMIT ' . $limit;
		}

		$query = implode(' ', $query);
		$db->setQuery($query);

		$result = $db->loadObjectList();
		
		return $result;
	}

	/**
	 * Delete hashtag related to the stream
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function delete($streamId, $type = 'hashtag')
	{
		$db = ES::db();
		$query = array();

		$query[] = 'DELETE FROM ' . $db->nameQuote('#__social_stream_tags');
		$query[] = 'WHERE ' . $db->nameQuote('stream_id') . ' = ' . $db->Quote($streamId);
		$query[] = 'AND ' . $db->nameQuote('utype') . ' = ' . $db->Quote($type);

		$query = implode(' ', $query);
		$db->setQuery($query);
		$db->Query();

		return true;
	}
}
