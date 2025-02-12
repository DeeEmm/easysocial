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

class SocialMaintenanceScriptFixFieldUniqueValue extends SocialMaintenanceScript
{
    public static $title = 'Fix Field Unique Value';
    public static $description = 'Reinitializes all the field unique values due to a unique value assignment bug.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        $sql->select('#__social_apps');
        $sql->where('type', 'fields');

        $db->setQuery($sql);

        $result = $db->loadObjectList();

        foreach ($result as $row) {
            $file = SOCIAL_FIELDS . '/' . $row->group . '/' . $row->element . '/' . $row->element . '.xml';

            if (!JFile::exists($file)) {
                continue;
            }

            $table = ES::table('App');
            $table->bind($row);

            $parser = ES::get('Parser');

            $parser->load($file);

            $val = $parser->xpath( 'unique' );

            $unique = 0;

            if ($val) {
                $unique = (string) $val[0];

                $unique = $unique == 'true' ? 1 : 0;
            }

            $table->unique = $unique;

            $table->store();
        }

        return true;
    }
}
