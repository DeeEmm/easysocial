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

class SocialMaintenanceScriptRemoveIndexerMenu extends SocialMaintenanceScript
{
    public static $title = 'Remove indexer menu';
    public static $description = 'Remove unused indexer menu from backend sidebar.';

    public function main()
    {
        $file = SOCIAL_ADMIN_DEFAULTS . '/sidebar/indexer.json';

        if (JFile::exists($file)) {
            JFile::delete($file);
        }

        return true;
    }
}
