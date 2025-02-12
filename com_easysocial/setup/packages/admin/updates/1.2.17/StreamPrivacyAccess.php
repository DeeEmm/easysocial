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

class SocialMaintenanceScriptStreamPrivacyAccess extends SocialMaintenanceScript
{
    public static $title = 'Sync privacy access in stream table.';
    public static $description = 'Sync privacy access in stream table.';

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


        $db = ES::db();
        $sql = $db->sql();

        //drop es_isfriend function
        $query = "DROP FUNCTION IF EXISTS `es_userprivacy`";
        $sql->raw($query);
        $db->setQuery($sql);
        $db->query();

        // create es_isfriend function
        $query = "CREATE FUNCTION `es_userprivacy` (p_ruleid int, p_userid int)
                    RETURNS INT DETERMINISTIC
                    BEGIN
                        DECLARE privacy INT;
                        select x.`value` into privacy from (
                            select a.`value` from `#__social_privacy_map` as a
                                where a.`uid` = p_userid
                                and a.`utype` = 'user'
                                and a.`privacy_id` = p_ruleid
                            union
                            select a.`value` from `#__social_privacy_map` as a
                                inner join `#__social_profiles_maps` as b on a.`uid` = b.`profile_id` and a.`utype` = 'profiles'
                                where b.`user_id` = p_userid
                                and a.`privacy_id` = p_ruleid
                            limit 1
                        ) as x;
                        RETURN privacy;
                    END
                ";

        $sql->raw($query);
        $db->setQuery($sql);

        $state = $db->query();

        if ($state) {

            //drop es_isfriend function
            $query = "DROP FUNCTION IF EXISTS `es_usercustomaccess`";
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();

            // create es_isfriend function
            $query = "CREATE FUNCTION `es_usercustomaccess` (p_ruleid int, p_userid int)
                        RETURNS TEXT DETERMINISTIC
                        BEGIN
                            DECLARE userlist TEXT;
                            DECLARE v_userid INT;
                            DECLARE done INT DEFAULT FALSE;

                            DECLARE csr CURSOR FOR
                                select a.`user_id` from `#__social_privacy_customize` as a
                                    inner join `#__social_privacy_map` as b on a.`uid` = b.`id` and a.`utype` = 'user'
                                where b.`privacy_id` = p_ruleid and b.`uid` = p_userid and b.`utype` = 'user';

                            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                            SET userlist = '';

                            OPEN csr;
                            read_loop: LOOP
                                FETCH csr INTO v_userid;

                                IF done THEN
                                    LEAVE read_loop;
                                END IF;

                                SET userlist = CONCAT(v_userid,',',userlist);

                            END LOOP;
                            CLOSE csr;

                            SET userlist = CONCAT(',',userlist);

                            IF userlist = ',' THEN
                                SET userlist = '';
                            END IF;

                            RETURN userlist;
                        END
                    ";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);

            $state = $db->query();

        }

        if ($state) {

            //drop es_isfriend function
            $query = "DROP FUNCTION IF EXISTS `es_itemcustomaccess`";
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();

            // create es_isfriend function
            $query = "CREATE FUNCTION `es_itemcustomaccess` (p_ruleid int, p_userid int, p_uid int, p_type varchar(255), p_value int)
                        RETURNS TEXT DETERMINISTIC
                        BEGIN
                            DECLARE userlist TEXT;
                            DECLARE v_userid INT;
                            DECLARE done INT DEFAULT FALSE;

                            DECLARE csr CURSOR FOR
                                select a.`user_id` from `#__social_privacy_customize` as a
                                    inner join `#__social_privacy_items` as b on a.`uid` = b.`id` and b.`type` = p_type and b.`uid` = p_uid
                                where a.`utype` = 'item' and b.`privacy_id` = p_ruleid and b.`user_id` = p_userid;

                            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                            SET userlist = '';

                            IF p_value = 100 THEN
                                OPEN csr;
                                read_loop: LOOP
                                    FETCH csr INTO v_userid;

                                    IF done THEN
                                        LEAVE read_loop;
                                    END IF;

                                    SET userlist = CONCAT(v_userid,',',userlist);

                                END LOOP;
                                CLOSE csr;

                                SET userlist = CONCAT(',',userlist);

                                IF userlist = ',' THEN
                                    SET userlist = '';
                                END IF;
                            END IF;

                            RETURN userlist;
                        END
                    ";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);

            $state = $db->query();
        }

        if ($state) {
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


            // update privacy based on user defined value for only story type.
            $query = "update `#__social_stream` as a";
            $query .= " inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
            $query .= " inner join `#__social_privacy_items` as c on c.`uid` = b.`uid` and c.`type` = 'story'";
            $query .= " set a.`access` = c.`value`,";
            $query .= "     a.`privacy_id` = c.`privacy_id`,";
            $query .= "     a.`custom_access` = es_itemcustomaccess(c.`privacy_id`, b.`actor_id`, b.`uid`, b.`context_type`, c.`value`)";
            $query .= " where a.`privacy_id` = 0";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();


            // update privacy based on user defined value for only story type.
            $query = "update `#__social_stream` as a";
            $query .= "  inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
            $query .= "  inner join `#__social_privacy_items` as c on c.`uid` = b.`id` and c.`type` = 'activity'";
            $query .= " set a.`access` = c.`value`,";
            $query .= "     a.`privacy_id` = c.`privacy_id`,";
            $query .= "     a.`custom_access` = es_itemcustomaccess(c.`privacy_id`, b.`actor_id`, b.`uid`, b.`context_type`, c.`value`)";
            $query .= " where a.`privacy_id` = 0";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();


            // update stream for type other than story custom access
            $query = "update `#__social_stream` as a";
            $query .= " inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
            $query .= "     set a.`custom_access` = es_itemcustomaccess(a.`privacy_id`, a.`actor_id`, b.`context_id`, b.`context_type`, a.`access`)";
            $query .= "     where a.`context_type` != 'story'";
            $query .= "     and a.`access` = 100";
            $query .= "     and a.`custom_access` = ''";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();


            // update stream thats using different rule than core.view
            foreach ($context as $contexttype => $rule) {
                $uprivcy = isset($privacy[$rule]) ? $privacy[$rule] : $privacy['core.view'];

                $query = "update `#__social_stream` set `privacy_id` = $uprivcy->id, `access` = es_userprivacy($uprivcy->id, actor_id) where `cluster_id` = 0 and `context_type` = '$contexttype' and `privacy_id` = 0";
                $sql->clear();

                $sql->raw($query);

                $db->setQuery($sql);
                $db->query();
            }

            //now we update stream that is using core.view
            $uprivcy = $privacy['core.view'];

            $query = "update `#__social_stream` set `privacy_id` = $uprivcy->id, `access` = es_userprivacy($uprivcy->id, actor_id) where `cluster_id` = 0 and `privacy_id` = 0";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();


            // now we need to update the custom_access column for those privacy set to custom.
            $query = "update #__social_stream set `custom_access` = es_usercustomaccess(`privacy_id`, `actor_id`) where `access` = 100 and custom_access = ''";

            $sql->clear();
            $sql->raw($query);
            $db->setQuery($sql);
            $db->query();


        }

        return $state;
    }
}
