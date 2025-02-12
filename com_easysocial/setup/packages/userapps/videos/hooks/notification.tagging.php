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

class SocialUserAppVideosHookNotificationTagging
{
    /**
     * Processes tagged notifications
     *
     * @since   2.0
     * @access  public
     */
    public function execute(&$item)
    {
        // Get the actor that is tagging the target
        $actor = ES::user($item->actor_id);

        // Try to get the video
        $table = ES::table('Video');
        $table->load($item->uid);

        // Load up the video
        $video = ES::video($table->uid, $table->type, $table);

        // Set the video image
        $item->image = $video->getThumbnail();
        $item->content = '';        

        // Set the notification title
        $item->title = JText::sprintf('APP_USER_VIDEOS_NOTIFICATIONS_TAGGED', $actor->getName(), $video->getTitle());

        return;
    }
}