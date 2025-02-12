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
<tr>
	<td bgcolor="#ffffff">
		<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td style="padding: 24px; text-align: left;">
					<h1 style="margin: 0; font-family: sans-serif; font-size: 22px; line-height: 27px; color: #666666; font-weight: normal;"><?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_GROUP_NEW_FILE', $group); ?></h1>
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
				<td bgcolor="#f6f9fb" align="center" style="padding: 24px;">
					<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
						<tr>
							<td valign="top" width="100%">
								<p style="color: #999999;line-height:1.5;text-align:left;margin: 0;padding: 0 0 40px;" class="stack-column">
									<?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_GROUP_NEW_FILE_CONTENT', '<a href="' . $posterLink . '" style="color:#00aeef; text-decoration:underline;">' . $actor . '</a>', '<a href="' . $groupLink . '" style="color:#00aeef; text-decoration:underline;">' . $group . '</a>');?>
								</p>
							</td>
						</tr>
					</table>
					<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
						<tbody>
							<tr>
								<td valign="top">
									<table align="left" style="font-size: 14px;margin: 0 auto 10px 20px; text-align:left;color:#798796" align="">
										<?php if ($streamContent) { ?>
										<tr>
											<td style="padding: 5px 0;">
												<b><?php echo $streamContent;?></b>
											</td>
										</tr>
										<?php } ?>
										<tr>
											<td style="padding: 5px 0;">
												<?php echo JText::_('COM_EASYSOCIAL_EMAILS_FILE_NAME'); ?>: <a href="<?php echo $permalink;?>" style="color:#00aeef;text-decoration:underline;"><b><?php echo $message;?></b></a>
											</td>
										</tr>
										<tr>
											<td style="padding: 5px 0;">
												<?php echo JText::_('COM_EASYSOCIAL_EMAILS_GROUP_NEW_FILE_BY');?>: <a href="<?php echo $posterLink;?>" style="color:#00aeef; text-decoration:underline;"><?php echo $actor;?></a>
											</td>
										</tr>
										<tr>
											<td style="padding: 24px 0 5px;">
												<a href="<?php echo $permalink;?>" style="color:#00aeef;text-decoration:none;"><?php echo JText::_('COM_EASYSOCIAL_EMAILS_VIEW_FILE_BUTTON');?> &rarr;</a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
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
