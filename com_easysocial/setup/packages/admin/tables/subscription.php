<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableSubscription extends SocialTable
{
	/**
	 * The unique id of the subscription.
	 * @var	int
	 */
	public $id = null;

	/**
	 * The unique id of the followed item.
	 * @var	int
	 */
	public $uid = null;

	/**
	 * The unique string (type) of the followed item.
	 * @var	string
	 */
	public $type = null;

	/**
	 * The owner that tries to follow.
	 * @var	int
	 */
	public $user_id = null;

	/**
	 * The nodify identifier
	 * @var	int
	 */
	public $notify = null;

	/**
	 * The creation timestamp
	 * @var	datetime
	 */
	public $created = null;

	public function __construct( $db )
	{
		parent::__construct('#__social_subscriptions', 'id', $db);
	}

	/**
	 * Determines if the user has already followed an item.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique id of the item being followed.
	 * @param	string	The unique type of the item being followed.
	 * @param	int		The user id that is trying to follow.
	 *
	 * @return	bool	True if success, false otherwise.
	 */
	public function hasFollowed( $uid , $type , $userId )
	{
		$model 	= ES::model( 'Subscriptions' );
		return $model->hasFollowed( $uid , $type , $userId );
	}
}
