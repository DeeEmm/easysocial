<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container" data-es-container data-edit-privacy>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/profile/manageblockedusers/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<?php echo $this->render('module', 'es-profile-manageblockedusers-before-contents'); ?>

		<form method="post" action="<?php echo JRoute::_('index.php');?>" class="es-forms">

			<div class="tab-content">
				<div class="tab-content__item is-active" data-contents data-type="blocked">
					<div class="es-forms__group <?php echo !$blockedUsers ? 'is-empty' : '';?>">
						<div class="es-forms__title">
							<?php echo $this->html('form.title', 'COM_EASYSOCIAL_MANAGE_BLOCKED_USERS'); ?>
						</div>

						<div class="es-forms__content">
							<p class="privacy-contents__title">
								<?php echo JText::_('COM_EASYSOCIAL_MANAGE_BLOCKED_USERS_DESC');?>
							</p>

							<?php if ($blockedUsers) { ?>
								<?php foreach ($blockedUsers as $block) { ?>

								<div class="es-list-item es-island" data-id="<?php echo $block->user->id;?>">

									<div class="es-list-item__media">
										<?php echo $this->html('avatar.user', $block->user); ?>
									</div>

									<div class="es-list-item__context">
										<div class="es-list-item__hd">
											<div class="es-list-item__content">

												<div class="es-list-item__title">
													<a href="<?php echo $block->user->getPermalink();?>" class="es-user-name"><?php echo $block->user->getName();?></a>
												</div>

												<div class="es-list-item__meta">
													<?php if (!$block->reason) { ?>
														<?php echo JText::_('COM_EASYSOCIAL_BLOCKED_USER_NO_REASONS_PROVIDED'); ?>
													<?php } else { ?>
														<?php echo $block->reason;?>
													<?php } ?>
												</div>
											</div>


											<div class="es-list-item__action">
												<?php echo ES::blocks()->form($block->user->id, true); ?>
											</div>
										</div>


									</div>

								</div>

								<?php } ?>
							<?php } ?>

							<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_PRIVACY_BLOCKED_NO_USERS_CURRENTLY', 'fa-users'); ?>
						</div>
					</div>
				</div>
			</div>

			<?php echo $this->html('form.action', 'profile', 'savePrivacy'); ?>
			<input type="hidden" name="activeTab" value="<?php echo $activeTab;?>" data-privacy-active />
		</form>

		<?php echo $this->render('module', 'es-profile-manageblockedusers-after-contents'); ?>
	</div>
</div>

