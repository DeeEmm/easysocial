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

class SocialMaintenanceScriptMigrateStreamVerb extends SocialMaintenanceScript
{
    public static $title = 'Migrate stream verb from stream_item.';
    public static $description = 'Migrate stream verb from stream_item.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $query = "update `#__social_stream` as a";
        $query .= " inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
        $query .= " set a.`verb` = b.`verb`";

        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        return true;
    }
}
