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

class SocialMaintenanceScriptStreamClusterAccess extends SocialMaintenanceScript
{
    public static $title = 'Sync cluster access in stream table.';
    public static $description = 'Sync cluster access in stream table.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $query = "update `#__social_stream` as a";
        $query .= ' inner join `#__social_clusters` as b on a.`cluster_id` = b.`id`';
        $query .= ' set a.`cluster_access` = b.`type`';
        $query .= ' where a.`cluster_id` != 0 and a.`cluster_access` = 1';

        $sql->raw($query);
        $db->setQuery($sql);

        $state = $db->query();

        return $state;
    }
}
