<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import( 'admin:/tables/table' );

class SocialTableTmp extends SocialTable
{
	/**
	 * The table id.
	 * @var	int
	 */
	public $id 		= null;

	/**
	 * The unique id of temporary data.
	 * @var	int
	 */
	public $uid 	= null;

	/**
	 * The type of the temporary data.
	 * @var	string
	 */
	public $type	= null;

	/**
	 * The key of the temporary data.
	 * @var	string
	 */
	public $key		= null;

	/**
	 * The value of the temporary data.
	 * @var	string
	 */
	public $value	= null;

	/**
	 * The created date time of the temporary data.
	 * @var	datetime
	 */
	public $created	= null;

	/**
	 * The expired date time of the temporary data.
	 * @var	datetime
	 */
	public $expired	= null;

	/**
	 * Constructor method for this class.
	 *
	 * @access	public
	 * @param	JDatabase	$db		The database object.
	 * @return	null
	 */
	public function __construct( $db )
	{
		parent::__construct( '#__social_tmp' , 'id' , $db );
	}

	public function store( $updateNulls = false )
	{
		$now = ES::date();

		// Set created to now by default
		if( empty( $this->created ) )
		{
			$this->created = $now->toSql();
		}

		// Set expired to 1 day later by default
		if( empty( $this->expired ) )
		{
			$this->expired = ES::date( $now->toUnix() + ( 24 * 60 * 60 ) )->toSql();
		}

		if( is_array( $this->value ) || is_object( $this->value ) )
		{
			$this->value = ES::json()->encode( $this->value );
		}

		return parent::store( $updateNulls );
	}
}
