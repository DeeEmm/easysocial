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
	<width>450</width>
	<height>250</height>
	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{deleteParticipantButton}": "[data-participant-delete-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{deleteParticipantButton} click": function(button) {
			var item = $(button);
			var conversationId = item.data('conversation-id');
			var participantId = item.data('participant-id');

			var parent = $(button).closest('[data-participant-item]');
			var notice = $('[data-participant-notice]');

			if (!notice.hasClass('t-hidden')) {
				// remove the notice message.
				notice.addClass('t-hidden');
			}

			EasySocial.ajax('site/controllers/conversations/deleteParticipant', {
				'conversationId': conversationId,
				'participantId': participantId
			}).done(function(msg) {
				parent.remove();

				notice.removeClass('t-hidden');
				notice.text(msg);

			}).fail(function(msg) {

				notice.removeClass('t-hidden');
				notice.text(msg);
			});
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_PARTICIPANTS'); ?></title>
	<content>
		<div class="es-reaction-stats-list">
			<div role="alert" class="o-alert o-alert--success o-alert--icon o-alert--dismissible t-hidden" data-participant-notice></div>

			<?php foreach ($participants as $participant) { ?>
			<div class="es-reaction-stats-list__item" data-participant-item>
				<div class="o-media">
					<div class="o-media__image">
						<?php echo $this->html('avatar.user', $participant, 'sm', false); ?>
					</div>
					<div class="o-media__body">
						<?php echo $this->html('html.user', $participant); ?>
					</div>

					<?php if ($showDeleteParticipantButton) { ?>
						<?php echo $this->html('user.deleteParticipant', $participant, 'sm', $conversation); ?>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm">
			<?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?>
		</button>
	</buttons>
</dialog>

