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
 * Object mapping for `#__social_apps_views` table.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.0
 */
class SocialTableAppView extends SocialTable
{
	/**
	 * The unique id of the application
	 * @var int
	 */
	public $id			= null;

	/**
	 * The application id
	 * @var int
	 */
	public $app_id		= null;

	/**
	 * The view of the application
	 * @var string
	 */
	public $view		= null;

	/**
	 * The type of the view
	 * @var string
	 */
	public $type		= null;

	/**
	 * The title of the view
	 * @var string
	 */
	public $title		= null;

	/**
	 * The description of the view.
	 * @var string
	 */
	public $description		= null;

	/**
	 * Class Constructor
	 *
	 * @since	1.0
	 */
	public function __construct(& $db )
	{
		parent::__construct( '#__social_apps_views' , 'id' , $db );
	}

	/**
	 * Loads the application given the `element`, `type` and `group`.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The unique element name. (E.g: notes )
	 * @param	string	The group of the application. (E.g: people or group)
	 * @param	string	The unique type of the app. (E.g: apps or fields )
	 *
	 * @return	bool	True on success false otherwise
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function loadByElement( $element , $group , $type )
	{
		$db 	= ES::db();

		$query		= array();
		$query[]	= 'SELECT * FROM ' . $db->nameQuote( $this->_tbl );
		$query[]	= 'WHERE ' . $db->nameQuote( 'element' ) . '=' . $db->Quote( $element );
		$query[]	= 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( $type );
		$query[]	= 'AND ' . $db->nameQuote( 'group' ) . '=' . $db->Quote( $group );

		$query 		= implode( ' ' , $query );
		$db->setQuery( $query );

		$result	= $db->loadObject();

		if( !$result )
		{
			return false;
		}

		return parent::bind( $result );
	}
}
