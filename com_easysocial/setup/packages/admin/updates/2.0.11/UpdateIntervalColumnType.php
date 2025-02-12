<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateIntervalColumnType extends SocialMaintenanceScript
{
    public static $title = 'Update Interval column type in #__social_points table';
    public static $description = 'Update Interval column type in #__social_points table';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $columns = $db->getTableColumns('#__social_points');

        // Check if the column is in the fields or not
        $columnExist = in_array('dummy', $columns);

        if (!$columnExist) {

            $query = "ALTER TABLE `#__social_points` MODIFY COLUMN `interval` int(11) NOT NULL;";

            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();

            $query = "ALTER TABLE `#__social_points` ADD COLUMN `dummy` tinyint(1) NULL default '1' AFTER `params`;";

            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();
        }

        return true;
    }
}
