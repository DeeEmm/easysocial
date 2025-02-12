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
 * Object mapping for indexer table.
 *
 * @author	Sam <sam@stackideas.com>
 * @since	1.0
 */
class SocialTableIndexer extends SocialTable
{
	public $id          = null;
	public $uid     	= null;
	public $utype 		= null;
	public $ucreator 	= null;
	public $component 	= null;
	public $title 		= null;
	public $content	 	= null;
	public $link 		= null;
	public $image 		= null;
	public $last_update = null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_indexer' , 'id' , $db );
	}

	//override load function
	public function load( $uid = null, $utype = '', $component = 'com_easysocial' )
	{
		if( empty( $uid ) )
			return false;

		if( $utype )
		{
			$db = ES::db();

			$query = 'select * from ' . $db->nameQuote( '#__social_indexer' );
			$query .= ' where ' . $db->nameQuote( 'uid' ) . ' = ' . $db->Quote( $uid );
			$query .= ' and ' . $db->nameQuote( 'utype' ) . ' = ' . $db->Quote( $utype );
			$query .= ' and ' . $db->nameQuote( 'component' ) . ' = ' . $db->Quote( $component );

			$db->setQuery( $query );
			$result = $db->loadObject();

			if( !$result )
			{
				return false;
			}

			parent::bind( $result );
		}
		else
		{
			parent::load( $uid );
		}

		return true;
	}

	public function toJSON()
	{
		return array('id' 			=> $this->id ,
					 'uid' 			=> $this->uid ,
					 'utype' 		=> $this->utype,
					 'ucreator'		=> $this->ucreator,
					 'component' 	=> $this->component,
					 'title' 		=> $this->title,
					 'content' 		=> $this->content,
					 'link' 		=> $this->link,
					 'image' 		=> $this->image,
					 'last_update' 	=> $this->last_update
		 );
	}

}
