<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptFixCommentsContext extends SocialMaintenanceScript
{
    public static $title = 'Migrate type context in comments';

    public static $description = 'Migrate context type in comments table to use proper context with verb.';

    public function main()
    {
        $queries = array();

        // photos.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = CONCAT_WS('.', a.`element`, 'create')",
            "WHERE a.`element` = 'photos.user'"
        );

        // albums.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = CONCAT_WS('.', a.`element`, 'create')",
            "WHERE a.`element` = 'albums.user'"
        );

        // comments.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = CONCAT_WS('.', a.`element`, 'like')",
            "WHERE a.`element` = 'comments.user'"
        );

        // notes.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = CONCAT_WS('.', a.`element`, 'create')",
            "WHERE a.`element` = 'notes.user'"
        );

        // followers.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = CONCAT_WS('.', a.`element`, 'follow')",
            "WHERE a.`element` = 'followers.user'"
        );

        // links.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = CONCAT_WS('.', a.`element`, 'create')",
            "WHERE a.`element` = 'links.user'"
        );

        // story.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = CONCAT_WS('.', a.`element`, 'create')",
            "WHERE a.`element` = 'story.user'"
        );

        // kunena-create | kunena-reply
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "SET a.`element` = if(a.`element` = 'kunena-create.user', 'kunena.user.create', 'kunena.user.reply')",
            "WHERE a.`element` IN ('kunena-create.user', 'kunena-reply.user')"
        );

        // friends.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`context_id`",
            "SET a.`element` = CONCAT_WS('.', a.`element`, b.`verb`)",
            "WHERE a.`element` = 'friends.user'",
            "AND b.`context_type` = 'friends'"
        );

        // calendar.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`context_id`",
            "SET a.`element` = CONCAT_WS('.', a.`element`, b.`verb`)",
            "WHERE a.`element` = 'calendar.user'",
            "AND b.`context_type` = 'calendar'"
        );

        // friends.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`context_id`",
            "SET a.`element` = CONCAT_WS('.', a.`element`, b.`verb`)",
            "WHERE a.`element` = 'friends.user'",
            "AND b.`context_type` = 'friends'"
        );

        // shares.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`context_id`",
            "SET a.`element` = CONCAT_WS('.', a.`element`, b.`verb`)",
            "WHERE a.`element` = 'shares.user'",
            "AND b.`context_type` = 'shares'"
        );

        // badges.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`uid`",
            "SET a.`element` = CONCAT_WS('.', b.`context_type`, 'user', b.`verb`, b.`actor_id`),",
            "a.`uid` = b.`context_id`",
            "WHERE a.`element` = 'stream.user'",
            "AND b.`context_type` = 'badges'"
        );

        // users.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`uid`",
            "SET a.`element` = CONCAT_WS('.', b.`context_type`, 'user', b.`verb`, b.`uid`),",
            "a.`uid` = b.`context_id`",
            "WHERE a.`element` = 'users.user'"
        );

        // apps.user
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`context_id`",
            "SET a.`element` = CONCAT_WS('.', b.`context_type`, 'user', b.`verb`, b.`actor_id`)",
            "WHERE a.`element` = 'apps.user'"
        );

        // profiles
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`id`",
            "SET a.`element` = CONCAT_WS('.', b.`context_type`, 'user', b.`verb`),",
            "a.`uid` = b.`context_id`",
            "WHERE b.`context_type` = 'profiles'"
        );

        // Others
        $queries[] = array(
            "UPDATE `#__social_comments` AS a",
            "INNER JOIN `#__social_stream_item` AS b",
            "ON a.`uid` = b.`uid`",
            "SET a.`element` = CONCAT_WS('.', b.`context_type`, 'user', b.`verb`),",
            "a.`uid` = b.`context_id`",
            "WHERE a.`element` IN ('articles.user', 'discuss.user', 'feeds.user', 'facebook.user', 'task.user', 'komento.user')"
        );

        $db = ES::db();
        $sql = $db->sql();

        foreach ($queries as $query)
        {
            $sql->raw(implode(' ', $query));
            $db->setQuery($sql);

            $db->query();
        }

        return true;
    }
}
