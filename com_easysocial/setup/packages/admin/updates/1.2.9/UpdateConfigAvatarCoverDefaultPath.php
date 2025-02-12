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

class SocialMaintenanceScriptUpdateConfigAvatarCoverDefaultPath extends SocialMaintenanceScript
{
    public static $title = 'Update default path for avatar and cover.';
    public static $description = 'Update the default path stored in the db for avatar and cover.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->select('#__social_config');
        $sql->column('value');
        $sql->where('type', 'site');

        $db->setQuery($sql);
        $value = $db->loadResult();

        $obj = ES::makeObject($value);

        $default = ES::makeObject(SOCIAL_ADMIN_DEFAULTS . '/site.json');

        $obj->avatars->default = $default->avatars->default;
        $obj->covers->default = $default->covers->default;

        $string = ES::makeJSON($obj);

        $sql->clear();
        $sql->update('#__social_config');
        $sql->set('value', $string);
        $sql->where('type', 'site');

        $db->setQuery($sql);
        $db->query();

        return true;
    }
}
