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

class SocialIndexer
{
	var $component = null;

	public function __construct($component = 'com_easysocial')
	{
		$this->component = $component;
	}

	public static function factory($component = 'com_easysocial')
	{
		return new self($component);
	}

	/**
	 * Get's the likes data for a particular item.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique id to lookup for.
	 * @param	string	The unique type to lookup for
	 * @return	boolean		Return itself for chaining.
	 */
	public function index( SocialIndexerItem $item )
	{
		//for now we always return true;
		return true;

		$model = ES::model( 'Indexer' );
		$state = $model->index( $item );

		if( $state === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public function delete( $uid, $utype )
	{
		//for now we always return true;
		return true;

		// delete record based on uid, utype and component
		$item = new SocialIndexerItem( $this->component );

		$item->uid 		= $uid;
		$item->utype 	= $utype;

		$model = ES::model( 'Indexer' );
		$state = $model->delete( $item );

		return $state;
	}

	public function purge()
	{
		// clear all records from indexer
		$model = ES::model( 'Indexer' );
		$state = $model->purge();

		return $state;
	}


	/**
	 * Method to change the indexed item state 
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function itemStateChange($context, $id, $state)
	{
		// Trigger our own finder plugin
		JPluginHelper::importPlugin('finder');

		ESDispatcher::trigger('onFinderChangeState', array($context, $id, $state));
	}

	/**
	 * perform item reindexing
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reindex()
	{
		// $coreType = array( 'users', 'photos', 'lists', 'albums', 'groups');
		$coreType = array( SOCIAL_INDEXER_TYPE_USERS, SOCIAL_INDEXER_TYPE_PHOTOS, SOCIAL_INDEXER_TYPE_LISTS,  SOCIAL_INDEXER_TYPE_ALBUMS, SOCIAL_INDEXER_TYPE_GROUPS );
		//$coreType = array( 'photos');

		$model = ES::model( 'indexer' );

		$total = 0;

		foreach( $coreType as $type )
		{
			$result = $model->getIndexingItem( $type );

			if( $result->count )
			{
				$items = $result->data;

				if( count( $items ) > 0 )
				{
					foreach( $items as $item )
					{

						if( $type == SOCIAL_INDEXER_TYPE_USERS )
						{
							$user = ES::user( $item->uid );
							$user->syncIndex();
						}
						else
						{
							$url 		= '';
							$thumbnail 	= '';

							switch( $type )
							{

								case SOCIAL_INDEXER_TYPE_ALBUMS:

									$album = ES::table( 'Album' );
									$album->load( $item->uid );

									$creator 	= ES::user( $album->uid );
									$userAlias 	= $creator->getAlias();

									//$url 		= FRoute::albums( array( 'id' => $album->getAlias() , 'userid' => $userAlias , 'layout' => 'item' ) );
									$url 		= $album->getPermalink();
									$url 		= $this->removeAdminSegment( $url );

									if( $album->cover_id )
									{
										$photo = ES::table( 'Photo' );
										$photo->load( $album->cover_id );

										$thumbnail 	= $photo->getSource( 'thumbnail' );
									}

									break;

								case SOCIAL_INDEXER_TYPE_PHOTOS:
									$photo = ES::table( 'Photo' );
									$photo->load( $item->uid );

									// $url 		= FRoute::photos( array( 'layout' => 'item', 'id' => $photo->getAlias() ) );
									$url 		= $photo->getPermalink();
									$url 		= $this->removeAdminSegment( $url );
									$thumbnail 	= $photo->getSource( 'thumbnail' );
									break;

								case SOCIAL_INDEXER_TYPE_LISTS:

									$url 		= FRoute::friends( array( 'listid' => $item->uid ) );
									$url 		= $this->removeAdminSegment( $url );
									break;

								case SOCIAL_INDEXER_TYPE_GROUPS:

									$group = ES::group( $item->uid );

									$url 		= $group->getPermalink();
									$url 		= $this->removeAdminSegment( $url );

									$thumbnail 	= $group->getAvatar( SOCIAL_AVATAR_SQUARE );

									break;

								case 'users':
								default:
									break;

							}


							$tmpl 		= $this->getTemplate();
							$tmpl->setContent( $item->title, $item->content );
							$tmpl->setSource( $item->uid, $item->utype, $item->creatorid, $url);

							if( $thumbnail )
							{
								$tmpl->setThumbnail( $thumbnail );
							}

							$this->index( $tmpl );
						}

					}//end foreach

				}//if count(items)
			}

			$total = $total + $result->count;

		}

		// now we need to trigger
		$indexerCount 	= 0;
		$dispatcher 	= ES::dispatcher();
		$args 			= array( &$indexerCount );

		// @trigger: onIndexerReIndex
		$dispatcher->trigger( SOCIAL_APPS_GROUP_USER , 'onIndexerReIndex' , $args );
		$total = $total + $indexerCount;

		return $total;

	}

	public function getTemplate()
	{
		$item = new SocialIndexerItem( $this->component );
		return $item;
	}

	private function removeAdminSegment( $url = '' )
	{
		if( $url )
		{
			$url 	= '/' . ltrim( $url , '/' );
			$url 	= str_replace('/administrator/', '/', $url );
		}

		return $url;
	}


}

/**
 * Any tables that wants to implement an indexer interface will need to implement this.
 *
 * @since	1.0
 * @author	Sam
 */
interface ISocialIndexerTable
{
	public function syncIndex();
	public function deleteIndex();
}

class SocialIndexerItem
{
	var $component 		= null;

	var $title 			= null;
	var $content 		= null;
	var $uid			= null;
	var $utype			= null;
	var $ucreator 		= null;
	var $ulink 			= null;
	var $uimage   		= null;
	var $last_update 	= null;

	public function __construct( $component = 'com_easysocial' )
	{
		$this->component = $component;
	}

	public static function factory( $component = 'com_easysocial' )
	{
		return new self( $component );
	}

	public function setContent( $title, $content )
	{
		$this->title 	= $title;
		$this->content 	= strip_tags( $content ); // we only snapshot plain text
	}

	public function setSource( $uid, $utype, $ucreator, $ulink = '' )
	{
		$this->uid 		= $uid;
		$this->utype 	= $utype;
		$this->ucreator = $ucreator;
		$this->ulink 	= ltrim( $ulink, '/' );
	}

	public function setThumbnail( $imgLink )
	{
		$this->uimage = $imgLink;
	}

	public function setLastUpdate( $dateStr )
	{
		$this->last_update = $dateStr;
	}

}
