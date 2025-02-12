<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateQuickRegistration extends SocialMaintenanceScript
{
    public static $title = 'Update mini registration visibility value';
    public static $description = 'Update mini registration visibility value';

    public function main()
    {
    	$allowedFields = array('joomla_username', 'joomla_fullname', 'joomla_email', 'joomla_password', 'birthday', 'gender', 'recaptcha', 'address', 'checkbox');

        $db = ES::db();
        $sql = $db->sql();

        $query = "UPDATE `#__social_fields` AS a LEFT JOIN `#__social_apps` AS b ON b.`id` = a.`app_id` SET a.`visible_mini_registration` = '1' WHERE b.`element` IN(";

		$elementQuery = '';

		$total = count($allowedFields);

		for ($i = 0; $i < $total; $i++) {

			$allowedField = $allowedFields[$i];
			$elementQuery .= $db->Quote($allowedField);

			if (($i + 1) < $total) {
				$elementQuery .= ',';
			}
		}

		$query .= $elementQuery . ')';

        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        return true;
    }
}