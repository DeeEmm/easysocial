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

class SocialMaintenanceScriptUpdatePhotoMetaPath extends SocialMaintenanceScript
{
    public static $title = 'Update photo meta path.';
    public static $description = 'Update the photo meta path to the path set in configuration for existing photos.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $config     = ES::config();
        $relative   = rtrim( $config->get('photos.storage.container'), '/' ) . '/';

        // lets get the paths that need to be updated.
        $query = "select `value` from `#__social_photos_meta`";
        $query .= " where `group` = 'path'";
        $query .= " and `value` not like '$relative%'";
        $query .= ' limit 1';
        // $query .= " and `photo_id` in ( select id from `#__social_photos` where `user_id` = '84' and `album_id` = '229' )";

        $sql->raw($query);
        $db->setQuery($sql);
        $results = $db->loadObjectList();

        $paths = array();
        if ($results) {
            // foreach ($results as $item) {
                $item = $results[0];

                //$pattern = '/.*\/\D*\//i';
                $pattern = '/\/.*\//i';

                preg_match( $pattern , $item->value , $matches );

                if ($matches) {
                    // we do not want the [albumid]/[photoid] segments.
                    $path = $matches[0];
                    $path = rtrim( $path, '/' );
                    $path = ltrim( $path, '/' );
                    $segments = explode( '/' , $path);

                    // remove the last two elements since we knwo that is the album id and photo id.
                    array_pop($segments);
                    array_pop($segments);

                    //now we glue the segments back
                    $segmentPath = implode('/', $segments);

                    //now we need to add back the leading / and ending /
                    $segmentPath = '/' . $segmentPath . '/';

                    if (! in_array($segmentPath, $paths)) {
                        $paths[] = $segmentPath;
                    }
                }
            // } //end foreach
        } //end if

        // if found, lets replace these paths to the one admin configured.
        if ($paths) {
            foreach ($paths as $pitem) {

                $query  = "UPDATE `#__social_photos_meta` SET `value` = replace(`value`, '$pitem' , '$relative')";
                $query .= " WHERE `group`= 'path'";
                $query .= " and `value` like '$pitem%'";

                $sql->clear();
                $sql->raw($query);

                $db->setQuery($sql);
                $db->Query();
            }
        }

        return true;
    }
}
