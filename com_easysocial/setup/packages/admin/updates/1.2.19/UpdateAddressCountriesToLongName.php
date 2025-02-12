<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateAddressCountriesToLongName extends SocialMaintenanceScript
{
    public static $title = 'Update address countries to long name.';
    public static $description = 'Update countries short code to full country name in address field.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->select('#__social_fields_data', 'a');
        $sql->column('a.id');
        $sql->column('a.data');
        $sql->leftjoin('#__social_fields', 'b');
        $sql->on('a.field_id', 'b.id');
        $sql->leftjoin('#__social_apps', 'c');
        $sql->on('b.app_id', 'c.id');
        $sql->where('c.type', 'fields');
        $sql->where('c.element', 'address');
        $sql->where('a.datakey', '');

        $db->setQuery($sql);
        $result = $db->loadObjectList();

        $json = ES::json();

        // Load up the list of countries
        $file = SOCIAL_ADMIN_DEFAULTS . '/countries.json';
        $list = ES::makeObject($file);

        foreach ($result as $row) {
            if (!$json->isJsonString($row->data)) {
                continue;
            }

            $data = $json->decode($row->data);

            if ($data->city) {
                $data->city = str_replace( '`', '\'', $data->city);
            }

            if ($data->state) {
                $data->state = str_replace( '`', '\'', $data->state);
            }

            if ($data->zip) {
                $data->zip = str_replace( '`', '\'', $data->zip);
            }

            if (!empty($data->country) && strlen($data->country) === 2 && isset($list->{$data->country})) {
                $data->country = $list->{$data->country};
            }

            // Recreate the json string
            $string = $json->encode($data);

            // Recreate the raw data
            unset($data->latitude);
            unset($data->longitude);
            $raw = implode(array_values((array) $data), ' ');
            $raw = str_replace( '`', '\'', $raw);

            $sql->clear();
            $sql->update('#__social_fields_data');
            $sql->set('data', $string);
            $sql->set('raw', $raw);
            $sql->where('id', $row->id);

            $db->setQuery($sql);
            $db->query();
        }

        return true;
    }
}
