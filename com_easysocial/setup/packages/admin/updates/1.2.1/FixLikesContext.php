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
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptFixLikesContext extends SocialMaintenanceScript
{
	public static $title = 'Migrate type context in likes';

	public static $description = 'Migrate context type in like table to use proper context with verb.';

	public function main()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		// friends.user
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`context_id`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, b.`verb` )';
		$query .= ' where a.`type` = ' . $db->Quote( 'friends.user' );
		$query .= ' and b.`context_type` = ' . $db->Quote( 'friends' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();


		// photos.user
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, ' . $db->Quote( 'create' ) . ' )';
		$query .= ' where a.`type` = ' . $db->Quote( 'photos.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();



		// albums.user
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, ' . $db->Quote( 'create' ) . ' )';
		$query .= ' where a.`type` = ' . $db->Quote( 'albums.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();




		// comments.user
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, ' . $db->Quote( 'like' ) . ' )';
		$query .= ' where a.`type` = ' . $db->Quote( 'comments.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();




		// calendar.user
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`context_id`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, b.`verb` )';
		$query .= ' where a.`type` = ' . $db->Quote( 'calendar.user' );
		$query .= ' and b.`context_type` = ' . $db->Quote( 'calendar' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();



		// kunena-create | kunena-reply
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = if( a.`type` = ' . $db->Quote( 'kunena-create.user' ) . ', ' . $db->Quote( 'kunena.user.create' ) . ', ' . $db->Quote( 'kunena.user.reply' ) . ')';
		$query .= ' where a.`type` in ( ' . $db->Quote( 'kunena-create.user' ) . ', ' . $db->Quote( 'kunena-reply.user' ) . ')';

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();




		// notes.user
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, ' . $db->Quote( 'create' ) . ' )';
		$query .= ' where a.`type` = ' . $db->Quote( 'notes.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();




		// followers.user
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, ' . $db->Quote( 'follow' ) . ' )';
		$query .= ' where a.`type` = ' . $db->Quote( 'followers.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();




		// links.user
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, ' . $db->Quote( 'create' ) . ' )';
		$query .= ' where a.`type` = ' . $db->Quote( 'links.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();



		// profiles
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`id`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', b.`context_type` , ' . $db->Quote( 'user' ) . ', b.`verb` )';
		$query .= ' , a.`uid` = b.`context_id`';
		$query .= ' where b.`context_type` = ' . $db->Quote( 'profiles' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();



		// shares.user
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`context_id`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, b.`verb` )';
		$query .= ' where a.`type` = ' . $db->Quote( 'shares.user' );
		$query .= ' and b.`context_type` = ' . $db->Quote( 'shares' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();



		// story.user
		$query = 'update `#__social_likes` as a';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', a.`type`, ' . $db->Quote( 'create' ) . ' )';
		$query .= ' where a.`type` = ' . $db->Quote( 'story.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();




		// badges.user
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`uid`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', b.`context_type` , ' . $db->Quote( 'user' ) . ', b.`verb`, b.`actor_id` )';
		$query .= ' , a.`uid` = b.`context_id`';
		$query .= ' where a.`type` = ' . $db->Quote( 'stream.user' );
		$query .= ' and b.`context_type` = ' . $db->Quote( 'badges' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();

		// users.user
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`uid`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', b.`context_type` , ' . $db->Quote( 'user' ) . ', b.`verb`, b.`uid` )';
		$query .= ' , a.`uid` = b.`context_id`';
		$query .= ' where a.`type` = ' . $db->Quote( 'users.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();

		// apps.user
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`context_id`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', b.`context_type` , ' . $db->Quote( 'user' ) . ', b.`verb`, b.`actor_id` )';
		$query .= ' where a.`type` = ' . $db->Quote( 'apps.user' );

		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();


		// others
		$query = 'update `#__social_likes` as a';
		$query .= ' inner join `#__social_stream_item` as b on a.`uid` = b.`uid`';
		$query .= ' set a.`type` = concat_ws( ' . $db->Quote( '.' ) . ', b.`context_type` , ' . $db->Quote( 'user' ) . ', b.`verb` )';
		$query .= ' , a.`uid` = b.`context_id`';
		$query .= ' where a.`type` IN (';
		$query .= $db->Quote( 'articles.user' ) .',';
		$query .= $db->Quote( 'discuss.user' ) .',';
		$query .= $db->Quote( 'komento.user' ) .',';
		$query .= $db->Quote( 'feeds.user' ) .',';
		$query .= $db->Quote( 'facebook.user' ) .',';
		$query .= $db->Quote( 'task.user' );
		$query .= ')';


		$sql->raw( $query );

		$db->setQuery( $sql );
		$db->query();

		return true;
	}

}
