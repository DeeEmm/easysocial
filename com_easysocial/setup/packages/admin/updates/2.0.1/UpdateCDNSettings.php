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

class SocialMaintenanceScriptUpdateCDNSettings extends SocialMaintenanceScript
{
    public static $title = 'Update CDN Settings.';
    public static $description = 'Remove CDN url if CDN disabled in previous version.';

    public function main()
    {

        $state = true;
        $config = ES::config();
        $cdnEnabled = $config->get('general.cdn.enabled', null);
        $cdnURL = $config->get('general.cdn.url', '');

        if (!is_null($cdnEnabled) && !$cdnEnabled && $cdnURL) {
            // cdn disabled. lets remove the cdn url.
            $config->set('general.cdn.url', '');

            // Convert the config object to a json string.
            $jsonString = $config->toString();

            $configTable = ES::table('Config');
            if ( !$configTable->load('site')) {
                $configTable->type  = 'site';
            }

            $configTable->set('value' , $jsonString);
            $state = $configTable->store();
        }

        return $state;
    }
}
