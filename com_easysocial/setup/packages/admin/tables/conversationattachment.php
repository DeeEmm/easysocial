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

class SocialTableConversationAttachment extends SocialTable
{
	public $id				= null;
	public $message_id		= null;
	public $title			= null;
	public $mime			= null;
	public $size			= null;
	public $state			= null;
	public $path			= null;
	public $created_by		= null;
	public $created			= null;

	public function __construct( $db )
	{
		parent::__construct('#__social_conversations_attachments', 'id' , $db);
	}

	public function store( $updateNulls = false )
	{
		if( !$this->created )
		{
			$this->created	= ES::get( 'Date' , 'now' , false )->toMySQL();
		}

		return parent::store();
	}

	/**
	 * Retrieves the title of the item
	 *
	 * @param	null
	 * @return	string
	 */
	public function getTitle()
	{
		$title	= $this->get( 'title' );

		return $title;
	}

	/**
	 * Retrieves the size of the attachment item.
	 * @todo	We could add some options here to use certain units.
	 *
	 * @param	null
	 * @return	string	The attachment size
	 */
	public function getSize()
	{
		return ES::math()->convertUnits( $this->size );
	}

	/**
	 * Returns the exact file size on the system.
	 *
	 * @param	null
	 * @return	int
	 */
	public function getFileSize()
	{
		return filesize( $ths->getPath() );
	}

	/**
	 * Retrieves the URL of an attachment item so that we control the viewing privileges.
	 *
	 * @param	null
	 * @return	string	The url encoded with @JRoute
	 */
	public function getUrl()
	{
		$url	= JRoute::_( 'index.php?option=com_easysocial&view=conversations&layout=attachments&id=' . $this->id );

		return $url;
	}

	/**
	 * Retrieves the path to the attachment item
	 *
	 * @param	boolean	$absolute	True to retrieve absolute path. (optional)
	 * @return	string				The path to the item.
	 */
	public function getPath( $absolute = true )
	{
		$path		= $this->get( 'path' );

		if( $absolute )
		{
			$path	= JPATH_ROOT . DS . $path;
		}

		return $path;
	}
	/**
	 * Gets the mime type for this attachment.
	 *
	 * @param	null
	 * @return	string		The mime type.
	 **/
	public function getMime()
	{
		return $this->get( 'mime' );
	}

	/**
	 * Determines whether the current browser is allowed to download the file.
	 *
	 * @param	int		$node_id
	 */
	public function hasAccess( $node_id = '' )
	{
		$node_id	= empty( $node_id ) ? ES::get( 'People' )->get( 'node_id' ) : $node_id;

		$db		= ES::db();
		$query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( $this->_tbl ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( '#__social_conversations_message' ) . ' AS b '
				. 'ON b.' . $db->nameQuote( 'id' ) . '= a.' . $db->nameQuote( 'message_id' ) . ' '
				. 'INNER JOIN ' . $db->nameQuote( '#__social_conversations_message_maps' ) . ' AS c '
				. 'ON b.' . $db->nameQuote( 'id' ) . ' = c.' . $db->nameQuote( 'message_id' ) . ' '
				. 'WHERE a.' . $db->nameQuote( 'id' ) . ' = ' . $db->Quote( $this->id ) . ' '
				. 'AND c.' . $db->nameQuote( 'node_id' ) . ' = ' . $db->Quote( $node_id );

		$db->setQuery( $query );

		$result	= (int) $db->loadResult();

		return $result > 0;
	}
}
