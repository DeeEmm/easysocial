<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<tr>
	<td style="text-align: center;padding: 40px 10px 0;">
		<div style="margin-bottom:15px;">
			<div style="font-family:Arial;font-size:32px;font-weight:normal;color:#333;display:block; margin: 4px 0">
				<?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_EVENT_GUEST_WITHDRAW_TITLE', $actor, $event); ?>
			</div>
		</div>
	</td>
</tr>
<tr>
	<td style="text-align: center;font-size:12px;color:#888">
		<div style="margin:30px auto;text-align:center;display:block">
			<img src="<?php echo $divider;?>" alt="<?php echo JText::_('divider'); ?>" />
		</div>

		<table align="center" border="0" cellpadding="0" cellspacing="0" style="table-layout:fixed;width:100%;">
		<tr>
		<td align="center">
			<table width="540" cellspacing="0" cellpadding="0" border="0" align="center" style="table-layout:fixed;margin: 0 auto;">
				<tr>
					<td>
						<p style="text-align:left;">
							<?php echo JText::_('COM_EASYSOCIAL_EMAILS_HELLO'); ?> <?php echo $recipientName; ?>,
						</p>
					</td>
				</tr>
			</table>

			<table width="540" cellspacing="0" cellpadding="0" border="0" align="center" style="table-layout:fixed;margin: 20px auto 0;background-color:#f8f9fb;padding:15px 20px;">
				<tbody>
					<tr>
						<td valign="top" width="100">
							<span style="display:block;margin: 0px auto 20px;border:1px solid #f5f5f5;width:64px;padding:3px;border-radius:50%; -moz-border-radius:50%; -webkit-border-radius:50%;background:#fff">
								<a href="<?php echo $actorLink;?>"><img src="<?php echo $actorAvatar;?>" alt="<?php echo $this->html('string.escape', $actor);?>" style="border-radius:50%; -moz-border-radius:50%; -webkit-border-radius:50%;background:#fff" width="64" height="64"/></a>
							</span>
						</td>
						<td valign="top" style="color:#888;background-color:#f8f9fb;text-align:left">
							<p style="margin:0 0 5px;font-weight:bold;font-size:13px;">
								<?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_EVENT_GUEST_WITHDRAW_CONTENT', $actor, $event); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		</tr>
		</table>
	</td>
</tr>
