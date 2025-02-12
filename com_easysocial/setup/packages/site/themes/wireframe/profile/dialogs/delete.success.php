<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{listingButton}"  : "[data-listing-button]",
		"{dashboardButton}" : "[data-dashboard-button]",
		"{closeButton}": ".dialog-closeButton"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{listingButton} click": function() {
			window.location.href = "<?php echo $userListingLink; ?>";
		},
		"{dashboardButton} click": function() {
			window.location.href = "<?php echo $dashboardLink; ?>";
		},
		"{closeButton} click": function() {
			window.location.href = "<?php echo $userListingLink; ?>";
		},
	}
	</bindings>
	<title></title>
	<content>
		<p>
			<?php echo $msgObj->message; ?>
		</p>
	</content>
	<buttons>
		<button data-listing-button type="button" class="btn btn-sm btn-es-default"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_TO_USERLISTING'); ?></button>
		<button data-dashboard-button type="button" class="btn btn-sm btn-es-default"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_TO_DASHBOARD'); ?></button>
	</buttons>
</dialog>
