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
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

/**
 * Access logs table
 *
 * @author	Sam <sam@stackideas.com>
 * @since	1.4
 */
class SocialTableAccessLogs extends SocialTable
{
	/**
	 * The id of the access rule
	 * @var int
	 */
	public $id = null;

	/**
	 * The name of the access rule
	 * @var string
	 */
	public $rule = null;

	/**
	 * The creator id
	 * @var string
	 */
	public $user_id = null;

	/**
	 * object id, e.g group id / event id
	 * @var string
	 */
	public $uid = null;

	/**
	 * object type, e.g group / event
	 * @var string
	 */
	public $utype = null;

	/**
	 * The created date of the access rule
	 * @var datetime
	 */
	public $created = null;


	public function __construct(& $db)
	{
		parent::__construct('#__social_access_logs', 'id', $db);
	}
}
