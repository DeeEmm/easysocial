<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<tr>
    <td bgcolor="#ffffff">
        <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td style="padding: 24px 24px 10px; font-family: sans-serif; font-size: 13px; line-height: 20px; color: #999999; text-align: left;">
                    <p style="margin: 0;"><?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_SUBHEADING'); ?></p>
                </td>
            </tr>
            <tr>
                <td style="padding: 0px 24px; text-align: left;">
                    <h1 style="margin: 0; font-family: sans-serif; font-size: 22px; line-height: 27px; color: #666666; font-weight: normal;"><?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_HEADING'); ?></h1>
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td dir="ltr" bgcolor="#ffffff" height="100%" valign="top" width="100%" style="padding: 20px 24px 24px; font-family: sans-serif; font-size: 14px; color: #555555; text-align: center;">

        <!--[if mso]>
        <table role="presentation" aria-hidden="true" border="0" cellspacing="0" cellpadding="0" width="660" style="width: 660px;">
        <tr>
        <td valign="top" width="660" style="width: 660px;">
        <![endif]-->
        <table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
            <tr>
                <td bgcolor="#f6f9fb" align="center" style="padding: 24px;">
                    <table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
                        <tr>
                            <td valign="top" width="100%">
                                <p style="color: #999999;line-height:1.5;text-align:left;margin: 0;padding: 0 0 24px;" class="stack-column">
                                    <?php echo JText::_('COM_EASYSOCIAL_EMAILS_HELLO'); ?>,
                                </p>
                                <p style="color: #999999;line-height:1.5;text-align:left;margin: 0;padding: 0 0 24px;" class="stack-column">
                                    <?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_REPORT_NEW_ITEM_REPORTED'); ?>
                                    <a href="<?php echo $reporterLink;?>"><?php echo $reporter;?></a>. <?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_NEW_ITEM_REPORTED_VIEW_DETAILS'); ?>
                                </p>
                                <h3 style="margin-top: 30px;"><u><?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_DETAILS'); ?></u></h3>
                                <div>
                                    <?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_REPORT_TITLE');?>:<br />
                                    <a href="<?php echo $url;?>" target="_blank" style="color:#00aeef; text-decoration:underline;"><?php echo $title; ?></a>
                                </div>

                                <?php if ($actorName) { ?>
                                <div style="margin-top: 15px;">
                                    <?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_REPORT_ITEM_ACTOR_TITLE');?>:<br />
                                    <a href="<?php echo $actorPermalink;?>" target="_blank" style="color:#00aeef; text-decoration:underline;"><?php echo $actorName; ?></a>
                                </div>
                            	<?php } ?>

                                <div style="margin-top: 15px;">
                                    <?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_REPORT_ON');?>:<br />
                                    <?php echo $this->html('string.date', $created, JText::_('DATE_FORMAT_LC2')); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
                        <tr>
                            <td>
                            	<p style="color: #999999;line-height:1.5;text-align:left;margin: 0;padding-top:24px;" class="stack-column">
                                	<?php echo JText::_('COM_EASYSOCIAL_EMAILS_REPORT_REPORT_REASON');?>:
                                </p>

                                <blockquote style="font: 14px/22px normal helvetica, sans-serif;margin-top: 10px;margin-bottom: 10px;margin-left: 0;padding-left: 15px;border-left: 3px solid #ccc;"><?php echo $message; ?></blockquote>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!--[if mso]>
        </td>
        </tr>
        </table>
        <![endif]-->

    </td>
</tr>
