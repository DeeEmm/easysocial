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
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:<?php echo md5(uniqid(mt_rand(), true));?>

DTSTAMP:<?php echo gmdate('Ymd').'T'. gmdate('His');?>Z

DTSTART:<?php echo $event->getEventStart()->format('Ymd\THis', true);?>

DTEND:<?php echo $event->getEventEnd()->format('Ymd\THis', true);?>

SUMMARY:<?php echo $event->getname();?>

DESCRIPTION:<?php echo $description;?>


X-ALT-DESC;FMTTYPE=text/html:<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">\n<HTML>\n<HEAD>\n<META NAME="Generator" CONTENT="MS Exchange Server version 08.00.0681.000">\n<TITLE></TITLE>\n</HEAD>\n<BODY>\n<!-- Converted from text/rtf format -->\n\n<?php echo preg_replace("/\r\n/", "<br />", $event->description);?>\n\n</BODY>\n</HTML>

LOCATION:<?php echo $event->address;?>

END:VEVENT

END:VCALENDAR
