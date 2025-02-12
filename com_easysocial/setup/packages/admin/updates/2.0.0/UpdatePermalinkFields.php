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

class SocialMaintenanceScriptUpdatePermalinkFields extends SocialMaintenanceScript
{
    public static $title = 'Update permalink fields.';
    public static $description = 'Update permalink fields to prevent conflicts with system view names.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sysViews = FRoute::getSystemViews();

        $tmpStr = '';
        foreach ($sysViews as $view) {
            $tmpStr .= ($tmpStr) ? ',' . $db->Quote($view) : $db->Quote($view);
        }

        // user's permalink
        $query = "update `#__social_users` as a";
        $query .= " set a.`permalink` = ''";
        $query .= " where a.`permalink` IN (" . $tmpStr . ")";

        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        // cluster's permalink
        $query = "update `#__social_clusters` as a";
        $query .= " set a.`alias` = concat(a.`id`, a.`alias`)";
        $query .= " where a.`alias` IN (" . $tmpStr . ")";

        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();


        return true;
    }
}
