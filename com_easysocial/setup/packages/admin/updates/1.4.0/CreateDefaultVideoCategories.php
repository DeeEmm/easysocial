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

class SocialMaintenanceScriptCreateDefaultVideoCategories extends SocialMaintenanceScript
{
    public static $title = 'Create Default Video Categories.';
    public static $description = 'Creates the default video categories if there are no categories created yet.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        // Check if there are any video categories already exists on the site
        $sql->select('#__social_videos_categories');
        $sql->column('COUNT(1)');

        $db->setQuery($sql);
        $total = $db->loadResult();

        if ($total > 0) {
            return true;
        }

        $items = array('General', 'Music', 'Sports', 'News', 'Gaming', 'Movies', 'Documentary', 'Fashion', 'Travel', 'Technology');

        $i = 0;

        foreach ($items as $item) {
            $category = ES::table('VideoCategory');
            $category->title = ucfirst($item);
            $category->alias = strtolower($item);

            if ($i == 0) {
                $category->default = true;
            }

            // Get the current user's id
            $category->user_id = ES::user()->id;

            $category->state = true;
            $category->store();

            $i++;
        }

        return true;
    }
}
