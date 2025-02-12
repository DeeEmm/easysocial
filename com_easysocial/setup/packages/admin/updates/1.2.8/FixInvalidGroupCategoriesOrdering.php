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

class SocialMaintenanceScriptFixInvalidGroupCategoriesOrdering extends SocialMaintenanceScript
{
    public static $title = 'Fix invalid group categories ordering';
    public static $description = 'Reassigned newly created group categories with ordering of 0 to a valid ordering and repopulate ordering accordingly.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->select('#__social_clusters_categories');
        $sql->where('type', 'group');
        $sql->where('ordering', '0');

        $db->setQuery($sql);

        $result = $db->loadObjectList();

        foreach ($result as $row) {
            $table = ES::table('GroupCategory');
            $table->bind($row);

            $table->ordering = $table->getNextOrder('type = ' . $db->quote('group'));

            $table->store();
        }

        $sql->clear();

        $sql->select('#__social_clusters_categories');
        $sql->where('type', 'group');
        $sql->order('ordering');
        $sql->order('id');

        $db->setQuery($sql);

        $result = $db->loadObjectList();

        $counter = 1;

        foreach ($result as $row) {
            $table = ES::table('GroupCategory');
            $table->bind($row);

            $table->ordering = $counter;

            $table->store();

            $counter++;
        }

        return true;
    }
}
