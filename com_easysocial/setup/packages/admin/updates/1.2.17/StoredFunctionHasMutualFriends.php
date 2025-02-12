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

class SocialMaintenanceScriptStoredFunctionHasMutualFriends extends SocialMaintenanceScript
{
    public static $title = 'create db stored function - hasmutualfriends';
    public static $description = 'create db stored function - hasmutualfriends';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        //drop es_isfriend function
        $query = "DROP FUNCTION IF EXISTS `es_hasmutualfriend`";
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        // create es_isfriend function
        $query = "CREATE FUNCTION `es_hasmutualfriend` (p_source int, p_target int)
                    RETURNS INT DETERMINISTIC
                    BEGIN
                        DECLARE cnt INT;
                        select count(1) into cnt from (
                            SELECT ( CASE f2.`actor_id` WHEN CASE f1.`actor_id` WHEN p_source THEN f1.`target_id` ELSE f1.`actor_id` END THEN f2.`target_id` ELSE f2.`actor_id` END ) as fid
                            FROM    jos_social_friends f1
                            JOIN    jos_social_friends f2
                            ON      f2.`actor_id` = CASE f1.`actor_id` WHEN p_source THEN f1.`target_id` ELSE f1.`actor_id` END
                                    OR f2.`target_id` = CASE f1.`actor_id` WHEN p_source THEN f1.target_id ELSE f1.`actor_id` END
                            WHERE   (f1.`actor_id` = p_source OR f1.`target_id` = p_source)
                                    AND f1.`state` = 1
                                    AND f2.`state` = 1
                                    AND NOT (f1.`actor_id`, f1.`target_id`) = (f2.`actor_id`, f2.`target_id`) ) as ff where ff.`fid` = p_target;
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
