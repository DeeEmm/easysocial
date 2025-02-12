<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateNewsApps extends SocialMaintenanceScript
{
    public static $title = 'Update news apps to announcements apps';
    public static $description = 'Update news apps title to announcements across all easysocial page';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $query = "UPDATE `#__social_apps` SET `title` = 'Announcements', `alias` = 'announcements' WHERE `element` = 'news' AND `type` = 'apps'";
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        return true;
    }
}