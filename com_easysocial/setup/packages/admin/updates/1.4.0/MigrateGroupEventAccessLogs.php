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

class SocialMaintenanceScriptMigrateGroupEventAccessLogs extends SocialMaintenanceScript
{
    public static $title = 'Migrate Group and Event creation logs.';
    public static $description = 'Migrate group and event creation into access logs for the limit interval checking.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();


        // Step1: migrate group creation
        $query = "insert into `#__social_access_logs` (`rule`, `user_id`, `uid`, `utype`, `created`)";
        $query .= " select 'groups.limit', a.`creator_uid`, a.`id`, 'group', a.`created` from `#__social_clusters` as a";
        $query .= "     where a.`cluster_type` = 'group'";
        $query .= "     and not exists (select b.`uid` from `#__social_access_logs` as b where a.`id` = b.`uid` and b.`utype` = 'group')";

        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        // Step2: migrate event creation
        $query = "insert into `#__social_access_logs` (`rule`, `user_id`, `uid`, `utype`, `created`)";
        $query .= " select 'events.limit', a.`creator_uid`, a.`id`, 'event', a.`created` from `#__social_clusters` as a";
        $query .= "     where a.`cluster_type` = 'event'";
        $query .= "     and not exists (select b.`uid` from `#__social_access_logs` as b where a.`id` = b.`uid` and b.`utype` = 'event')";

        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        return true;
    }
}
