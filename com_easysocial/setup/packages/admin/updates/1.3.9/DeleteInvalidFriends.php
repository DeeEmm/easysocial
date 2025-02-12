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

class SocialMaintenanceScriptDeleteInvalidFriends extends SocialMaintenanceScript
{
    public static $title = 'Deletes invalid friends';
    public static $description = 'Deletes friends that no longer exist on the site as user.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        /*

        delete from jos_social_friends
        where actor_id not in (select id from jos_users)
        or target_id not in (select id from jos_users);

        */

        $sql->raw('DELETE FROM `#__social_friends` WHERE `actor_id` NOT IN (SELECT `user_id` FROM `#__social_users`) OR `target_id` NOT IN (SELECT `user_id` FROM `#__social_users`)');
        $db->setQuery($sql);
        return $db->query();
    }
}
