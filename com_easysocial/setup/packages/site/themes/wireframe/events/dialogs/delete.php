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
<dialog>
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}": "[data-close-button]",
		"{submitButton}": "[data-submit-button]",
		"{form}": "[data-form-delete=event]"
	}
	</selectors>
	<bindings type="javascript">
	{
		clicked: false,
		"{closeButton} click": function() {
			this.parent.close();
		},

		"{submitButton} click": function() {

			if (!this.clicked) {
				this.clicked = true;

				this.form().submit();
			}
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_EVENTS_DIALOG_DELETE_EVENT_TITLE'); ?></title>
	<content>
		<p><?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_DIALOG_DELETE_EVENT_CONTENT', $event->getName());?></p>

		<form data-form-delete="event" method="post" action="<?php echo JRoute::_('index.php'); ?>">
			<?php echo $this->html('form.action', 'events', 'delete'); ?>
			<?php echo $this->html('form.hidden', 'id', $event->id); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_YES_DELETE_THIS_EVENT_BUTTON'); ?></button>
	</buttons>
</dialog>

