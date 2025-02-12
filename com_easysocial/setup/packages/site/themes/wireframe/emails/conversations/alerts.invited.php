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
?>
<tr>
	<td style="text-align: center;padding: 40px 10px 0;">
		<div style="margin-bottom:15px;">
			<div style="font-family:Arial;font-size:32px;font-weight:normal;color:#333;display:block; margin: 4px 0">
				<?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_CONVERSATIONS_ADDED_TO_CONVERSATION' ); ?>
			</div>
			<div style="font-size:12px; color: #798796;font-weight:normal">
				<?php echo JText::sprintf( 'COM_EASYSOCIAL_EMAILS_CONVERSATIONS_ADDED_TO_CONVERSATION_DESC' , $authorName , $total ); ?>
			</div>
		</div>
	</td>
</tr>


<tr>
	<td style="text-align: center;font-size:12px;color:#888">

		<div style="margin:30px auto;text-align:center;display:block">
			<img src="<?php echo $divider;?>" alt="<?php echo JText::_( 'divider' );?>" />
		</div>

		<table align="center" border="0" cellpadding="0" cellspacing="0" style="table-layout:fixed;width:100%;">
		<tr>
		<td align="center">
			<table width="540" cellspacing="0" cellpadding="0" border="0" align="center" style="table-layout:fixed;margin: 0 auto;">
				<tr>
					<td>
						<p style="text-align:left;">
							<?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_HELLO' ); ?> <?php echo $name; ?>,
						</p>

						<p style="text-align:left;">
							<?php echo JText::sprintf( 'COM_EASYSOCIAL_EMAILS_CONVERSATIONS_ADDED_TO_CONVERSATION_INFO' , $authorName );?>
						</p>
					</td>
				</tr>
			</table>
		</td>
		</tr>
		</table>

		<a style="
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
				" href="<?php echo $conversationLink;?>"><?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_VIEW_CONVERSATION' );?></a>

	</td>
</tr>
