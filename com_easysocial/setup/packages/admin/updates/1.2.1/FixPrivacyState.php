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

class SocialMaintenanceScriptFixPrivacyState extends SocialMaintenanceScript
{
    public static $title = 'Fix privacy state column';

    public static $description = 'Update privacy state column to be published by default.';

    public function main()
    {
        $db  = ES::db();
        $sql = $db->sql();

        // Update all privacy column for the `state` to be published
        $sql->update('#__social_privacy')
            ->set('state', 1)
            ->set('core', 1);

        $db->setQuery($sql);
        $db->query();

        return true;
    }
}
