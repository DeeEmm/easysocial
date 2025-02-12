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
defined('_JEXEC') or die ('Unauthorized Access');
?>
<div class="es-container">
	<div class="es-content">
		<form name="conversation-compose" enctype="multipart/form-data" method="post" class="es-forms" data-conversations-composer data-composer-form>
			<div class="es-forms__group">
				<div class="es-forms__title">
					<?php echo $this->html('form.title', 'COM_EASYSOCIAL_CONVERSATIONS_COMPOSE_HEADING', 'h1'); ?>
				</div>

				<div class="es-forms__content">
					<div class="o-form-horizontal">
						<div class="o-form-group" data-composer-recipients>
							<label class="o-control-label"><?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_RECIPIENTS'); ?>:</label>
							<div class="o-control-input">
								<div class="textboxlist controls disabled" data-friends-suggest>
									<input type="text" autocomplete="off" disabled class="participants textboxlist-textField" data-textboxlist-textField placeholder="<?php echo $this->config->get('friends.enabled') ? JText::_('COM_EASYSOCIAL_CONVERSATIONS_START_TYPING') : JText::_('COM_EASYSOCIAL_CONVERSATIONS_START_TYPING_NON_FRIEND');?>" data-textboxlist-textField />
								</div>
							</div>
						</div>

						<?php if ($this->my->isSiteAdmin()) { ?>
						<div class="o-form-group" data-composer-mass-conversation>
							<label class="o-control-label"></label>
							<div class="o-control-input">
								<div class="o-checkbox">
									<input id="sendToAll" name="sendToAll" type="checkbox" data-mass-conversation-checkbox />
									<label for="sendToAll">
										<?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_SEND_TO_ALL_USERS'); ?>
									</label>
								</div>
							</div>
						</div>
						<?php } ?>

						<div class="o-form-group" data-composer-message>
							<label class="o-control-label"><?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_MESSAGE'); ?>:</label>

							<div class="o-control-input">
								<div class="es-single-composer-textarea" data-composer-editor-header>
									<div class="mentions-textfield" data-composer-editor-area>
										<div class="mentions">
											<div data-mentions-overlay data-default="<?php echo $this->html('string.escape', $message); ?>"><?php echo $message; ?></div>
											<textarea class="o-form-control" name="message" autocomplete="off"
												data-mentions-textarea
												data-default="<?php echo $this->html( 'string.escape' , $message );?>"
												data-initial="0"
												data-composer-editor
												placeholder="<?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_MESSAGE_PLACEHOLDER');?>"><?php echo $message; ?></textarea>
											<?php if ($this->config->get('honeypot.conversations')) { ?>
												<?php echo $this->html('form.honeypot'); ?>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
						</div>

						<?php if ($this->config->get('conversations.attachments.enabled') || $this->config->get('conversations.location')) { ?>
						<div class="o-form-group">
							<label class="o-control-label"></label>
							<div class="o-control-input">
								<div class="es-composer-attach">
									<?php if ($this->config->get('conversations.attachments.enabled')) { ?>
									<div class="attachment-service" data-composer-attachment>
										<?php echo $this->loadTemplate('site/uploader/form', array( 'size' => $this->config->get( 'conversations.attachments.maxsize' ) ) ); ?>
									</div>
									<?php } ?>

								</div>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<div class="t-pull-left">
						<a href="<?php echo ESR::conversations();?>" class="btn btn-es-default-o"><?php echo JText::_('COM_ES_CANCEL'); ?></a>
					</div>
					<div class="t-pull-right">
						<button type="button" class="btn btn-es-primary-o" data-composer-submit><?php echo JText::_('COM_EASYSOCIAL_SEND_MESSAGE_BUTTON');?></button>
					</div>
				</div>
			</div>
			<?php echo $this->html('form.action', 'conversations', 'store'); ?>
		</form>
	</div>
</div>
