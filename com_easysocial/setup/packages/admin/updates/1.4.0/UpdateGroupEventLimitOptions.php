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

class SocialMaintenanceScriptUpdateGroupEventLimitOptions extends SocialMaintenanceScript
{
    public static $title = 'Update Group and Event Limit access options.';
    public static $description = 'Update group and event limit access rule options to new limitinterval format.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();


        // Step1: Update params to new limitinterval format.
        $params = '{"type":"limitinterval","default":{"value":"0","interval":"0"}}';

        $query = "update `#__social_access_rules` set `params` = " . $db->Quote($params);
        $query .= " where `name` IN (" . $db->Quote('groups.limit') . "," . $db->Quote('events.limit') . ")";
        $query .= " and `group` = " . $db->Quote('user');

        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        return true;
    }
}
