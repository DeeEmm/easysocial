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

class SocialMaintenanceScriptResetFieldsPrivacy extends SocialMaintenanceScript
{
    public static $title = 'Remove custom priavcy option for fields privacy';
    public static $description = 'Remove custom priavcy option for fields privacy.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        // reset fields privacy if the option is customize, set it back to only me.
        $query = "update `#__social_privacy` as a";
        $query .= " left join `#__social_privacy_map` as b on a.`id` = b.`privacy_id`";
        $query .= " left join `#__social_privacy_items` as c on a.`id` = c.`privacy_id`";
        $query .= " set a.`options` = " . $db->Quote('{"options":["public","member","friend","only_me"]}') . ",";
        $query .= "     b.`value` = if(b.`value` = '100', '40', b.`value`),";
        $query .= "     c.`value` = if(c.`value` = '100', '40', c.`value`)";
        $query .= " where a.`type` = 'field'";

        $sql->raw($query);
        $db->setQuery($sql);

        $db->query();

        // now we need to remove fields privacy of 'joomla_username' and 'joomla_fullname'
        $query = "delete a, b, c";
        $query .= " from `#__social_privacy` as a";
        $query .= "     left join `#__social_privacy_map` as b on a.`id` = b.`privacy_id`";
        $query .= "     left join `#__social_privacy_items` as c on a.`id` = c.`privacy_id`";
        $query .= " where a.`type` = 'field'";
        $query .= " and a.`rule` in ('joomla_fullname', 'joomla_username')";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);

        $db->query();

        return true;
    }
}
