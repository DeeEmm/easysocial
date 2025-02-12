<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}": "[data-close-button]",
		"{suggest}": "[data-friends-suggest]",
		"{sendInvite}": "[data-invite-button]",
		"{form}": "[data-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function() {
			this.suggest()
				.addController(
					"EasySocial.Controller.Friends.Suggest", {
						exclusion: <?php echo ES::json()->encode($exclusion); ?>,
						type: "inviteevent",
						"emptyMessage": "<?php echo JText::_('COM_ES_NO_FRIENDS_FOUND_OR_INVITED_TO_CLUSER', true);?>"
					}
			   );
		},
		
		"{sendInvite} click": function() {
			this.form().submit();
		},

		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_INVITE_FRIENDS'); ?></title>
	<content>
		<form data-form method="post" action="<?php echo JRoute::_('index.php');?>">
			<p>
				<?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_DIALOG_INVITE_TO_EVENT_CONTENT', $event->getName());?>
			</p>

			<div class="textboxlist controls disabled" data-friends-suggest>
				<input type="text" disabled autocomplete="off" class="participants textboxlist-textField" placeholder="<?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_START_TYPING');?>" data-textboxlist-textField data-textboxlist-textField />
			</div>

			<?php echo $this->html('form.token'); ?>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="events" />
			<input type="hidden" name="task" value="invite" />
			<input type="hidden" name="id" value="<?php echo $event->id;?>" />

			<?php if ($returnUrl) { ?>
			<input type="hidden" name="return" value="<?php echo $returnUrl; ?>" />
			<?php } ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-sm btn-es"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-invite-button type="button" class="btn btn-sm btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_SEND_INVITATIONS_BUTTON'); ?></button>
	</buttons>
</dialog>
