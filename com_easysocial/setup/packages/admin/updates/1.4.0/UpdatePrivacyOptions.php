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

class SocialMaintenanceScriptUpdatePrivacyOptions extends SocialMaintenanceScript
{
    public static $title = 'Update privacy options.';
    public static $description = 'Update privacy options to have Only Me option on certain rules.';

    public function main()
    {
        $db = ES::db();
        $sql = $db->sql();

        // followers
        $rules['followers'] = array('view' => '{"options":["public","member","friend","only_me","custom"]}');

        // story
        $rules['story'] = array('post.comment' => '{"options":["member","friend","only_me","custom"]}');

        //profiles
        $rules['profiles'] = array('view' => '{"options":["public","member","friend","only_me","custom"]}',
                                   'search' => '{"options":["public","member","friends_of_friend","friend","only_me"]}',
                                   'post.message' => '{"options":["public","member","friend","only_me","custom"]}'
                                   );

        foreach($rules as $type => $items) {
            foreach($items as $rule => $options) {
                $query = 'update `#__social_privacy` set `options` = ' . $db->Quote($options) . ' where `type` = ' . $db->Quote($type) . ' and `rule` = '. $db->Quote($rule);

                // echo $query; echo '<br><br>';

                $sql->clear();

                $sql->raw($query);
                $db->setQuery($sql);
                $db->query();
            }
        }

        return true;
    }
}
