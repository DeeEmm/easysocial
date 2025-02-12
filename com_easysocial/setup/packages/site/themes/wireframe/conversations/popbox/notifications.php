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
<div class="popbox-dropdown">
	<div class="popbox-dropdown__hd">
		<div class="t-d--flex t-align-items--c">
			<div class="t-min-width--0 t-text--truncate t-pr--sm">
				<div class="popbox-dropdown__title t-text--truncate"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS'); ?></div>
			</div>
			<div class="t-ml--auto t-flex-shrink--0">
				<div class="">
					<?php if (ES::conversation()->canCreate()) { ?>
						<a href="<?php echo ESR::conversations(array('layout' => 'compose'));?>" class="popbox-dropdown__note">
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS_COMPOSE');?>
						</a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>


	<div class="popbox-dropdown__bd">
		<?php if ($conversations) { ?>
			<div class="popbox-dropdown-nav">
			<?php foreach ($conversations as $conversation) { ?>
				<div class="popbox-dropdown-nav__item <?php echo $conversation->isNew() ? 'is-unread' : ''; ?>">
					<?php if (ES::conversekit()->exists($view)) { ?>
					<a href="javascript:void(0);" class="popbox-dropdown-nav__link" data-ck-chat data-conversation-id="<?php echo $conversation->id;?>">
					<?php } else { ?>
					<a href="<?php echo $conversation->getPermalink();?>" class="popbox-dropdown-nav__link">
					<?php } ?>
						<div class="o-flag">
							<div class="o-flag__image o-flag--top">
								<?php echo $conversation->getAvatar();?>
							</div>

							<div class="o-flag__body">
								<div class="popbox-dropdown-nav__post">
									<?php if ($conversation->getLastMessage()) { ?>

										<div class="object-title">
											<b><?php echo $conversation->getTitle(); ?></b>
										</div>

										<div class="object-content t-fs--sm">
											<?php echo $this->loadTemplate('site/conversations/popbox/' . $conversation->getLastMessageType(), array('conversation' => $conversation)); ?>
										</div>

										<div class="object-timestamp t-text--muted t-fs--sm">
											<i class="far fa-clock"></i>&nbsp; <?php echo $conversation->getLastMessage()->getRepliedDate(true);?>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</a>
				</div>
			<?php } ?>
			</div>
		<?php } else { ?>
		<div class="t-text--muted is-empty">
			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS_NO_CONVERSATIONS_YET', 'fa-envelope', false, false); ?>
		</div>
		<?php } ?>
	</div>
	<div class="popbox-dropdown__ft">
		<a href="<?php echo ESR::conversations();?>" class="popbox-dropdown__note">
			<?php echo JText::_('COM_ES_VIEW_ALL'); ?>
		</a>
	</div>
</div>

