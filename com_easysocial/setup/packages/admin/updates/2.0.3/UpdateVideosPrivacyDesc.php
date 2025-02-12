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

class SocialMaintenanceScriptUpdateVideosPrivacyDesc extends SocialMaintenanceScript
{
    public static $title = 'Update videos view privacy description';
    public static $description = 'Update the description of video view privacy rules';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $query = "UPDATE `#__social_privacy` SET `description` = 'COM_EASYSOCIAL_PRIVACY_DESC_VIDEOS_VIEW' WHERE `type` = 'videos' AND `rule` = 'view'";
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        return true;
    }
}