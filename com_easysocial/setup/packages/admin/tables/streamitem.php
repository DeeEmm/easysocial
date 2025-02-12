<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableStreamItem extends SocialTable
{
	public $id = null;
	public $actor_id = null;
	public $actor_type = null;
	public $context_type = null;
	public $context_id = null;
	public $verb = null;
	public $target_id = null;
	public $created = null;
	public $uid = null;
	public $sitewide = null;
	public $params = null;
	public $state = null;

	static $_streamitems = array();

	public function __construct(&$db)
	{
		parent::__construct('#__social_stream_item', 'id', $db);
	}

	/**
	 * Checks if the provided user is allowed to delete this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteable($id = null)
	{
		$isOwner = $this->isOwner( $id );
		$isAdmin = $this->isAdmin( $id );

		if ($isOwner || $isAdmin) {
			return true;
		}

		return false;
	}

	/**
	 * Overrides parent's load implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load( $keys = null, $reset = true )
	{
		$loadFromUid = false;

		if( is_array( $keys ) )
		{
			if( count( $keys ) == 1 && isset( $keys['uid'] ) )
			{
				$loadFromUid = true;
			}
			else
			{
				return parent::load( $keys, $reset );
			}
		}

		$state = false;
		if( $loadFromUid )
		{
			$uid = $keys['uid'];

			if(! isset( self::$_streamitems[ $uid ] ) )
			{
				$state = parent::load( array( 'uid' => $uid ) );
				self::$_streamitems[ $uid ] = $this;
				return $state;
			}

			$state = parent::bind( self::$_streamitems[ $uid ] );
		}
		else
		{
			$state = parent::load( $keys, $reset );
		}

		return $state;
	}

	public function loadByUIDBatch( $ids )
	{
		$db = ES::db();
		$sql = $db->sql();

		$streamIds = array();

		foreach( $ids as $pid )
		{
			if(! isset( self::$_streamitems[$pid] ) )
			{
				$streamIds[] = $pid;
			}
		}

		if( $streamIds )
		{
			foreach( $streamIds as $pid )
			{
				self::$_streamitems[$pid] = false;
			}

			$idSegments = array_chunk( $streamIds, 5 );
			//$idSegments = array_chunk( $streamIds, count($streamIds) );

			$query = '';
			for( $i = 0; $i < count( $idSegments ); $i++ )
			{
				$segment    = $idSegments[$i];

				$ids = implode( ',', $segment );

				$query .= 'select * from `#__social_stream_item` where `uid` IN ( ' . $ids . ')';

				if( ($i + 1)  < count( $idSegments ) )
				{
					$query .= ' UNION ';
				}

			}

			$sql->raw( $query );
			$db->setQuery( $sql );

			$results = $db->loadObjectList();

			if( $results )
			{
				foreach( $results as $row )
				{
					self::$_streamitems[$row->uid] = $row;
				}
			}
		}
	}

	/**
	 * Returns the stream's permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermalink( $xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		return FRoute::stream( array( 'id' => $this->uid , 'layout' => 'item', 'external' => $external, 'sef' => $sef, 'adminSef' => $adminSef) , $xhtml );
	}

	/**
	 * Retrieves the giphy url
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getGiphyUrl()
	{
		static $items = array();

		if (empty($this->params)) {
			return false;
		}

		if (!isset($items[$this->id])) {
			$params = $this->getParams();

			$items[$this->id] = $params->get('giphy', false);
		}

		return $items[$this->id];
	}

	/**
	 * Checks if the provided user is allowed to hide this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hideable($id = null)
	{
		$isOwner 	= $this->isOwner( $id );
		$isAdmin	= $this->isAdmin( $id );

		if( $isOwner || $isAdmin )
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if there is giphy of the stream item
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function hasGiphy()
	{
		static $items = array();

		if (empty($this->params)) {
			return false;
		}

		if (!isset($items[$this->id])) {
			$params = $this->getParams();

			$items[$this->id] = (bool) $params->get('giphy', false);
		}

		return $items[$this->id];
	}

	/**
	 * Checks if the provided user is the owner of this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isAdmin($id = null)
	{
		$user = ES::user($id);

		if ($user->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided user is the owner of this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isOwner($id = null)
	{
		$user = ES::user($id);

		if ($this->actor_id == $user->id) {
			return true;
		}

		return false;
	}

	/**
	 * Update giphy of the stream item
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function updateGiphy($giphyURL)
	{
		$params = $this->getParams();

		if (empty($this->params)) {
			$params = new JRegistry();
		}
		
		$params->set('giphy', $giphyURL);

		$this->params = $params->toString();
		return $this->store();
	}

	/**
	 * Determine the stream item is scheduled.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function isScheduled()
	{
		return ($this->state == SOCIAL_STREAM_STATE_SCHEDULED) ? true : false;
	}
}
