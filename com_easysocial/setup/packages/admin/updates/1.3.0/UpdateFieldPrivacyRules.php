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

class SocialMaintenanceScriptUpdateFieldPrivacyRules extends SocialMaintenanceScript
{
    public static $title = 'Update fields to new privacy rules';
    public static $description = 'Update core.view to field.element for all the fields in the privacy table.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        // First we get the core.view privacy id

        $sql->select('#__social_privacy')
            ->column('id')
            ->where('type', 'core')
            ->where('rule', 'view');

        $db->setQuery($sql);

        $origId = $db->loadResult();

        // Then we cache a copy of all the new field rules and the id

        $sql->clear();
        $sql->select('#__social_privacy')
            ->column('id')
            ->column('rule')
            ->where('type', 'field');

        $db->setQuery($sql);

        $rules = $db->loadObjectList('rule');

        // Then we get all the privacy items with this id and type = 'field'

        $sql->clear();
        $sql->select('#__social_privacy_items', 'a')
            ->column('a.*')
            ->column('c.element')
            ->leftjoin('#__social_fields', 'b')
            ->on('b.id', 'a.uid')
            ->leftjoin('#__social_apps', 'c')
            ->on('b.app_id', 'c.id')
            ->where('(')
            ->where('a.type', 'field')
            ->where('a.type', 'field.datetime.year', '=', 'OR')
            ->where(')');

        $db->setQuery($sql);
        $result = $db->loadObjectList();

        // Based on the element, we need to find the new id to map it to the rule
        foreach ($result as $row) {
            if (isset($rules[$row->element])) {
                $table = ES::table('privacyitems');
                $table->bind($row);
                $table->privacy_id = $rules[$row->element]->id;
                $table->store();
            }
        }

        // field.datetime.year need to change to year
        $sql->clear();
        $sql->update('#__social_privacy_items')
            ->set('type', 'year')
            ->where('type', 'field.datetime.year');

        $db->setQuery($sql);
        $db->query();

        return true;
    }
}
