<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( 'JPATH_BASE' ) or die( 'Unauthorized Access' );

ES::import( 'admin:/tables/table' );

class SocialTableStreamTags extends SocialTable
{
	public $id			= null;
	public $stream_id	= null;

	/**
	 * This stores the unique item id of the item that is being tagged.
	 * @var int
	 */
	public $uid			= null;

	/**
	 * This stores the unique item type of the item that is being tagged
	 * @var string
	 */
	public $utype		= null;

	/**
	 * This determines if the tagged item is a "with" option.
	 * @var bool
	 */
	public $with 		= null;

	/**
	 * This stores the offset of the item that needs to be replaced
	 * @var int
	 */
	public $offset 		= null;

	/**
	 * This stores the length of the string of the item that needs to be replaced
	 * @var int
	 */
	public $length 		= null;

	/**
	 * This stores the hashtag title if it's a hastag tag.
	 * @var string
	 */
	public $title 		= null;

	/**
	 * This stores the state of the tags; 1 = published, 4 = scheduled.
	 * @var string
	 */
	public $state = null;

	public function __construct( $db )
	{
		parent::__construct('#__social_stream_tags', 'id', $db);
	}

	public function toJSON()
	{
		return array(
			'id' => $this->id,
			'stream_id' => $this->stream_id,
			'uid' => $this->uid,
			'utype' => $this->utype,
			'with' => $this->with,
			'offset' => $this->offset,
			'title' => $this->title,
			'length' => $this->length,
			'state' => $this->state
		 );
	}

	/**
	 * Load stream tags by title
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function loadByTitle($title)
	{
		$db = ES::db();
		$query = 'SELECT * FROM ' . $db->nameQuote($this->_tbl);
		$query .= ' WHERE ' . $db->nameQuote('title') . '=' . $db->Quote($title);

		$db->setQuery($query);
		$data = $db->loadObject();

		return parent::bind($data);
	}
}
