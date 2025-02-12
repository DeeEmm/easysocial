<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<base href="<?php echo JURI::root();?>" target="_blank" />
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="x-apple-disable-message-reformatting">
	<title></title>

	<style>

		/* What it does: Remove spaces around the email design added by some email clients. */
		/* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
		html,
		body {
			margin: 0 auto !important;
			padding: 0 !important;
			height: 100% !important;
			width: 100% !important;
		}

		/* What it does: Stops email clients resizing small text. */
		* {
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		/* What it does: Centers email on Android 4.4 */
		div[style*="margin: 16px 0"] {
			margin:0 !important;
		}

		/* What it does: Stops Outlook from adding extra spacing to tables. */
		table,
		td {
			mso-table-lspace: 0pt !important;
			mso-table-rspace: 0pt !important;
		}

		/* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
		table {
			border-spacing: 0 !important;
			border-collapse: collapse !important;
			table-layout: fixed !important;
			margin: 0 auto !important;
		}
		table table table {
			table-layout: auto;
		}

		/* What it does: Uses a better rendering method when resizing images in IE. */
		img {
			-ms-interpolation-mode:bicubic;
		}

		/* What it does: A work-around for iOS meddling in triggered links. */
		*[x-apple-data-detectors] {
			color: inherit !important;
			text-decoration: none !important;
		}

		/* What it does: A work-around for Gmail meddling in triggered links. */
		.x-gmail-data-detectors,
		.x-gmail-data-detectors *,
		.aBn {
			border-bottom: 0 !important;
			cursor: default !important;
		}

		/* What it does: Prevents Gmail from displaying an download button on large, non-linked images. */
		.a6S {
			display: none !important;
			opacity: 0.01 !important;
		}
		/* If the above doesn't work, add a .g-img class to any image in question. */
		img.g-img + div {
			display:none !important;
		   }

		/* What it does: Prevents underlining the button text in Windows 10 */
		.button-link {
			text-decoration: none !important;
		}

		/* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
		/* Create one of these media queries for each additional viewport size you'd like to fix */
		/* Thanks to Eric Lepetit @ericlepetitsf) for help troubleshooting */
		@media only screen and (min-device-width: 375px) and (max-device-width: 413px) { /* iPhone 6 and 6+ */
			.email-container {
				min-width: 375px !important;
			}
		}

	</style>

	<!-- Progressive Enhancements -->
	<style>

		/* What it does: Hover styles for buttons */
		.button-td,
		.button-a {
			transition: all 100ms ease-in;
		}
		.button-td:hover,
		.button-a:hover {
			background: #555555 !important;
			border-color: #555555 !important;
		}

		/* Media Queries */
		@media screen and (max-width: 480px) {

			/* What it does: Forces elements to resize to the full width of their container. Useful for resizing images beyond their max-width. */
			.fluid {
				width: 100% !important;
				max-width: 100% !important;
				height: auto !important;
				margin-left: auto !important;
				margin-right: auto !important;
			}

			/* What it does: Forces table cells into full-width rows. */
			.stack-column,
			.stack-column-center {
				display: block !important;
				width: 100% !important;
				max-width: 100% !important;
				direction: ltr !important;
			}
			/* And center justify these ones. */
			.stack-column-center {
				text-align: center !important;
			}

			/* What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. */
			.center-on-narrow {
				text-align: center !important;
				display: block !important;
				margin-left: auto !important;
				margin-right: auto !important;
				float: none !important;
			}
			table.center-on-narrow {
				display: inline-block !important;
			}

			/* What it does: Adjust typography on small screens to improve readability */
			.email-container p {
				font-size: 17px !important;
				line-height: 22px !important;
			}
		}

	</style>

	<!-- What it does: Makes background images in 72ppi Outlook render at correct size. --> 
	<!--[if gte mso 9]>
	<xml>
	  <o:OfficeDocumentSettings>
		<o:AllowPNG/>
		<o:PixelsPerInch>96</o:PixelsPerInch>
	 </o:OfficeDocumentSettings>
	</xml>
	<![endif]-->

</head>
<body width="100%" bgcolor="#DBDFE2" style="margin: 0; mso-line-height-rule: exactly;">
	<center style="width: 100%; background: #DBDFE2; text-align: left;">

		<!-- Ignore for Outlook to duplicate the content -->
		<!--[if !mso ]><!-->
		<div style="display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;font-family: sans-serif;">
			<?php echo $trimContents; ?>
		</div>
		<!--<![endif]-->

		<div style="max-width: 680px; margin: auto;" class="email-container">
			
			<!--[if mso]>
			<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" width="680" align="center">
			<tr>
			<td>
			<![endif]-->

			<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;">
				<tr>
					<td style="padding: 10px 0; text-align: left">
						<img src="<?php echo $logo;?>" aria-hidden="true" border="0" style="width: 133px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
					</td>
				</tr>
			</table>

			<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;" class="email-container">
				<?php echo $contents; ?>
			</table>
			
			<table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;">
				<tr>
					<td style="padding: 24px 10px 40px;width: 100%;font-size: 12px; font-family: sans-serif; line-height:18px; text-align: left; color: #888888;" class="x-gmail-data-detectors">
						<br />
						<?php if ($manageAlerts) { ?>
						<div style="font-size:12px; color:#999; line-height:18px;">
							<?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_FOOTER_BECAUSE' ); ?><br />
							<a href="<?php echo FRoute::profile( array( 'layout' => 'editNotifications' , 'external' => true ) );?>" style="color:#00aeef; text-decoration:none;"><?php echo JText::_( 'COM_EASYSOCIAL_EMAILS_MANAGE_ALERTS' );?></a>
						</div>
						<?php } else { ?><br /><br /><?php } ?>
					</td>
				</tr>
			</table>

			<!--[if mso]>
			</td>
			</tr>
			</table>
			<![endif]-->
		</div>
	</center>
</body>
</html>
