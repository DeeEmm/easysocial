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

class SocialMaintenanceScriptStreamUserAccess extends SocialMaintenanceScript
{
    public static $title = "Sync user's privacy access in stream table.";
    public static $description = "Sync user's privacy access in stream table.";

    public function main()
    {

        // determine what privacy rule to used for each context_type
        $context = array();
        // $context['photos'] = 'photos.view';
        // $context['discuss'] = 'core.view';
        // $context['kunena'] = 'core.view';
        // $context['k2'] = 'core.view';
        // $context['komento'] = 'core.view';
        // $context['blog'] = 'easyblog.blog.view';
        // $context['badges'] = 'core.view';
        // $context['friends'] = 'core.view';
        // $context['links'] = 'story.view';
        // $context['profiles'] = 'core.view';
        // $context['shares'] = 'core.view';
        // $context['story'] = 'story.view';
        // $context['calendar'] = 'core.view';
        // $context['users'] = 'core.view';
        // $context['facebook'] = 'core.view';
        // $context['apps'] = 'core.view';
        // $context['article'] = 'core.view';
        // $context['feeds'] = 'core.view';
        // $context['followers'] = 'followers.view';
        // $context['notes'] = 'core.view';
        // $context['relationship'] = 'core.view';
        //

        $context['photos']  = 'photos.view';
        $context['blog']    = 'easyblog.blog.view';
        $context['links']   = 'story.view';
        $context['story']   = 'story.view';
        $context['followers'] = 'followers.view';


        $state = true;
        $db = ES::db();
        $sql = $db->sql();


        // stored function created. lets get the privacy rules.
        $query = "select `id`, concat( `type`, '.', `rule`) as `rule`, `value` from `#__social_privacy`";

        $sql->raw($query);
        $db->setQuery($sql);

        $results = $db->loadObjectList();

        $privacy = array();
        foreach( $results as $item) {
            $privacy[$item->rule] = $item;
        }


        // lets update the privacy based on user defined privacy which having the one-to-one relationship in stream table vs stream_item table.
        $query = "update `#__social_stream` as s";
        $query .= "    inner join (select b.`uid`, c.`value`, c.`privacy_id` from `#__social_stream_item` as b";
        $query .= "        inner join `#__social_privacy_items` as c on c.`uid` = b.`context_id` and c.`type` = b.`context_type`";
        $query .= "        where `type` != 'story'";
        $query .= "        group by b.`uid`, c.`value`, c.`privacy_id` having (count(b.`uid`) = 1) ) as x on s.`id` = x.`uid`";
        $query .= "    set s.`access` = x.`value`,";
        $query .= "        s.`privacy_id` = x.`privacy_id`";
        $query .= "    where s.`privacy_id` = 0";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        // update story privacy based on user defined value for only story type which the privacy is lower than custom
        $query = "update `#__social_stream` as a";
        $query .= " inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
        $query .= " inner join `#__social_privacy_items` as c on c.`uid` = b.`uid` and c.`type` IN ('story', 'links')";
        $query .= " set a.`access` = c.`value`,";
        $query .= "     a.`privacy_id` = c.`privacy_id`";
        $query .= " where a.`privacy_id` = 0";
        $query .= " and c.`value` < 100";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();



        // update privacy based on user defined value for only story type.
        $query = "update `#__social_stream` as a";
        $query .= " inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
        $query .= " inner join `#__social_privacy_items` as c on c.`uid` = b.`uid` and c.`type` IN ('story', 'links')";
        $query .= " set a.`access` = c.`value`,";
        $query .= "     a.`privacy_id` = c.`privacy_id`,";
        $query .= "     a.`custom_access` = (" . $this->genCustomItemAccess( 'c.`privacy_id`', 'b.`actor_id`', 'b.`uid`', 'b.`context_type`') . ")";
        $query .= " where a.`privacy_id` = 0";
        $query .= " and c.`value` = 100";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();


        // // update privacy based on user defined value for only story type.
        $query = "update `#__social_stream` as a";
        $query .= "  inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
        $query .= "  inner join `#__social_privacy_items` as c on c.`uid` = b.`id` and c.`type` = 'activity'";
        $query .= " set a.`access` = c.`value`,";
        $query .= "     a.`privacy_id` = c.`privacy_id`,";
        $query .= "     a.`custom_access` = (" . $this->genCustomItemAccess( 'c.`privacy_id`', 'b.`actor_id`', 'b.`uid`', 'b.`context_type`') . ")";
        $query .= " where a.`privacy_id` = 0";
        $query .= " and c.`value` = 100";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();


        // // update stream for type other than story custom access
        $query = "update `#__social_stream` as a";
        $query .= " inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
        $query .= "     set a.`custom_access` = (" . $this->genCustomItemAccess( 'a.`privacy_id`', 'a.`actor_id`', 'b.`context_id`', 'b.`context_type`') . ")";
        $query .= "     where a.`context_type` != 'story'";
        $query .= "     and a.`access` = 100";
        $query .= "     and a.`custom_access` = ''";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();


        // // update stream thats using different rule than core.view
        foreach ($context as $contexttype => $rule) {
            $uprivcy = isset($privacy[$rule]) ? $privacy[$rule] : $privacy['core.view'];

            $query = "update `#__social_stream` as a set a.`privacy_id` = $uprivcy->id, a.`access` = (" . $this->genUserPrivacy( $uprivcy->id, 'a.`actor_id`') . ") where a.`cluster_id` = 0 and a.`context_type` = '$contexttype' and a.`privacy_id` = 0";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();
        }

        // //now we update stream that is using core.view
        $uprivcy = $privacy['core.view'];

        $query = "update `#__social_stream` as a set a.`privacy_id` = $uprivcy->id, a.`access` = (" . $this->genUserPrivacy( $uprivcy->id, 'a.`actor_id`') . ") where a.`cluster_id` = 0 and a.`privacy_id` = 0";

        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();


        // // now we need to update the custom_access column for those privacy set to custom.
        $query = "update `#__social_stream` as a set a.`custom_access` = (" . $this->genUserItemAccess('a.`privacy_id`', 'a.`actor_id`') .") where a.`access` = 100 and a.`custom_access` = ''";


        $sql->clear();
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        return $state;
    }

    public function genUserPrivacy($p_ruleid, $p_userid)
    {
        $query = "select pm.`value` from `#__social_privacy_map` as pm";
        $query .= "  where pm.`uid` = $p_userid";
        $query .= "  and pm.`utype` = 'user'";
        $query .= "  and pm.`privacy_id` = $p_ruleid";
        $query .= " union ";
        $query .= " select pm1.`value` from `#__social_privacy_map` as pm1";
        $query .= "  inner join `#__social_profiles_maps` as prm on pm1.`uid` = prm.`profile_id` and pm1.`utype` = 'profiles'";
        $query .= "  where prm.`user_id` = $p_userid";
        $query .= "  and pm1.`privacy_id` = $p_ruleid";
        $query .= " limit 1";

        return $query;
    }
    public function genUserItemAccess($p_ruleid, $p_userid)
    {
        $query = "select concat( ',', group_concat( pc.`user_id` SEPARATOR ',' ), ',' ) from `#__social_privacy_customize` as pc";
        $query .= "    inner join `#__social_privacy_map` as pm on pc.`uid` = pm.`id` and pc.`utype` = 'user'";
        $query .= " where pm.`privacy_id` = $p_ruleid and pm.`uid` = $p_userid and pm.`utype` = 'user'";

        return $query;
    }

    public function genCustomItemAccess($p_ruleid, $p_userid, $p_uid, $p_type)
    {
        $query = "select concat( ',', group_concat( pc.`user_id` SEPARATOR ',' ), ',' ) from `#__social_privacy_customize` as pc";
        $query .= " inner join `#__social_privacy_items` as pi on pc.`uid` = pi.`id`";
        $query .= " where pi.`type` = $p_type and pi.`uid` = $p_uid and pc.`utype` = 'item' and pi.`privacy_id` = $p_ruleid and pi.`user_id` = $p_userid";

        return $query;
    }
}
