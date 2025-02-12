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

ES::import( 'admin:/tables/table' );

/**
 * Object mapping for `#__social_discussions_files` table.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.2
 */
class SocialTableDiscussionFile extends SocialTable
{
	/**
	 * The unique id of the cluster
	 * @var int
	 */
	public $id			= null;

	/**
	 * The relation to the table `#__files`.id
	 * @var int
	 */
	public $file_id		= null;

	/**
	 * The relation to the table `#__discussions`.id
	 * @var int
	 */
	public $discussion_id		= null;

	/**
	 * The creation date
	 * @var int
	 */
	public $created		= null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_discussions_files' , 'id' , $db );
	}
}
