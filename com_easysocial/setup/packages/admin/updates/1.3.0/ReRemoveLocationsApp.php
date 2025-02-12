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

class SocialMaintenanceScriptReRemoveLocationsApp extends SocialMaintenanceScript
{
    public static $title = 'Remove locations application.';

    public static $description = 'Remove locations app since it is no longer used.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->delete('#__social_apps');
        $sql->where('element', 'locations');
        $sql->where('type', 'apps');
        $sql->where('group', 'user');

        $db->setQuery($sql);

        $db->query();

        return true;
    }
}
