<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>400</width>
	<height>120</height>
	<selectors type="json">
	{
		"{refreshButton}": "[data-refresh-button]",
		"{cancelButton}": "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{refreshButton} click": function() {
			window.location.reload();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_INACTIVITY_DIALOG_TITLE'); ?></title>
	<content>
		<p><?php echo JText::sprintf('COM_ES_INACTIVITY_NOTICE', $minute); ?></p>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default-o btn-sm"><?php echo JText::_('COM_ES_INACTIVITY_STAY_ON_PAGE_BUTTON'); ?></button>
		<button data-refresh-button type="button" class="btn btn-es-primary-o btn-sm"><?php echo JText::_('COM_ES_INACTIVITY_REFRESH_BUTTON'); ?></button>
	</buttons>
</dialog>
