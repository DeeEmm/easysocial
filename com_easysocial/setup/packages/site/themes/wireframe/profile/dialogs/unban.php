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
		"{closeButton}"  : "[data-close-button]",
		"{unbanButton}" : "[data-unban-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_DIALOG_UNBAN_USER_TITLE'); ?></title>
	<content>
		<p>
			<?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_UNBAN_CONFIRMATION'); ?> <br /><br />
		</p>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-sm btn-es-default"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-unban-button type="button" class="btn btn-sm btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_UNBAN_BUTTON'); ?>
			<div data-unban-button-loader class="o-loader o-loader--sm o-loader--inline"></div>
		</button>
	</buttons>
</dialog>
