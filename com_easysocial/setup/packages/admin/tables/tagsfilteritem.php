<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

/**
 * Object relation mapping for tags filter.
 *
 * @since	2.0
 */
class SocialTableTagsFilterItem extends SocialTable
{
	/**
	 * The unique id.
	 * @var	int
	 */
	public $id = null;

	/**
	 * FK to stream_fitler.id
	 * @var	int
	 */
	public $filter_id = null;

	/**
	 * Filter type - hashtag, mention
	 * @var	string
	 */
	public $type = null;

	/**
	 * content
	 * @var	int
	 */
	public $content = null;


	/**
	 * Class Constructor.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function __construct($db)
	{
		parent::__construct('#__social_tags_filter_item', 'id', $db);
	}
}