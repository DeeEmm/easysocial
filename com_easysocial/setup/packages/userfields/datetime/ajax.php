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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserDatetime extends SocialFieldItem
{
    public function getDatetime()
    {
        $userid = $this->input->get('userid', 0, 'int');
        $user = ES::user($userid);

        $tz = $this->input->get('tz', '', 'string');
        $datetime = $this->input->get('datetime', '', 'string');

        $date = ES::date($datetime, false);

        if ($tz === 'local') {
            $local = $this->input->get('local', 0, 'int');

            $seconds = $local * 60 * 60;

            $date = ES::date($date->toUnix() + $seconds, false);
        } else {
            $dtz = new DateTimeZone($tz);

            $date->setTimezone($dtz);
        }

        $allowYear = true;

        if ($this->params->get('year_privacy')) {
            $allowYear = $this->allowedPrivacy($user, 'year');
        }

        $format = $allowYear ? 'd M Y' : 'd M';

        switch ($this->params->get('date_format')) {
            case 2:
            case '2':
                $format = $allowYear ? 'M d, Y' : 'M d';
                break;
            case 3:
            case '3':
                $format = $allowYear ? 'Y d M' : 'd M';
                break;
            case 4:
            case '4':
                $format = $allowYear ? 'Y M d' : 'M d';
                break;
        }

        if ($this->params->get('allow_time')) {
            $format .= $this->params->get('time_format') == 1 ? ' g:i:sA' : ' H:i:s';
        }

        $string = $date->format($format, true);

        ES::ajax()->resolve($string);
    }
}
