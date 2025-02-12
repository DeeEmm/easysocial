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

class SocialMaintenanceScriptFixBlogCommentsLikesContext extends SocialMaintenanceScript
{
	public static $title = 'Migrate EasyBlog items the context in likes and comments';

	public static $description = 'Migrate context type in like and comment table for EasyBlog items to use proper context with verb.';

	public function main()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();
		$queries = array();


		// blog.comments.user on likes and comments
		$query = "update `#__social_likes` as a";
		$query .= " set a.`type` = concat_ws( '.', 'blog', 'user', 'comments' )";
		$query .= " where a.`type` = 'blog.comments.user'";
		$queries[] = $query;


        $query = "UPDATE `#__social_comments` AS a";
        $query .= " SET a.`element` = CONCAT_WS('.', 'blog', 'user', 'comments')";
        $query .= " WHERE a.`element` = 'blogcomment.user'";
		$queries[] = $query;


		// blogfeatured.user on comments
        $query = "UPDATE `#__social_comments` AS a";
        $query .= " SET a.`element` = CONCAT_WS('.', 'blog', 'user', 'featured')";
        $query .= " WHERE a.`element` = 'blogfeatured.user'";
		$queries[] = $query;

		// blog.user on likes and comments
		$query = "update `#__social_likes` as a";
		$query .= " set a.`type` = concat_ws( '.', 'blog', 'user', 'create' )";
		$query .= " where a.`type` = 'blog.user'";
		$queries[] = $query;


        $query = "UPDATE `#__social_comments` AS a";
        $query .= " SET a.`element` = CONCAT_WS('.', 'blog', 'user', 'create')";
        $query .= " WHERE a.`element` = 'blog.user'";
		$queries[] = $query;


        foreach ($queries as $query)
        {
        	$sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);

            $db->query();
        }

		return true;
	}

}
