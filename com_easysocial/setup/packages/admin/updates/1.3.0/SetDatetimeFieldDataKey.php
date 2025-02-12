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

class SocialMaintenanceScriptSetDatetimeFieldDataKey extends SocialMaintenanceScript
{
    public static $title = 'Set datetime field data key';
    public static $description = 'Updates datetime and birthday field data to initialising datakey for the fields data.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->select('#__social_fields_data', 'a');
        $sql->column('a.*');
        $sql->leftjoin('#__social_fields', 'b');
        $sql->on('a.field_id', 'b.id');
        $sql->leftjoin('#__social_apps', 'c');
        $sql->on('b.app_id', 'c.id');
        $sql->where('c.type', 'fields');
        $sql->where('c.element', array('birthday', 'datetime'), 'IN');

        $db->setQuery($sql);

        $result = $db->loadObjectList();

        $json = ES::json();

        foreach ($result as $row) {
            $table = ES::table('fielddata');
            $table->bind($row);

            if ($json->isJsonString($table->data)) {
                $table->datakey = 'date';

                $table->store();
            }
        }

        return true;
    }
}
