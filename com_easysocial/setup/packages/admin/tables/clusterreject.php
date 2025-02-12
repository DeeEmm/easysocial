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
 *
 * @author	Stackideas <support@stackideas.com>
 * @since	2.0
 */
class SocialTableClusterReject extends SocialTable
{
	/**
	 * primary key
	 * @var int
	 */
	public $id = null;

	/**
	 * the reject reason
	 * @var string
	 */
	public $message	= null;

	/**
	 * the cluster id that being rejected
	 * @var int
	 */
	public $cluster_id = null;

	/**
	 * user who rejected the approval
	 * @var int
	 */
	public $created_by = null;

	/**
	 * datetime when the cluster being rejected
	 * @var string
	 */
	public $created = null;

	public function __construct(& $db)
	{
		parent::__construct('#__social_clusters_reject' , 'id' , $db);
	}
}
