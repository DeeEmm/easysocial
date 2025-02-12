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
?>
<tr>
	<td bgcolor="#ffffff">
		<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td style="padding: 24px 24px 10px; font-family: sans-serif; font-size: 13px; line-height: 20px; color: #999999; text-align: left;">
					<p style="margin: 0;"><?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_REGISTRATION_SUBHEADING' ); ?></p>
				</td>
			</tr>
			<tr>
				<td style="padding: 0px 24px; text-align: left;">
					<h1 style="margin: 0; font-family: sans-serif; font-size: 22px; line-height: 27px; color: #666666; font-weight: normal;"><?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_REGISTRATION_HEADING' ); ?></h1>
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
									<?php echo JText::sprintf('COM_ES_EMAILS_REGISTRATION_THANK_YOU_FOR_REGISTERING', $site); ?>
								</p>
							</td>
						</tr>
						<tr>
							<td style="padding: 5px 0;">
								<a href="<?php echo $activation;?>" style="color:#00aeef;text-decoration:none;"><?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_ACTIVATE_NOW' );?> &rarr;</a>
							</td>
						</tr>
						<tr>
							<td valign="top" width="100%">
								<p style="color: #999999;line-height:1.5;text-align:left;margin: 0;padding: 24px 0 40px;" class="stack-column">
									<?php echo JText::_('COM_ES_EMAILS_REGISTRATION_ACTIVATION_ALTERNATIVE'); ?>
									<a href="<?php echo FRoute::registration( array( 'external' => true , 'layout' => 'activation' , 'userid' => $id ) );?>"><?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_REGISTRATION_ACTIVATION_ALTERNATIVE_THIS_PAGE');?></a>.
								</p>

								<div style="margin-top: 40px; margin-bottom: 40px;">
									<span style="font-size:16px;"><?php echo $token; ?></span>
								</div>
							</td>
						</tr>
					</table>
					<table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
						<tr>
							<td valign="top" width="64">
								<span style="display:block;width:64px;border-radius:50%; -moz-border-radius:50%; -webkit-border-radius:50%;background:#fff">
									<img src="<?php echo $avatar;?>" alt="" style="border-radius:50%; -moz-border-radius:50%; -webkit-border-radius:50%;background:#fff;vertical-align:middle;" width="64" height="64"/>
								</span>
							</td>
							<td valign="middle" style="padding: 0 16px;">
								<table align="left" style="font-size: 14px;margin: 0 auto 10px 20px; text-align:left;color:#798796" align="">
									<tr>
										<td style="padding: 5px 0;">
											<?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_USERNAME' ); ?>: <?php echo $username;?>
										</td>
									</tr>
								</table>
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