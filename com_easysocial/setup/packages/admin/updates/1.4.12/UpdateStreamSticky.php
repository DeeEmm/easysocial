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

class SocialMaintenanceScriptUpdateStreamSticky extends SocialMaintenanceScript
{
    public static $title = 'Update stream sticky.';
    public static $description = 'Update stream sticky to increase sql performance.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        // clean up data
        $query = "delete from `#__social_stream_sticky` where `id` = 0";
        $sql->raw($query);
        $db->setQuery($sql);


        $query = "update `#__social_stream` as a";
        $query .= " inner join `#__social_stream_sticky` as b on a.`id` = b.`stream_id`";
        $query .= " set a.`sticky_id` = b.`id`";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);

        $db->query();
        return true;
    }
}
