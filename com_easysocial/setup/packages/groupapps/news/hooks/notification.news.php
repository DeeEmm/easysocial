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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class SocialGroupAppNewsHookNotificationNews
{
    /**
     * Processes comment notifications
     *
     * @since   1.2
     * @access  public
     * @param   string
     * @return
     */
    public function execute(SocialTableNotification &$item)
    {
        // Get the group item
        $group      = ES::group($item->uid);

        // Get the actor
        $actor      = ES::user($item->actor_id);

        // Set the title of the notification
        $item->title    = JText::sprintf('APP_GROUP_NEWS_USER_POSTED_NEW_NEWS', $actor->getName(), $group->getName());

        if (!$item->context_ids) {
            return;
        }

        // Set the news item
        $news       = ES::table('ClusterNews');
        $news->load($item->context_ids);

        $item->content  = $news->title;

        return $item;
    }
}
