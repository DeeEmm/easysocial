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
	<width>450</width>
	<selectors type="json">
	{
		"{form}": "[data-addParticipant-form]",
		"{confirmButton}": "[data-add-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{suggest}": "[data-friends-suggest]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function() {

			this.suggest()
				.addController(
					"EasySocial.Controller.Friends.Suggest", {
						exclusion: <?php echo json_encode($ids); ?>,
						showNonFriend : <?php echo ES::config()->get('conversations.nonfriend') ? '1' : '0'; ?>,
						emptyMessage: '<?php echo JText::_('COM_ES_CONVERSATION_NO_USERS_FOUND_MESSAGE'); ?>',
						privacyRule: 'profiles.post.message'
					}
				);
		},

		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{confirmButton} click": function(el) {
			$(el).attr('disabled', 'disabled');
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_( 'COM_EASYSOCIAL_CONVERSATIONS_ADD_PARTICIPANTS_DIALOG_TITLE' ); ?></title>
	<content>
		<form name="addparticipant-conversation-form" method="post" data-addParticipant-form>
			<p class="t-fs--sm">
				<?php echo JText::_( 'COM_EASYSOCIAL_CONVERSATIONS_ADD_PARTICIPANTS_DESC' ); ?>
			</p>

			<div class="textboxlist controls disabled" data-friends-suggest>
				<input type="text" disabled autocomplete="off" class="participants textboxlist-textField" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_CONVERSATIONS_START_TYPING' );?>" data-textboxlist-textField data-textboxlist-textField />
			</div>

			<div class="t-lg-mt--md t-fs--sm">
				<?php echo JText::_( 'COM_EASYSOCIAL_CONVERSATIONS_ADD_PARTICIPANTS_FOOTER' ); ?>
			</div>
			<input type="hidden" name="id" value="<?php echo $id;?>" />
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="conversations" />
			<input type="hidden" name="task" value="addParticipant" />
			<input type="hidden" name="<?php echo ES::token();?>" value="1" />
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-add-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_INVITE_USERS_BUTTON'); ?></button>
	</buttons>
</dialog>