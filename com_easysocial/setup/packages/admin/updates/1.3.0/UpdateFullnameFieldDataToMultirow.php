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

class SocialMaintenanceScriptUpdateFullnameFieldDataToMultirow extends SocialMaintenanceScript
{
    public static $title = 'Update fullname field data to multirow';
    public static $description = 'Populate fullname field data from single row json data to multirow data.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->select('#__social_fields_data', 'a')
            ->column('a.*')
            ->leftjoin('#__social_fields', 'b')
            ->on('b.id', 'a.field_id')
            ->leftjoin('#__social_apps', 'c')
            ->on('c.id', 'b.app_id')
            ->where('a.datakey', '')
            ->where('c.type', 'fields')
            ->where('c.element', 'joomla_fullname');

        $db->setQuery($sql);

        $result = $db->loadObjectList();

        $json = ES::json();

        foreach ($result as $row) {
            if ($json->isJsonString($row->data)) {
                $data = $json->decode($row->data);

                // Split json object into each individual row
                foreach ($data as $k => $v) {
                    if (!empty($v)) {
                        $table = $this->getTable($row->field_id, $row->uid, $row->type, $k);
                        $table->data = $v;
                        $table->raw = $v;
                        $table->store();
                    }
                }
            }

            // Remove the row with empty key
            $sql->clear();
            $sql->delete('#__social_fields_data')
                ->where('id', $row->id);
            $db->setQuery($sql);
            $db->query();
        }

        return true;
    }

    private function getTable($field_id, $uid, $type, $key)
    {
        $table = ES::table('FieldData');
        $table->load(array('field_id' => $field_id, 'uid' => $uid, 'type' => $type, 'datakey' => $key));

        return $table;
    }
}
