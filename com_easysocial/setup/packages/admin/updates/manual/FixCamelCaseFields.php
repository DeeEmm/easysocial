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

class SocialMaintenanceScriptFixCamelCaseFields extends SocialMaintenanceScript
{
    public static $title = 'Fix camel case fields';
    public static $description = 'Fix all camel case fields that have issues with custom fields form.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $elements = array('configallowmaybe', 'confignotgoingguest', 'guestlimit');

        $sql->select('#__social_apps');
        $sql->where('element', $elements, 'IN');

        $db->setQuery($sql);
        $result = $db->loadObjectList();

        $toDelete = array();

        foreach ($result as $row) {
            if (in_array($row->element, $elements, true)) {
                $toDelete[] = $row->id;
            }
        }

        if (!empty($toDelete)) {
            $sql->clear();

            $sql->delete('#__social_apps');
            $sql->where('id', $toDelete, 'IN');

            $db->setQuery($sql);

            $db->query();
        }

        return true;
    }
}
