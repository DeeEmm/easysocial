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
 * Object mapping for migrators table.
 *
 * @author	Sam <sam@stackideas.com>
 * @since	1.0
 */
class SocialTableMigrators extends SocialTable
{
	public $id          = null;
	public $oid     	= null;
	public $element 	= null;
	public $component 	= null;
	public $uid     	= null;
	public $created		= null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_migrators' , 'id' , $db );
	}

	public function toJSON()
	{
		return array('id' 			=> $this->id ,
					 'oid' 			=> $this->oid ,
					 'component' 	=> $this->component,
					 'uid' 			=> $this->uid ,
					 'created' 		=> $this->created
		 );
	}

}
