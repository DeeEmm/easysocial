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

class SocialMaintenanceScriptMergeUsersPointsHistory extends SocialMaintenanceScript
{
    public static $title = 'Merge Users Points History';
    public static $description = 'Automatically merge all user points history into a single record to reduce amount of overhead on the points history table';

    public function main()
    {
        // Loop through 200 users at one time
        $limit = 200;


        // Set all points status to 0 first.
        $db = ES::db();
        $sql = $db->sql();

        $sql->update('#__social_points_history');
        $sql->set('state', 0);

        $db->setQuery($sql);
        $db->Query();


        // Now we need to merge all of these records into 1
        $sql->clear();

        // We need to use Joomla's date instead of mysql's NOW() because the timezone on mysql could be different.
        $date = ES::date();

        $query   = array();
        $query[] = 'INSERT INTO `#__social_points_history`(`points_id`,`user_id`,`points`,`created`,`state`,`message`)';
        $query[] = "SELECT 0, `user_id` ,SUM(`points`), '" . $date->toSql() . "', 1, 'COM_EASYSOCIAL_POINTS_AGGREGATED' FROM `#__social_points_history` GROUP BY `user_id`";

        $query = implode(' ', $query);
        $sql->raw($query);
        $db->setQuery($sql);
        $db->Query();

        // Then we need to delete all the unpublished records
        $sql->clear();
        $sql->delete('#__social_points_history');
        $sql->where('state', 0);
        $db->setQuery($sql);
        $db->Query();


        // Also delete any points history that is assigned to guests
        $sql->clear();
        $sql->delete('#__social_points_history');
        $sql->where('user_id', 0);
        $db->setQuery($sql);
        $db->Query();

    }
}
