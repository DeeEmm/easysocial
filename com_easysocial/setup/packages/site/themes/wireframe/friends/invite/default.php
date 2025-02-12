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
<div class="es-container">
	<div class="es-content">
		<form id="es-friend-invite-form" method="post" action="<?php echo JRoute::_('index.php');?>" class="es-forms">
			<div class="es-forms__group es-invite-url">
				<?php echo $this->html('form.title', 'COM_ES_INVITE_FRIENDS_VIA_URL', 'h2'); ?>

				<div class="es-forms__content">
					<p><?php echo JText::_('COM_ES_INVITE_FRIENDS_VIA_URL_INFO'); ?></p>

					<div class="o-row show-grid">
						<div class="o-col--9">
							<div class="o-input-group t-lg-mb--md">
								<input type="text" name="facebook-oauth-uri" class="o-form-control" value="<?php echo $inviteUrl;?>" size="60" style="pointer-events:none;" data-clipboard-input />
								<span class="o-input-group__btn">
									<a href="javascript:void(0);" class="btn btn-es-default-o" data-es-copy>
										<?php echo JText::_('COM_ES_COPY_TO_CLIPBOARD');?>
									</a>
								</span>
							</div>
						</div>
						<div class="o-col--3"></div>
					</div>
				</div>
			</div>

			<div class="t-mt--xl es-invite-mail">
				<div class="es-forms__group">
					<?php echo $this->html('form.title', 'COM_ES_INVITE_FRIENDS_VIA_EMAIL', 'h2'); ?>

					<div class="es-forms__content">
						<p><?php echo JText::_('COM_EASYSOCIAL_HEADING_INVITE_FRIENDS_DESC'); ?></p>

						<div class="o-form-horizontal">
							<div class="o-form-group">
								<label class="o-control-label"><?php echo JText::_('COM_EASYSOCIAL_FRIENDS_INVITE_EMAIL_ADDRESSES'); ?>:</label>
								<div class="o-control-input">
									<textarea class="o-form-control" name="emails" name="emails" placeholder="john@email.com"></textarea>
									<div class="o-help-block">
										<strong><?php echo JText::_('COM_EASYSOCIAL_NOTE');?>:</strong> <?php echo JText::_('COM_EASYSOCIAL_FRIENDS_INVITE_EMAIL_ADDRESSES_NOTE');?>
									</div>
								</div>
							</div>

							<div class="o-form-group">
								<label class="o-control-label"><?php echo JText::_('COM_EASYSOCIAL_FRIENDS_INVITE_MESSAGE'); ?>: </label>

								<div class="o-control-input">
									<?php echo $editor->display('message', JText::sprintf('COM_EASYSOCIAL_FRIENDS_INVITE_MESSAGE_CONTENT', ES::jconfig()->sitename), '100%', '200', '10', '5', array('image', 'pagebreak', 'ninjazemanta', 'article', 'readmore', 'module'), null, 'com_easysocial'); ?>

									<div class="o-help-block">
										<strong><?php echo JText::_('COM_EASYSOCIAL_NOTE');?>:</strong> <?php echo JText::_('COM_EASYSOCIAL_FRIENDS_INVITE_MESSAGE_NOTE');?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="es-forms__actions">
					<div class="o-form-actions">
						<a href="<?php echo ESR::friends();?>" class="btn btn-es-default-o t-lg-pull-left"><?php echo JText::_('COM_ES_CANCEL'); ?></a>
						<button class="btn btn-es-primary-o t-lg-pull-right"><?php echo JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON');?></button>
					</div>
				</div>
			</div>

			<?php echo $this->html('form.action', 'friends', 'sendInvites'); ?>
		</form>
	</div>
</div>
