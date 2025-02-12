<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EventLinksHelper
{
     /**
     * Processes videos
     *
     * @since    1.0
     * @access    public
     * @param    string
     * @return
     */
    public static function processVideos($url)
    {
        $output = self::getVideoHtml($url);

        return $output;
    }

    /**
     * Retrieves HTML code for the respective video provider
     *
     * @since    1.0
     * @access    public
     * @param    string
     * @return
     */
    public static function getVideoHtml($url)
    {
        $provider = self::getVideoProvider($url);

        $theme = ES::themes();
        $theme->set('url', $url);
        $output = $theme->output('themes:/apps/user/links/videos/' . $provider);

        return $output;
    }

    /**
     * Retrieve the video provider
     *
     * @since    1.0
     * @access    public
     */
    public static function getVideoProvider($video)
    {
        $providers = array(
                            'youtube.com' => 'youtube',
                            'youtu.be' => 'youtube',
                            'vimeo.com' => 'vimeo',
                            'yahoo.com' => 'yahoo',
                            'metacafe.com' => 'metacafe',
                            'google.com' => 'google',
                            'mtv.com' => 'mtv',
                            'revver.com' => 'revver',
                            'dailymotion.com' => 'dailymotion'
                        );

        preg_match('/http\:\/\/(.*)\//i', $video, $matches);
        $url = $matches[0];
        $url = parse_url($url);
        $url = explode('.', $url['host']);

        // Last two parts will always be the domain name.
        $url = $url[count($url) - 2] . '.' . $url[count($url) - 1];

        if (!empty($url) && array_key_exists($url, $providers)) {
            $provider = $providers[$url];

            return $provider;
        }

        return false;
    }
}
