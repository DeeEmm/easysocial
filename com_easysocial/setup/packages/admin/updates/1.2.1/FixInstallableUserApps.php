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

class SocialMaintenanceScriptFixInstallableUserApps extends SocialMaintenanceScript
{
    public static $title = 'Fix installable user apps.';

    public static $description = 'Fix installable user apps.';

    public function main()
    {
        $db  = ES::db();
        $sql = $db->sql();

        $query = "update `#__social_apps` set `installable` = 0";
        $query .= " where `type` = 'apps' and `group` = 'user'";
        $query .= " and `element` IN (";
        $query .= "'apps','badges','facebook','files','followers','groups','links','locations','profiles','shares','story','users'";
        $query .= ")";

        $sql->raw( $query );
        $db->setQuery($sql);
        $db->query();

        return true;
    }
}
