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

jimport('joomla.application.component.model');

// Include the main model parent.
ES::import('admin:/includes/model');

class EasySocialModelMailer extends EasySocialModel
{
	public function __construct( $config = array() )
	{
		parent::__construct( 'mailer' , $config );
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		$state = $this->getUserStateFromRequest('published', 'all');
		$this->setState('published', $state);

		$search = $this->getUserStateFromRequest('search', '');
		$this->setState('search', $search);

		parent::initStates();
	}

	/**
	 * Returns a list of menus for the admin sidebar.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItemsWithState( $options = array() )
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_mailer');


		// Determines if user is filtering the items
		$state 	= $this->getState( 'published' );

		if( $state != 'all' && !is_null( $state ) )
		{
			$sql->where( 'state' , $state );
		}

		// Determines if user is searching for a mail
		$search 	= $this->getState( 'search' );

		if( $search )
		{
			$sql->where( '(' );
			$sql->where( 'title' , '%' . $search . '%' , 'LIKE' , 'OR' );
			$sql->where( 'recipient_email' , '%' . $search . '%' , 'LIKE' , 'OR' );
			$sql->where( 'recipient_name' , '%' . $search . '%' , 'LIKE' , 'OR' );
			$sql->where( ')' );
		}

		// Ordering
		$ordering 	= $this->getState( 'ordering' );

		if( $ordering )
		{
			$direction 	= $this->getState( 'direction' ) ? $this->getState( 'direction' ) : 'DESC';

			$sql->order( $ordering , $direction );
		}

		// Set the total
		$this->setTotal( $sql->getTotalSql() );

		$result 	= parent::getData( $sql->getSql() );

		if( !$result )
		{
			return $result;
		}

		$emails 	= array();

		foreach( $result as $row )
		{
			$mail 	= ES::table( 'Mailer' );
			$mail->bind( $row );

			$emails[]	= $mail;
		}

		return $emails;
	}

	/**
	 * Returns a list of menus for the admin sidebar.
	 *
	 * Example:
	 * <code>
	 * <?php
	 * $model 	= ES::model( 'Mailer' );
	 *
	 * // Returns an array of mail items.
	 * $model->getItems();
	 * ?>
	 * </code>
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	An array of options.
	 * @return	Array	An array of SocialTableMailer
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getItems( $options = array() )
	{
		$db 	= ES::db();

		$sql 	= $db->sql();

		$sql->select( '#__social_mailer' );

		// Determines if user is filtering the items
		$state 		= isset( $options[ 'state' ] ) ? $options[ 'state' ] : null;

		if( !is_null( $state ) )
		{
			$sql->where( 'state' , $state );
		}

		// Determines if we need to order the items by column.
		$ordering 	= $this->getState( 'ordering' );

		// Ordering based on caller
		if( $ordering )
		{
			$direction 	= $this->getState( 'direction' , 'DESC' );

			$sql->order( $ordering , $direction );
		}

		// Set the total
		$this->setTotal( $sql->getTotalSql() );

		$result 	= parent::getData( $sql->getSql() );

		if( !$result )
		{
			return $result;
		}

		$emails 	= array();

		foreach( $result as $row )
		{
			$mail 	= ES::table( 'Mailer' );
			$mail->bind( $row );

			$emails[]	= $mail;
		}

		return $emails;
	}

	/**
	 * Purge emails from the spool.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The type of purge. (E.g: pending , all , sent )
	 * @return	bool	True on success false otherwise.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function purge( $type )
	{
		$db 		= ES::db();

		$query		= array();
		$query[]	= 'DELETE FROM ' . $db->nameQuote( '#__social_mailer' );

		switch( $type )
		{
			case 'pending':
				$query[]	= 'WHERE ' . $db->nameQuote( 'state' ) . '=' . $db->Quote( SOCIAL_STATE_UNPUBLISHED );
			break;
			case 'sent':
				$query[]	= 'WHERE ' . $db->nameQuote( 'state' ) . '=' . $db->Quote( SOCIAL_STATE_PUBLISHED );
			break;
			case 'all':
			default:
			break;
		}

		$query 		= implode( ' ' , $query );
		$db->setQuery( $query );

		return $db->Query();
	}

	/**
	 * Retrieves the past 7 days statistics for mail activities
	 *
	 * @since	1.0
	 * @access	public
	 * @return	Array
	 */
	public function getDeliveryStats()
	{
		$db			= ES::db();
		$sql 		= $db->sql();
		$dates 		= array();

		// Get the past 7 days
		$curDate 	= ES::date();
		for( $i = 0 ; $i < 7; $i++ )
		{
			$obj = new stdClass();

			if( $i == 0 )
			{
				$dates[]		= $curDate->toMySQL();
			}
			else
			{
				$unixdate 		= $curDate->toUnix();
				$new_unixdate 	= $unixdate - ( $i * 86400);
				$newdate  		= ES::date( $new_unixdate );

				$dates[] 	= $newdate->toMySQL();
			}
		}

		// Reverse the dates
		$dates 			= array_reverse( $dates );

		$result 		= new stdClass();
		$result->dates	= $dates;

		$profiles 	= array();

		$states[ 0 ]			= new stdClass();
		$states[ 0 ]->title		= JText::_( 'COM_EASYSOCIAL_MAILER_STATS_PENDING_DELIVERY' );
		$states[ 0 ]->items 	= array();

		$states[ 1 ]			= new stdClass();
		$states[ 1 ]->title		= JText::_( 'COM_EASYSOCIAL_MAILER_STATS_PENDING_DELIVERED' );
		$states[ 1 ]->items 	= array();


		$states[ 2 ]			= new stdClass();
		$states[ 2 ]->title		= JText::_( 'COM_EASYSOCIAL_MAILER_STATS_PENDING_ERRORS' );
		$states[ 2 ]->items 	= array();

		foreach( $dates as $date )
		{
			// Registration date should be Y, n, j
			$date	= ES::date( $date )->format( 'Y-m-d' );

			$query 		= array();
			$query[]	= 'SELECT a.`id`,a.`state`, COUNT(1) AS `cnt` FROM `#__social_mailer` AS a';
			$query[]	= 'WHERE DATE_FORMAT( a.created , GET_FORMAT( DATE , "ISO" ) ) =' . $db->Quote( $date );
			$query[]	= 'GROUP BY a.`state`';
			$query 		= implode( ' ' , $query );
			$sql->raw( $query );

			$db->setQuery( $sql );

			$items		= $db->loadObjectList();

			if( $items )
			{
				$sets 	= array();

				foreach( $items as $item )
				{
					$sets[ $item->state ]	= true;
					$states[ $item->state ]->items[]	= $item->cnt;
				}

				if( !isset( $sets[ 0 ] ) )
				{
					$states[ 0 ]->items[]	= 0;
				}

				if( !isset( $sets[ 1 ] ) )
				{
					$states[ 1 ]->items[]	= 0;
				}

				if( !isset( $sets[ 2 ] ) )
				{
					$states[ 2 ]->items[]	= 0;
				}
			}
			else
			{
				$states[ 0 ]->items[]	= 0;
				$states[ 1 ]->items[]	= 0;
				$states[ 2 ]->items[]	= 0;
			}

		}

		$result->states 	= $states;

		return $result;
	}

	/**
	 * Proxy method to purge sent items.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	bool	True if success, false otherwise.
	 */
	public function purgeSent()
	{
		return $this->purge( 'sent' );
	}

	/**
	 * Proxy method to purge pending items.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	bool	True if success, false otherwise.
	 */
	public function purgePending()
	{
		return $this->purge( 'pending' );
	}

	/**
	 * Proxy method to purge all items.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	bool	True if success, false otherwise.
	 */
	public function purgeAll()
	{
		return $this->purge( 'all' );
	}

	/**
	 * Proxy method to purge all items.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array
	 * @return	bool	True if success, false otherwise.
	 */
	public function markSent( $items )
	{
		$db			= ES::db();
		$sql 		= $db->sql();

		$ids = array();
		foreach ($items as $item) {
			$ids[] = $item->id;
		}

		$ids = implode(',',$ids);

		if ($ids) {
			$query = "update `#__social_mailer` set `state` = " . SOCIAL_STATE_PUBLISHED;
			$query .= " where `id` IN (" . $ids . ")";
			$query .= " and `state` = " . SOCIAL_STATE_UNPUBLISHED;

			$sql->raw($query);

			$db->setQuery($sql);
			$db->query();
		}

		return true;
	}

	/**
	 * Publishing scheduled emails.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function publishScheduledEmails($sid)
	{
		$db = ES::db();
		$now = ES::date();

		$query = 'UPDATE `#__social_mailer` SET `state` = ' . $db->Quote(SOCIAL_STATE_UNPUBLISHED) . ', `created` = ' . $db->Quote($now->toMySQL());
		$query .= ' WHERE `sid` = ' . $db->Quote($sid);
		$query .= ' AND `state` = ' . $db->Quote(SOCIAL_STATE_SCHEDULED);

		$db->setQuery($query);

		return $db->query();
	}
}
