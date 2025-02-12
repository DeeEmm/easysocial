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

class SocialMaintenanceScriptAddUserFieldsPrivacyItem extends SocialMaintenanceScript
{
    public static $title = 'Add fields privacy item for users.';
    public static $description = 'Add privacy item rules for fields for user that does not have the rules initiated.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        /*

        insert into jos_social_privacy_items (privacy_id, user_id, uid, type, value)
        select f.id as privacy_id, a.id as user_id, d.id as field_id, f.type, f.value from jos_users as a
        left join jos_social_profiles_maps as b
        on a.id = b.user_id
        left join jos_social_fields_steps as c
        on c.uid = b.profile_id
        left join jos_social_fields as d
        on d.step_id = c.id
        left join jos_social_apps as e
        on e.id = d.app_id
        left join jos_social_privacy as f
        on e.element = f.rule
        where c.type = 'profiles'
        and f.type = 'field'
        and d.id not in (select g.uid from jos_social_privacy_items as g where g.type = 'field' and g.user_id = a.id)
        order by a.id, d.id;

         */

        $query = array();

        $query[] = "INSERT INTO `#__social_privacy_items` (`privacy_id`, `user_id`, `uid`, `type`, `value`)";
        $query[] = "SELECT `f`.`id` AS `privacy_id`, `a`.`id` AS `user_id`, `d`.`id` AS `field_id`, `f`.`type`, `f`.`value` FROM `#__users` AS `a`";
        $query[] = "LEFT JOIN `#__social_profiles_maps` AS `b`";
        $query[] = "ON `a`.`id` = `b`.`user_id`";
        $query[] = "LEFT JOIN `#__social_fields_steps` AS `c`";
        $query[] = "ON `c`.`uid` = `b`.`profile_id`";
        $query[] = "LEFT JOIN `#__social_fields` AS `d`";
        $query[] = "ON `d`.`step_id` = `c`.`id`";
        $query[] = "LEFT JOIN `#__social_apps` AS `e`";
        $query[] = "ON `e`.`id` = `d`.`app_id`";
        $query[] = "LEFT JOIN `#__social_privacy` AS `f`";
        $query[] = "ON `e`.`element` = `f`.`rule`";
        $query[] = "WHERE `c`.`type` = 'profiles'";
        $query[] = "AND `f`.`type` = 'field'";
        $query[] = "AND `d`.`id` NOT IN (SELECT `g`.`uid` FROM `#__social_privacy_items` AS `g` WHERE `g`.`type` = 'field' AND `g`.`user_id` = `a`.`id`)";
        $query[] = "ORDER BY `a`.`id`, `d`.`id`";

        $string = implode(' ', $query);

        $sql->raw($string);
        $db->setQuery($sql);

        $db->query();

        /*

        insert into jos_social_privacy_map (privacy_id, uid, utype, value)
        select b.id as privacy_id, a.id as uid, 'user' as utype, b.value from jos_users as a
        left join jos_social_privacy as b
        on b.type = 'field'
        where b.id not in (select c.privacy_id from jos_social_privacy_map as c where c.utype = 'user' and c.uid = a.id)
        order by a.id, b.id;

         */

        $query = array();

        $query[] = "INSERT INTO `#__social_privacy_map` (`privacy_id`, `uid`, `utype, `value`)";
        $query[] = "SELECT `b`.`id` AS `privacy_id`, `a`.`id` AS `uid`, 'user' AS `utype`, `b`.`value` FROM `#__users` AS `a`";
        $query[] = "LEFT JOIN `#__social_privacy` AS `b`";
        $query[] = "ON `b`.`type` = 'field'";
        $query[] = "WHERE `b`.`id` NOT IN (SELECT `c`.`privacy_id` FROM `#__social_privacy_map` AS `c` WHERE `c`.`utype` = 'user' AND `c`.`uid` = `a`.`id`)";
        $query[] = "ORDER BY `a`.`id`, `b`.`id`";

        $sql->clear();
        $sql->raw($string);
        $db->setQuery($sql);

        $db->query();

        return true;
    }
}
