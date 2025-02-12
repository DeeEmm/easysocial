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

class SocialMaintenanceScriptAddNewPrivacyIntoProfiles extends SocialMaintenanceScript
{
    public static $title = 'Add new privacy rule into profile types';
    public static $description = 'Add new privacy rule story.post.comment into profile types.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

       //get privacy rule id.
       $query = 'select id from `#__social_privacy` where `type` = ' . $db->Quote('story') . ' and `rule` = ' . $db->Quote('post.comment');
       $sql->raw($query);
       $db->setQuery($sql);

       $ruleId = $db->loadResult();
       if (empty($ruleId)) {
            return false;
       }

       // now lets insert the new privacy rules into privacy mapping table for profile types
       $query = "insert into `#__social_privacy_map` (`privacy_id`, `uid`, `utype`, `value`)";
       $query .= " select '$ruleId', `id`, 'profiles', '10' from `#__social_profiles` where id not in (select distinct uid from `#__social_privacy_map` where `utype` = 'profiles' and `privacy_id` = $ruleId)";
       $sql->clear();

       $sql->raw($query);
       $db->setQuery($sql);
       return $db->query();
    }
}
