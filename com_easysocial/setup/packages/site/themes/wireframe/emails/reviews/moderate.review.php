<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
				<td style="padding: 24px; text-align: left;">
					<h1 style="margin: 0; font-family: sans-serif; font-size: 22px; line-height: 27px; color: #666666; font-weight: normal;"><?php echo JText::sprintf('COM_ES_EMAILS_REVIEW_PENDING_MODERATION_HEADING'); ?></h1>
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
									<?php echo JText::_('COM_EASYSOCIAL_EMAILS_HELLO'); ?> <?php echo $recipientName; ?>,
								</p>
								<p style="color: #999999;line-height:1.5;text-align:left;margin: 0;padding: 0 0 40px;" class="stack-column">
									<?php echo JText::sprintf('COM_ES_EMAILS_REVIEW_PENDING_MODERATION_CONTENT', '<a href="' . $userLink . '">' . $userName . '</a>', '<a href="' . $clusterLink . '">' . $clusterName . '</a>');?>
								</p>
							</td>
						</tr>
					</table>
					<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
						<tbody>
							<tr>
								<td valign="middle" style="padding: 0 16px;">
								<table align="left" style="font-size: 14px;margin: 0 auto 10px 20px; text-align:left;color:#798796" align="">
										<tr>
											<td style="text-align: left;">
												<h2 style="margin-bottom: 10px;">
													<a href="<?php echo $permalink;?>" style="text-decoration: none;color:#00aeef;"><strong><?php echo $title;?></strong></a>
												</h2>
											</td>
										</tr>
										<tr>
											<td style="padding: 5px 0;">
												<?php echo $message; ?>
											</td>
										</tr>
										<tr>
											<td style="padding: 5px 0;">
												<a href="<?php echo $reject;?>" style="
													display:inline-block;
													text-decoration:none;
													font-weight:bold;
													margin-top: 20px;
													border-top: 10px solid #B41C1B;
													border-bottom: 10px solid #B41C1B;
													border-left: 25px solid #B41C1B;
													border-right: 25px solid #B41C1B;
													line-height:20px;
													color:#fff;font-size: 12px;
													background-color: #B41C1B;
													text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
													border-style: solid;
													box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
													border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;
													">
														<?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON');?>
													</a>

													<a href="<?php echo $approve;?>" style="
													display:inline-block;
													text-decoration:none;
													font-weight:bold;
													margin-top: 20px;
													border-top: 10px solid #83B3DD;
													border-bottom: 10px solid #83B3DD;
													border-left: 25px solid #83B3DD;
													border-right: 25px solid #83B3DD;
													line-height:20px;
													color:#fff;font-size: 12px;
													background-color: #83B3DD;
													text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
													border-style: solid;
													box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
													border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;
													">
														<?php echo JText::_('COM_ES_EMAILS_APPROVE_REVIEW_BUTTON');?>
													</a>
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