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

class SocialMaintenanceScriptCreateStoredFunction extends SocialMaintenanceScript
{
    public static $title = 'create db stored function - es_isfriend';
    public static $description = 'create db stored function - es_isfriend';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        //drop function
        $query = "DROP FUNCTION IF EXISTS `es_isfriend`";
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        // create function
        $query = "CREATE FUNCTION `es_isfriend` (p_source int, p_target int)
                    RETURNS INT DETERMINISTIC
                    BEGIN
                        DECLARE cnt INT;
                        select count(1) into cnt from `#__social_friends` where ( `actor_id` = p_source and `target_id` = p_target) OR (`target_id` = p_source and `actor_id` = p_target) and `state` = 1;
                        RETURN cnt;
                    END
                ";

        // $state = true;

        $sql->raw($query);
        $db->setQuery($sql);

        $state = $db->query();

        return $state;
    }
}
