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

class SocialMaintenanceScriptResetClusterCategoriesOrdering extends SocialMaintenanceScript
{
    public static $title = 'Reset cluster categories ordering';
    public static $description = 'Reset the value for the newly introduced cluster cateogires ordering.';

    public function main()
    {
        // No longer in used. Rely on 1.2.8/FixInvalidGroupCategoriesOrdering instead.
        // Return instead of deleting the files to ensure that the file will get overriden with this copy that executes nothing.
        // Deleting the file in the repo doesn't mean that this file will get deleted on client's site.

        return true;

        /*
        $db = ES::db();
        $sql = $db->sql();

        $sql->raw('UPDATE `#__social_clusters_categories` SET `ordering` = `id`');

        $db->setQuery($sql);

        $db->query();

        return true;
        */
    }
}
