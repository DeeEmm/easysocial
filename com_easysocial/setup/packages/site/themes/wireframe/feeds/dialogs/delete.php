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
		"{deleteButton}": "[data-delete-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{id}": "[data-id]",
		"{uid}": "[data-uid]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{deleteButton} click": function() {

			var id = this.id().val();
			var uid = this.uid().val();
			var caller = this.caller;
			var item = caller.item('[data-id=' + id + ']');

			EasySocial.ajax('site/controllers/feeds/delete', {
				"id": id,
				"uid": uid
			}).done(function(){

				// Remove the dom
				item.remove();

				// Determine if there's no more item to be displayed
				if (caller.sources().children().length == 0) {
					caller.browser().addClass('is-empty');
				}

				EasySocial.dialog().close();
			});
		}
	}
	</bindings>
	<title><?php echo JText::_('APPS_FEEDS_DIALOG_DELETE_CONFIRMATION'); ?></title>
	<content>
		<p><?php echo JText::_('APPS_FEEDS_DIALOG_DELETE_CONFIRMATION_INFO'); ?></p>

		<input type="hidden" data-uid value="<?php echo $feed->uid;?>" />
		<input type="hidden" data-id value="<?php echo $feed->id;?>" />
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-delete-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_DELETE_BUTTON'); ?></button>
	</buttons>
</dialog>
