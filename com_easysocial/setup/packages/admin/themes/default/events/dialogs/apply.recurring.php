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
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>500</width>
	<height>200</height>
	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{applyAllButton}": "[data-apply-all-button]",
		"{applyThisButton}": "[data-apply-this-button]"
	}
	</selectors>
	<title><?php echo JText::_('COM_EASYSOCIAL_EVENTS_EDIT_RECURRING_EVENT_DIALOG_TITLE'); ?></title>
	<content>
		<p><?php echo JText::_('COM_EASYSOCIAL_EVENTS_EDIT_RECURRING_EVENT_DIALOG_CONTENT'); ?></p>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-apply-all-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_EDIT_RECURRING_EVENT_APPLY_ALL_BUTTON'); ?></button>
		<button data-apply-this-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_EDIT_RECURRING_EVENT_APPLY_THIS_BUTTON'); ?></button>
	</buttons>
</dialog>
