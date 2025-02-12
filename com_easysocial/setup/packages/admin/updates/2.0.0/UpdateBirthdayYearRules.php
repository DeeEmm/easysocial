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

class SocialMaintenanceScriptUpdateBirthdayYearRules extends SocialMaintenanceScript
{
    public static $title = 'Update birthday year privacy rule.';
    public static $description = 'Update birthday year privacy rule to use the new added privacy.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        // first get the birthday year privacy id.
        $query = "select `id`, `value` from `#__social_privacy` where `type` = " . $db->Quote('field') . " and `rule` = " . $db->Quote('birthday.year');
        $sql->raw($query);
        $db->setQuery($sql);

        $result = $db->loadObject();

        if ($result) {
            $id = $result->id;
            $val = $result->value;

            // insert this rule into each profile mapping only if there is existing mapping for profiles
            $query = "insert into `#__social_privacy_map` (`privacy_id`, `uid`, `utype`, `value`) select $id, p.id, 'profiles', $val from `#__social_profiles` as p";
            $query .= " where exists (select pm.`uid` from `#__social_privacy_map` as pm where pm.`uid` = p.`id` and pm.`utype` = 'profiles')";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();

            // now we need to migrate the old id with this new one.
            $query = "update `#__social_privacy_items` set `privacy_id` = $id, `type` = 'field' where `type` = 'birthday.year'";
            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();

        }

        return true;
    }
}
